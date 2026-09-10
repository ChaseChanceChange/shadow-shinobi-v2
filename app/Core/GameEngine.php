<?php
declare(strict_types=1);

namespace ShadowShinobi\Core;

use PDO;
use RuntimeException;

final class GameEngine
{
    public const VERSION = '0.1.0-ashen-frontier';

    /**
     * Resolve a single authoritative game command.
     * Presentation layers should never mutate game state directly.
     */
    public static function command(PDO $pdo, int $playerId, string $command, array $payload = []): array
    {
        return match ($command) {
            'heartbeat' => self::heartbeat($pdo, $playerId),
            'profile' => self::profile($pdo, $playerId),
            default => throw new RuntimeException('Unknown game command: ' . $command),
        };
    }

    public static function heartbeat(PDO $pdo, int $playerId): array
    {
        $player = self::profile($pdo, $playerId);

        return [
            'engine_version' => self::VERSION,
            'server_time' => gmdate('c'),
            'player' => $player,
        ];
    }

    public static function profile(PDO $pdo, int $playerId): array
    {
        $stmt = $pdo->prepare(
            'SELECT id, username, display_name, coins, gear_fragments, created_at
             FROM players WHERE id=? LIMIT 1'
        );
        $stmt->execute([$playerId]);
        $player = $stmt->fetch();

        if (!$player) {
            throw new RuntimeException('Player not found.');
        }

        return [
            'id' => (int)$player['id'],
            'username' => (string)$player['username'],
            'display_name' => (string)$player['display_name'],
            'coins' => (int)$player['coins'],
            'gear_fragments' => (int)$player['gear_fragments'],
            'created_at' => (string)$player['created_at'],
        ];
    }
}
