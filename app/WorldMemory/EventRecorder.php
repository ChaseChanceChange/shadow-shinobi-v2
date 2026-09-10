<?php
declare(strict_types=1);

namespace ShadowShinobi\WorldMemory;

use PDO;
use ShadowShinobi\Core\Database;

final class EventRecorder
{
    public static function record(int $playerId, string $type, string $title, string $summary, array $payload = []): int
    {
        return self::recordInTransaction(Database::connection(), $playerId, $type, $title, $summary, $payload);
    }

    public static function recordInTransaction(PDO $pdo, int $playerId, string $type, string $title, string $summary, array $payload = []): int
    {
        $stmt = $pdo->prepare('INSERT INTO world_events (player_id,event_type,title,summary,payload_json) VALUES (?,?,?,?,?)');
        $stmt->execute([$playerId, $type, $title, $summary, json_encode($payload, JSON_THROW_ON_ERROR)]);
        return (int)$pdo->lastInsertId();
    }
}
