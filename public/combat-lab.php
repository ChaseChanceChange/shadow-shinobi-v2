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

$pdo = Database::connection();
$ops = $pdo->query("SELECT name,class_name,role_name,level,health,attack,defense,skill_power,speed FROM operatives ORDER BY id LIMIT 4")->fetchAll();
$ops = $ops ?: [
    ['name'=>'Sable Kest','class_name'=>'Shadowblade','role_name'=>'Striker','level'=>1,'health'=>100,'attack'=>28,'defense'=>14,'skill_power'=>24,'speed'=>22],
    ['name'=>'Rook Vale','class_name'=>'Iron Veil','role_name'=>'Guardian','level'=>1,'health'=>130,'attack'=>18,'defense'=>28,'skill_power'=>12,'speed'=>12],
];
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Shadow Shinobi — Combat Lab</title>
<style>
:root{--bg:#05070a;--panel:#0d1219;--line:#263241;--text:#eef2f5;--muted:#7e8998;--blood:#b64a55;--steel:#b8c4d1;--gold:#d5ae62}
*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at 50% 0,#1b212b 0,#07090d 42%,#030405 100%);color:var(--text);font:14px/1.5 system-ui,Segoe UI,sans-serif}.wrap{max-width:1200px;margin:auto;padding:18px}.head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}.brand{font-weight:950;letter-spacing:.16em}.link{color:#9eabba;text-decoration:none}.arena{border:1px solid var(--line);border-radius:18px;overflow:hidden;background:linear-gradient(180deg,#101721,#070a0e);box-shadow:0 20px 70px rgba(0,0,0,.35)}.sky{height:74px;background:linear-gradient(180deg,rgba(70,79,92,.16),transparent)}.stage{position:relative;min-height:430px;padding:26px;display:grid;grid-template-columns:1fr 180px 1fr;gap:20px;align-items:center;background:radial-gradient(circle at 50% 65%,rgba(115,38,45,.18),transparent 28%),linear-gradient(180deg,transparent 25%,rgba(0,0,0,.45))}.side{text-align:center}.unit{width:180px;margin:auto;padding:14px;border:1px solid #303b4b;background:rgba(8,11,16,.76);border-radius:15px;backdrop-filter:blur(4px)}.unit.enemy{border-color:#53313a}.sigil{width:98px;height:98px;margin:0 auto 10px;border-radius:18px;background:linear-gradient(145deg,#273443,#0c1118);display:grid;place-items:center;font-size:42px;font-weight:950}.enemy .sigil{background:linear-gradient(145deg,#40262c,#12090b)}.hp{height:8px;margin:9px 0;background:#1d2530;border-radius:20px;overflow:hidden}.hp i{display:block;width:100%;height:100%;background:#7eb18e;transition:width .2s}.enemy .hp i{background:var(--blood)}.meta{color:var(--muted);font-size:11px}.versus{height:180px;display:grid;place-items:center;font-weight:950;font-size:26px;letter-spacing:.15em;color:#8390a0}.versus span{font-size:10px;color:#4f5c6d;letter-spacing:.22em}.log{position:absolute;left:50%;bottom:22px;transform:translateX(-50%);width:min(680px,calc(100% - 32px));min-height:58px;padding:12px 16px;border:1px solid #313d4d;border-radius:11px;background:rgba(5,7,10,.92);text-align:center;box-shadow:0 10px 35px rgba(0,0,0,.32)}.numbers{position:absolute;inset:0;pointer-events:none;overflow:hidden}.dmg{position:absolute;font-size:28px;font-weight:950;color:#fff;text-shadow:0 3px 18px #000;animation:rise .8s ease-out forwards}.crit{color:#f2c56d;font-size:34px}.blood{color:#ec7a83}@keyframes rise{0%{opacity:0;transform:translate(-50%,8px) scale(.75)}20%{opacity:1;transform:translate(-50%,0) scale(1.05)}100%{opacity:0;transform:translate(-50%,-85px) scale(.95)}}.shake{animation:shake .16s linear}@keyframes shake{0%,100%{transform:translate(0)}25%{transform:translate(5px,-2px)}50%{transform:translate(-4px,2px)}75%{transform:translate(3px,1px)}}.impact{position:absolute;left:50%;top:55%;width:30px;height:30px;border-radius:50%;border:3px solid #e5edf5;opacity:0;transform:translate(-50%,-50%);pointer-events:none}.impact.flash{animation:flash .25s ease-out}@keyframes flash{0%{opacity:.9;transform:translate(-50%,-50%) scale(.4)}100%{opacity:0;transform:translate(-50%,-50%) scale(7)}}.actions{display:flex;flex-wrap:wrap;gap:9px;padding:16px;border-top:1px solid var(--line);background:#090d12}.actions button{border:1px solid #394656;border-radius:9px;background:#141b24;color:var(--text);padding:10px 14px;font-weight:850;cursor:pointer}.actions button:hover{background:#1b2531}.actions .finisher{border-color:#795052;color:#efb9bd}.timeline{display:flex;gap:8px;overflow:auto;padding:14px 16px;border-bottom:1px solid var(--line);background:#080b10}.turn{min-width:74px;border:1px solid #273140;border-radius:9px;padding:7px;text-align:center;font-size:10px;color:#a9b4c1}.turn.active{border-color:#abb8c8;box-shadow:0 0 0 1px #abb8c8 inset}.foot{padding:14px;color:#566272;font-size:11px}
@media(max-width:780px){.stage{grid-template-columns:1fr 90px 1fr;min-height:500px}.unit{width:145px}.sigil{width:78px;height:78px}.versus{font-size:18px}.log{bottom:12px}}
</style>
</head>
<body><div class="wrap">
<div class="head"><div class="brand">SHADOW // COMBAT LAB</div><a class="link" href="/">← Command</a></div>
<div class="arena" id="arena">
<div class="timeline"><div class="turn active">SABLE<br>TURN 01</div><div class="turn">WRAITH<br>TURN 02</div><div class="turn">ROOK<br>TURN 03</div><div class="turn">WRAITH<br>TURN 04</div><div class="turn">SABLE<br>TURN 05</div></div>
<div class="stage" id="stage">
<div class="side"><div class="unit" id="hero"><div class="sigil">S</div><strong><?=h((string)$ops[0]['name'])?></strong><div class="meta"><?=h((string)$ops[0]['class_name'])?> · <?=h((string)$ops[0]['role_name'])?></div><div class="hp"><i id="heroHp" style="width:100%"></i></div><div class="meta" id="heroText">HP <?=h((string)$ops[0]['health'])?> · READY</div></div></div>
<div class="versus">VS<span>TACTICAL ENCOUNTER</span></div>
<div class="side"><div class="unit enemy" id="enemy"><div class="sigil">W</div><strong>Wraith of the Red Vale</strong><div class="meta">Apex Hunter · Bleed</div><div class="hp"><i id="enemyHp" style="width:100%"></i></div><div class="meta" id="enemyText">HP 520 · THREAT 04</div></div></div>
<div class="numbers" id="numbers"></div><div class="impact" id="impact"></div><div class="log" id="log">The Wraith waits inside the ruined shrine. Choose an action.</div>
</div>
<div class="actions"><button onclick="attack(false)">Quick Strike</button><button onclick="attack(true)">Shadow Art</button><button onclick="guard()">Guard</button><button class="finisher" onclick="finisher()">Signature — Crimson Sever</button></div>
</div>
<div class="foot">Combat Lab prototype · visual effects are client-side presentation; final outcomes remain server-authoritative.</div>
</div>
<script>
const state={heroHp:<?= (int)$ops[0]['health']?>,heroMax:<?= (int)$ops[0]['health']?>,enemyHp:520,enemyMax:520,turn:0,guard:false};
const log=document.getElementById('log');const stage=document.getElementById('stage');const numbers=document.getElementById('numbers');
function fx(target,amount,crit=false,blood=false){const r=target.getBoundingClientRect();const s=stage.getBoundingClientRect();const n=document.createElement('div');n.className='dmg '+(crit?'crit ':'')+(blood?'blood':'');n.textContent=(crit?'CRIT ':'−')+amount;n.style.left=(r.left-s.left+r.width/2)+'px';n.style.top=(r.top-s.top+20)+'px';numbers.appendChild(n);setTimeout(()=>n.remove(),800);document.getElementById('impact').classList.remove('flash');void document.getElementById('impact').offsetWidth;document.getElementById('impact').classList.add('flash');stage.classList.remove('shake');void stage.offsetWidth;stage.classList.add('shake')}
function render(){document.getElementById('heroHp').style.width=Math.max(0,state.heroHp/state.heroMax*100)+'%';document.getElementById('enemyHp').style.width=Math.max(0,state.enemyHp/state.enemyMax*100)+'%';document.getElementById('heroText').textContent='HP '+state.heroHp+' · '+(state.guard?'GUARDING':'READY');document.getElementById('enemyText').textContent='HP '+state.enemyHp+' · '+(state.enemyHp<=0?'DEFEATED':'THREAT 04')}
function attack(skill){if(state.enemyHp<=0)return;state.turn++;let amount=skill?Math.floor(45+Math.random()*34):Math.floor(25+Math.random()*22);const crit=Math.random()<(skill?.22:.12);if(crit)amount=Math.floor(amount*1.8);state.enemyHp=Math.max(0,state.enemyHp-amount);fx(document.getElementById('enemy'),amount,crit,true);log.innerHTML='<b>'+ (skill?'SHADOW ART':'QUICK STRIKE')+'</b> lands for <b>'+amount+'</b> damage'+(crit?' — CRITICAL IMPACT!':'')+'.';enemyTurn()}
function guard(){state.guard=true;log.textContent='The shinobi lowers their stance. Incoming damage is reduced until the next attack.';enemyTurn()}
function finisher(){if(state.enemyHp<=0)return; if(state.enemyHp/state.enemyMax>.35){log.textContent='CRIMSON SEVER requires the enemy below 35% health.';return}state.enemyHp=0;fx(document.getElementById('enemy'),999,true,true);log.innerHTML='<b class="crit">CRIMSON SEVER</b> — execution sequence triggered. <span class="blood">The Wraith is torn apart.</span>';render()}
function enemyTurn(){setTimeout(()=>{if(state.enemyHp<=0){log.innerHTML+='<br><span class="crit">ENCOUNTER CLEARED.</span>';render();return}let amount=Math.floor(18+Math.random()*20);if(state.guard)amount=Math.floor(amount*.45);state.heroHp=Math.max(1,state.heroHp-amount);fx(document.getElementById('hero'),amount,false,true);state.guard=false;log.innerHTML+='<br>Wraith counterattack: <b>'+amount+'</b> damage.';render()},420);render()}
render();
</script></body></html>
