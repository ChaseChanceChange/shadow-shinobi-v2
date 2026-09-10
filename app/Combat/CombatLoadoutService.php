<?php
declare(strict_types=1);

namespace ShadowShinobi\Combat;

use PDO;

final class CombatLoadoutService
{
    public static function build(PDO $pdo, array $operative): array
    {
        $stmt = $pdo->prepare('SELECT * FROM equipment_items WHERE operative_id=? AND destroyed=0 ORDER BY is_core_weapon DESC, id');
        $stmt->execute([(int)$operative['id']]);
        $items = $stmt->fetchAll();

        $stats = [
            'attack' => (int)$operative['attack'],
            'defense' => (int)$operative['defense'],
            'health' => (int)$operative['health'],
            'speed' => (int)$operative['speed'],
            'skill_power' => (int)$operative['skill_power'],
            'critical_rate' => (float)$operative['critical_rate'],
            'critical_damage' => (float)$operative['critical_damage'],
            'essence_capacity' => (int)$operative['essence_capacity'],
        ];

        $weapon = null;
        $talent = null;
        $gearPower = 0;
        $gearNames = [];

        foreach ($items as $item) {
            $rarity = (string)$item['rarity'];
            $enhancement = max(0, (int)$item['enhancement_level']);
            $rarityMultiplier = match (strtolower($rarity)) {
                'mythic' => 1.40,
                'legendary' => 1.28,
                'epic' => 1.18,
                'rare' => 1.10,
                'uncommon' => 1.05,
                default => 1.00,
            };
            $enhancementMultiplier = 1 + ($enhancement * 0.035);
            $mainValue = (float)$item['main_stat_value'] * $rarityMultiplier * $enhancementMultiplier;
            self::applyStat($stats, (string)$item['main_stat_name'], $mainValue);
            $gearPower += (int)round($mainValue);
            $gearNames[] = (string)$item['item_name'];

            $substats = json_decode((string)$item['substats_json'], true);
            if (is_array($substats)) {
                foreach ($substats as $sub) {
                    if (!is_array($sub) || !isset($sub['name'], $sub['value'])) continue;
                    self::applyStat($stats, (string)$sub['name'], (float)$sub['value'] * $rarityMultiplier);
                }
            }

            if ((int)$item['is_core_weapon'] === 1 && $weapon === null) {
                $weapon = [
                    'name' => (string)$item['item_name'],
                    'rarity' => $rarity,
                    'enhancement' => $enhancement,
                    'family' => (string)($item['weapon_family'] ?? ''),
                    'talent_tree' => (string)($item['talent_tree_key'] ?? ''),
                ];
                $talent = self::resolveTalent($pdo, $item, $enhancement);
                if ($talent !== null) {
                    self::applyTalent($stats, $talent['stat_mod']);
                    $gearPower += (int)($talent['power'] ?? 0);
                }
            }
        }

        $powerIndex = (int)round(
            $stats['attack'] + $stats['defense'] + $stats['health'] / 8 +
            $stats['speed'] * 2 + $stats['skill_power'] * 1.5 +
            $stats['critical_rate'] * 3 + $gearPower
        );

        return [
            ...$operative,
            ...$stats,
            'gear_power' => $gearPower,
            'power_index' => $powerIndex,
            'gear_names' => $gearNames,
            'weapon_name' => $weapon['name'] ?? 'Unarmed',
            'weapon_rarity' => $weapon['rarity'] ?? 'Common',
            'weapon_enhancement' => $weapon['enhancement'] ?? 0,
            'talent_name' => $talent['node_name'] ?? 'No awakened weapon talent',
        ];
    }

    private static function resolveTalent(PDO $pdo, array $item, int $enhancement): ?array
    {
        $tree = (string)($item['talent_tree_key'] ?? '');
        if ($tree === '') return null;

        $stmt = $pdo->prepare('SELECT node_name, stat_mod_json, unlock_cost, rarity FROM weapon_talent_nodes WHERE talent_tree_key=? ORDER BY unlock_cost,id');
        $stmt->execute([$tree]);
        $nodes = $stmt->fetchAll();
        if (!$nodes) return null;

        // The prototype automatically exposes one deeper node at roughly each two enhancement tiers.
        $index = min(count($nodes) - 1, max(0, (int)floor($enhancement / 2)));
        $node = $nodes[$index];
        $mods = json_decode((string)$node['stat_mod_json'], true);
        if (!is_array($mods)) $mods = [];

        return [
            'node_name' => (string)$node['node_name'],
            'rarity' => (string)$node['rarity'],
            'stat_mod' => $mods,
            'power' => (int)($node['unlock_cost'] ?? 0) * 12,
        ];
    }

    private static function applyTalent(array &$stats, array $mods): void
    {
        foreach ($mods as $key => $value) {
            $normalized = strtolower((string)$key);
            if ($normalized === 'attack_percent') {
                $stats['attack'] = (int)round($stats['attack'] * (1 + ((float)$value / 100)));
            } elseif ($normalized === 'critical_rate') {
                $stats['critical_rate'] += (float)$value;
            } elseif ($normalized === 'critical_damage') {
                $stats['critical_damage'] += (float)$value;
            } elseif (array_key_exists($normalized, $stats)) {
                $stats[$normalized] += is_numeric($value) ? $value : 0;
            }
        }
    }

    private static function applyStat(array &$stats, string $name, float $value): void
    {
        $key = match (strtolower(trim($name))) {
            'attack' => 'attack',
            'defense' => 'defense',
            'health', 'hp' => 'health',
            'speed' => 'speed',
            'skill power' => 'skill_power',
            'critical rate' => 'critical_rate',
            'critical damage' => 'critical_damage',
            'essence recovery' => 'essence_capacity',
            default => null,
        };
        if ($key === null) return;
        $stats[$key] += $value;
    }
}
