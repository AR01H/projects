/* =========================================================
   CANE RUSH — utils.js
   Generic helpers + small self-contained systems shared by
   everything else: math, color, particles, floating text,
   save storage, synthesized audio.
   ========================================================= */
'use strict';

/* ---------------- MATH ---------------- */
function lerp(a,b,t){ return a+(b-a)*t; }
function clamp(v,mn,mx){ return Math.max(mn,Math.min(mx,v)); }
function easeOutCubic(t){ return 1-Math.pow(1-t,3); }
function easeInOutSine(t){ return -(Math.cos(Math.PI*t)-1)/2; }
function rand(a,b){ return a+Math.random()*(b-a); }
function randInt(a,b){ return Math.floor(rand(a,b+1)); }
function pick(arr){ return arr[Math.floor(Math.random()*arr.length)]; }
// Frame-rate-independent exponential smoothing toward a target.
// `rate` is the per-second decay constant (smaller = snappier).
function smoothTo(current, target, dt, rate){
  return lerp(current, target, 1-Math.pow(rate, dt));
}

function roundRect(ctx,x,y,w,h,r){
  if(typeof r === 'number') r={tl:r,tr:r,br:r,bl:r};
  ctx.beginPath();
  ctx.moveTo(x+r.tl,y);
  ctx.lineTo(x+w-r.tr,y);
  ctx.arcTo(x+w,y,x+w,y+r.tr,r.tr);
  ctx.lineTo(x+w,y+h-r.br);
  ctx.arcTo(x+w,y+h,x+w-r.br,y+h,r.br);
  ctx.lineTo(x+r.bl,y+h);
  ctx.arcTo(x,y+h,x,y+h-r.bl,r.bl);
  ctx.lineTo(x,y+r.tl);
  ctx.arcTo(x,y,x+r.tl,y,r.tl);
  ctx.closePath();
}

function drawStar(ctx,x,y,r,points){
  ctx.save(); ctx.translate(x,y); ctx.beginPath();
  for(let i=0;i<points*2;i++){
    const rad = i%2===0?r:r*0.45;
    const ang = (Math.PI/points)*i - Math.PI/2;
    ctx.lineTo(Math.cos(ang)*rad, Math.sin(ang)*rad);
  }
  ctx.closePath(); ctx.fill(); ctx.restore();
}

/* ---------------- COLOR ---------------- */
function lighten(hex, amt){
  const c = hex.replace('#','');
  const num = parseInt(c,16);
  let r=(num>>16)+amt, g=((num>>8)&0xff)+amt, b=(num&0xff)+amt;
  r=clamp(r,0,255); g=clamp(g,0,255); b=clamp(b,0,255);
  return `rgb(${r},${g},${b})`;
}
function hexToRgb(hex){
  const n=parseInt(hex.replace('#',''),16);
  return {r:(n>>16)&255,g:(n>>8)&255,b:n&255};
}
function lerpColor(a,b,t){
  const pa=hexToRgb(a), pb=hexToRgb(b);
  return `rgb(${Math.round(lerp(pa.r,pb.r,t))},${Math.round(lerp(pa.g,pb.g,t))},${Math.round(lerp(pa.b,pb.b,t))})`;
}

/* ---------------- PARTICLES ---------------- */
class Particles{
  constructor(){ this.list=[]; }
  burst(x,y,opts={}){
    const n = opts.count||10;
    for(let i=0;i<n;i++){
      const ang = rand(0,Math.PI*2);
      const spd = rand(opts.minSpd||1, opts.maxSpd||4);
      this.list.push({
        x,y, vx:Math.cos(ang)*spd, vy:Math.sin(ang)*spd - (opts.up||0),
        g: opts.gravity!=null?opts.gravity:0.15,
        life: opts.life||40, maxLife: opts.life||40,
        color: opts.color||COLORS.yellow, size: rand(opts.minSize||3,opts.maxSize||7),
        shape: opts.shape||'circle'
      });
    }
  }
  update(dtScale){
    for(let i=this.list.length-1;i>=0;i--){
      const p=this.list[i];
      p.x+=p.vx*dtScale; p.y+=p.vy*dtScale; p.vy+=p.g*dtScale; p.life-=dtScale;
      if(p.life<=0) this.list.splice(i,1);
    }
  }
  draw(ctx){
    for(const p of this.list){
      const a = clamp(p.life/p.maxLife,0,1);
      ctx.globalAlpha = a;
      ctx.fillStyle = p.color;
      if(p.shape==='circle'){
        ctx.beginPath(); ctx.arc(p.x,p.y,p.size*a,0,Math.PI*2); ctx.fill();
      } else if(p.shape==='star'){
        drawStar(ctx,p.x,p.y,p.size*a,4);
      } else {
        ctx.fillRect(p.x-p.size/2,p.y-p.size/2,p.size,p.size);
      }
      ctx.globalAlpha=1;
    }
  }
}

/* ---------------- FLOATING TEXT ---------------- */
class Floaters{
  constructor(){ this.list=[]; }
  add(x,y,text,opts={}){
    this.list.push({ x,y,text, life:opts.life||55, maxLife:opts.life||55,
      color:opts.color||COLORS.ink, size:opts.size||20, vy:opts.vy!=null?opts.vy:-1.1 });
  }
  update(dtScale){
    for(let i=this.list.length-1;i>=0;i--){
      const f=this.list[i]; f.y+=f.vy*dtScale; f.life-=dtScale; if(f.life<=0) this.list.splice(i,1);
    }
  }
  draw(ctx){
    ctx.textAlign='center';
    for(const f of this.list){
      const t = 1-f.life/f.maxLife;
      const a = f.life<12 ? f.life/12 : 1;
      const scale = t<0.15 ? lerp(0.4,1.15,t/0.15) : (t<0.25? lerp(1.15,1,(t-0.15)/0.1) : 1);
      ctx.save();
      ctx.globalAlpha=a;
      ctx.translate(f.x,f.y);
      ctx.scale(scale,scale);
      ctx.font = `900 ${f.size}px Baloo, Poppins, sans-serif`;
      ctx.lineWidth=4; ctx.strokeStyle='rgba(255,253,247,0.95)';
      ctx.strokeText(f.text,0,0);
      ctx.fillStyle=f.color;
      ctx.fillText(f.text,0,0);
      ctx.restore();
    }
  }
}

/* ---------------- STORAGE MANAGER ---------------- */
const StorageManager = (function(){
  const SAVE_KEY = 'caneRushSave_v1';
  function defaultSave(){
    return {
      bestScore:0, bestDistance:0, totalCoins:0, totalCane:0, totalFruit:0,
      bestCombo:1, runs:0, goldenCaneCollected:0,
      unlockedChars:['cane'], selectedChar:'cane',
      unlockedBottles:['original'], selectedBottle:'original',
      achievements:[], soundOn:false, musicOn:false
    };
  }
  function load(){
    try{
      const raw = localStorage.getItem(SAVE_KEY);
      if(!raw) return defaultSave();
      const parsed = JSON.parse(raw);
      return Object.assign(defaultSave(), parsed);
    }catch(e){ return defaultSave(); }
  }
  let data = load();
  return {
    get data(){ return data; },
    save(){ try{ localStorage.setItem(SAVE_KEY, JSON.stringify(data)); }catch(e){} },
    reset(){ data = defaultSave(); this.save(); }
  };
})();
// Kept as `SAVE` for terse call-sites, mirrors StorageManager.data (same object reference).
const SAVE = StorageManager.data;

/* ---------------- AUDIO MANAGER (WebAudio synthesized, no asset files) ---------------- */
const AudioManager = (function(){
  let ctx=null, musicTimer=null, musicStep=0, musicPlaying=false;
  function ensure(){
    if(!ctx){
      try{ ctx = new (window.AudioContext||window.webkitAudioContext)(); }catch(e){ ctx=null; }
    }
    if(ctx && ctx.state==='suspended') ctx.resume();
    return ctx;
  }
  function tone(freq, dur, type='sine', vol=0.18, delay=0, glideTo=null){
    if(!SAVE.soundOn) return;
    const c = ensure(); if(!c) return;
    const t0 = c.currentTime + delay;
    const osc = c.createOscillator();
    const gain = c.createGain();
    osc.type = type; osc.frequency.setValueAtTime(freq, t0);
    if(glideTo) osc.frequency.exponentialRampToValueAtTime(glideTo, t0+dur);
    gain.gain.setValueAtTime(0.0001, t0);
    gain.gain.exponentialRampToValueAtTime(vol, t0+0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, t0+dur);
    osc.connect(gain); gain.connect(c.destination);
    osc.start(t0); osc.stop(t0+dur+0.02);
  }
  function noiseBurst(dur, vol=0.15, delay=0){
    if(!SAVE.soundOn) return;
    const c = ensure(); if(!c) return;
    const bufferSize = c.sampleRate*dur;
    const buffer = c.createBuffer(1, bufferSize, c.sampleRate);
    const data = buffer.getChannelData(0);
    for(let i=0;i<bufferSize;i++) data[i] = (Math.random()*2-1) * (1-i/bufferSize);
    const src = c.createBufferSource(); src.buffer = buffer;
    const gain = c.createGain(); gain.gain.value = vol;
    src.connect(gain); gain.connect(c.destination);
    src.start(c.currentTime+delay);
  }
  return {
    unlock(){ ensure(); },
    coin(){ tone(1046,0.09,'square',0.12); tone(1568,0.09,'square',0.1,0.05); },
    cane(){ tone(660,0.12,'triangle',0.16); },
    fruit(){ tone(523,0.08,'sine',0.15); tone(784,0.1,'sine',0.13,0.06); },
    jump(){ tone(330,0.12,'sine',0.15,0,660); },
    slide(){ tone(220,0.1,'sawtooth',0.1,0,120); },
    powerup(){ tone(440,0.14,'triangle',0.18); tone(660,0.14,'triangle',0.16,0.08); tone(880,0.18,'triangle',0.15,0.16); },
    combo(n){ tone(500+n*40,0.12,'square',0.14); },
    nearMiss(){ tone(900,0.08,'sine',0.12); tone(1200,0.08,'sine',0.1,0.05); },
    collision(){ noiseBurst(0.3,0.2); tone(120,0.3,'sawtooth',0.16,0,60); },
    record(){ [523,659,784,1046].forEach((f,i)=>tone(f,0.16,'triangle',0.16,i*0.09)); },
    click(){ tone(700,0.05,'square',0.08); },
    warn(){ tone(880,0.06,'square',0.06); },
    swipe(){ tone(500,0.04,'sine',0.05); },
    startMusic(){
      if(musicPlaying || !SAVE.musicOn) return;
      musicPlaying = true;
      const notes = [523,587,659,523,784,659,587,523];
      musicStep=0;
      musicTimer = setInterval(()=>{
        if(!musicPlaying) return;
        if(SAVE.musicOn) tone(notes[musicStep%notes.length],0.16,'triangle',0.05);
        musicStep++;
      }, 260);
    },
    stopMusic(){ musicPlaying=false; if(musicTimer) clearInterval(musicTimer); }
  };
})();
