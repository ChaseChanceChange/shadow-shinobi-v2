<?php
declare(strict_types=1);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Shadow Shinobi — The Ashen Frontier</title>
<style>
:root{--bg:#08090b;--panel:#101216;--line:#262a32;--text:#e8e9ed;--muted:#898e9b;--accent:#c92336;--accent2:#e0a23a;--good:#68c27f;}
*{box-sizing:border-box}html,body{margin:0;min-height:100%;background:radial-gradient(circle at 50% 10%,#171a21 0,#08090b 55%);color:var(--text);font-family:Inter,Segoe UI,Arial,sans-serif}body{overflow:hidden}
.app{height:100vh;display:grid;grid-template-rows:62px 1fr;}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 18px;background:rgba(9,10,13,.94);border-bottom:1px solid var(--line);z-index:5;backdrop-filter:blur(12px)}
.brand{display:flex;gap:12px;align-items:center}.sigil{width:34px;height:34px;border:1px solid #3a3e47;border-radius:8px;display:grid;place-items:center;color:var(--accent);font-weight:900;font-size:18px;box-shadow:0 0 22px rgba(201,35,54,.15)}.brand b{letter-spacing:.14em;font-size:12px}.brand span{display:block;color:var(--muted);font-size:11px;margin-top:3px}.stats{display:flex;gap:18px;align-items:center;font-size:12px;color:var(--muted)}.stat strong{display:block;color:#f1f2f5;font-size:13px;margin-top:2px}.back{color:#cfd2da;text-decoration:none;border:1px solid var(--line);border-radius:7px;padding:8px 12px}.back:hover{border-color:#4a4f59}
.main{position:relative;min-height:0}.world-shell{position:absolute;inset:14px;display:grid;grid-template-columns:minmax(0,1fr) 310px;gap:14px}.viewport{position:relative;min-width:0;min-height:0;border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#0a0b0d;box-shadow:0 18px 60px rgba(0,0,0,.45)}canvas{width:100%;height:100%;display:block;image-rendering:pixelated}.vignette{pointer-events:none;position:absolute;inset:0;background:radial-gradient(circle at 50% 45%,transparent 48%,rgba(0,0,0,.55) 100%)}
.zone-banner{position:absolute;left:18px;top:16px;padding:10px 12px;background:rgba(7,8,10,.74);border:1px solid rgba(255,255,255,.08);border-radius:8px;backdrop-filter:blur(6px)}.zone-banner b{display:block;font-size:14px;letter-spacing:.04em}.zone-banner span{display:block;color:var(--muted);font-size:11px;margin-top:4px}.coords{position:absolute;right:18px;top:16px;color:#9ba1ac;font:11px ui-monospace,SFMono-Regular,Consolas,monospace;background:rgba(7,8,10,.7);padding:8px 10px;border-radius:7px;border:1px solid rgba(255,255,255,.06)}
.panel{border:1px solid var(--line);border-radius:12px;background:linear-gradient(180deg,#111318,#0d0f13);padding:16px;display:flex;flex-direction:column;gap:14px;min-height:0;box-shadow:0 18px 60px rgba(0,0,0,.25)}.panel h1{font-size:19px;margin:0;letter-spacing:.02em}.eyebrow{font-size:10px;letter-spacing:.18em;color:var(--accent2);text-transform:uppercase}.lore{font-size:12px;color:#9ca1ac;line-height:1.5}.card{border:1px solid #252932;border-radius:9px;background:#0b0d10;padding:12px}.card b{font-size:13px}.card p{font-size:11px;color:#8f949e;line-height:1.45;margin:7px 0 0}.meter{height:7px;background:#1b1e24;border-radius:99px;overflow:hidden;margin-top:8px}.meter i{display:block;height:100%;width:100%;background:linear-gradient(90deg,#8f1d2c,#e33a4d)}.actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}.btn{border:1px solid #2a2e37;background:#15181e;color:#dfe2e8;border-radius:7px;padding:9px 8px;font-weight:700;font-size:11px;cursor:pointer}.btn:hover{border-color:#515763;background:#1a1e25}.btn.primary{border-color:#6e1d28;background:#351219}.btn:disabled{opacity:.45;cursor:not-allowed}.feed{flex:1;min-height:120px;overflow:auto;border-top:1px solid #20232a;padding-top:8px}.feed div{font-size:11px;color:#8f949f;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.035)}.feed .hot{color:#e9a3aa}.poi{min-height:84px}.controls{font-size:10px;color:#707581;text-align:center;line-height:1.5}
.encounter{position:absolute;left:50%;bottom:22px;transform:translateX(-50%);display:none;min-width:360px;max-width:90%;padding:14px 16px;border:1px solid #76202c;border-radius:10px;background:rgba(29,8,11,.95);box-shadow:0 14px 40px rgba(0,0,0,.55);z-index:4}.encounter.show{display:flex;align-items:center;justify-content:space-between;gap:16px}.encounter strong{display:block;color:#fff}.encounter span{display:block;color:#c99ca1;font-size:11px;margin-top:4px}.encounter a{white-space:nowrap;text-decoration:none;color:#fff;background:#9f2334;padding:9px 12px;border-radius:7px;font-size:11px;font-weight:800}
@media(max-width:980px){.world-shell{grid-template-columns:1fr}.panel{position:absolute;right:12px;top:12px;width:290px;max-height:calc(100% - 24px);opacity:.96}.viewport{margin-right:0}.stats{display:none}}@media(max-width:640px){.world-shell{inset:8px}.panel{width:250px}.encounter{min-width:0;width:92%}}
</style>
</head>
<body>
<div class="app">
  <header class="topbar">
    <div class="brand"><div class="sigil">刃</div><div><b>SHADOW SHINOBI</b><span>THE ASHEN FRONTIER</span></div></div>
    <div class="stats"><div class="stat">COINS<strong id="coins">—</strong></div><div class="stat">GEAR FRAGMENTS<strong id="fragments">—</strong></div><a class="back" href="/">Command Deck</a></div>
  </header>
  <main class="main">
    <div class="world-shell">
      <section class="viewport" id="viewport">
        <canvas id="world" aria-label="The Ashen Frontier overworld"></canvas>
        <div class="vignette"></div>
        <div class="zone-banner"><b id="zoneName">Loading frontier…</b><span id="zoneDesc">The world is waking.</span></div>
        <div class="coords" id="coords">x— y—</div>
        <div class="encounter" id="encounter"><div><strong id="encounterName">Hostile contact</strong><span id="encounterText">Something is hunting the cell.</span></div><a id="fight" href="#">ENTER COMBAT</a></div>
      </section>
      <aside class="panel">
        <div><div class="eyebrow">Persistent overworld</div><h1>Explore the Ashen Frontier</h1></div>
        <div class="lore">The province did not end when the old shinobi orders fell. It became something else. Cross the ruins, find what survived, and decide what deserves to be brought back.</div>
        <div class="card"><b id="commander">Commander</b><div class="meter"><i id="energyBar"></i></div><p id="energyText">Energy 100 / 100</p></div>
        <div class="card poi"><div class="eyebrow">Nearby landmark</div><b id="poiName">None</b><p id="poiText">Move toward a marked location to reveal it.</p></div>
        <div class="actions"><button class="btn" id="interact">INTERACT</button><button class="btn primary" id="hq">RETURN TO HQ</button></div>
        <div class="controls"><b>WASD / ARROW KEYS</b><br>One tile per authoritative server move<br>Explore landmarks to reveal them on your world record.</div>
        <div class="feed" id="feed"></div>
      </aside>
    </div>
  </main>
</div>
<script>
const canvas=document.getElementById('world'),ctx=canvas.getContext('2d');
const TILE=24; let world=null,state=null,commander=null,cam={x:0,y:0},keys=new Set(),moveTimer=0,moving=false;
const $=id=>document.getElementById(id);
function hash(x,y){let n=(x*374761393+y*668265263)>>>0;n=(n^(n>>>13))*1274126177;n^=n>>>16;return (n>>>0)/4294967295}
function roadCells(){const s=new Set();for(const r of world.roads){let x=r.x1,y=r.y1,dx=Math.sign(r.x2-r.x1),dy=Math.sign(r.y2-r.y1);s.add(x+','+y);while(x!==r.x2||y!==r.y2){if(x!==r.x2)x+=dx;if(y!==r.y2)y+=dy;s.add(x+','+y)}}return s}
let roads;
function zoneAt(x,y){for(const z of world.zones)if(x>=z.x&&x<z.x+z.w&&y>=z.y&&y<z.y+z.h)return z;return {key:'frontier',name:'Outer Frontier',type:'frontier',description:'Unmapped ground at the edge of the known province.'}}
function blocked(x,y){const b=[[4,38,9,4],[18,37,2,9],[27,48,4,6],[34,39,3,7],[41,34,5,3],[49,48,4,6],[53,36,4,5],[62,45,3,6],[72,38,4,4],[83,47,5,5],[20,8,5,5],[28,21,6,4],[36,7,4,6],[43,17,5,4],[51,5,6,4],[60,22,7,5],[68,7,5,5],[84,9,4,6],[75,27,8,3]];for(const [bx,by,bw,bh]of b)if(x>=bx&&x<bx+bw&&y>=by&&y<by+bh)return true;if(x>=55&&x<=57&&y>=14&&y<=26&&y%2===0)return true;if(x>=71&&x<=73&&y>=4&&y<=28&&y%3!==0)return true;return false}
function resize(){const r=$('viewport').getBoundingClientRect(),d=Math.min(2,devicePixelRatio||1);canvas.width=Math.floor(r.width*d);canvas.height=Math.floor(r.height*d);canvas.style.width=r.width+'px';canvas.style.height=r.height+'px';ctx.setTransform(d,0,0,d,0,0);draw()}
function tilePalette(type){return({safe:['#282c2a','#303733'],ruins:['#24262a','#2d2e31'],wild:['#1e2924','#26342c'],district:['#1b2226','#222b30'],relic:['#29242c','#332a36'],void:['#171b27','#20243a'],frontier:['#202225','#27292d']})[type]||['#202225','#27292d']}
function drawTerrain(px,py,z,x,y){const [a,b]=tilePalette(z.type),h=hash(x,y);ctx.fillStyle=(h>.62?b:a);ctx.fillRect(px,py,TILE+1,TILE+1);if(roads.has(x+','+y)){ctx.fillStyle=z.type==='void'?'#2d2a31':'#403d3a';ctx.fillRect(px+3,py+3,TILE-6,TILE-6);ctx.fillStyle='rgba(255,255,255,.035)';ctx.fillRect(px+7,py+6,2,TILE-12)}if(z.type==='wild'&&h>.76){ctx.fillStyle='#111813';ctx.fillRect(px+10,py+4,4,15);ctx.fillRect(px+5,py+8,14,4);ctx.fillStyle='#17221b';ctx.fillRect(px+7,py+3,10,5)}if(z.type==='district'&&h>.72){ctx.fillStyle='#101419';ctx.fillRect(px+4,py+4,16,15);ctx.fillStyle='#383844';ctx.fillRect(px+6,py+6,12,2);ctx.fillRect(px+6,py+10,8,2)}if(z.type==='ruins'&&h>.7){ctx.fillStyle='#121419';ctx.fillRect(px+5,py+6,13,13);ctx.fillStyle='#46444a';ctx.fillRect(px+7,py+4,7,3)}if(z.type==='void'&&h>.68){ctx.strokeStyle='rgba(125,91,168,.55)';ctx.beginPath();ctx.moveTo(px+3,py+17);ctx.lineTo(px+9,py+7);ctx.lineTo(px+15,py+16);ctx.lineTo(px+22,py+5);ctx.stroke()}}
function drawLandmark(l,ox,oy){const x=l.x*TILE-ox+TILE/2,y=l.y*TILE-oy+TILE/2;ctx.save();ctx.translate(x,y);ctx.shadowBlur=14;ctx.shadowColor=l.kind==='boss'?'#9f2334':'#d7a447';ctx.fillStyle=l.kind==='boss'?'#8b2232':l.kind==='relic'?'#b18a3e':'#c5c8cf';ctx.strokeStyle='#0c0d10';ctx.lineWidth=3;ctx.beginPath();ctx.arc(0,0,l.kind==='hq'?8:6,0,Math.PI*2);ctx.fill();ctx.stroke();ctx.shadowBlur=0;ctx.fillStyle='#0c0d10';ctx.font='bold 9px Arial';ctx.textAlign='center';ctx.fillText(l.kind==='hq'?'H':l.kind==='boss'?'!':l.kind==='relic'?'R':l.kind==='shrine'?'S':'•',0,3);ctx.restore()}
function drawPlayer(ox,oy){const x=state.position_x*TILE-ox+TILE/2,y=state.position_y*TILE-oy+TILE/2;ctx.save();ctx.translate(x,y);ctx.shadowBlur=18;ctx.shadowColor='#d0273e';ctx.fillStyle='rgba(210,39,62,.18)';ctx.beginPath();ctx.arc(0,2,14,0,Math.PI*2);ctx.fill();ctx.shadowBlur=0;ctx.fillStyle='#0a0b0e';ctx.beginPath();ctx.moveTo(-7,9);ctx.lineTo(-5,-5);ctx.lineTo(0,-10);ctx.lineTo(5,-5);ctx.lineTo(8,9);ctx.closePath();ctx.fill();ctx.strokeStyle='#bfc4cc';ctx.lineWidth=2;ctx.beginPath();ctx.moveTo(-3,-6);ctx.lineTo(4,-8);ctx.stroke();ctx.strokeStyle='#c62b3f';ctx.beginPath();ctx.moveTo(-4,9);ctx.lineTo(7,1);ctx.stroke();ctx.fillStyle='#fff';ctx.fillRect(-3,-4,2,1);ctx.fillRect(2,-5,2,1);ctx.restore()}
function drawMinimap(){const w=232,h=145,x=16,y=canvas.clientHeight-h-16;ctx.save();ctx.globalAlpha=.92;ctx.fillStyle='rgba(5,6,8,.82)';ctx.fillRect(x-6,y-6,w+12,h+12);ctx.strokeStyle='#343943';ctx.strokeRect(x-6,y-6,w+12,h+12);const sx=w/world.width,sy=h/world.height;for(let gy=0;gy<world.height;gy++)for(let gx=0;gx<world.width;gx++){const z=zoneAt(gx,gy);ctx.fillStyle=tilePalette(z.type)[0];ctx.fillRect(x+gx*sx,y+gy*sy,Math.ceil(sx),Math.ceil(sy))}ctx.fillStyle='#dc3246';ctx.fillRect(x+state.position_x*sx-2,y+state.position_y*sy-2,4,4);for(const l of world.landmarks){ctx.fillStyle=l.kind==='boss'?'#c92336':'#d1a447';ctx.fillRect(x+l.x*sx-1,y+l.y*sy-1,3,3)}ctx.restore()}
function draw(){if(!world||!state)return;const W=canvas.clientWidth,H=canvas.clientHeight;const maxX=world.width*TILE-W,maxY=world.height*TILE-H;cam.x=Math.max(0,Math.min(maxX,state.position_x*TILE-W/2));cam.y=Math.max(0,Math.min(maxY,state.position_y*TILE-H/2));ctx.clearRect(0,0,W,H);const sx=Math.floor(cam.x/TILE),sy=Math.floor(cam.y/TILE),ex=Math.min(world.width,Math.ceil((cam.x+W)/TILE)+1),ey=Math.min(world.height,Math.ceil((cam.y+H)/TILE)+1);for(let y=sy;y<ey;y++)for(let x=sx;x<ex;x++)drawTerrain(x*TILE-cam.x,y*TILE-cam.y,zoneAt(x,y),x,y);for(const l of world.landmarks)drawLandmark(l,cam.x,cam.y);drawPlayer(cam.x,cam.y);drawMinimap()}
function addFeed(text,hot=false){const el=document.createElement('div');if(hot)el.className='hot';el.textContent=text;$('feed').prepend(el);while($('feed').children.length>8)$('feed').lastChild.remove()}
function nearby(){let best=null,bd=999;for(const l of world.landmarks){const d=Math.hypot(state.position_x-l.x,state.position_y-l.y);if(d<=l.radius&&d<bd){best=l;bd=d}}if(best){$('poiName').textContent=best.name;$('poiText').textContent=best.description}else{$('poiName').textContent='No landmark in range';$('poiText').textContent='Keep exploring. Hidden locations become part of your persistent discovery record.'}}
async function api(payload=null){const opts=payload?{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}:{};const r=await fetch('/api/world.php',opts);const j=await r.json();if(!j.ok)throw new Error(j.error||'World request failed');world=j.map;state=j.state;commander=j.commander;return j}
function refreshUI(j){$('coins').textContent=commander.coins.toLocaleString();$('fragments').textContent=commander.gear_fragments.toLocaleString();$('commander').textContent=commander.name||'Commander';$('zoneName').textContent=state.zone.name;$('zoneDesc').textContent=state.zone.description;$('coords').textContent=`x${state.position_x} y${state.position_y}`;const pct=Math.max(0,Math.min(100,state.energy));$('energyBar').style.width=pct+'%';$('energyText').textContent=`Energy ${state.energy} / 100`;nearby();if(state.message)addFeed(state.message,!!j?.state?.encounter);draw()}
async function move(dx,dy){if(moving)return;moving=true;try{const j=await api({action:'move',dx,dy});refreshUI(j);if(state.encounter){$('encounterName').textContent=state.encounter.name;$('encounterText').textContent=`Threat ${state.encounter.threat} • ${state.encounter.reason}`;$('fight').href='/combat.php?encounter='+encodeURIComponent(state.encounter.key)+'&from=world';$('encounter').classList.add('show');addFeed('HOSTILE CONTACT: '+state.encounter.name,true)}else{$('encounter').classList.remove('show')}}catch(e){addFeed(e.message,true)}finally{moving=false}}
async function interact(){try{const j=await api({action:'interact'});refreshUI(j)}catch(e){addFeed(e.message,true)}}
function tryKey(k){if(k==='w'||k==='arrowup')move(0,-1);else if(k==='s'||k==='arrowdown')move(0,1);else if(k==='a'||k==='arrowleft')move(-1,0);else if(k==='d'||k==='arrowright')move(1,0);else if(k==='e')interact()}
window.addEventListener('keydown',e=>{const k=e.key.toLowerCase();if(['w','a','s','d','arrowup','arrowdown','arrowleft','arrowright','e'].includes(k)){e.preventDefault();tryKey(k)}});
$('interact').addEventListener('click',interact);
$('hq').addEventListener('click',async()=>{if(!state)return;while(state.position_x!==12||state.position_y!==46){const dx=Math.sign(12-state.position_x),dy=Math.sign(46-state.position_y);await move(Math.abs(dx)>0?dx:0,Math.abs(dx)>0?0:dy);if(state.encounter)break}});
window.addEventListener('resize',resize);
(async()=>{try{const j=await api();roads=roadCells();refreshUI(j);addFeed('The frontier is persistent. Your position is stored server-side.');resize()}catch(e){addFeed(e.message,true)}})();
</script>
</body>
</html>
