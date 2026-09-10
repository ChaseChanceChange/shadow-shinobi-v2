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
use ShadowShinobi\Combat\EncounterCatalog;
use ShadowShinobi\Core\Database;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $pdo = Database::connection();
    $op = $pdo->query("SELECT name,class_name,role_name,level,health,attack,defense,speed,critical_rate,critical_damage,skill_power,essence_capacity FROM operatives WHERE player_id=(SELECT id FROM players WHERE username='demo' LIMIT 1) ORDER BY id LIMIT 3")->fetchAll();
    $op = $op ?: [['name'=>'Kael Veyr','class_name'=>'Shadowblade','role_name'=>'Damage','health'=>540,'attack'=>125,'defense'=>80,'speed'=>58,'skill_power'=>70,'essence_capacity'=>100]];

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method === 'POST') {
        $body = json_decode((string)file_get_contents('php://input'), true) ?: [];
        $action = (string)($body['action'] ?? '');
        if (!isset($_SESSION['shadow_combat'])) throw new RuntimeException('No active encounter.');
        $_SESSION['shadow_combat'] = CombatEngine::act($_SESSION['shadow_combat'], (string)$_SESSION['shadow_combat']['active_id'], $action);
    } else {
        $requested = isset($_GET['encounter']) ? trim((string)$_GET['encounter']) : null;
        $encounter = EncounterCatalog::get($requested !== '' ? $requested : null);
        $_SESSION['shadow_combat'] = CombatEngine::start($op, $encounter);
        $_SESSION['shadow_combat']['encounter'] = [
            'key' => $encounter['key'],
            'name' => $encounter['name'],
            'threat' => (int)$encounter['threat'],
        ];
    }

    echo json_encode(['ok'=>true,'battle'=>$_SESSION['shadow_combat']], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_THROW_ON_ERROR);
}
