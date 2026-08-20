/* =========================================================
   CANE RUSH — world-render.js
   Everything that makes up the track itself: obstacles/hurdles,
   the scrolling sky/ground/road, and roadside decorations. Kept as
   hand-drawn canvas vector art (unchanged look) — only the
   collectibles/power-ups moved to real SVG art, see collectibles-render.js.
   ========================================================= */
'use strict';

function drawObstacle(ctx, type, x, y, scale){
  ctx.save(); ctx.translate(x,y); ctx.scale(scale,scale);
  switch(type){
    case 'fallenCane': {
      // a bundle of big cut sugarcane logs, same segmented look as the collectible
      const log = (dx,dy,rot,w,h,tone)=>{
        ctx.save(); ctx.translate(dx,dy); ctx.rotate(rot);
        const grad = ctx.createLinearGradient(0,-h/2,0,h/2);
        grad.addColorStop(0, lighten(tone,26)); grad.addColorStop(1, tone);
        ctx.fillStyle=grad;
        roundRect(ctx,-w/2,-h/2,w,h,h/2); ctx.fill();
        ctx.strokeStyle='rgba(46,42,31,0.28)'; ctx.lineWidth=2.5;
        for(let i=1;i<4;i++){ const lx=-w/2+w*(i/4); ctx.beginPath(); ctx.moveTo(lx,-h/2+2); ctx.lineTo(lx,h/2-2); ctx.stroke(); }
        // cut end cross-section
        ctx.fillStyle='#EFE6CE'; ctx.strokeStyle='rgba(46,42,31,0.35)'; ctx.lineWidth=2;
        ctx.beginPath(); ctx.ellipse(w/2-2,0,7,h*0.42,0,0,Math.PI*2); ctx.fill(); ctx.stroke();
        ctx.restore();
      };
      log(-8,4,-0.05,116,26,COLORS.limeDark);
      log(10,-22,0.06,96,22,COLORS.lime);
      break;
    }
    case 'crate': {
      const grad = ctx.createLinearGradient(0,-62,0,0);
      grad.addColorStop(0,'#F2BE72'); grad.addColorStop(1,'#E8A14A');
      ctx.fillStyle=grad;
      roundRect(ctx,-42,-62,84,62,10); ctx.fill();
      ctx.strokeStyle='#B97A2A'; ctx.lineWidth=5;
      roundRect(ctx,-42,-62,84,62,10); ctx.stroke();
      ctx.beginPath(); ctx.moveTo(-42,-62); ctx.lineTo(42,0); ctx.moveTo(42,-62); ctx.lineTo(-42,0); ctx.stroke();
      ctx.strokeStyle='rgba(46,42,31,0.25)'; ctx.lineWidth=3;
      ctx.strokeRect(-36,-56,72,50);
      break;
    }
    case 'puddle': {
      ctx.fillStyle='rgba(63,209,217,0.55)';
      ctx.beginPath(); ctx.ellipse(0,-4,44,12,0,0,Math.PI*2); ctx.fill();
      ctx.strokeStyle='rgba(255,255,255,0.7)'; ctx.lineWidth=2;
      ctx.beginPath(); ctx.ellipse(0,-4,30,7,0,0,Math.PI*2); ctx.stroke();
      break;
    }
    case 'coconutPile': {
      // big beach boulders
      const boulder=(cx,cy,r)=>{
        const grad = ctx.createRadialGradient(cx-r*0.35,cy-r*0.4,r*0.2,cx,cy,r);
        grad.addColorStop(0,'#A9967F'); grad.addColorStop(1,'#6B5A44');
        ctx.fillStyle=grad;
        ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2); ctx.fill();
        ctx.strokeStyle='rgba(46,42,31,0.3)'; ctx.lineWidth=2.5; ctx.stroke();
        ctx.fillStyle='rgba(255,255,255,0.22)';
        ctx.beginPath(); ctx.arc(cx-r*0.32,cy-r*0.34,r*0.32,0,Math.PI*2); ctx.fill();
      };
      boulder(-24,-16,28); boulder(24,-16,28); boulder(0,-50,30);
      break;
    }
    case 'barrier': case 'archway': {
      ctx.fillStyle = type==='archway' ? '#8B6FE0' : '#E8A14A';
      roundRect(ctx,-60,-152,120,30,8); ctx.fill();
      ctx.fillStyle = type==='archway' ? '#6B4FC0' : '#B97A2A';
      roundRect(ctx,-60,-152,120,12,5); ctx.fill();
      ctx.strokeStyle='rgba(46,42,31,0.25)'; ctx.lineWidth=2;
      for(let i=-3;i<=3;i++){ ctx.beginPath(); ctx.moveTo(i*16,-152); ctx.lineTo(i*16,-122); ctx.stroke(); }
      // wood-grain wavy lines for the wooden barrier
      if(type==='barrier'){
        ctx.strokeStyle='rgba(46,42,31,0.18)'; ctx.lineWidth=1.5;
        ctx.beginPath(); ctx.moveTo(-54,-140); ctx.quadraticCurveTo(0,-134,54,-140); ctx.stroke();
      }
      // posts
      ctx.fillStyle='rgba(0,0,0,0.14)';
      roundRect(ctx,-66,-124,12,128,4); ctx.fill();
      roundRect(ctx,54,-124,12,128,4); ctx.fill();
      break;
    }
    case 'cart': {
      const grad = ctx.createLinearGradient(0,-92,0,-16);
      grad.addColorStop(0,'#FFA25E'); grad.addColorStop(1,COLORS.orange);
      ctx.fillStyle=grad;
      roundRect(ctx,-52,-92,104,64,12); ctx.fill();
      ctx.strokeStyle='#C24E10'; ctx.lineWidth=3;
      roundRect(ctx,-52,-92,104,64,12); ctx.stroke();
      ctx.fillStyle=COLORS.coconut;
      roundRect(ctx,-44,-84,88,26,7); ctx.fill();
      [-26,0,26].forEach((dx,i)=>{
        ctx.fillStyle=[COLORS.yellow,COLORS.mango,'#FFF066'][i];
        ctx.beginPath(); ctx.arc(dx,-72,11,0,Math.PI*2); ctx.fill();
        ctx.fillStyle='rgba(255,255,255,0.4)';
        ctx.beginPath(); ctx.arc(dx-3,-75,3.5,0,Math.PI*2); ctx.fill();
      });
      ctx.strokeStyle='#B9611C'; ctx.lineWidth=5;
      ctx.beginPath(); ctx.moveTo(-46,-26); ctx.lineTo(-46,0); ctx.moveTo(46,-26); ctx.lineTo(46,0); ctx.stroke();
      ctx.fillStyle='#2E2A1F';
      [-30,30].forEach(dx=>{ ctx.beginPath(); ctx.arc(dx,2,10,0,Math.PI*2); ctx.fill(); ctx.fillStyle='#6B5A44'; ctx.beginPath(); ctx.arc(dx,2,4,0,Math.PI*2); ctx.fill(); ctx.fillStyle='#2E2A1F'; });
      break;
    }
    case 'truck': {
      const grad = ctx.createLinearGradient(0,-118,0,-18);
      grad.addColorStop(0,'#6BE0E6'); grad.addColorStop(1,COLORS.aqua);
      ctx.fillStyle=grad;
      roundRect(ctx,-62,-118,124,96,12); ctx.fill();
      ctx.strokeStyle=COLORS.aquaDark; ctx.lineWidth=3;
      roundRect(ctx,-62,-118,124,96,12); ctx.stroke();
      ctx.fillStyle=COLORS.coconut;
      roundRect(ctx,-52,-108,104,36,7); ctx.fill();
      ctx.fillStyle='rgba(255,255,255,0.5)';
      roundRect(ctx,-46,-66,92,12,5); ctx.fill();
      ctx.fillStyle='#2E2A1F';
      [-36,36].forEach(dx=>{ ctx.beginPath(); ctx.arc(dx,2,14,0,Math.PI*2); ctx.fill(); ctx.fillStyle='#6B5A44'; ctx.beginPath(); ctx.arc(dx,2,5.5,0,Math.PI*2); ctx.fill(); ctx.fillStyle='#2E2A1F'; });
      break;
    }
    case 'stall': {
      ctx.fillStyle=COLORS.hotpink;
      roundRect(ctx,-58,-128,116,24,6); ctx.fill();
      for(let i=-3;i<=2;i++){ ctx.beginPath(); ctx.moveTo(i*20-10,-104); ctx.lineTo(i*20+10,-104); ctx.lineTo(i*20,-80); ctx.closePath(); ctx.fillStyle=i%2===0?COLORS.hotpink:COLORS.coconut; ctx.fill(); }
      const grad = ctx.createLinearGradient(0,-80,0,0);
      grad.addColorStop(0,'#F2D9A0'); grad.addColorStop(1,'#E8C98A');
      ctx.fillStyle=grad;
      roundRect(ctx,-54,-80,108,80,4); ctx.fill();
      ctx.strokeStyle='rgba(46,42,31,0.2)'; ctx.lineWidth=2; ctx.strokeRect(-54,-80,108,80);
      [-24,0,24].forEach((dx,i)=>{ ctx.fillStyle=[COLORS.mango,COLORS.lime,COLORS.yellow][i]; ctx.beginPath(); ctx.arc(dx,-48,13,0,Math.PI*2); ctx.fill(); });
      break;
    }
    case 'bottleStack': {
      const drawBottle=(dx,dy,c)=>{
        const grad = ctx.createLinearGradient(dx-14,dy-52,dx+14,dy);
        grad.addColorStop(0, lighten(c,22)); grad.addColorStop(1, c);
        ctx.fillStyle=grad; roundRect(ctx,dx-15,dy-50,30,50,10); ctx.fill();
        ctx.strokeStyle='rgba(46,42,31,0.25)'; ctx.lineWidth=2; roundRect(ctx,dx-15,dy-50,30,50,10); ctx.stroke();
        ctx.fillStyle=COLORS.coconut; roundRect(ctx,dx-6,dy-62,12,14,4); ctx.fill();
      };
      drawBottle(-20,-2,'#D9E86B'); drawBottle(20,-2,'#FFB347'); drawBottle(0,-58,'#7CE0C0');
      break;
    }
  }
  ctx.restore();
}

/* ---------------- ENVIRONMENT BACKGROUND ---------------- */
function drawEnvironment(ctx, proj, env, nextEnv, envBlend){
  const w=proj.w, h=proj.h;
  // sky
  const skyA = env.sky, skyB = nextEnv.sky;
  const s0 = lerpColor(skyA[0], skyB[0], envBlend);
  const s1 = lerpColor(skyA[1], skyB[1], envBlend);
  const grad = ctx.createLinearGradient(0,0,0,proj.horizonY);
  grad.addColorStop(0,s0); grad.addColorStop(1,s1);
  ctx.fillStyle=grad; ctx.fillRect(0,0,w,proj.horizonY);

  // sun
  ctx.save();
  ctx.globalAlpha=0.85;
  ctx.fillStyle='rgba(255,255,255,0.55)';
  ctx.beginPath(); ctx.arc(w*0.82, proj.horizonY*0.35, 46,0,Math.PI*2); ctx.fill();
  ctx.restore();

  // ground
  const gA = env.ground, gB = nextEnv.ground;
  ctx.fillStyle = lerpColor(gA,gB,envBlend);
  ctx.fillRect(0,proj.horizonY,w,h-proj.horizonY);

  // road trapezoid
  const rA = env.road, rB = nextEnv.road;
  ctx.fillStyle = lerpColor(rA,rB,envBlend);
  const topL = proj.x(-1.35, 0), topR = proj.x(1.35,0);
  const botL = proj.x(-1.35, 1), botR = proj.x(1.35,1);
  ctx.beginPath(); ctx.moveTo(topL,proj.horizonY); ctx.lineTo(topR,proj.horizonY);
  ctx.lineTo(botR,proj.groundY); ctx.lineTo(botL,proj.groundY); ctx.closePath(); ctx.fill();

  // wooden boardwalk texture: plank seams + alternating shade bands,
  // tinted by whatever the current biome's road color is (so every
  // environment reads as a wooden walkway, not just the brown ones)
  const PLANKS = 16;
  ctx.save();
  for(let i=0;i<PLANKS;i++){
    const z0=i/PLANKS, z1=(i+1)/PLANKS;
    if(i%2===1){
      const tl=proj.x(-1.35,z0), tr=proj.x(1.35,z0), bl=proj.x(-1.35,z1), br=proj.x(1.35,z1);
      const ty=proj.y(z0), by=proj.y(z1);
      ctx.fillStyle='rgba(46,42,31,0.07)';
      ctx.beginPath(); ctx.moveTo(tl,ty); ctx.lineTo(tr,ty); ctx.lineTo(br,by); ctx.lineTo(bl,by); ctx.closePath(); ctx.fill();
    }
  }
  for(let i=1;i<PLANKS;i++){
    const z=i/PLANKS;
    const lx=proj.x(-1.35,z), rx=proj.x(1.35,z), sy=proj.y(z);
    ctx.strokeStyle='rgba(46,42,31,0.22)';
    ctx.lineWidth = lerp(0.6,2.6,z);
    ctx.beginPath(); ctx.moveTo(lx,sy); ctx.lineTo(rx,sy); ctx.stroke();
  }
  ctx.restore();

  // lane dividers
  ctx.strokeStyle='rgba(255,255,255,0.5)';
  [-0.5,0.5].forEach(lo=>{
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(proj.x(lo,0), proj.horizonY);
    ctx.lineTo(proj.x(lo,1), proj.groundY);
    ctx.stroke();
  });
}

function drawDeco(ctx, deco, x, y, scale, envDecoType){
  ctx.save(); ctx.translate(x,y); ctx.scale(scale,scale);
  ctx.globalAlpha=0.95;
  if(envDecoType==='farm'){
    ctx.fillStyle=COLORS.lime;
    for(let i=0;i<5;i++){ ctx.save(); ctx.translate(-20+i*10,0); ctx.rotate((i-2)*0.08);
      roundRect(ctx,-4,-70,8,70,4); ctx.fill(); ctx.restore(); }
  } else if(envDecoType==='road'){
    ctx.fillStyle='#3FA34D'; roundRect(ctx,-6,-90,12,90,6); ctx.fill();
    ctx.fillStyle='#5FA82C'; ctx.beginPath(); ctx.arc(0,-95,26,0,Math.PI*2); ctx.fill();
  } else if(envDecoType==='garden'){
    ctx.fillStyle=COLORS.hotpink; ctx.beginPath(); ctx.arc(0,-40,20,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=COLORS.yellow; ctx.beginPath(); ctx.arc(-14,-20,14,0,Math.PI*2); ctx.fill();
  } else if(envDecoType==='factory'){
    ctx.fillStyle=COLORS.aqua; roundRect(ctx,-16,-90,32,90,6); ctx.fill();
    ctx.fillStyle=COLORS.coconut; roundRect(ctx,-16,-100,32,14,4); ctx.fill();
  } else if(envDecoType==='beach'){
    ctx.strokeStyle='#8B5A2B'; ctx.lineWidth=8; ctx.beginPath(); ctx.moveTo(0,0); ctx.quadraticCurveTo(10,-60,0,-110); ctx.stroke();
    ctx.fillStyle='#3FA34D';
    for(let i=-1;i<=1;i++){ ctx.save(); ctx.rotate(i*0.7); ctx.beginPath(); ctx.ellipse(0,-120,22,10,0,0,Math.PI*2); ctx.fill(); ctx.restore(); }
  } else if(envDecoType==='market'){
    ctx.fillStyle=COLORS.purple; roundRect(ctx,-20,-70,40,70,6); ctx.fill();
    ctx.fillStyle=COLORS.coconut; roundRect(ctx,-20,-84,40,16,4); ctx.fill();
  } else if(envDecoType==='festival'){
    ctx.fillStyle=COLORS.red; ctx.beginPath(); ctx.moveTo(0,-90); ctx.lineTo(-14,-60); ctx.lineTo(14,-60); ctx.closePath(); ctx.fill();
    ctx.fillStyle=COLORS.yellow; roundRect(ctx,-4,-60,8,60,4); ctx.fill();
  }
  ctx.restore();
}
