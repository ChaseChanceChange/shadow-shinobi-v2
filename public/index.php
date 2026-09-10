<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'ShadowShinobi\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = '/var/www/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use ShadowShinobi\Core\Database;
use ShadowShinobi\Enhancement\EnhancementService;
use ShadowShinobi\LivingWorld\SimulationService;
use ShadowShinobi\Weapons\WeaponTalentService;

$pdo = Database::connection();
$player = $pdo->query("SELECT * FROM players WHERE username='demo' LIMIT 1")->fetch();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'enhance') {
            $result = EnhancementService::enhance((int)$player['id'], (int)($_POST['item_id'] ?? 0), array_map('intval', $_POST['sacrifices'] ?? []));
            $message = $result['success']
                ? 'Enhancement succeeded at +' . $result['level'] . ($result['awakened'] ? ' — Awakened.' : '.') . ' ' . $result['fragments'] . ' fragments returned.'
                : 'The target shattered. ' . $result['fragments'] . ' fragments returned; Lost Relic #' . (int)($result['lost_relic_id'] ?? 0) . ' entered the world.';
        } elseif ($action === 'living_world_start') {
            $runId = SimulationService::createRun((int)$player['id']);
            $message = 'Living Wilds expedition #' . $runId . ' created.';
        } elseif ($action === 'living_world_tick') {
            $tick = SimulationService::tick((int)$player['id'], (int)($_POST['run_id'] ?? 0));
            $message = 'Living Wilds advanced to tick ' . $tick['tick'] . '.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$player = $pdo->query("SELECT * FROM players WHERE username='demo' LIMIT 1")->fetch();
$operatives = $pdo->prepare('SELECT * FROM operatives WHERE player_id=? ORDER BY id');
$operatives->execute([$player['id']]);
$operatives = $operatives->fetchAll();
$gearStmt = $pdo->prepare('SELECT * FROM equipment_items WHERE player_id=? AND destroyed=0 ORDER BY is_core_weapon DESC, id');
$gearStmt->execute([$player['id']]);
$gear = $gearStmt->fetchAll();
$events = $pdo->prepare('SELECT * FROM world_events WHERE player_id=? OR global_visibility=1 ORDER BY id DESC LIMIT 8');
$events->execute([$player['id']]);
$events = $events->fetchAll();
$relics = $pdo->query('SELECT * FROM lost_relics WHERE discovered=0 ORDER BY id DESC LIMIT 6')->fetchAll();
$missions = $pdo->query('SELECT * FROM missions ORDER BY id')->fetchAll();
$living = $pdo->prepare('SELECT * FROM living_world_runs WHERE player_id=? ORDER BY id DESC LIMIT 1');
$living->execute([$player['id']]);
$livingRun = $living->fetch();
$livingEntities = [];
if ($livingRun) {
    $stmt = $pdo->prepare('SELECT * FROM living_world_entities WHERE run_id=? ORDER BY id');
    $stmt->execute([$livingRun['id']]);
    $livingEntities = $stmt->fetchAll();
}
function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shadow Shinobi</title>
<style>body{margin:0;background:#07090d;color:#edf1f6;font:14px/1.5 system-ui,sans-serif}.wrap{max-width:1200px;margin:auto;padding:24px}.hero,.card{background:#10151d;border:1px solid #2a3340;border-radius:14px;padding:18px;margin:12px 0}.hero{background:linear-gradient(145deg,#171d27,#0a0d12)}h1{font-size:42px;margin:4px 0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px}.wide{grid-column:1/-1}.muted{color:#8994a3}.tag{display:inline-block;border:1px solid #3a4452;border-radius:999px;padding:2px 8px;margin:2px;font-size:11px}.item{padding:9px;border-bottom:1px solid #202834}.result{padding:12px;border:1px solid #435064;border-radius:10px;margin:10px 0}.good{color:#83c9a0}.bad{color:#e28b8b}select,button{font:inherit;background:#171e28;color:#edf1f6;border:1px solid #3a4452;border-radius:8px;padding:8px}button{cursor:pointer;font-weight:700}.small{font-size:12px}</style></head><body><main class="wrap">
<header class="hero"><div class="muted">PERSISTENT SQUAD RPG · WORLD MEMORY</div><h1>SHADOW SHINOBI</h1><div class="muted">Build the squad. Risk the forge. Leave a world behind you.</div></header>
<?php if($message): ?><div class="result good"><?=h($message)?></div><?php endif; ?><?php if($error): ?><div class="result bad"><?=h($error)?></div><?php endif; ?>
<div class="grid">
<section class="card"><h2>Commander</h2><strong><?=h($player['display_name'])?></strong><div><span class="tag"><?=number_format((int)$player['coins'])?> Coin</span><span class="tag"><?=number_format((int)$player['gear_fragments'])?> Fragments</span></div></section>
<section class="card"><h2>Squad</h2><?php foreach($operatives as $op): ?><div class="item"><strong><?=h($op['name'])?></strong><div><span class="tag"><?=h($op['class_name'])?></span><span class="tag"><?=h($op['role_name'])?></span></div><div class="small muted">Lv <?=$op['level']?> · ATK <?=$op['attack']?> · DEF <?=$op['defense']?> · HP <?=$op['health']?> · SPD <?=$op['speed']?></div></div><?php endforeach; ?></section>
<section class="card"><h2>Core Weapon Doctrine</h2><p class="muted">Rarity exposes progressively deeper hidden talent nodes.</p><?php foreach($gear as $item): if(!(int)$item['is_core_weapon']) continue; $nodes=WeaponTalentService::unlockedNodes((string)$item['talent_tree_key'],(string)$item['rarity']); ?><div class="item"><strong><?=h($item['item_name'])?></strong><span class="tag"><?=h($item['rarity'])?></span><?php foreach($nodes as $node): ?><div class="small">◇ <?=h($node['node_name'])?> — <?=h($node['description'])?></div><?php endforeach; ?></div><?php endforeach; ?></section>
<section class="card wide"><h2>Gear &amp; Enhancement Ritual</h2><p class="muted">The target plus at least two additional gear pieces are consumed. Success recreates the target at the next level; failure destroys it, returns fragments and may create a Lost Relic.</p><form method="post"><input type="hidden" name="action" value="enhance"><select name="item_id" required><?php foreach($gear as $item): ?><option value="<?=$item['id']?>"><?=h($item['item_name'])?> [<?=h($item['rarity'])?> +<?=$item['enhancement_level']?>]</option><?php endforeach; ?></select><?php foreach($gear as $item): ?><label style="display:block;margin:5px 0"><input type="checkbox" name="sacrifices[]" value="<?=$item['id']?>"> <?=h($item['item_name'])?> — <?=h($item['rarity'])?></label><?php endforeach; ?><button type="submit">Attempt Enhancement</button></form></section>
<section class="card"><h2>Gear Reserve</h2><?php foreach($gear as $item): ?><div class="item"><strong><?=h($item['item_name'])?></strong> <span class="tag"><?=h($item['rarity'])?></span><span class="tag">+<?=$item['enhancement_level']?></span><?php if($item['awakened']): ?><span class="tag">Awakened</span><?php endif; ?><div class="small muted"><?=h($item['main_stat_name'])?> <?=number_format((float)$item['main_stat_value'],0)?></div></div><?php endforeach; ?></section>
<section class="card"><h2>Lost Relics</h2><?php foreach($relics as $r): ?><div class="item"><strong><?=h($r['name'])?></strong><div class="small muted"><?=h($r['rarity'])?> · Power <?=$r['power_rating']?></div></div><?php endforeach; if(!$relics): ?><div class="muted">No unresolved relic echoes yet.</div><?php endif; ?></section>
<section class="card"><h2>World Memory</h2><?php foreach($events as $e): ?><div class="item"><strong><?=h($e['title'])?></strong><div><?=h($e['summary'])?></div><small class="muted"><?=h($e['created_at'])?></small></div><?php endforeach; if(!$events): ?><div class="muted">The world is waiting for its first meaningful memory.</div><?php endif; ?></section>
<section class="card wide"><h2>Living Wilds <span class="tag">Optional Mode</span></h2><p class="muted">A separate simulated ecosystem for exploration and resource play.</p><?php if(!$livingRun): ?><form method="post"><input type="hidden" name="action" value="living_world_start"><button type="submit">Enter the Living Wilds</button></form><?php else: ?><strong>Expedition #<?=$livingRun['id']?></strong><span class="tag">Tick <?=$livingRun['ticks']?></span><?php foreach($livingEntities as $entity): ?><div class="item"><strong><?=h($entity['name'])?></strong> <span class="tag"><?=h($entity['species'])?></span><div class="small muted">Position <?=$entity['position_x']?>, <?=$entity['position_y']?> · Energy <?=$entity['energy']?> · <?=h($entity['status'])?></div></div><?php endforeach; ?><form method="post"><input type="hidden" name="action" value="living_world_tick"><input type="hidden" name="run_id" value="<?=$livingRun['id']?>"><button type="submit">Advance Ecosystem Tick</button></form><?php endif; ?></section>
<section class="card wide"><h2>Contracts</h2><div class="grid"><?php foreach($missions as $m): ?><div class="card"><strong><?=h($m['name'])?></strong><div><span class="tag"><?=h($m['difficulty'])?></span><span class="tag">Recommended <?=$m['recommended_level']?></span></div><p class="muted"><?=h($m['description'])?></p></div><?php endforeach; ?></div></section>
</div></main></body></html>
