<?php
declare(strict_types=1);

namespace ShadowShinobi\WorldMemory;

use ShadowShinobi\Core\Database;

final class LegacySummary
{
    public static function forPlayer(int $playerId): array
    {
        $stmt = Database::connection()->prepare('SELECT event_type,title,summary,created_at FROM world_events WHERE player_id=? ORDER BY created_at DESC LIMIT 20');
        $stmt->execute([$playerId]);
        return $stmt->fetchAll();
    }
}
