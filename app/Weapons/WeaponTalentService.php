<?php
declare(strict_types=1);

namespace ShadowShinobi\Weapons;

use ShadowShinobi\Core\Database;

/** Exposes the hidden talent tree appropriate to the equipped weapon's rarity. */
final class WeaponTalentService
{
    private const RARITY_DEPTH = [
        'Common' => 1,
        'Uncommon' => 1,
        'Rare' => 2,
        'Epic' => 3,
        'Legendary' => 4,
        'Mythic' => 5,
        'Ascendant' => 5,
        'Transcendent' => 5,
    ];

    public static function unlockedNodes(string $talentTreeKey, string $rarity): array
    {
        $depth = self::RARITY_DEPTH[$rarity] ?? 1;
        $limit = max(1, min(50, $depth));
        $stmt = Database::connection()->prepare("SELECT * FROM weapon_talent_nodes WHERE talent_tree_key=? ORDER BY id LIMIT {$limit}");
        $stmt->execute([$talentTreeKey]);
        return $stmt->fetchAll();
    }

    public static function visibleDepth(string $rarity): int
    {
        return self::RARITY_DEPTH[$rarity] ?? 1;
    }
}
