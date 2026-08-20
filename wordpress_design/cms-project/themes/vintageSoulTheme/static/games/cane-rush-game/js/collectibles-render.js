/* =========================================================
   CANE RUSH — collectibles-render.js
   Draws collectibles (sugarcane / coin / fruit) and power-up tokens.
   Each one prefers the real SVG art loaded by AssetManager and falls
   back to the original hand-drawn vector shape whenever that art
   isn't ready yet (or fails to load) — so nothing ever renders blank.
   ========================================================= */
'use strict';

const FRUIT_FALLBACK = {
  mango:      { body:'#FFB347', dark:'#E8641C', shape:'oval', r:22 },
  pineapple:  { body:COLORS.yellow, dark:'#6FAF3C', shape:'pine', r:22 },
  lemon:      { body:'#FFF066', dark:'#E0C400', shape:'oval', r:20 },
  coconut:    { body:'#EADFC4', dark:'#8B5A2B', shape:'circle', r:22 },
  watermelon: { body:'#FF5D6C', dark:'#3FA34D', shape:'circle', r:22 },
};

function drawCollectible(ctx, type, x, y, scale, bob, sub){
  ctx.save(); ctx.translate(x, y + Math.sin(bob)*4); ctx.scale(scale,scale);
  switch(type){
    case 'cane': {
      const variant = sub==='golden' ? 'golden' : (sub==='fresh' ? 'fresh' : 'normal');
      const img = AssetManager.get('collectible-cane-'+variant);
      if(img){
        const H=90, W=H*(150/230);
        ctx.drawImage(img, -W/2, 26-H, W, H);
      } else {
        const c = sub==='golden' ? COLORS.yellow : (sub==='fresh' ? '#B6F27A' : COLORS.lime);
        ctx.save(); ctx.rotate(0.15);
        ctx.fillStyle=c; roundRect(ctx,-8,-34,16,60,8); ctx.fill();
        ctx.strokeStyle='rgba(0,0,0,0.18)'; ctx.lineWidth=2;
        for(let i=0;i<4;i++){ ctx.beginPath(); ctx.moveTo(-8,-24+i*14); ctx.lineTo(8,-24+i*14); ctx.stroke(); }
        ctx.fillStyle='#6FAF3C';
        ctx.beginPath(); ctx.moveTo(0,-34); ctx.quadraticCurveTo(-10,-56,0,-64); ctx.quadraticCurveTo(10,-50,0,-34); ctx.fill();
        ctx.restore();
      }
      if(sub==='golden'){
        ctx.save(); ctx.globalAlpha=0.7; ctx.fillStyle='#fff';
        drawStar(ctx,-4,-10,4,4); drawStar(ctx,6,6,3,4);
        ctx.restore();
      }
      break;
    }
    case 'coin': {
      const img = AssetManager.get('collectible-coin');
      if(img){
        ctx.drawImage(img, -20,-20,40,40);
      } else {
        const grad = ctx.createLinearGradient(-14,-14,14,14);
        grad.addColorStop(0,'#FFEA9E'); grad.addColorStop(1,COLORS.yellow);
        ctx.fillStyle=grad;
        ctx.beginPath(); ctx.arc(0,0,15,0,Math.PI*2); ctx.fill();
        ctx.strokeStyle='#E8AC1A'; ctx.lineWidth=3; ctx.stroke();
        ctx.fillStyle='#E8AC1A'; ctx.font="800 16px 'Baloo 2', sans-serif"; ctx.textAlign='center'; ctx.textBaseline='middle';
        ctx.fillText('C',0,1);
      }
      break;
    }
    case 'mango': case 'pineapple': case 'lemon': case 'coconut': case 'watermelon': {
      const img = AssetManager.get('collectible-'+type);
      if(img){
        ctx.drawImage(img, -24,-24,48,48);
      } else {
        const f = FRUIT_FALLBACK[type];
        drawFruitIcon(ctx, f.body, f.dark, f.r, f.shape);
      }
      break;
    }
  }
  ctx.restore();
}
function drawFruitIcon(ctx,body,dark,r,shape){
  ctx.fillStyle=body;
  if(shape==='circle'){ ctx.beginPath(); ctx.arc(0,0,r,0,Math.PI*2); ctx.fill(); ctx.strokeStyle=dark; ctx.lineWidth=2; ctx.stroke(); }
  else if(shape==='oval'){ ctx.beginPath(); ctx.ellipse(0,0,r*0.85,r,0.2,0,Math.PI*2); ctx.fill(); }
  else if(shape==='pine'){
    ctx.beginPath(); ctx.ellipse(0,4,r*0.75,r*0.9,0,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=dark;
    ctx.beginPath(); ctx.moveTo(0,-r*0.5); ctx.quadraticCurveTo(-8,-r*1.5,0,-r*1.8); ctx.quadraticCurveTo(8,-r*1.5,0,-r*0.5); ctx.fill();
  }
  ctx.fillStyle='rgba(255,255,255,0.5)';
  ctx.beginPath(); ctx.ellipse(-r*0.25,-r*0.25,r*0.28,r*0.18,0.4,0,Math.PI*2); ctx.fill();
}

/* ---------------- POWER-UP TOKENS ---------------- */
function drawPowerupToken(ctx, key, x, y, scale, bob){
  const meta = POWERUP_META[key];
  ctx.save(); ctx.translate(x, y+Math.sin(bob)*5); ctx.scale(scale,scale);
  const img = AssetManager.get('powerup-'+key);
  if(img){
    ctx.save();
    ctx.shadowColor=meta.color; ctx.shadowBlur=14;
    ctx.drawImage(img, -26,-26,52,52);
    ctx.restore();
  } else {
    ctx.save();
    ctx.shadowColor=meta.color; ctx.shadowBlur=18;
    ctx.fillStyle=meta.color;
    ctx.beginPath(); ctx.arc(0,0,24,0,Math.PI*2); ctx.fill();
    ctx.restore();
    ctx.fillStyle='rgba(255,255,255,0.9)';
    ctx.beginPath(); ctx.arc(0,0,18,0,Math.PI*2); ctx.fill();
    ctx.font='24px sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillText(meta.icon,0,2);
  }
  ctx.restore();
}
