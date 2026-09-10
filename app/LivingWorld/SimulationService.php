<?php
declare(strict_types=1);

namespace ShadowShinobi\LivingWorld;

use ShadowShinobi\Core\Database;
use ShadowShinobi\WorldMemory\EventRecorder;

/**
 * Lightweight prototype for the optional Living Wilds mode.
 * The simulation is deliberately separate from the main mission loop so the
 * normal MMO remains predictable while the world mode can evolve later.
 */
final class SimulationService
{
    public static function createRun(int $playerId): int
    {
        $pdo = Database::connection();
        $seed = random_int(1, PHP_INT_MAX);
        $stmt = $pdo->prepare('INSERT INTO living_world_runs (player_id, world_seed, status) VALUES (?,?,?)');
        $stmt->execute([$playerId, $seed, 'active']);
        $runId = (int)$pdo->lastInsertId();

        $entities = [
            ['Deer','herbivore',14,22,40],
            ['Marsh Stalker','predator',34,18,25],
            ['Ruin Beetle','scavenger',7,37,70],
            ['Ash Crow','scavenger',46,12,55],
            ['Moon Hound','predator',25,31,35],
        ];
        $insert = $pdo->prepare('INSERT INTO living_world_entities (run_id,name,species,position_x,position_y,energy,status) VALUES (?,?,?,?,?,?,?)');
        foreach ($entities as $e) {
            $insert->execute([$runId,$e[0],$e[1],$e[2],$e[3],$e[4],'alive']);
        }
        EventRecorder::record($playerId, 'living_world_started', 'Entered the Living Wilds', 'A simulated ecosystem has been created for this resource expedition.', ['run_id'=>$runId,'seed'=>$seed]);
        return $runId;
    }

    public static function tick(int $playerId, int $runId): array
    {
        $pdo = Database::connection();
        $check = $pdo->prepare('SELECT * FROM living_world_runs WHERE id=? AND player_id=? AND status="active" FOR UPDATE');
        $check->execute([$runId,$playerId]);
        $run = $check->fetch();
        if (!$run) throw new \RuntimeException('Living Wilds run not found.');
        $pdo->beginTransaction();
        try {
            $rows = $pdo->prepare('SELECT * FROM living_world_entities WHERE run_id=? AND status="alive" FOR UPDATE');
            $rows->execute([$runId]);
            $entities = $rows->fetchAll();
            foreach ($entities as $entity) {
                $dx = random_int(-2,2);
                $dy = random_int(-2,2);
                $energy = max(0, (int)$entity['energy'] - 1);
                $status = $energy === 0 ? 'resting' : 'alive';
                $pdo->prepare('UPDATE living_world_entities SET position_x=position_x+?, position_y=position_y+?, energy=?, status=? WHERE id=?')
                    ->execute([$dx,$dy,$energy,$status,$entity['id']]);
            }
            $pdo->prepare('UPDATE living_world_runs SET ticks=ticks+1 WHERE id=?')->execute([$runId]);
            $pdo->commit();
            return ['entities'=>$entities,'tick'=>(int)$run['ticks']+1];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
