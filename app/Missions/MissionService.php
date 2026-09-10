<?php
declare(strict_types=1);

namespace ShadowShinobi\Missions;

use RuntimeException;
use ShadowShinobi\Core\Database;
use ShadowShinobi\WorldMemory\EventRecorder;

final class MissionService
{
    public static function run(int $playerId, int $missionId): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $missionStmt = $pdo->prepare('SELECT * FROM missions WHERE id=? LIMIT 1 FOR UPDATE');
            $missionStmt->execute([$missionId]);
            $mission = $missionStmt->fetch();
            if (!$mission) throw new RuntimeException('Contract not found.');

            $ops = $pdo->prepare('SELECT * FROM operatives WHERE player_id=? ORDER BY id');
            $ops->execute([$playerId]);
            $operatives = $ops->fetchAll();
            if (!$operatives) throw new RuntimeException('You need at least one operative before accepting a contract.');

            $avgLevel = array_sum(array_map(fn(array $o): int => (int)$o['level'], $operatives)) / count($operatives);
            $power = 0;
            foreach ($operatives as $op) {
                $power += (int)$op['attack'] + (int)$op['defense'] + (int)$op['skill_power'] + ((int)$op['speed'] * 2);
            }
            $power = (int)round($power / count($operatives));

            $difficulty = strtolower((string)$mission['difficulty']);
            $threshold = match ($difficulty) {
                'normal' => 250,
                'hard' => 300,
                'brutal' => 390,
                'nightmare' => 470,
                default => 250,
            };
            $levelGap = ((int)$mission['recommended_level'] - $avgLevel) * 35;
            $chance = 0.62 + (($power - $threshold - $levelGap) / 500);
            $chance = max(0.12, min(0.96, $chance));
            $success = (random_int(1, 10000) / 10000) <= $chance;

            $rewardCoins = (int)$mission['reward_coins'];
            $coins = $success ? $rewardCoins : (int)floor($rewardCoins * 0.20);
            $reward = [
                'coins' => $coins,
                'loot' => $success ? (json_decode((string)$mission['loot_json'], true) ?: []) : [],
                'success_chance' => round($chance * 100, 1),
                'power' => $power,
            ];

            if ($coins > 0) {
                $pdo->prepare('UPDATE players SET coins=coins+? WHERE id=?')->execute([$coins, $playerId]);
            }

            $result = $success ? 'success' : 'failure';
            $pdo->prepare('INSERT INTO mission_runs (player_id,mission_id,result,reward_json) VALUES (?,?,?,?)')
                ->execute([$playerId, $missionId, $result, json_encode($reward, JSON_THROW_ON_ERROR)]);

            $title = $success ? 'Contract Complete' : 'Contract Failed';
            $summary = $success
                ? sprintf('%s cleared. The cell returned with %d coins.', $mission['name'], $coins)
                : sprintf('%s broke the cell\'s momentum. The retreat yielded %d coins.', $mission['name'], $coins);
            $eventId = EventRecorder::recordInTransaction($pdo, $playerId, 'mission_'.$result, $title, $summary, [
                'mission_id' => $missionId,
                'mission_key' => $mission['mission_key'],
                'chance' => $chance,
                'power' => $power,
                'reward' => $reward,
            ]);

            $pdo->commit();
            return ['success' => $success, 'coins' => $coins, 'chance' => $chance, 'power' => $power, 'event_id' => $eventId, 'mission' => $mission['name']];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
