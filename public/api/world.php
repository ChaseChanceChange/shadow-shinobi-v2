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

use ShadowShinobi\Core\Database;
use ShadowShinobi\World\OverworldService;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $pdo = Database::connection();
    $player = $pdo->query("SELECT id,username,display_name,coins,gear_fragments FROM players WHERE username='demo' LIMIT 1")->fetch();
    if (!$player) throw new RuntimeException('Demo commander not found.');

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $playerId = (int)$player['id'];

    if ($method === 'POST') {
        $body = json_decode((string)file_get_contents('php://input'), true) ?: [];
        $action = (string)($body['action'] ?? '');

        if ($action === 'move') {
            $dx = (int)($body['dx'] ?? 0);
            $dy = (int)($body['dy'] ?? 0);
            $state = OverworldService::move($pdo, $playerId, $dx, $dy);
        } elseif ($action === 'interact') {
            $state = OverworldService::state($pdo, $playerId);
            $x = (int)$state['position_x'];
            $y = (int)$state['position_y'];
            $interaction = null;
            foreach (OverworldService::map()['landmarks'] as $landmark) {
                if (hypot($x - $landmark['x'], $y - $landmark['y']) <= (float)$landmark['radius']) {
                    $interaction = $landmark;
                    break;
                }
            }
            if ($interaction === null) {
                $state['message'] = 'Nothing here answers your call.';
            } elseif ($interaction['kind'] === 'hq') {
                $pdo->prepare('UPDATE world_player_state SET energy=100 WHERE player_id=?')->execute([$playerId]);
                $state = OverworldService::state($pdo, $playerId);
                $state['message'] = 'Night Cell HQ: the cell recovers and prepares for another hunt.';
                $state['interaction'] = ['id'=>$interaction['id'],'name'=>$interaction['name'],'kind'=>$interaction['kind']];
            } else {
                $state['message'] = $interaction['name'] . ': ' . $interaction['description'];
                $state['interaction'] = ['id'=>$interaction['id'],'name'=>$interaction['name'],'kind'=>$interaction['kind']];
            }
        } else {
            throw new InvalidArgumentException('Unknown world action.');
        }
    } else {
        $state = OverworldService::state($pdo, $playerId);
    }

    echo json_encode([
        'ok' => true,
        'map' => OverworldService::map(),
        'state' => $state,
        'commander' => [
            'id' => $playerId,
            'name' => (string)$player['display_name'],
            'coins' => (int)$player['coins'],
            'gear_fragments' => (int)$player['gear_fragments'],
        ],
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_THROW_ON_ERROR);
}
