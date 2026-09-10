<?php
declare(strict_types=1);
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Shadow Shinobi // Night Hunt</title>
<style>
:root{--bg:#040609;--panel:#0b1017;--panel2:#111823;--line:#273241;--text:#edf1f5;--muted:#758092;--red:#bc4d58;--ember:#d6a859;--green:#79a98a;--blue:#6f93bb}
*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at 50% 5%,#232a34 0,#090c11 36%,#020305 100%);color:var(--text);font:14px/1.45 Inter,system-ui,Segoe UI,sans-serif}.wrap{max-width:1280px;margin:auto;padding:18px}.top{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}.brand{font-weight:950;letter-spacing:.16em}.top a{color:#a7b1bf;text-decoration:none}.arena{border:1px solid var(--line);border-radius:18px;overflow:hidden;background:rgba(7,10,14,.94);box-shadow:0 30px 100px rgba(0,0,0,.48)}.encounterbar{padding:12px 15px;border-bottom:1px solid var(--line);background:#080b10;display:flex;justify-content:space-between;gap:10px;align-items:center}.encounterbar strong{letter-spacing:.08em}.encounterbar span{color:#9ba7b5;font-size:11px}.turnbar{display:flex;gap:8px;padding:11px 12px;border-bottom:1px solid var(--line);overflow:auto;background:#070a0e}.turn{min-width:96px;padding:8px 10px;border:1px solid #27303c;border-radius:10px;background:#0b1016;color:#8e9aab;text-align:center;font-size:10px}.turn.active{border-color:#c0cad5;color:#f3f6f8;box-shadow:0 0 0 1px #aeb9c5 inset}.turn.enemy{border-color:#503039}.battle{position:relative;min-height:480px;padding:28px 22px 105px;display:grid;grid-template-columns:1fr 1.35fr 1fr;gap:18px;align-items:center;background:radial-gradient(circle at 50% 68%,rgba(150,48,58,.18),transparent 30%),linear-gradient(180deg,rgba(70,78,90,.08),transparent 25%,rgba(0,0,0,.56)),repeating-linear-gradient(175deg,rgba(255,255,255,.018) 0 2px,transparent 2px 10px)}.battle:before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 18% 35%,rgba(170,180,190,.045) 0 1px,transparent 1px),radial-gradient(circle at 76% 28%,rgba(170,180,190,.04) 0 1px,transparent 1px);background-size:75px 58px,91px 67px;pointer-events:none}.side{position:relative;z-index:2}.stack{display:grid;gap:10px;max-width:280px;margin:auto}.card{position:relative;border:1px solid #2c3744;border-radius:15px;padding:12px;background:linear-gradient(150deg,rgba(22,29,39,.96),rgba(7,10,15,.96));box-shadow:0 18px 45px rgba(0,0,0,.28);transition:transform .2s,border-color .2s,opacity .2s}.card.active{border-color:#c0cad5;transform:translateY(-2px);box-shadow:0 0 0 1px rgba(192,202,213,.12) inset,0 18px 45px rgba(0,0,0,.3)}.card.down{opacity:.42;filter:grayscale(.7)}.card.bleeding{border-color:#7a4148}.unithead{display:flex;gap:10px;align-items:center}.sigil{width:52px;height:52px;border-radius:13px;border:1px solid #394858;background:linear-gradient(145deg,#334353,#0b1017);display:grid;place-items:center;font-weight:950;font-size:22px}.boss .sigil{background:linear-gradient(145deg,#4a2b31,#12080a);border-color:#684047}.name{font-weight:900}.sub{font-size:10px;color:var(--muted)}.badge{display:inline-block;margin-top:5px;padding:2px 6px;border:1px solid #394452;border-radius:999px;font-size:9px;color:#9da8b7}.badge.bleed{border-color:#7a4148;color:#d98189}.badge.guard{border-color:#526b86;color:#9ab4d2}.bar{height:8px;margin:9px 0 4px;border-radius:20px;background:#202833;overflow:hidden}.bar i{display:block;height:100%;background:var(--green);width:100%;transition:width .25s}.boss .bar i{background:var(--red)}.energy i{background:var(--blue)}.stats{display:flex;justify-content:space-between;color:#7e8998;font-size:10px}.center{text-align:center;position:relative;z-index:2}.vs{font-weight:950;font-size:34px;letter-spacing:.18em;color:#9ba7b5}.threat{margin-top:4px;color:#586474;font-size:10px;letter-spacing:.22em}.log{margin:26px auto 0;max-width:560px;padding:14px 16px;border:1px solid #2d3846;border-radius:11px;background:rgba(4,6,9,.91);box-shadow:0 12px 40px rgba(0,0,0,.35);min-height:68px}.victory{border-color:#6d593a;box-shadow:0 0 30px rgba(214,168,89,.07),0 12px 40px rgba(0,0,0,.35)}.reward{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:10px}.reward span{padding:5px 8px;border:1px solid #4a3d29;border-radius:8px;color:#e1c180;background:#16120d;font-size:10px;font-weight:800}.fx{position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:5}.float{position:absolute;font-size:30px;font-weight:950;text-shadow:0 4px 18px #000;animation:rise .86s ease-out forwards}.float.crit{font-size:36px;color:var(--ember)}.float.blood{color:#ed7a84}.float.good{color:#b9d6c1}.slash{position:absolute;width:180px;height:8px;border-radius:20px;background:linear-gradient(90deg,transparent,#e6edf4,transparent);transform:rotate(-25deg);opacity:0;animation:slash .28s ease-out forwards}.flash{position:absolute;left:50%;top:55%;width:24px;height:24px;border:3px solid #eef3f6;border-radius:50%;opacity:0;animation:flash .28s ease-out forwards}.controls{display:flex;flex-wrap:wrap;gap:9px;padding:15px;border-top:1px solid var(--line);background:#080c11}.controls button{border:1px solid #364351;border-radius:10px;background:#141b24;color:#edf1f5;padding:11px 15px;font-weight:850;cursor:pointer}.controls button:hover{background:#1c2632}.controls button:disabled{opacity:.38;cursor:not-allowed}.controls .danger{border-color:#71454a;color:#f0bdc2}.note{padding:11px 15px;color:#5b6777;font-size:11px;border-top:1px solid #1e2630}@keyframes rise{0%{opacity:0;transform:translate(-50%,12px) scale(.75)}20%{opacity:1;transform:translate(-50%,0) scale(1.05)}100%{opacity:0;transform:translate(-50%,-90px) scale(.95)}}@keyframes slash{0%{opacity:0;transform:translateX(-70px) rotate(-25deg) scaleX(.4)}20%{opacity:.95}100%{opacity:0;transform:translateX(90px) rotate(-25deg) scaleX(1.4)}}@keyframes flash{0%{opacity:.9;transform:translate(-50%,-50%) scale(.4)}100%{opacity:0;transform:translate(-50%,-50%) scale(8)}}.shake{animation:shake .16s linear}@keyframes shake{0%,100%{transform:translate(0)}25%{transform:translate(5px,-2px)}50%{transform:translate(-4px,2px)}75%{transform:translate(3px,1px)}}@media(max-width:850px){.battle{grid-template-columns:1fr;min-height:780px}.center{order:-1}.stack{max-width:360px}.log{margin-top:16px}.vs{font-size:26px}.encounterbar{align-items:flex-start;flex-direction:column}}
</style>
</head>
<body><div class="wrap">
<div class="top"><div class="brand">SHADOW // NIGHT HUNT</div><a href="/">← Command Deck</a></div>
<div class="arena">
<div class="encounterbar"><div><strong id="encounterName">NIGHT HUNT</strong><div><span id="encounterKey">Loading encounter</span></div></div><span id="encounterThreat">THREAT —</span></div>
<div id="turnbar" class="turnbar"></div>
<div id="battle" class="battle">
<div class="side"><div id="allies" class="stack"></div></div>
<div class="center"><div class="vs">VS</div><div class="threat">TACTICAL ENCOUNTER // SERVER AUTHORITATIVE</div><div id="log" class="log">Loading the ruined shrine…</div></div>
<div class="side"><div id="enemy"></div></div>
<div id="fx" class="fx"></div>
</div>
<div class="controls">
<button data-action="quick">Quick Strike</button><button data-action="shadow">Shadow Art · 25</button><button data-action="guard">Guard · +10</button><button data-action="signature" class="danger">Crimson Sever · 100</button><button id="restart">New Hunt</button>
</div><div class="note">Damage, crits, initiative, status and victory are resolved by the PHP combat engine. The browser only presents the resulting event stream.</div>
</div></div>
<script>
let battle=null,busy=false;
const $=s=>document.querySelector(s);
const esc=s=>String(s).replace(/[&<>\"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','\\':'&#92;','"':'&quot;'}[c]));
async function request(action=null){
  busy=true; buttons();
  try{const r=await fetch('/api/combat.php',{method:action?'POST':'GET',headers:action?{'Content-Type':'application/json'}:{},body:action?JSON.stringify({action}):undefined});const data=await r.json();if(!data.ok)throw new Error(data.error||'Combat error');battle=data.battle;render();animate(battle.events_tail||[]);}catch(e){$('#log').textContent=e.message}finally{busy=false;buttons();}
}
function render(){
  const units=Object.values(battle.units), allies=units.filter(u=>u.side==='ally'), enemy=units.find(u=>u.side==='enemy');
  $('#encounterName').textContent=battle.encounter?.name||enemy?.name||'NIGHT HUNT';
  $('#encounterKey').textContent=(battle.encounter?.key||'random')+' · SHADOW HUNT';
  $('#encounterThreat').textContent='THREAT '+String(battle.encounter?.threat??'?').padStart(2,'0');
  $('#allies').innerHTML=allies.map(u=>card(u,false)).join('');
  $('#enemy').innerHTML=enemy?card(enemy,true):'';
  const order=units.filter(u=>u.hp>0).sort((a,b)=>b.speed-a.speed);$('#turnbar').innerHTML=order.map(u=>`<div class="turn ${u.side==='enemy'?'enemy':''} ${u.id===battle.active_id?'active':''}">${esc(u.name)}<br>SPD ${u.speed}</div>`).join('');
  const active=units.find(u=>u.id===battle.active_id);
  if(battle.status==='victory'){$('#log').className='log victory';$('#log').innerHTML='<b style="color:#e1c180">HUNT COMPLETE</b><br>'+esc(battle.encounter?.name||enemy?.name||'The target')+' breaks apart beneath the shrine ash.<div class="reward"><span>+ Ancient Echo</span><span>+ Relic Trace</span><span>+ Combat Mastery</span></div>';}else if(battle.status==='defeat'){$('#log').className='log';$('#log').textContent='THE CELL HAS FALLEN.';}else{$('#log').className='log';$('#log').innerHTML=active?`<b>${esc(active.name)}</b> has initiative. Choose the kill route.`:'Resolving…';}
}
function card(u,boss=false){
  const hp=Math.max(0,u.hp/u.max_hp*100).toFixed(1), en=Math.max(0,u.energy/u.max_energy*100).toFixed(1);
  const classes=['card']; if(boss)classes.push('boss'); if(u.hp<=0)classes.push('down'); if(u.bleed)classes.push('bleeding'); if(u.id===battle.active_id)classes.push('active');
  const badges=[]; if(u.bleed)badges.push(`<span class="badge bleed">BLEED ×${u.bleed}</span>`); if(u.guard)badges.push(`<span class="badge guard">GUARD</span>`);
  return `<div class="${classes.join(' ')}" data-id="${esc(u.id)}"><div class="unithead"><div class="sigil">${esc(u.name.slice(0,1))}</div><div><div class="name">${esc(u.name)}</div><div class="sub">${esc(u.class)} · ${esc(u.role)}</div>${badges.join(' ')}</div></div><div class="bar"><i style="width:${hp}%"></i></div><div class="stats"><span>HP ${u.hp}/${u.max_hp}</span><span>SPD ${u.speed}</span></div><div class="bar energy"><i style="width:${en}%"></i></div><div class="stats"><span>ESSENCE ${u.energy}/${u.max_energy}</span><span>${u.guard?'GUARDING':'READY'}</span></div></div>`
}
function buttons(){
  document.querySelectorAll('[data-action]').forEach(b=>b.disabled=busy||!battle||battle.status!=='active'||battle.units[battle.active_id]?.side!=='ally');
  const sig=document.querySelector('[data-action="signature"]'); if(sig&&battle){const active=battle.units[battle.active_id];sig.disabled=sig.disabled||!active||active.energy<100;}
}
function targetCard(id){return document.querySelector(`[data-id="${CSS.escape(id)}"]`)}
function animate(events){if(!events.length)return;events.forEach((e,i)=>setTimeout(()=>fxEvent(e),i*220));}
function fxEvent(e){
  const target=e.data?.target; const actor=e.data?.actor; const unitId=target||actor; if(!unitId)return;
  const cardEl=targetCard(unitId); const rect=cardEl?.getBoundingClientRect(); const br=$('#battle').getBoundingClientRect(); if(!rect)return;
  if(e.type==='damage'||e.type==='enemy_attack'||e.type==='bleed_tick'){
    const n=document.createElement('div');n.className='float '+(e.data.critical?'crit ':'')+(e.type==='bleed_tick'?'blood':'');n.textContent=(e.data.critical?'CRIT ':'−')+e.data.amount;n.style.left=(rect.left-br.left+rect.width/2)+'px';n.style.top=(rect.top-br.top+22)+'px';$('#fx').appendChild(n);setTimeout(()=>n.remove(),900);
    const f=document.createElement('div');f.className='flash';$('#fx').appendChild(f);setTimeout(()=>f.remove(),300);$('#battle').classList.remove('shake');void $('#battle').offsetWidth;$('#battle').classList.add('shake');
  }
  if(e.type==='status'){const n=document.createElement('div');n.className='float blood';n.textContent='BLEED';n.style.left=(rect.left-br.left+rect.width/2)+'px';n.style.top=(rect.top-br.top+70)+'px';$('#fx').appendChild(n);setTimeout(()=>n.remove(),900)}
  if(e.type==='guard'){const n=document.createElement('div');n.className='float good';n.textContent='GUARD';n.style.left=(rect.left-br.left+rect.width/2)+'px';n.style.top=(rect.top-br.top+60)+'px';$('#fx').appendChild(n);setTimeout(()=>n.remove(),900)}
  if(e.type==='finisher'){for(let i=0;i<6;i++){const s=document.createElement('div');s.className='slash';s.style.left=(rect.left-br.left-20)+'px';s.style.top=(rect.top-br.top+8+i*20)+'px';$('#fx').appendChild(s);setTimeout(()=>s.remove(),350)}}
}
document.querySelectorAll('[data-action]').forEach(b=>b.addEventListener('click',()=>request(b.dataset.action)));$('#restart').addEventListener('click',()=>request());request();
</script>
</body></html>
