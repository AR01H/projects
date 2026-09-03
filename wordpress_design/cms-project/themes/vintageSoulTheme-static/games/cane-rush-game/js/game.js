/* =========================================================
   CANE RUSH — game.js
   The CaneRush class: boot, resize, per-frame render, and screen
   flow (start/end run, pause, home). Gameplay simulation methods
   (input, spawning, physics/judging) are attached in entities.js;
   DOM/menu/HUD methods are attached in ui.js — both via
   Object.assign(CaneRush.prototype, {...}) once this file has
   defined the class.
   ========================================================= */
'use strict';

class CaneRush{
  constructor(){
    this.canvas = document.getElementById('game');
    this.ctx = this.canvas.getContext('2d');
    this.proj = null;
    this.dpr = Math.min(window.devicePixelRatio||1, 2);
    this.resize();
    window.addEventListener('resize', ()=>this.resize());
    window.addEventListener('orientationchange', ()=>setTimeout(()=>this.resize(),200));

    AssetManager.preload(ASSET_MANIFEST);

    this.particles = new Particles();
    this.floaters = new Floaters();

    this.state = 'idle'; // idle(menu bg) | playing | paused | gameover
    this.selectedChar = SAVE.selectedChar;
    this.resetRun();

    this.lastT = performance.now();
    this.animPhase = 0;

    this.bindInput();
    this.bindUI();
    this.populateCharacters();
    this.populateShop();
    this.populateAchievements();
    this.updateMenuStats();

    requestAnimationFrame(t=>this.loop(t));
  }

  resize(){
    const w = window.innerWidth, h = window.innerHeight;
    this.canvas.width = w*this.dpr; this.canvas.height = h*this.dpr;
    this.canvas.style.width = w+'px'; this.canvas.style.height = h+'px';
    this.ctx.setTransform(this.dpr,0,0,this.dpr,0,0);
    this.proj = new Projector({width:w, height:h});
    this.proj.canvas = {width:w, height:h};
    this.proj.recalc();
  }

  resetRun(){
    this.player = {
      lane:0, laneF:0, jumping:false, jumpT:0, jumpDur:0.62, jumpHeight:70,
      sliding:false, slideT:0, slideDur:0.62, hit:false, hitT:0
    };
    this.obstacles = []; this.collectibles = []; this.powerTokens = []; this.decos=[];
    this.distance = 0; this.score = 0; this.coins = 0;
    this.combo = 1; this.comboTimer = 0; this.bestComboThisRun = 1;
    this.caneCount = 0; this.fruitCount = 0; this.goldenThisRun=0;
    this.envIndex = 0; this.envBlend = 0;
    this.spawnTimer = 0.4; this.decoTimer = 0; this.collectTimer = 0.3; this.powerTimer = rand(7,10);
    this.specialTimer = rand(14,20); this.specialActive=null; this.specialT=0;
    this.powerups = { magnet:0, rush:0, shield:0, golden:0, blast:0, superjump:0 };
    this.shakeT = 0; this.shakeMag=0; this.slowMoT=0;
    this.gameOverHandled=false;
    this.decoSpawnAcc = 0;
  }

  /* ---------------- RENDER ---------------- */
  render(){
    const ctx=this.ctx, proj=this.proj;
    ctx.save();
    if(this.shakeT>0){
      ctx.translate(rand(-this.shakeMag,this.shakeMag), rand(-this.shakeMag,this.shakeMag));
    }
    const env = ENVIRONMENTS[this.envIndex];
    const nextEnv = ENVIRONMENTS[(this.envIndex+1)%ENVIRONMENTS.length];
    drawEnvironment(ctx, proj, env, nextEnv, this.envBlend);

    // juice boost overlay stream
    if(this.specialActive==='juiceBoost'){
      ctx.save(); ctx.globalAlpha=0.18;
      ctx.fillStyle=COLORS.aqua;
      ctx.fillRect(0,proj.horizonY,proj.w,proj.groundY-proj.horizonY);
      ctx.restore();
    }

    // gather all z-sorted entities (decos, obstacles, collectibles, powerups) far->near
    const all = [];
    this.decos.forEach(d=>all.push({t:'deco', z:d.z, e:d}));
    this.obstacles.forEach(o=>{ if(!o.destroyed) all.push({t:'obs', z:o.z, e:o}); });
    this.collectibles.forEach(c=>{ if(!c.collected) all.push({t:'col', z:c.z, e:c}); });
    this.powerTokens.forEach(pw=>{ if(!pw.collected) all.push({t:'pow', z:pw.z, e:pw}); });
    all.sort((a,b)=>a.z-b.z);

    const bob = this.distance*0.06;
    for(const item of all){
      const z = clamp(item.z,0,1.18);
      const scale = proj.scale(z);
      if(item.t==='deco'){
        drawDeco(ctx, item.e, proj.x(item.e.lane,z), proj.y(z), scale, item.e.kind);
      } else if(item.t==='obs'){
        drawObstacle(ctx, item.e.type, proj.x(item.e.lane,z), proj.y(z), scale);
      } else if(item.t==='col'){
        drawCollectible(ctx, item.e.kind, proj.x(item.e.lane,z), proj.y(z), scale, bob+item.e.id*10, item.e.sub);
      } else if(item.t==='pow'){
        drawPowerupToken(ctx, item.e.key, proj.x(item.e.lane,z), proj.y(z), scale*1.1, bob*1.5+item.e.id*10);
      }
      // simple ground shadow for near items
      if(z>0.25){
        ctx.save(); ctx.globalAlpha=0.18*z; ctx.fillStyle='#000';
        ctx.beginPath(); ctx.ellipse(proj.x(item.e.lane,z), proj.y(z)+4, 30*scale,8*scale,0,0,Math.PI*2); ctx.fill();
        ctx.restore();
      }
    }

    // player
    this.renderPlayer();

    this.particles.draw(ctx);
    this.floaters.draw(ctx);

    ctx.restore();
  }

  renderPlayer(){
    const ctx=this.ctx, proj=this.proj, p=this.player;
    const z = PLAYER_Z;
    const baseX = proj.x(p.laneF, z);
    const baseY = proj.y(z);
    const scale = proj.scale(z)*1.35;

    let state='run', jumpLift=0;
    if(p.hit) state='hit';
    else if(p.jumping){
      state='jump';
      const prog = clamp(p.jumpT/p.jumpDur,0,1);
      jumpLift = Math.sin(prog*Math.PI) * p.jumpHeight;
    } else if(p.sliding){ state='slide'; }

    // ground shadow
    ctx.save();
    ctx.globalAlpha = 0.28;
    ctx.fillStyle='#000';
    ctx.beginPath(); ctx.ellipse(baseX, baseY+6, 34*scale*(1-jumpLift/300), 10*scale*(1-jumpLift/300),0,0,Math.PI*2); ctx.fill();
    ctx.restore();

    // shield glow
    if(this.powerups.shield>0){
      ctx.save(); ctx.globalAlpha=0.35+Math.sin(this.animPhase*20)*0.1;
      ctx.strokeStyle=COLORS.aqua; ctx.lineWidth=4;
      ctx.beginPath(); ctx.arc(baseX, baseY - jumpLift - 60*scale, 55*scale,0,Math.PI*2); ctx.stroke();
      ctx.restore();
    }

    drawRunner(ctx, this.selectedChar, baseX, baseY - jumpLift, scale, state, this.animPhase, {});

    // rush trail
    if(this.powerups.rush>0 && Math.random()<0.6){
      this.particles.burst(baseX, baseY-jumpLift-40*scale, {count:1,color:COLORS.orange,up:0,life:16,minSpd:0.2,maxSpd:0.6,minSize:4,maxSize:8});
    }
  }

  /* ---------------- SCREEN FLOW ---------------- */
  startRun(){
    Audio1.unlock();
    this.resetRun();
    this.state='playing';
    document.getElementById('startScreen').classList.add('hidden');
    document.getElementById('gameOverScreen').classList.add('hidden');
    document.getElementById('hud').classList.remove('hidden');
    document.getElementById('pauseBtn').classList.remove('hidden');
    document.getElementById('swipeHint').classList.remove('hidden');
    setTimeout(()=>document.getElementById('swipeHint').classList.add('hidden'), 3000);
    this.showBanner(ENVIRONMENTS[0].name);
    Audio1.startMusic();
  }

  endRun(){
    this.state='gameover';
    Audio1.stopMusic();
    document.getElementById('hud').classList.add('hidden');
    document.getElementById('pauseBtn').classList.add('hidden');

    const isRecord = this.score > SAVE.bestScore;
    SAVE.bestScore = Math.max(SAVE.bestScore, Math.floor(this.score));
    SAVE.bestDistance = Math.max(SAVE.bestDistance, Math.floor(this.distance));
    SAVE.totalCoins += this.coins;
    SAVE.totalCane += this.caneCount;
    SAVE.totalFruit += this.fruitCount;
    SAVE.bestCombo = Math.max(SAVE.bestCombo, this.bestComboThisRun);
    SAVE.runs += 1;
    SAVE.goldenCaneCollected += this.goldenThisRun;
    writeSave();

    const newly = this.checkAchievements();

    document.getElementById('goScore').textContent = Math.floor(this.score).toLocaleString();
    document.getElementById('goDistance').textContent = Math.floor(this.distance)+'m';
    document.getElementById('goCoins').textContent = this.coins;
    document.getElementById('goCombo').textContent = 'x'+this.bestComboThisRun;
    document.getElementById('goCane').textContent = this.caneCount;
    const rec = document.getElementById('goRecord');
    if(isRecord){ rec.classList.add('show'); Audio1.record(); } else rec.classList.remove('show');

    document.getElementById('gameOverScreen').classList.remove('hidden');
    animateCount('goScore', Math.floor(this.score));
    this.updateMenuStats();
    this.populateCharacters(); this.populateShop(); this.populateAchievements();

    if(newly.length){
      setTimeout(()=>this.showBanner('🏅 '+newly[0].name.toUpperCase()), 900);
    }
  }

  checkAchievements(){
    const newly=[];
    ACHIEVEMENTS.forEach(a=>{
      if(!SAVE.achievements.includes(a.id) && a.check(SAVE)){
        SAVE.achievements.push(a.id); newly.push(a);
      }
    });
    if(newly.length) writeSave();
    return newly;
  }

  togglePause(){
    if(this.state==='playing'){ this.state='paused'; Audio1.stopMusic(); document.getElementById('pauseScreen').classList.remove('hidden'); }
    else if(this.state==='paused'){ this.state='playing'; Audio1.startMusic(); document.getElementById('pauseScreen').classList.add('hidden'); }
  }

  goHome(){
    this.state='idle';
    Audio1.stopMusic();
    ['hud','pauseBtn','pauseScreen','gameOverScreen','charScreen','shopScreen','achScreen','settingsScreen'].forEach(id=>document.getElementById(id).classList.add('hidden'));
    document.getElementById('startScreen').classList.remove('hidden');
    this.updateMenuStats();
  }

  /* ---------------- LOOP ---------------- */
  loop(t){
    let dt = (t-this.lastT)/1000; this.lastT=t;
    dt = clamp(dt, 0, 0.05);
    this.update(dt);
    this.render();
    requestAnimationFrame(tt=>this.loop(tt));
  }
}
