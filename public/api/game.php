<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'ShadowShinobi\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = '/var/www/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require $file;
});

use ShadowShinobi\Core\Database;
use ShadowShinobi\Core\GameEngine;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $pdo = Database::connection();
    $player = $pdo->query("SELECT id FROM players WHERE username='demo' LIMIT 1")->fetch();
    if (!$player) {
        throw new RuntimeException('Demo player is not initialized.');
    }

    $body = json_decode((string)file_get_contents('php://input'), true) ?: [];
    $command = (string)($body['command'] ?? $_GET['command'] ?? 'heartbeat');
    $payload = is_array($body['payload'] ?? null) ? $body['payload'] : [];

    echo json_encode([
        'ok' => true,
        'result' => GameEngine::command($pdo, (int)$player['id'], $command, $payload),
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
}
