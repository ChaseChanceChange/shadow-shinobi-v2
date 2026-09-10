<?php
declare(strict_types=1);

namespace ShadowShinobi\Combat;

use PDO;

final class CombatRewardService
{
    public static function grantVictory(PDO $pdo, int $playerId, array $encounter): array
    {
        $coinReward = max(60, (int)($encounter['reward_coins'] ?? 120));
        $fragmentReward = max(1, (int)($encounter['reward_fragments'] ?? 2));
        $title = (string)($encounter['name'] ?? 'Unknown Hunt');

        $pdo->beginTransaction();
        try {
            $update = $pdo->prepare('UPDATE players SET coins=coins+?, gear_fragments=gear_fragments+? WHERE id=?');
            $update->execute([$coinReward, $fragmentReward, $playerId]);

            $payload = json_encode([
                'encounter' => $encounter['key'] ?? null,
                'encounter_name' => $title,
                'coins' => $coinReward,
                'gear_fragments' => $fragmentReward,
            ], JSON_THROW_ON_ERROR);

            $event = $pdo->prepare('INSERT INTO world_events (player_id,event_type,title,summary,payload_json,global_visibility) VALUES (?,?,?,?,?,0)');
            $event->execute([
                $playerId,
                'hunt_victory',
                'Hunt Cleared: ' . $title,
                'A cell survived the hunt and recovered traces from the ruined battlefield.',
                $payload,
            ]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }

        return [
            'coins' => $coinReward,
            'gear_fragments' => $fragmentReward,
            'summary' => '+' . $coinReward . ' coins · +' . $fragmentReward . ' gear fragments',
        ];
    }
}
