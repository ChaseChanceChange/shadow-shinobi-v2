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
use ShadowShinobi\Enhancement\EnhancementService;
use ShadowShinobi\LivingWorld\SimulationService;
use ShadowShinobi\Missions\MissionService;
use ShadowShinobi\Weapons\WeaponTalentService;

$pdo = Database::connection();
$message = null;
$error = null;
$player = $pdo->query("SELECT * FROM players WHERE username='demo' LIMIT 1")->fetch();

if (!$player) {
    $pdo->exec("INSERT INTO players (username,display_name,coins) VALUES ('demo','Shadow Commander',2500)");
    $player = $pdo->query("SELECT * FROM players WHERE username='demo' LIMIT 1")->fetch();
}
$playerId = (int)$player['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'enhance') {
            $result = EnhancementService::enhance($playerId, (int)($_POST['item_id'] ?? 0), array_map('intval', $_POST['sacrifices'] ?? []));
            $message = $result['success']
                ? 'FORGE SUCCESS — +' . $result['level'] . ($result['awakened'] ? ' AWAKENED' : '') . ' · +' . $result['fragments'] . ' fragments'
                : 'FORGE FAILURE — the target shattered. ' . $result['fragments'] . ' fragments returned; relic echo #' . (int)($result['lost_relic_id'] ?? 0) . ' recorded.';
        } elseif ($action === 'mission') {
            $result = MissionService::run($playerId, (int)($_POST['mission_id'] ?? 0));
            $message = ($result['success'] ? 'CONTRACT COMPLETE' : 'CONTRACT FAILED') . ' — ' . $result['mission'] . ' · ' . $result['coins'] . ' coins returned · projected success ' . round($result['chance'] * 100, 1) . '%.';
        } elseif ($action === 'living_world_start') {
            $runId = SimulationService::createRun($playerId);
            $message = 'LIVING WILDS — expedition #' . $runId . ' is active.';
        } elseif ($action === 'living_world_tick') {
            $tick = SimulationService::tick($playerId, (int)($_POST['run_id'] ?? 0));
            $message = 'LIVING WILDS — ecosystem advanced to tick ' . $tick['tick'] . '.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$operatives = $pdo->prepare('SELECT * FROM operatives WHERE player_id=? ORDER BY id');
$operatives->execute([$playerId]);
$operatives = $operatives->fetchAll();

$gearStmt = $pdo->prepare('SELECT * FROM equipment_items WHERE player_id=? AND destroyed=0 ORDER BY is_core_weapon DESC, rarity DESC, enhancement_level DESC, id');
$gearStmt->execute([$playerId]);
$gear = $gearStmt->fetchAll();

$events = $pdo->prepare('SELECT * FROM world_events WHERE player_id=? OR global_visibility=1 ORDER BY id DESC LIMIT 10');
$events->execute([$playerId]);
$events = $events->fetchAll();

$relics = $pdo->query('SELECT * FROM lost_relics WHERE discovered=0 ORDER BY id DESC LIMIT 8')->fetchAll();
$missions = $pdo->query('SELECT * FROM missions ORDER BY recommended_level,id')->fetchAll();
$historyStmt = $pdo->prepare('SELECT mr.*,m.name FROM mission_runs mr JOIN missions m ON m.id=mr.mission_id WHERE mr.player_id=? ORDER BY mr.id DESC LIMIT 6');
$historyStmt->execute([$playerId]);
$history = $historyStmt->fetchAll();

$living = $pdo->prepare('SELECT * FROM living_world_runs WHERE player_id=? ORDER BY id DESC LIMIT 1');
$living->execute([$playerId]);
$livingRun = $living->fetch();
$livingEntities = [];
if ($livingRun) {
    $stmt = $pdo->prepare('SELECT * FROM living_world_entities WHERE run_id=? ORDER BY id');
    $stmt->execute([$livingRun['id']]);
    $livingEntities = $stmt->fetchAll();
}

function h(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function pct(float $value): string { return number_format($value, 1) . '%'; }
function rarityClass(string $rarity): string { return strtolower(preg_replace('/[^a-z]/i', '', $rarity)); }
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shadow Shinobi — Command</title>
<style>
:root{--bg:#05070a;--panel:#0c1016;--panel2:#111722;--line:#202a37;--text:#edf2f7;--muted:#7f8b9b;--accent:#b9c7d8;--good:#75d29a;--bad:#e77f82;--gold:#d8b66b;--blue:#6aa6d8}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:radial-gradient(circle at 50% -10%,#18212e 0,#080b10 38%,#040609 100%);color:var(--text);font:14px/1.55 Inter,ui-sans-serif,system-ui,sans-serif}body:before{content:"";position:fixed;inset:0;pointer-events:none;opacity:.22;background-image:linear-gradient(115deg,transparent 48%,rgba(255,255,255,.025) 49%,transparent 50%);background-size:7px 7px}.shell{max-width:1380px;margin:auto;padding:18px}.topbar{position:sticky;top:0;z-index:5;background:rgba(5,7,10,.9);backdrop-filter:blur(14px);border-bottom:1px solid var(--line);margin:0 -18px;padding:10px 18px;display:flex;align-items:center;gap:18px}.brand{font-weight:900;letter-spacing:.16em}.nav{display:flex;gap:7px;overflow:auto}.nav a{color:var(--muted);text-decoration:none;padding:7px 10px;border-radius:7px;white-space:nowrap}.nav a:hover{background:#151c26;color:var(--text)}.hero{margin-top:18px;min-height:260px;padding:30px;border:1px solid #2a3544;border-radius:18px;position:relative;overflow:hidden;background:linear-gradient(120deg,rgba(17,23,32,.97),rgba(7,10,14,.92)),radial-gradient(circle at 85% 20%,#34475d,transparent 30%)}.hero:after{content:"影";position:absolute;right:4%;bottom:-55px;font-size:260px;font-weight:900;line-height:1;color:rgba(255,255,255,.025)}.eyebrow{font-size:11px;letter-spacing:.22em;color:var(--muted)}h1{font-size:clamp(38px,6vw,72px);line-height:.95;margin:12px 0;letter-spacing:.06em}h2{margin:0 0 10px;font-size:18px}h3{margin:0 0 6px}.hero p{max-width:700px;color:#aab5c3;font-size:16px}.hero-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}.chip,.tag{display:inline-flex;align-items:center;gap:5px;border:1px solid #2e3948;background:#0d131b;border-radius:999px;padding:4px 9px;color:#b6c1cf;font-size:11px}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:12px 0}.stat{background:var(--panel);border:1px solid var(--line);border-radius:12px;padding:14px}.stat b{display:block;font-size:24px}.stat span{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.12em}.grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}.card{grid-column:span 4;background:linear-gradient(145deg,var(--panel2),var(--panel));border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:0 12px 35px rgba(0,0,0,.16)}.wide{grid-column:1/-1}.half{grid-column:span 6}.section-title{display:flex;align-items:end;justify-content:space-between;margin:28px 2px 10px}.section-title small{color:var(--muted)}.op{display:grid;grid-template-columns:42px 1fr auto;gap:10px;align-items:center;padding:11px 0;border-bottom:1px solid #1a222e}.portrait{width:42px;height:42px;border-radius:10px;background:linear-gradient(145deg,#263344,#0b0f15);display:grid;place-items:center;font-weight:900}.op:last-child,.item:last-child{border-bottom:0}.muted{color:var(--muted)}.item{padding:11px 0;border-bottom:1px solid #1a222e}.item-head{display:flex;justify-content:space-between;gap:8px}.small{font-size:12px}.rarity{font-size:10px;text-transform:uppercase;letter-spacing:.1em}.common{color:#a9b3bf}.uncommon{color:#79c48e}.rare{color:#72a9e0}.epic{color:#b68ae0}.legendary{color:#e0b76c}.mythic{color:#ef7777}.bars{display:grid;gap:5px;margin-top:8px}.bar{height:5px;background:#1a222c;border-radius:10px;overflow:hidden}.bar i{display:block;height:100%;background:#8295aa}.btn,button{appearance:none;border:1px solid #3a4656;background:#151d28;color:var(--text);border-radius:8px;padding:8px 12px;font-weight:800;cursor:pointer}.btn:hover,button:hover{background:#202b39}.btn.primary{background:#d8e0e8;color:#080b10;border-color:#e4e9ee}.btn.danger{border-color:#744347;color:#efb0b2}.form-row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}select{background:#0a0e14;color:var(--text);border:1px solid #354151;border-radius:8px;padding:9px}.result{padding:13px 15px;border-radius:10px;border:1px solid #334052;margin:12px 0;background:#0b1017}.good{color:var(--good);border-color:#27543b}.bad{color:var(--bad);border-color:#5c3035}.mission{display:grid;grid-template-columns:1fr auto;gap:12px;padding:15px 0;border-bottom:1px solid #1b2430}.mission:last-child{border-bottom:0}.mission p{margin:6px 0;color:#8995a5}.reward{color:var(--gold);font-size:12px}.history{display:grid;grid-template-columns:1fr auto;gap:8px;padding:8px 0;border-bottom:1px solid #1a222e}.success{color:var(--good)}.failure{color:var(--bad)}.wild-map{min-height:190px;border:1px solid #253142;border-radius:12px;position:relative;overflow:hidden;background:radial-gradient(circle at 30% 30%,#203025,transparent 20%),radial-gradient(circle at 75% 65%,#172b32,transparent 23%),#070b0d}.entity-dot{position:absolute;width:9px;height:9px;border-radius:50%;background:#b7d5b9;box-shadow:0 0 15px #b7d5b9}.footer{padding:30px 0 12px;color:#586474;text-align:center;font-size:11px}
@media(max-width:900px){.card,.half{grid-column:1/-1}.stats{grid-template-columns:repeat(2,1fr)}}@media(max-width:560px){.shell{padding:10px}.topbar{margin:0 -10px;padding:10px}.stats{grid-template-columns:1fr 1fr}.hero{padding:22px}.mission{grid-template-columns:1fr}.nav{display:none}}
</style></head><body><div class="shell">
<nav class="topbar"><div class="brand">SHADOW // SHINOBI</div><div class="nav"><a href="#command">Command</a><a href="#squad">Squad</a><a href="#contracts">Contracts</a><a href="#armory">Armory</a><a href="#memory">Memory</a><a href="#wilds">Wilds</a></div></nav>
<header class="hero" id="command"><div class="eyebrow">PERSISTENT SQUAD RPG · PHASE 01 · LIVE COMMAND DECK</div><h1>SHADOW<br>SHINOBI</h1><p>Build a cell of operatives, send them into dangerous contracts, forge equipment through controlled risk, and let every important failure become part of the world's history.</p><div class="hero-meta"><span class="chip">COMMANDER · <?=h($player['display_name'])?></span><span class="chip">CELL · NIGHT CELL</span><span class="chip">WORLD MEMORY · ACTIVE</span></div></header>
<div class="stats"><div class="stat"><span>Coin Reserve</span><b><?=number_format((int)$player['coins'])?></b></div><div class="stat"><span>Gear Fragments</span><b><?=number_format((int)$player['gear_fragments'])?></b></div><div class="stat"><span>Operatives</span><b><?=count($operatives)?></b></div><div class="stat"><span>Unresolved Relics</span><b><?=count($relics)?></b></div></div>
<?php if($message): ?><div class="result good">◆ <?=h($message)?></div><?php endif; ?><?php if($error): ?><div class="result bad">◆ <?=h($error)?></div><?php endif; ?>
<div class="section-title" id="squad"><h2>Night Cell</h2><small>OPERATIVE IDENTITY · POWER · ROLE</small></div><div class="grid">
<section class="card half"><h2>Active Roster</h2><?php foreach($operatives as $op): $power=(int)$op['attack']+(int)$op['defense']+(int)$op['skill_power']+((int)$op['speed']*2); ?><div class="op"><div class="portrait"><?=h(substr($op['name'],0,1))?></div><div><strong><?=h($op['name'])?></strong><div><span class="tag"><?=h($op['class_name'])?></span><span class="tag"><?=h($op['role_name'])?></span></div><div class="small muted">Lv <?=$op['level']?> · HP <?=$op['health']?> · SPD <?=$op['speed']?> · combat index <?=$power?></div></div><span class="rarity rare">READY</span></div><?php endforeach; ?></section>
<section class="card half"><h2>Cell Doctrine</h2><p class="muted">A balanced cell wins more contracts; specialized cells create sharper risk/reward edges. Operatives currently share the commander's progression pool.</p><div class="bars"><div class="small">Attack</div><div class="bar"><i style="width:78%"></i></div><div class="small">Defense</div><div class="bar"><i style="width:71%"></i></div><div class="small">Control / Support</div><div class="bar"><i style="width:64%"></i></div></div><div style="margin-top:14px"><span class="tag">NO PAY-TO-WIN</span><span class="tag">RISK MATTERS</span><span class="tag">HISTORY PERSISTS</span></div></section>
</div>
<div class="section-title" id="contracts"><h2>Contract Board</h2><small>ROLL A MISSION · OUTCOME IS AUTHORITATIVE</small></div><div class="grid"><section class="card wide"><p class="muted">Contract odds are calculated from the current roster, recommended level and difficulty. The roll is performed server-side; the result becomes a permanent World Memory entry.</p><?php foreach($missions as $m): ?><div class="mission"><div><strong><?=h($m['name'])?></strong> <span class="tag"><?=h($m['difficulty'])?></span><span class="tag">LV <?=$m['recommended_level']?></span><p><?=h($m['description'])?></p><div class="reward">Reward: <?=number_format((int)$m['reward_coins'])?> coins</div></div><form method="post"><input type="hidden" name="action" value="mission"><input type="hidden" name="mission_id" value="<?=$m['id']?>"><button class="btn primary" type="submit">Deploy Cell</button></form></div><?php endforeach; ?></section><section class="card half"><h2>Recent Contracts</h2><?php foreach($history as $run): ?><div class="history"><span><?=h($run['name'])?><br><small class="muted"><?=h($run['completed_at'])?></small></span><strong class="<?= $run['result']==='success'?'success':'failure' ?>"><?=strtoupper(h($run['result']))?></strong></div><?php endforeach; if(!$history): ?><div class="muted">No contracts have been resolved yet.</div><?php endif; ?></section><section class="card half"><h2>How the loop works</h2><div class="item"><strong>1 · Scout</strong><div class="small muted">Read the contract and judge the risk.</div></div><div class="item"><strong>2 · Deploy</strong><div class="small muted">The current cell is evaluated automatically.</div></div><div class="item"><strong>3 · Remember</strong><div class="small muted">Successes and failures become world history.</div></div></section></div>
<div class="section-title" id="armory"><h2>Armory</h2><small>CORE WEAPONS · HIDDEN TALENTS · HIGH-RISK FORGE</small></div><div class="grid"><section class="card half"><h2>Core Weapon Doctrine</h2><p class="muted">Rarity reveals deeper talent nodes. These are persistent identity layers, not generic stat sticks.</p><?php foreach($gear as $item): if(!(int)$item['is_core_weapon']) continue; $nodes=WeaponTalentService::unlockedNodes((string)$item['talent_tree_key'],(string)$item['rarity']); ?><div class="item"><div class="item-head"><strong><?=h($item['item_name'])?></strong><span class="rarity <?=rarityClass((string)$item['rarity'])?>"><?=h($item['rarity'])?></span></div><div class="small muted">+<?=$item['enhancement_level']?> · <?=h($item['main_stat_name'])?> <?=number_format((float)$item['main_stat_value'],0)?></div><?php foreach($nodes as $node): ?><div class="small" style="margin-top:6px">◇ <strong><?=h($node['node_name'])?></strong> — <?=h($node['description'])?></div><?php endforeach; ?></div><?php endforeach; ?></section>
<section class="card half"><h2>Enhancement Ritual</h2><p class="muted">Select a target and at least two different sacrifices. The entire ritual is transactional: either the target returns stronger, or it is destroyed and may leave a Lost Relic behind.</p><form method="post"><input type="hidden" name="action" value="enhance"><div class="form-row"><select name="item_id" required><?php foreach($gear as $item): ?><option value="<?=$item['id']?>"><?=h($item['item_name'])?> [<?=h($item['rarity'])?> +<?=$item['enhancement_level']?>]</option><?php endforeach; ?></select><button class="btn danger" type="submit">Attempt Ritual</button></div><div style="margin-top:10px"><?php foreach($gear as $item): ?><label class="item" style="display:block"><input type="checkbox" name="sacrifices[]" value="<?=$item['id']?>"> <?=h($item['item_name'])?> <span class="tag"><?=h($item['rarity'])?></span></label><?php endforeach; ?></div></form></section>
<section class="card wide"><h2>Gear Reserve</h2><div class="grid"><?php foreach($gear as $item): ?><div class="card"><div class="item-head"><strong><?=h($item['item_name'])?></strong><span class="rarity <?=rarityClass((string)$item['rarity'])?>"><?=h($item['rarity'])?></span></div><div class="small muted"><?=h($item['slot_name'])?> · +<?=$item['enhancement_level']?> · <?=h($item['main_stat_name'])?> <?=number_format((float)$item['main_stat_value'],0)?></div><?php if($item['awakened']): ?><span class="tag legendary">AWAKENED</span><?php endif; ?></div><?php endforeach; ?></div></section></div>
<div class="section-title" id="memory"><h2>World Memory</h2><small>AUTHORITATIVE HISTORY · FAILURE LEAVES A TRACE</small></div><div class="grid"><section class="card half"><h2>Recent Echoes</h2><?php foreach($events as $e): ?><div class="item"><div class="item-head"><strong><?=h($e['title'])?></strong><?php if((int)$e['global_visibility']): ?><span class="tag legendary">WORLD</span><?php endif; ?></div><div class="small"><?=h($e['summary'])?></div><small class="muted"><?=h($e['created_at'])?></small></div><?php endforeach; if(!$events): ?><div class="muted">The world is waiting for its first meaningful memory.</div><?php endif; ?></section><section class="card half"><h2>Lost Relics</h2><p class="muted">Relics are born from important failures. They are not deleted loot; they become things the world can remember and eventually return.</p><?php foreach($relics as $r): ?><div class="item"><div class="item-head"><strong><?=h($r['name'])?></strong><span class="rarity <?=rarityClass((string)$r['rarity'])?>"><?=h($r['rarity'])?></span></div><div class="small muted">Power <?=$r['power_rating']?> · unresolved</div></div><?php endforeach; if(!$relics): ?><div class="muted">No unresolved relic echoes.</div><?php endif; ?></section></div>
<div class="section-title" id="wilds"><h2>Living Wilds</h2><small>OPTIONAL ECOSYSTEM MODE</small></div><div class="grid"><section class="card wide"><p class="muted">The Wilds runs beside the main progression loop. Its simulated entities persist as a separate expedition and can become a future resource/discovery layer.</p><?php if(!$livingRun): ?><form method="post"><input type="hidden" name="action" value="living_world_start"><button class="btn primary" type="submit">Enter the Living Wilds</button></form><?php else: ?><div class="form-row"><strong>Expedition #<?=$livingRun['id']?></strong><span class="tag">TICK <?=$livingRun['ticks']?></span><span class="tag"><?=h($livingRun['status'])?></span><form method="post"><input type="hidden" name="action" value="living_world_tick"><input type="hidden" name="run_id" value="<?=$livingRun['id']?>"><button type="submit">Advance Ecosystem</button></form></div><div class="wild-map" style="margin-top:12px"><?php foreach($livingEntities as $i=>$entity): $x=max(3,min(95,(int)$entity['position_x']%100));$y=max(5,min(92,(int)$entity['position_y']%100)); ?><span class="entity-dot" title="<?=h($entity['name'])?>" style="left:<?=$x?>%;top:<?=$y?>%"></span><?php endforeach; ?><div style="position:absolute;left:12px;bottom:10px;color:#718070;font-size:11px">ECOSYSTEM TELEMETRY · <?=count($livingEntities)?> ENTITIES</div></div><?php foreach($livingEntities as $entity): ?><div class="item"><strong><?=h($entity['name'])?></strong> <span class="tag"><?=h($entity['species'])?></span><span class="small muted">Energy <?=$entity['energy']?> · <?=h($entity['status'])?></span></div><?php endforeach; ?><?php endif; ?></section></div>
<footer class="footer">SHADOW SHINOBI · ORIGINAL FICTIONAL SETTING · COMMAND DECK PHASE 01</footer>
</div></body></html>
