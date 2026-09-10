<?php
declare(strict_types=1);
session_start();

spl_autoload_register(function (string $class): void {
    $prefix = 'ShadowShinobi\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = '/var/www/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require $file;
});

use ShadowShinobi\Combat\CombatEngine;
use ShadowShinobi\Combat\CombatLoadoutService;
use ShadowShinobi\Combat\EncounterCatalog;
use ShadowShinobi\Core\Database;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $pdo = Database::connection();
    $player = $pdo->query("SELECT id,username,display_name,coins,gear_fragments FROM players WHERE username='demo' LIMIT 1")->fetch();
    if (!$player) throw new RuntimeException('Demo commander not found.');

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'POST') {
        if (!isset($_SESSION['shadow_combat'])) throw new RuntimeException('No active encounter.');
        $body = json_decode((string)file_get_contents('php://input'), true) ?: [];
        $action = (string)($body['action'] ?? '');
        $state = $_SESSION['shadow_combat'];
        $actorId = (string)($state['active_id'] ?? '');
        $_SESSION['shadow_combat'] = CombatEngine::act($state, $actorId, $action);
        $state = $_SESSION['shadow_combat'];

        if (($state['status'] ?? '') === 'victory' && empty($_SESSION['shadow_combat_rewarded'])) {
            $threat = max(1, (int)($state['encounter']['threat'] ?? 1));
            $coins = 90 + ($threat * 35);
            $fragments = 2 + intdiv($threat, 4);
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('UPDATE players SET coins=coins+?, gear_fragments=gear_fragments+? WHERE id=?');
                $stmt->execute([$coins, $fragments, (int)$player['id']]);
                $event = $pdo->prepare('INSERT INTO world_events (player_id,event_type,title,summary,payload_json,global_visibility) VALUES (?,?,?,?,?,0)');
                $event->execute([
                    (int)$player['id'],
                    'combat_hunt',
                    'Hunt Cleared: ' . (string)($state['encounter']['name'] ?? 'Unknown Threat'),
                    'The cell survived a tactical hunt and recovered materials from the ruined encounter site.',
                    json_encode(['encounter'=>$state['encounter'],'coins'=>$coins,'fragments'=>$fragments], JSON_THROW_ON_ERROR),
                ]);
                $pdo->commit();
                $_SESSION['shadow_combat_rewarded'] = true;
                $state['reward'] = ['coins'=>$coins,'fragments'=>$fragments];
                $_SESSION['shadow_combat'] = $state;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
        }
    } else {
        $rows = $pdo->prepare('SELECT o.* FROM operatives o WHERE o.player_id=? ORDER BY o.id LIMIT 3');
        $rows->execute([(int)$player['id']]);
        $rawOps = $rows->fetchAll();
        if (!$rawOps) throw new RuntimeException('No operatives are available for combat.');

        $ops = [];
        foreach ($rawOps as $operative) {
            $ops[] = CombatLoadoutService::build($pdo, $operative);
        }

        $encounter = EncounterCatalog::get(isset($_GET['encounter']) ? trim((string)$_GET['encounter']) : null);
        $state = CombatEngine::start($ops, $encounter);
        $state['encounter'] = [
            'key' => $encounter['key'],
            'name' => $encounter['name'],
            'threat' => (int)$encounter['threat'],
        ];
        $state['commander'] = ['id'=>(int)$player['id'],'name'=>(string)$player['display_name']];
        $_SESSION['shadow_combat'] = $state;
        $_SESSION['shadow_combat_rewarded'] = false;
    }

    $current = $_SESSION['shadow_combat'];
    echo json_encode(['ok'=>true,'battle'=>$current], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_THROW_ON_ERROR);
}
