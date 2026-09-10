<?php
declare(strict_types=1);

namespace ShadowShinobi\Enhancement;

use RuntimeException;
use ShadowShinobi\Core\Database;
use ShadowShinobi\Legendary\LegendaryWeaponService;
use ShadowShinobi\WorldMemory\EventRecorder;

/**
 * Handles the core Shadow Shinobi risk/reward enhancement ritual.
 *
 * Default rule: the chosen item plus at least two additional gear pieces
 * are consumed as sacrifice material. A failed attempt destroys the chosen
 * item as well. Every consumed item contributes fragments back to the world.
 */
final class EnhancementService
{
    public static function enhance(int $playerId, int $itemId, array $sacrificeIds): array
    {
        $pdo = Database::connection();
        $config = require '/var/www/config/app.php';
        $minimumSacrificePieces = (int)($config['enhancement']['minimum_sacrifice_pieces'] ?? 3);

        $allIds = array_values(array_unique(array_map('intval', array_merge([$itemId], $sacrificeIds))));
        if (count($allIds) < $minimumSacrificePieces) {
            throw new RuntimeException("Enhancement requires at least {$minimumSacrificePieces} total gear pieces, including the selected item.");
        }
        if (in_array($itemId, $sacrificeIds, true)) {
            throw new RuntimeException('The selected item cannot also be entered as a separate sacrifice.');
        }

        $pdo->beginTransaction();
        try {
            $placeholders = implode(',', array_fill(0, count($allIds), '?'));
            $params = array_merge([$playerId], $allIds);
            $stmt = $pdo->prepare("SELECT * FROM equipment_items WHERE player_id=? AND id IN ({$placeholders}) AND destroyed=0 FOR UPDATE");
            $stmt->execute($params);
            $items = $stmt->fetchAll();
            if (count($items) !== count($allIds)) {
                throw new RuntimeException('One or more selected gear pieces are unavailable.');
            }

            $selected = null;
            foreach ($items as $row) {
                if ((int)$row['id'] === $itemId) {
                    $selected = $row;
                    break;
                }
            }
            if (!$selected) throw new RuntimeException('Selected gear was not found.');

            $nextLevel = (int)$selected['enhancement_level'] + 1;
            $band = $nextLevel >= 16 ? 16 : ($nextLevel >= 11 ? 15 : ($nextLevel >= 6 ? 10 : 5));
            $odds = $config['enhancement'][$band];
            $roll = random_int(1, 1_000_000) / 1_000_000;
            $success = $roll <= (float)$odds['success'];
            $destroyOnFailure = (float)$odds['destroy'] > 0
                && random_int(1, 1_000_000) / 1_000_000 < (float)$odds['destroy'];

            $totalSetValue = 0;
            foreach ($items as $item) $totalSetValue += (int)$item['set_value'];

            // The selected item and all sacrifices are consumed by the ritual.
            $pdo->prepare("UPDATE equipment_items SET destroyed=1 WHERE id IN ({$placeholders})")->execute($allIds);

            $fragmentCount = 0;
            foreach ($items as $item) $fragmentCount += max(1, (int)$item['set_value']);
            $pdo->prepare('INSERT INTO gear_fragments (player_id, source_item_id, quantity, reason) VALUES (?,?,?,?)')
                ->execute([$playerId, $itemId, $fragmentCount, $success ? 'enhancement_sacrifice' : 'enhancement_failure']);
            $pdo->prepare('UPDATE players SET gear_fragments = gear_fragments + ? WHERE id=?')->execute([$fragmentCount, $playerId]);

            if ($success) {
                $enhancedSubstats = json_decode((string)$selected['substats_json'], true) ?: [];
                $awakened = (int)$selected['awakened'];
                $awakenedNow = false;
                if ($nextLevel >= 16 && random_int(1, 100) <= 8) {
                    $awakened = 1;
                    $awakenedNow = true;
                }

                $pdo->prepare('INSERT INTO equipment_items (player_id, operative_id, item_name, slot_name, rarity, enhancement_level, awakened, is_core_weapon, weapon_family, talent_tree_key, main_stat_name, main_stat_value, substats_json, visual_json, set_value, destroyed, origin_item_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                    ->execute([
                        $playerId, $selected['operative_id'], $selected['item_name'], $selected['slot_name'], $selected['rarity'],
                        $nextLevel, $awakened, $selected['is_core_weapon'], $selected['weapon_family'], $selected['talent_tree_key'],
                        $selected['main_stat_name'], (float)$selected['main_stat_value'], json_encode($enhancedSubstats, JSON_THROW_ON_ERROR),
                        $selected['visual_json'], (int)$selected['set_value'] * 2, 0, $itemId
                    ]);
                $newItemId = (int)$pdo->lastInsertId();
                $title = $awakenedNow ? 'A Weapon Awoke' : 'The Forge Answered';
                $summary = sprintf('%s consumed %d gear pieces and emerged at +%d.', $selected['item_name'], count($items), $nextLevel);
                $eventId = EventRecorder::recordInTransaction($pdo, $playerId, 'gear_enhanced', $title, $summary, [
                    'item_id' => $newItemId,
                    'origin_item_id' => $itemId,
                    'sacrifice_ids' => $allIds,
                    'level' => $nextLevel,
                    'set_value' => $totalSetValue,
                    'awakened' => $awakenedNow,
                    'fragments' => $fragmentCount,
                ]);
                if ($awakenedNow) {
                    EventRecorder::recordInTransaction($pdo, $playerId, 'gear_awakened', 'Awakening Recorded', $summary . ' Its hidden potential has surfaced.', [
                        'item_id' => $newItemId,
                    ]);
                }
                $pdo->commit();
                return ['success' => true, 'destroyed' => false, 'level' => $nextLevel, 'fragments' => $fragmentCount, 'awakened' => $awakenedNow, 'event_id' => $eventId];
            }

            $legendaryShatter = null;
            if ((string)$selected['rarity'] === 'Legendary' && strcasecmp((string)($selected['weapon_family'] ?? ''), 'Kageboshi') === 0) {
                $legendaryShatter = LegendaryWeaponService::shatterInTransaction($pdo, $playerId, $selected, 'enhancement_failure');
            }

            $lostRelicId = null;
            $createsWorldRelic = $legendaryShatter === null && ($destroyOnFailure || $nextLevel >= 11 || in_array((string)$selected['rarity'], ['Legendary','Mythic'], true));
            if ($createsWorldRelic) {
                $relicPower = max(25, (int)$selected['main_stat_value'] * 35 / max(1, $nextLevel + 1));
                $name = 'Echo of ' . preg_replace('/\s+\+\d+$/', '', (string)$selected['item_name']);
                $payload = json_encode([
                    'origin_item_id' => $itemId,
                    'origin_name' => $selected['item_name'],
                    'failed_level' => $nextLevel,
                    'rarity' => $selected['rarity'],
                    'main_stat_name' => $selected['main_stat_name'],
                    'power' => (int)$relicPower,
                    'fragments' => $fragmentCount,
                ], JSON_THROW_ON_ERROR);
                $pdo->prepare('INSERT INTO lost_relics (player_id, name, source_item_id, rarity, power_rating, payload_json, discovered, created_at) VALUES (?,?,?,?,?,?,0,NOW())')
                    ->execute([$playerId, $name, $itemId, $selected['rarity'], (int)$relicPower, $payload]);
                $lostRelicId = (int)$pdo->lastInsertId();
            }

            $summary = $legendaryShatter !== null
                ? $legendaryShatter['summary']
                : sprintf('%s shattered during an attempt at +%d. The fragments remain, and a relic echo has entered the world.', $selected['item_name'], $nextLevel);
            $eventId = EventRecorder::recordInTransaction($pdo, $playerId, 'gear_destroyed', $legendaryShatter !== null ? 'A Legendary Weapon Has Shattered' : 'A Relic Was Born From Failure', $summary, [
                'item_id' => $itemId,
                'sacrifice_ids' => $allIds,
                'attempted_level' => $nextLevel,
                'fragments' => $fragmentCount,
                'lost_relic_id' => $lostRelicId,
                'legendary_shatter' => $legendaryShatter,
                'global_event' => $legendaryShatter !== null || $lostRelicId !== null,
            ]);
            if ($lostRelicId !== null) {
                EventRecorder::recordInTransaction($pdo, $playerId, 'world_relic_created', 'The World Remembers', 'A lost relic created from a failed enhancement is now eligible to surface during exploration and world events.', [
                    'lost_relic_id' => $lostRelicId,
                    'origin_event_id' => $eventId,
                ]);
            }
            if ($legendaryShatter !== null || $lostRelicId !== null) {
                EventRecorder::recordInTransaction($pdo, $playerId, 'global_event', $legendaryShatter !== null ? 'WORLD EVENT: Kageboshi Has Shattered' : 'WORLD EVENT: A Relic Has Been Lost', $summary, [
                    'origin_event_id' => $eventId,
                    'lost_relic_id' => $lostRelicId,
                    'legendary_shatter' => $legendaryShatter,
                    'visibility' => 'global',
                ]);
            }
            $pdo->commit();
            return [
                'success' => false,
                'destroyed' => true,
                'level' => $nextLevel,
                'fragments' => $fragmentCount,
                'lost_relic_id' => $lostRelicId,
                'legendary_shatter' => $legendaryShatter,
                'event_id' => $eventId,
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
