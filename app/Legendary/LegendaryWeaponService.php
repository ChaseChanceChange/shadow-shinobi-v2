<?php
declare(strict_types=1);

namespace ShadowShinobi\Legendary;

use PDO;

final class LegendaryWeaponService
{
    /** Create the persistent shatter record inside an existing transaction. */
    public static function shatterInTransaction(PDO $pdo, int $playerId, array $item, string $reason = 'enhancement_failure'): array
    {
        $weapon = LegendaryWeaponCatalog::get('kageboshi');
        if ((string)($item['rarity'] ?? '') !== 'Legendary' || strcasecmp((string)($item['weapon_family'] ?? ''), 'Kageboshi') !== 0) {
            throw new \InvalidArgumentException('The selected item is not a Kageboshi legendary instance.');
        }

        self::ensureTables($pdo);
        $sourceId = (int)$item['id'];
        $originPayload = [
            'legendary_key' => 'kageboshi',
            'weapon_name' => (string)$item['item_name'],
            'source_item_id' => $sourceId,
            'original_player_id' => $playerId,
            'original_enhancement_level' => (int)$item['enhancement_level'],
            'original_main_stat' => (float)$item['main_stat_value'],
            'original_substats' => json_decode((string)$item['substats_json'], true) ?: [],
            'original_visual' => json_decode((string)$item['visual_json'], true) ?: [],
            'shatter_reason' => $reason,
            'shattered_at' => gmdate('c'),
        ];

        $insert = $pdo->prepare('INSERT INTO lost_relics (player_id,name,source_item_id,rarity,power_rating,payload_json,discovered) VALUES (NULL,?,?,?,?,?,0)');
        $ids = [];
        for ($i = 1; $i <= (int)$weapon['shatter_fragments']; $i++) {
            $seed = crc32('kageboshi:' . $sourceId . ':' . $i . ':' . $playerId);
            $location = self::fragmentLocation($seed);
            $payload = $originPayload + [
                'fragment_index' => $i,
                'fragment_total' => (int)$weapon['shatter_fragments'],
                'map_key' => 'ashen-frontier',
                'position_x' => $location['x'],
                'position_y' => $location['y'],
                'expires_at' => gmdate('c', time() + ((int)$weapon['fragment_ttl_days'] * 86400)),
            ];
            $insert->execute([
                $weapon['name'] . ' — Memory Fragment ' . $i . '/' . $weapon['shatter_fragments'],
                $sourceId,
                $weapon['rarity'],
                (int)$item['main_stat_value'],
                json_encode($payload, JSON_THROW_ON_ERROR),
            ]);
            $ids[] = (int)$pdo->lastInsertId();
        }

        return ['legendary_key'=>'kageboshi','weapon'=>$weapon['name'],'fragment_ids'=>$ids,'fragment_count'=>count($ids),'summary'=>'Kageboshi shattered. Five memory fragments now exist in the Ashen Frontier.'];
    }

    /** @return array{ x:int, y:int } */
    public static function fragmentLocation(int $seed): array
    {
        $locations = [[31,15],[42,27],[57,11],[66,34],[79,45],[77,16],[24,41],[58,42]];
        return $locations[$seed % count($locations)];
    }

    private static function ensureTables(PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS legendary_weapons (\n            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n            weapon_key VARCHAR(64) NOT NULL UNIQUE,\n            name VARCHAR(128) NOT NULL,\n            lore_text TEXT NOT NULL,\n            talent_tree_json JSON NOT NULL,\n            shatter_fragments INT NOT NULL DEFAULT 5,\n            fragment_ttl_days INT NOT NULL DEFAULT 7,\n            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP\n        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
