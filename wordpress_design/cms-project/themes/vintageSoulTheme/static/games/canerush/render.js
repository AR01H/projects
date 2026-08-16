/* =========================================================
   CANE RUSH — render.js
   Everything that draws pixels. No game logic lives here.
   ========================================================= */
'use strict';

/* ---------------- PROJECTION (fake-3D lane system) ----------------
   Operates in VIRTUAL coordinates (see Game.resize()): width varies
   by device aspect ratio, height is always REF_HEIGHT. This keeps
   the perspective math resolution-independent. */
class Projector{
  constructor(vw, vh){ this.w=vw; this.h=vh; this.recalc(); }
  recalc(){
    this.horizonY = this.h*HORIZON_Y_RATIO;
    this.groundY = this.h*0.98;
    this.centerX = this.w/2;
    this.topHalfLane = this.w*0.03;
    this.bottomHalfLane = this.w*0.34;
  }
  // z: 0 (far/horizon) -> 1 (near/player). lane: -1,0,1 (can be fractional)
  laneHalfWidth(z){ return lerp(this.topHalfLane, this.bottomHalfLane, z); }
  x(lane, z){ return this.centerX + lane*this.laneHalfWidth(z); }
  y(z){ return lerp(this.horizonY, this.groundY, z); }
  scale(z){ return lerp(0.16, 1.0, z); }
}

/* ---------------- CHARACTER RENDERING ---------------- */
function limbTransform(ctx, pivotX, pivotY, angleDeg){
  ctx.save(); ctx.translate(pivotX,pivotY); ctx.rotate(angleDeg*Math.PI/180);
}

/* Draws a full runner character. state: run/jump/slide/hit/idle. phase: 0..1 cycling */
function drawRunner(ctx, charId, x, groundY, scale, state, phase, opts={}){
  const vis = CHAR_VISUALS[charId] || CHAR_VISUALS.cane;
  ctx.save();
  ctx.translate(x, groundY);
  ctx.scale(scale, scale);

  let squash=1, stretch=1, bodyLift=0, tilt=0;
  const swing = Math.sin(phase*Math.PI*2);
  const swing2 = Math.sin(phase*Math.PI*2 + Math.PI);

  if(state==='run'){
    // Leaning, high-knee sprint — matches the reference sheet's "Start Sprint" energy
    // instead of a stiff upright jog.
    bodyLift = Math.abs(Math.sin(phase*Math.PI*2))*7;
    squash = 1 - Math.abs(swing)*0.035; stretch = 1 + Math.abs(swing)*0.035;
    tilt = 7;
  } else if(state==='jump'){
    // Knees tucked, arms flared out to the sides — "Double Jump" pose.
    squash = 1.05; stretch = 0.97; bodyLift = 9; tilt = -3;
  } else if(state==='slide'){
    squash = 0.5; stretch = 1.32; bodyLift = -16; tilt = 9;
  } else if(state==='hit'){
    tilt = 18; squash=0.9; stretch=1.05;
  }

  ctx.rotate(tilt*Math.PI/180);
  ctx.translate(0, -bodyLift);

  const BW=44*stretch, BH=58*squash; // body box
  const armSwingAng  = state==='run' ? swing*30-14  : (state==='jump'? -68 : (state==='slide'? -58:0));
  const armSwingAng2 = state==='run' ? swing2*30-14 : (state==='jump'?  68 : (state==='slide'?  22:0));
  const legSwingAng  = state==='run' ? swing*52  : (state==='jump'? -68 : (state==='slide'? 10:0));
  const legSwingAng2 = state==='run' ? swing2*52 : (state==='jump'? -68 : (state==='slide'? -8:0));

  // --- legs ---
  ctx.fillStyle = vis.dark;
  if(state==='slide'){
    // Low, wide duck — both legs splayed out symmetrically rather than mid-stride.
    drawLimb(ctx, -20, BH*0.4, 11, 22, -18, true);
    drawLimb(ctx, 20, BH*0.4, 11, 22, 18, true);
  } else if(state==='jump'){
    // Knees tucked up under the body — "Double Jump" pose.
    drawLimb(ctx, -14, BH*0.3, 10, 19, -112, true);
    drawLimb(ctx, 14, BH*0.3, 10, 19, -68, true);
  } else {
    drawLimb(ctx, -12, BH*0.42, 10, 26, legSwingAng, true);
    drawLimb(ctx, 12, BH*0.42, 10, 26, legSwingAng2, true);
  }

  // --- scarf tail (drawn behind the body, trails opposite the motion) ---
  if(charId==='cane') drawScarfTail(ctx, BW, BH, phase, state);

  // --- back arm ---
  ctx.fillStyle = vis.dark;
  drawLimb(ctx, -BW*0.62, -BH*0.06, 8, 22, armSwingAng2*0.6+6, false);

  // --- body shape (depends on char) ---
  drawBodyShape(ctx, vis, BW, BH, state);

  // --- scarf neck wrap (sits on top of the shoulders) ---
  if(charId==='cane') drawScarfKnot(ctx, BW, BH);

  // --- face ---
  drawFace(ctx, vis, BH, state);

  // --- front arm ---
  ctx.fillStyle = vis.body==='#FFF066' ? '#E0C400' : vis.dark;
  drawLimb(ctx, BW*0.62, -BH*0.06, 8, 22, armSwingAng*0.6, false);

  // --- front leg ---
  ctx.fillStyle = vis.dark;
  if(state!=='slide' && state!=='jump'){
    drawLimb(ctx, 12, BH*0.42, 10, 26, legSwingAng2*0.4, true);
  }

  ctx.restore();
}

/* The flowing orange scarf is the mascot's signature accessory (see the
   reference sheet) — it trails behind the character opposite the direction
   of motion, with a light flutter tied to the run phase. */
const SCARF_COLOR = '#E8935B';
const SCARF_DARK = '#B8622E';
function drawScarfTail(ctx, BW, BH, phase, state){
  const flutter = state==='run' ? Math.sin(phase*Math.PI*4)*8 : 4;
  const reach = state==='slide' ? 0.5 : (state==='jump' ? 0.8 : 1);
  ctx.save();
  ctx.fillStyle = SCARF_COLOR;
  ctx.strokeStyle = SCARF_DARK; ctx.lineWidth=2;
  ctx.beginPath();
  ctx.moveTo(-BW*0.1, -BH*0.58);
  ctx.bezierCurveTo(-BW*0.5, -BH*0.62+flutter*0.3, -BW*0.75*reach, -BH*0.5+flutter, -BW*1.05*reach, -BH*0.22+flutter*0.8);
  ctx.bezierCurveTo(-BW*0.95*reach, -BH*0.1+flutter*0.6, -BW*0.8*reach, -BH*0.12+flutter*0.5, -BW*0.7*reach, -BH*0.26);
  ctx.bezierCurveTo(-BW*0.55, -BH*0.36, -BW*0.3, -BH*0.44, -BW*0.02, -BH*0.5);
  ctx.closePath();
  ctx.fill(); ctx.stroke();
  ctx.strokeStyle='rgba(255,255,255,0.4)'; ctx.lineWidth=3; ctx.lineCap='round';
  ctx.beginPath();
  ctx.moveTo(-BW*0.22,-BH*0.53);
  ctx.quadraticCurveTo(-BW*0.6*reach,-BH*0.44+flutter*0.5,-BW*0.9*reach,-BH*0.2+flutter*0.7);
  ctx.stroke();
  ctx.restore();
}
function drawScarfKnot(ctx, BW, BH){
  ctx.save();
  ctx.fillStyle = SCARF_COLOR;
  ctx.strokeStyle = 'rgba(59,42,30,0.3)'; ctx.lineWidth=1.5;
  roundRect(ctx, -BW*0.3, -BH*0.68, BW*0.6, BH*0.15, 6); ctx.fill(); ctx.stroke();
  ctx.fillStyle = SCARF_DARK;
  roundRect(ctx, -BW*0.06, -BH*0.6, BW*0.16, BH*0.22, 3); ctx.fill();
  ctx.restore();
}

function drawLimb(ctx, px, py, w, len, angleDeg, isLeg){
  ctx.save();
  ctx.translate(px,py);
  ctx.rotate(angleDeg*Math.PI/180);
  // Shoulder/hip bulge for a slightly more athletic silhouette (matches the
  // reference's stockier arms/legs rather than thin noodle limbs).
  ctx.beginPath(); ctx.arc(0, 3, w*0.72, 0, Math.PI*2); ctx.fill();
  roundRect(ctx, -w/2, 0, w, len, w/2);
  ctx.fill();
  ctx.beginPath(); ctx.ellipse(0,len, w*0.65, w*0.5,0,0,Math.PI*2); ctx.fill();
  ctx.restore();
}

function drawBodyShape(ctx, vis, BW, BH, state){
  ctx.save();
  const grad = ctx.createLinearGradient(0,-BH*0.7,0,BH*0.55);
  grad.addColorStop(0, lighten(vis.body,18));
  grad.addColorStop(1, vis.body);
  ctx.fillStyle = grad;

  if(vis.shape==='cane'){
    // Body: rounded cane cylinder with darker green segment bands, matching
    // the reference mascot (thecanehosuelog).
    roundRect(ctx, -BW*0.42, -BH*0.72, BW*0.84, BH*1.22, BW*0.42); ctx.fill();
    ctx.strokeStyle='rgba(0,0,0,0.22)'; ctx.lineWidth=4;
    for(let i=1;i<4;i++){ const yy=-BH*0.72+ (BH*1.22)*(i/4); ctx.beginPath(); ctx.moveTo(-BW*0.38,yy); ctx.lineTo(BW*0.38,yy); ctx.stroke(); }
    // Spiky leaf crown fanning out from the top of the head, like the mascot's hair.
    ctx.fillStyle=vis.accent;
    const leafN=5;
    for(let i=0;i<leafN;i++){
      const ang = -95 + i*(190/(leafN-1));
      ctx.save();
      ctx.translate(0,-BH*0.72);
      ctx.rotate(ang*Math.PI/180);
      ctx.beginPath();
      ctx.moveTo(-3,0);
      ctx.quadraticCurveTo(-7,-20,0,-32-(i%2)*4);
      ctx.quadraticCurveTo(7,-20,3,0);
      ctx.closePath(); ctx.fill();
      ctx.restore();
    }
  } else if(vis.shape==='pine'){
    ctx.beginPath(); ctx.ellipse(0,-BH*0.05,BW*0.46,BH*0.58,0,0,Math.PI*2); ctx.fill();
    ctx.strokeStyle='rgba(0,0,0,0.15)'; ctx.lineWidth=2;
    for(let i=-2;i<=2;i++){ ctx.beginPath(); ctx.moveTo(-BW*0.4,-BH*0.4+i*10); ctx.lineTo(BW*0.4,-BH*0.05+i*10); ctx.stroke(); }
    ctx.fillStyle=vis.accent;
    for(let i=-1;i<=1;i++){
      ctx.save(); ctx.translate(i*10,-BH*0.62); ctx.rotate(i*0.3);
      ctx.beginPath(); ctx.moveTo(0,0); ctx.quadraticCurveTo(-6,-24,0,-34); ctx.quadraticCurveTo(6,-24,0,0); ctx.fill();
      ctx.restore();
    }
  } else if(vis.shape==='mango'){
    ctx.beginPath(); ctx.ellipse(0,0,BW*0.48,BH*0.6,0.15,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=vis.accent; ctx.globalAlpha=0.5;
    ctx.beginPath(); ctx.ellipse(-BW*0.1,-BH*0.25,BW*0.22,BH*0.22,0.3,0,Math.PI*2); ctx.fill();
    ctx.globalAlpha=1;
    ctx.fillStyle='#6FAF3C';
    roundRect(ctx,-4,-BH*0.68,8,10,3); ctx.fill();
  } else if(vis.shape==='coco'){
    ctx.beginPath(); ctx.arc(0,0,BW*0.5,0,Math.PI*2); ctx.fill();
    ctx.fillStyle='rgba(255,255,255,0.4)';
    ctx.beginPath(); ctx.arc(-BW*0.15,-BH*0.18,BW*0.28,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=vis.dark;
    for(let i=0;i<3;i++){ ctx.beginPath(); ctx.arc(-4+i*8,-BH*0.58,3,0,Math.PI*2); ctx.fill(); }
  } else if(vis.shape==='lemon'){
    ctx.beginPath(); ctx.ellipse(0,0,BW*0.42,BH*0.62,0,0,Math.PI*2); ctx.fill();
    ctx.fillStyle=vis.dark;
    ctx.beginPath(); ctx.ellipse(0,-BH*0.6,5,7,0,0,Math.PI*2); ctx.fill();
    ctx.beginPath(); ctx.ellipse(0,BH*0.6,5,7,0,0,Math.PI*2); ctx.fill();
  } else if(vis.shape==='melon'){
    ctx.beginPath(); ctx.ellipse(0,0,BW*0.5,BH*0.6,0,0,Math.PI*2); ctx.fill();
    ctx.strokeStyle=vis.dark; ctx.lineWidth=5; ctx.lineCap='round';
    for(let i=-2;i<=2;i++){ ctx.beginPath(); ctx.moveTo(i*9,-BH*0.55); ctx.quadraticCurveTo(i*13,0,i*9,BH*0.55); ctx.stroke(); }
  }
  ctx.restore();
}

/* Sunglasses + big open grin — the mascot's signature confident look,
   shared across the whole cast for a consistent brand feel. */
function drawSunglasses(ctx, eyeY, crooked){
  ctx.save();
  if(crooked) ctx.rotate(0.22);
  ctx.fillStyle = COLORS.ink;
  roundRect(ctx, -25, eyeY-8, 19, 15, 6); ctx.fill();
  roundRect(ctx, 6, eyeY-8, 19, 15, 6); ctx.fill();
  ctx.fillRect(-6, eyeY-3, 12, 4);
  ctx.fillStyle='rgba(255,255,255,0.4)';
  ctx.beginPath(); ctx.ellipse(-18, eyeY-3, 4.5,3,0,0,Math.PI*2); ctx.fill();
  ctx.beginPath(); ctx.ellipse(13, eyeY-3, 4.5,3,0,0,Math.PI*2); ctx.fill();
  ctx.restore();
}
function drawGrin(ctx, eyeY){
  ctx.save();
  ctx.fillStyle = COLORS.cream;
  ctx.strokeStyle = COLORS.ink; ctx.lineWidth = 2.5;
  ctx.beginPath();
  ctx.moveTo(-15, eyeY+15);
  ctx.quadraticCurveTo(0, eyeY+31, 15, eyeY+15);
  ctx.quadraticCurveTo(0, eyeY+23, -15, eyeY+15);
  ctx.closePath();
  ctx.fill(); ctx.stroke();
  ctx.strokeStyle = 'rgba(59,42,30,0.5)'; ctx.lineWidth=1.4;
  ctx.beginPath(); ctx.moveTo(-11,eyeY+18); ctx.lineTo(11,eyeY+18); ctx.stroke();
  ctx.restore();
}

function drawFace(ctx, vis, BH, state){
  const eyeY = -BH*0.12;
  ctx.save();
  if(state==='hit'){
    drawSunglasses(ctx, eyeY, true);
    ctx.strokeStyle=COLORS.ink; ctx.lineWidth=2.5; ctx.lineCap='round';
    ctx.beginPath(); ctx.ellipse(0, eyeY+20, 8, 6, 0, 0, Math.PI*2); ctx.stroke();
  } else {
    drawSunglasses(ctx, eyeY, false);
    drawGrin(ctx, eyeY);
  }
  ctx.fillStyle='rgba(255,120,120,0.3)';
  [-1,1].forEach(s=>{ ctx.beginPath(); ctx.ellipse(s*22, eyeY+13, 6,4,0,0,Math.PI*2); ctx.fill(); });
  ctx.restore();
}

/* ---------------- OBSTACLES ----------------
   Obstacle art now lives in assets/svg/*.svg (see config.js
   ASSET_MANIFEST) — loaded once at boot by AssetManager and drawn
   here with drawImage. If an asset hasn't finished loading yet
   (first few frames only, files are tiny) we fall back to the
   original hand-drawn silhouette so the game never stalls waiting
   on network/disk.
   Every obstacle still gets: a dark ground-contact shadow, and a
   small action glyph that floats above it while `warning` is true
   (see ObstacleManager). Shape + glyph communicate what to do
   without relying on color alone. */
const OBSTACLE_ASSET_SCALE = 0.62; // converts the 140x160 svg viewBox into the previous obstacle's rough on-screen size
const OBSTACLE_GROUND_LINE = 146;  // shared "ground contact" row across the obstacle SVG set

function drawObstacle(ctx, type, x, y, scale, warning){
  ctx.save(); ctx.translate(x,y); ctx.scale(scale,scale);

  if(warning){
    const pulse = 1 + Math.sin(performance.now()/90)*0.05;
    ctx.save(); ctx.scale(pulse,pulse);
    ctx.shadowColor = COLORS.warn; ctx.shadowBlur = 22;
  }

  const def = OBSTACLE_CONFIG[type];
  const img = def && def.asset ? AssetManager.get(def.asset) : null;
  if(img){
    const s = OBSTACLE_ASSET_SCALE;
    ctx.drawImage(img, -70*s, -OBSTACLE_GROUND_LINE*s, 140*s, 160*s);
  } else {
    drawObstacleShape(ctx, type); // graceful fallback while the SVG streams in
  }

  if(warning) ctx.restore();
  ctx.restore();
}

function drawObstacleShape(ctx, type){
  switch(type){
    case 'fallenCane': case 'rollingCoconut': {
      if(type==='rollingCoconut'){
        ctx.fillStyle='#8B5A2B';
        ctx.beginPath(); ctx.arc(0,-24,26,0,Math.PI*2); ctx.fill();
        ctx.strokeStyle='#5A3714'; ctx.lineWidth=3; ctx.stroke();
        ctx.fillStyle='rgba(255,255,255,0.3)';
        ctx.beginPath(); ctx.arc(-8,-32,7,0,Math.PI*2); ctx.fill();
        break;
      }
      ctx.fillStyle=COLORS.lime;
      ctx.save(); ctx.rotate(-0.08);
      roundRect(ctx,-46,-16,92,20,10); ctx.fill();
      ctx.strokeStyle='rgba(0,0,0,0.35)'; ctx.lineWidth=2.5;
      ctx.stroke();
      for(let i=0;i<4;i++){ ctx.beginPath(); ctx.moveTo(-38+i*22,-16); ctx.lineTo(-38+i*22,4); ctx.stroke(); }
      ctx.restore();
      break;
    }
    case 'crate': {
      ctx.fillStyle='#E8A14A';
      roundRect(ctx,-32,-46,64,46,8); ctx.fill();
      ctx.strokeStyle='#7A4E12'; ctx.lineWidth=4;
      ctx.strokeRect(-32,-46,64,46);
      ctx.beginPath(); ctx.moveTo(-32,-46); ctx.lineTo(32,0); ctx.moveTo(32,-46); ctx.lineTo(-32,0); ctx.stroke();
      break;
    }
    case 'puddle': {
      ctx.fillStyle='rgba(63,209,217,0.6)';
      ctx.beginPath(); ctx.ellipse(0,-4,44,12,0,0,Math.PI*2); ctx.fill();
      ctx.strokeStyle='rgba(0,90,95,0.6)'; ctx.lineWidth=2.5;
      ctx.beginPath(); ctx.ellipse(0,-4,44,12,0,0,Math.PI*2); ctx.stroke();
      ctx.strokeStyle='rgba(255,255,255,0.7)'; ctx.lineWidth=2;
      ctx.beginPath(); ctx.ellipse(0,-4,30,7,0,0,Math.PI*2); ctx.stroke();
      break;
    }
    case 'coconutPile': {
      ctx.fillStyle='#8B5A2B';
      [[-18,-14,20],[18,-14,20],[0,-38,22]].forEach(([cx,cy,r])=>{
        ctx.beginPath(); ctx.arc(cx,cy,r,0,Math.PI*2); ctx.fill();
        ctx.strokeStyle='#4E2F10'; ctx.lineWidth=2; ctx.stroke();
      });
      ctx.fillStyle='rgba(255,255,255,0.25)';
      ctx.beginPath(); ctx.arc(-6,-42,7,0,Math.PI*2); ctx.fill();
      break;
    }
    case 'barrier': case 'archway': {
      ctx.fillStyle = type==='archway' ? '#8B6FE0' : '#E8A14A';
      roundRect(ctx,-52,-140,104,26,8); ctx.fill();
      ctx.strokeStyle='rgba(0,0,0,0.4)'; ctx.lineWidth=3;
      ctx.strokeRect(-52,-140,104,26);
      ctx.fillStyle = type==='archway' ? '#6B4FC0' : '#B97A2A';
      roundRect(ctx,-52,-140,104,10,5); ctx.fill();
      ctx.strokeStyle='rgba(0,0,0,0.15)'; ctx.lineWidth=2;
      for(let i=-2;i<=2;i++){ ctx.beginPath(); ctx.moveTo(i*18,-140); ctx.lineTo(i*18,-114); ctx.stroke(); }
      ctx.fillStyle='rgba(0,0,0,0.2)';
      roundRect(ctx,-58,-118,10,120,4); ctx.fill();
      roundRect(ctx,48,-118,10,120,4); ctx.fill();
      break;
    }
    case 'cart': {
      ctx.fillStyle=COLORS.orange;
      roundRect(ctx,-40,-70,80,50,10); ctx.fill();
      ctx.strokeStyle='rgba(0,0,0,0.3)'; ctx.lineWidth=3; ctx.strokeRect(-40,-70,80,50);
      ctx.fillStyle=COLORS.coconut;
      roundRect(ctx,-34,-64,68,20,6); ctx.fill();
      [-18,0,18].forEach((dx,i)=>{
        ctx.fillStyle=[COLORS.yellow,COLORS.mango,'#FFF066'][i];
        ctx.beginPath(); ctx.arc(dx,-56,8,0,Math.PI*2); ctx.fill();
      });
      ctx.strokeStyle='#B9611C'; ctx.lineWidth=4;
      ctx.beginPath(); ctx.moveTo(-36,-20); ctx.lineTo(-36,0); ctx.moveTo(36,-20); ctx.lineTo(36,0); ctx.stroke();
      ctx.fillStyle='#2E2A1F';
      [-24,24].forEach(dx=>{ ctx.beginPath(); ctx.arc(dx,2,8,0,Math.PI*2); ctx.fill(); });
      break;
    }
    case 'truck': {
      ctx.fillStyle=COLORS.aqua;
      roundRect(ctx,-52,-98,104,80,10); ctx.fill();
      ctx.strokeStyle='rgba(0,0,0,0.3)'; ctx.lineWidth=3; ctx.strokeRect(-52,-98,104,80);
      ctx.fillStyle=COLORS.coconut;
      roundRect(ctx,-44,-90,88,30,6); ctx.fill();
      ctx.fillStyle='rgba(255,255,255,0.5)';
      roundRect(ctx,-38,-56,76,10,4); ctx.fill();
      ctx.fillStyle='#2E2A1F';
      [-30,30].forEach(dx=>{ ctx.beginPath(); ctx.arc(dx,2,12,0,Math.PI*2); ctx.fill(); });
      break;
    }
    case 'stall': {
      ctx.fillStyle=COLORS.hotpink;
      roundRect(ctx,-50,-110,100,20,6); ctx.fill();
      for(let i=-2;i<=2;i++){ ctx.beginPath(); ctx.moveTo(i*20-10,-90); ctx.lineTo(i*20+10,-90); ctx.lineTo(i*20,-70); ctx.closePath(); ctx.fillStyle=i%2===0?COLORS.hotpink:COLORS.coconut; ctx.fill(); }
      ctx.fillStyle='#E8C98A';
      roundRect(ctx,-46,-70,92,60,4); ctx.fill();
      ctx.strokeStyle='rgba(0,0,0,0.3)'; ctx.lineWidth=3; ctx.strokeRect(-46,-70,92,60);
      [-20,0,20].forEach((dx,i)=>{ ctx.fillStyle=[COLORS.mango,COLORS.lime,COLORS.yellow][i]; ctx.beginPath(); ctx.arc(dx,-42,10,0,Math.PI*2); ctx.fill(); });
      break;
    }
    case 'bottleStack': {
      const drawBottle=(dx,dy,c)=>{
        ctx.fillStyle=c; roundRect(ctx,dx-12,dy-40,24,40,8); ctx.fill();
        ctx.strokeStyle='rgba(0,0,0,0.3)'; ctx.lineWidth=2; roundRect(ctx,dx-12,dy-40,24,40,8); ctx.stroke();
        ctx.fillStyle=COLORS.coconut; roundRect(ctx,dx-5,dy-50,10,12,3); ctx.fill();
      };
      drawBottle(-16,-2,'#D9E86B'); drawBottle(16,-2,'#FFB347'); drawBottle(0,-46,'#7CE0C0');
      break;
    }
  }
}

/* Small floating glyph above a warned obstacle showing the required
   action — shape + text, never color alone. */
const ACTION_GLYPH = { jump:'⬆', slide:'⬇', lane:'⬌' };
function drawWarningGlyph(ctx, action, x, y){
  ctx.save();
  ctx.translate(x, y);
  const bob = Math.sin(performance.now()/140)*4;
  ctx.translate(0, bob);
  ctx.globalAlpha = 0.55+Math.sin(performance.now()/90)*0.25;
  ctx.fillStyle = COLORS.warn;
  ctx.beginPath(); ctx.arc(0,0,15,0,Math.PI*2); ctx.fill();
  ctx.strokeStyle = COLORS.ink; ctx.lineWidth=2; ctx.stroke();
  ctx.fillStyle = COLORS.ink;
  ctx.font='900 16px Baloo, sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
  ctx.fillText(ACTION_GLYPH[action]||'!', 0, 1);
  ctx.restore();
}

/* ---------------- COLLECTIBLES ----------------
   Drawn from assets/svg/collectible-*.svg (vintage stamp badges),
   falling back to the original procedural icon if the asset hasn't
   loaded yet. */
const COLLECTIBLE_BADGE_DIAMETER = 46; // virtual units, matches the 112px circle inside the 140x160 viewBox
function drawImageBadge(ctx, assetKey, x, y, diameter){
  const img = AssetManager.get(assetKey);
  if(!img) return false;
  const s = diameter/112;
  ctx.drawImage(img, x-70*s, y-70*s, 140*s, 160*s);
  return true;
}

function drawCollectible(ctx, type, x, y, scale, bob, sub){
  ctx.save(); ctx.translate(x, y + Math.sin(bob)*4);
  const diameter = COLLECTIBLE_BADGE_DIAMETER*scale;
  let assetKey = null;
  if(type==='cane') assetKey = 'collectible-cane-'+(sub||'normal');
  else if(type==='coin') assetKey = 'collectible-coin';
  else assetKey = 'collectible-'+type; // mango/pineapple/lemon/coconut/watermelon
  const drew = drawImageBadge(ctx, assetKey, 0, 0, diameter);
  if(!drew){
    ctx.scale(scale,scale);
    switch(type){
      case 'cane': {
        const c = sub==='golden' ? COLORS.yellow : (sub==='fresh' ? '#B6D97A' : COLORS.lime);
        ctx.save(); ctx.rotate(0.15);
        ctx.fillStyle=c; roundRect(ctx,-8,-34,16,60,8); ctx.fill();
        ctx.restore();
        break;
      }
      case 'coin': {
        ctx.fillStyle=COLORS.yellow;
        ctx.beginPath(); ctx.arc(0,0,15,0,Math.PI*2); ctx.fill();
        break;
      }
      default: drawFruitIcon(ctx, COLORS.mango, COLORS.ink, 22, 'circle');
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

/* ---------------- POWERUP TOKENS ---------------- */
function drawPowerupToken(ctx, key, x, y, scale, bob){
  const meta = POWERUP_CONFIG[key]; if(!meta) return;
  ctx.save(); ctx.translate(x, y+Math.sin(bob)*5);
  const diameter = 48*scale;
  const drew = meta.asset ? drawImageBadge(ctx, meta.asset, 0, 0, diameter) : false;
  if(!drew){
    ctx.scale(scale,scale);
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

/* ---------------- ENVIRONMENT ---------------- */
function drawEnvironment(ctx, proj, env, nextEnv, envBlend){
  const w=proj.w, h=proj.h;
  const skyA = env.sky, skyB = nextEnv.sky;
  const s0 = lerpColor(skyA[0], skyB[0], envBlend);
  const s1 = lerpColor(skyA[1], skyB[1], envBlend);
  const grad = ctx.createLinearGradient(0,0,0,proj.horizonY);
  grad.addColorStop(0,s0); grad.addColorStop(1,s1);
  ctx.fillStyle=grad; ctx.fillRect(0,0,w,proj.horizonY);

  ctx.save();
  ctx.globalAlpha=0.85;
  ctx.fillStyle='rgba(255,255,255,0.55)';
  ctx.beginPath(); ctx.arc(w*0.82, proj.horizonY*0.35, 46,0,Math.PI*2); ctx.fill();
  ctx.restore();

  const gA = env.ground, gB = nextEnv.ground;
  ctx.fillStyle = lerpColor(gA,gB,envBlend);
  ctx.fillRect(0,proj.horizonY,w,h-proj.horizonY);

  const rA = env.road, rB = nextEnv.road;
  ctx.fillStyle = lerpColor(rA,rB,envBlend);
  const topL = proj.x(-1.35, 0), topR = proj.x(1.35,0);
  const botL = proj.x(-1.35, 1), botR = proj.x(1.35,1);
  ctx.beginPath(); ctx.moveTo(topL,proj.horizonY); ctx.lineTo(topR,proj.horizonY);
  ctx.lineTo(botR,proj.groundY); ctx.lineTo(botL,proj.groundY); ctx.closePath(); ctx.fill();

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

function drawShopBottle(ctx, color, x, y, alpha){
  ctx.save(); ctx.globalAlpha=alpha; ctx.translate(x,y);
  ctx.fillStyle='rgba(0,0,0,0.08)';
  ctx.beginPath(); ctx.ellipse(0,34,26,7,0,0,Math.PI*2); ctx.fill();
  ctx.fillStyle=color;
  roundRect(ctx,-20,-34,40,66,16); ctx.fill();
  ctx.strokeStyle='rgba(0,0,0,0.15)'; ctx.lineWidth=2;
  for(let i=0;i<3;i++){ ctx.beginPath(); ctx.moveTo(-18,-16+i*16); ctx.lineTo(18,-16+i*16); ctx.stroke(); }
  ctx.fillStyle=COLORS.coconut;
  roundRect(ctx,-9,-48,18,16,4); ctx.fill();
  ctx.fillStyle=COLORS.orange;
  roundRect(ctx,-9,-52,18,7,3); ctx.fill();
  ctx.fillStyle='rgba(255,255,255,0.55)';
  roundRect(ctx,-14,-26,7,40,4); ctx.fill();
  ctx.restore();
}

/* ---------------- DEBUG OVERLAY ---------------- */
function drawDebugHitbox(ctx, screenX, bottomScreenY, topScreenY, halfWidth, color){
  ctx.save();
  ctx.strokeStyle = color; ctx.lineWidth = 2; ctx.setLineDash([4,3]);
  ctx.strokeRect(screenX-halfWidth, topScreenY, halfWidth*2, bottomScreenY-topScreenY);
  ctx.restore();
}
