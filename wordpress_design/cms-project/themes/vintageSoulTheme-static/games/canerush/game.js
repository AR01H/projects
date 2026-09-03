/* =========================================================
   CANE RUSH — game.js
   Orchestrator only: wires managers together and runs the loop.
   Simulation rules live in the manager classes; drawing lives in
   render.js; DOM/HUD lives in ui.js.
   ========================================================= */
'use strict';

class Game{
  constructor(){
    this.canvas = document.getElementById('game');
    this.ctx = this.canvas.getContext('2d');
    this.proj = null;
    this.worldScale = 1;

    this.state = 'idle'; // idle | playing | paused | gameover
    this.debug = false;

    this.player = new Player();
    this.laneManager = new LaneManager(this.player);
    this.difficulty = new DifficultyManager();
    this.collision = new CollisionManager();
    this.obstacleManager = new ObstacleManager(this.collision);
    this.collectibleManager = new CollectibleManager();
    this.powerUpManager = new PowerUpManager();
    this.scoreManager = new ScoreManager();
    this.comboManager = new ComboManager();
    this.environmentManager = new EnvironmentManager();
    this.achievementManager = new AchievementManager();
    this.particles = new Particles();
    this.floaters = new Floaters();
    this.ui = new UIManager(this);

    this.selectedChar = SAVE.selectedChar;
    AssetManager.preload(ASSET_MANIFEST);

    this.resize();
    window.addEventListener('resize', ()=>this.resize());
    window.addEventListener('orientationchange', ()=>setTimeout(()=>this.resize(),200));

    this.input = new InputManager(this.canvas, {
      left: ()=>this.laneManager.move(-1),
      right: ()=>this.laneManager.move(1),
      jump: ()=>{ if(this.state==='playing' && this.player.jump(this.powerups.superjump>0)) AudioManager.jump(); },
      slide: ()=>{ if(this.state==='playing' && this.player.slide()) AudioManager.slide(); },
      pause: ()=>this.togglePause(),
      debugToggle: ()=>{ this.debug=!this.debug; this.ui.setDebugVisible(this.debug); }
    });
    this.input.enabled = ()=>this.state==='playing';
    this.input.onFeedback = (action)=>this.ui.showInputFeedback(action);
    this.ui.setDebugVisible(false);

    this.resetRun();
    this.lastT = performance.now();
    this.animPhase = 0;
    this.idleTimer = 0;     // seconds of inactivity (no jump/slide/bank)
    this.idlePhase = 0;    // 0-1 breathing cycle for idle animation
    this._fpsSmooth = 60;

    this.bindUI();
    this.ui.populateAchievements();
    this.ui.updateMenuStats();
    this.ui.initMenuMascot(()=>this.selectedChar);

    requestAnimationFrame(t=>this.loop(t));
  }

  /* ---------------- RESPONSIVE / VIRTUAL COORDINATE SPACE ----------------
     The canvas always renders in a fixed-height (REF_HEIGHT) virtual
     coordinate system; a single transform scales it to the real
     screen. Every physics constant, hitbox and sprite size is
     defined in virtual units, so gameplay is IDENTICAL in proportion
     on a 320px phone, a 430px phone, and a tablet — only the visible
     width changes, never the feel. */
  resize(){
    const w = window.innerWidth, h = window.innerHeight;
    const dpr = Math.min(window.devicePixelRatio||1, 2);
    this.canvas.width = Math.round(w*dpr);
    this.canvas.height = Math.round(h*dpr);
    this.canvas.style.width = w+'px'; this.canvas.style.height = h+'px';
    this.worldScale = h/REF_HEIGHT;
    const vw = w/this.worldScale, vh = REF_HEIGHT;
    this.ctx.setTransform(dpr*this.worldScale, 0, 0, dpr*this.worldScale, 0, 0);
    this.proj = new Projector(vw, vh);
  }

  resetRun(){
    this.player.reset();
    this.laneManager.player = this.player;
    this.obstacleManager.reset();
    this.collectibleManager.reset();
    this.powerUpManager.reset();
    this.scoreManager.reset();
    this.comboManager.reset();
    this.environmentManager.index = 0; this.environmentManager.blend = 0;
    this.distance = 0;
    this.decos = []; this.decoSpawnAcc = 0;
    this.specialTimer = rand(14,20); this.specialActive=null; this.specialT=0;
    this.powerups = {};
    Object.keys(this.powerUpManager.registry).forEach(k=>this.powerups[k]=0);
    this.shakeT=0; this.shakeMag=0; this.slowMoT=0;
    this.gameOverHandled=false;
    this.idleTimer = 0;
    this.idlePhase = 0;
  }

  /* ---------------- SPECIAL EVENTS ---------------- */
  triggerSpecial(){
    const kinds = ['goldenRush','fruitRain','juiceBoost','tropicalRush'];
    const kind = pick(kinds);
    this.specialActive = kind; this.specialT = 6.5;
    const labels = {
      goldenRush:'GOLDEN CANE RUSH!', fruitRain:'FRUIT RAIN!',
      juiceBoost:'JUICE BOOST!', tropicalRush:'TROPICAL RUSH!'
    };
    this.ui.showBanner(labels[kind]);
    if(kind==='juiceBoost' || kind==='tropicalRush'){ this.powerups.rush = Math.max(this.powerups.rush, 6.5); }
    AudioManager.powerup();
  }

  spawnDeco(){
    const side = Math.random()<0.5 ? -1.75 : 1.75;
    this.decos.push({ lane:side, z:0, id:Math.random(), kind: this.environmentManager.current.deco });
  }

  /* ---------------- UPDATE ---------------- */
  update(dtRaw){
    this._fpsSmooth = lerp(this._fpsSmooth, dtRaw>0?1/dtRaw:this._fpsSmooth, 0.1);
    const dt = dtRaw * (this.slowMoT>0 ? 0.35 : 1);
    if(this.slowMoT>0) this.slowMoT -= dtRaw;
    if(this.shakeT>0) this.shakeT -= dtRaw;
    this.input.update(dtRaw);

    if(this.state!=='playing'){
      this.animPhase += dt*1.6; if(this.animPhase>1) this.animPhase-=Math.floor(this.animPhase);
      this.particles.update(dt*60); this.floaters.update(dt*60);
      return;
    }

    const sf = this.difficulty.speedFactor;
    this.distance += sf*34*dt;
    this.animPhase += dt*(2.6*sf);
    if(this.animPhase>1) this.animPhase-=Math.floor(this.animPhase);

    // Idle timer: accumulates when the player isn't doing anything special
    const isActive = this.player.jumping || this.player.sliding || Math.abs(this.player.lane - this.player.laneF) > 0.08;
    if(isActive){
      this.idleTimer = 0;
    } else {
      this.idleTimer += dt;
    }
    // Idle breathing cycle advances only when truly idle (>2 s still)
    if(this.idleTimer > 2.0){
      this.idlePhase += dt * 0.5; // slow, 2-second breathing loop
      if(this.idlePhase > 1) this.idlePhase -= Math.floor(this.idlePhase);
    } else {
      this.idlePhase = 0;
    }

    this.environmentManager.update(this.distance, (env)=>this.ui.showBanner(env.name));

    const justLanded = this.player.update(dt);
    if(justLanded){
      const bx = this.proj.x(this.player.laneF, PLAYER_Z), by = this.proj.y(PLAYER_Z);
      this.particles.burst(bx, by, {count:8, color:'rgba(120,100,60,0.5)', up:0.5, life:18, minSpd:0.5, maxSpd:1.6, minSize:3, maxSize:6});
    }
    this.laneManager.update(dt);

    this.powerUpManager.tickTimers(this, dt);
    if(this.powerups.blast>0){
      this.obstacleManager.list.forEach(o=>{
        if(!o.judged && o.z>0.35 && o.z<0.85 && !o.destroyed){
          o.destroyed = true;
          const sx = this.proj.x(o.lane, o.z), sy = this.proj.y(o.z);
          this.particles.burst(sx,sy,{count:16,color:COLORS.orange,up:2,life:30});
        }
      });
    }

    this.comboManager.update(dt);

    this.specialTimer -= dt;
    if(this.specialTimer<=0 && !this.specialActive){ this.triggerSpecial(); this.specialTimer = rand(18,26); }
    if(this.specialActive){ this.specialT -= dt; if(this.specialT<=0) this.specialActive=null; }

    this.difficulty.update(this.distance, this.powerups.rush>0);

    this.obstacleManager.update(dt, this.difficulty, (o)=>this.judgeObstacle(o), this.powerups.slowSugar>0?1.6:1);
    this.collectibleManager.update(dt, this.difficulty, this.player.laneF, this.powerups.magnet>0, this.specialActive, (c)=>this.judgeCollectible(c,false), this.powerups.slowSugar>0?1.6:1);
    this.powerUpManager.update(dt, this.difficulty, (t)=>this.judgeCollectible(t,true), this.powerups.slowSugar>0?1.6:1);

    this.decoSpawnAcc -= dt;
    if(this.decoSpawnAcc<=0){ this.spawnDeco(); this.decoSpawnAcc = rand(0.5,0.9)/sf; }
    const dz = dt/this.difficulty.travelTime;
    this.decos.forEach(d=>d.z += dz*0.9);
    this.decos = this.decos.filter(d=>d.z<1.25);

    this.particles.update(dt*60); this.floaters.update(dt*60);
    this.scoreManager.addDistanceTrickle(sf, dt);

    this.ui.updateHUD(this.scoreManager.score, this.scoreManager.coins, this.distance, this.comboManager.combo, this.powerups, this.powerUpManager.registry);
    this.updateLaneWarnings();
    if(this.debug) this.updateDebugPanel(dtRaw, sf);
  }

  updateLaneWarnings(){
    const states = {left:null, center:null, right:null};
    const keyFor = lane => lane===-1?'left':(lane===0?'center':'right');
    for(const o of this.obstacleManager.list){
      if(o.warning && !o.judged && o.z<JUDGE_Z){
        const k = keyFor(o.lane);
        states[k] = 'blocked';
      }
    }
    ['left','center','right'].forEach(k=>{ if(states[k]===null && (states.left||states.center||states.right)) states[k]='safe'; });
    this.ui.updateLaneWarnings(states);
  }

  updateDebugPanel(dtRaw, sf){
    const activePowerups = Object.keys(this.powerups).filter(k=>this.powerups[k]>0);
    this.ui.updateDebug({
      fps: Math.round(this._fpsSmooth),
      speed: sf.toFixed(2),
      distance: Math.floor(this.distance),
      lane: this.player.roundedLane,
      state: this.player.hit?'HIT':(this.player.jumping?'JUMP':(this.player.sliding?'SLIDE':'RUN')),
      powerup: activePowerups.length?activePowerups.join(','):'none',
      input: this.input.lastInputLabel||'—',
      difficulty: this.difficulty.tier.name,
    });
  }

  /* ---------------- JUDGES ---------------- */
  judgeObstacle(o){
    if(o.destroyed) return;
    const pLane = this.player.roundedLane;
    if(pLane !== o.lane) return; // different lane: no interaction
    const def = this.obstacleManager.registry[o.type];
    const hit = this.collision.isHit(this.player, def);

    if(!hit){
      if(def.action!=='lane') this.doNearMiss();
      return;
    }
    if(this.powerups.shield>0){
      this.powerups.shield = 0;
      o.destroyed = true;
      const sx=this.proj.x(o.lane,o.z), sy=this.proj.y(o.z);
      this.particles.burst(sx,sy,{count:22,color:COLORS.aqua,up:3,life:34});
      this.floaters.add(sx,sy-30,'SHIELD BROKEN!',{color:COLORS.aqua,size:18});
      this.shake(6,0.2);
      AudioManager.powerup();
      return;
    }
    this.crash(o);
  }

  judgeCollectible(c, isPower){
    const pLane = this.player.roundedLane;
    if(pLane !== c.lane) return;
    c.collected = true;
    const sx=this.proj.x(c.lane, JUDGE_Z), sy=this.proj.y(JUDGE_Z);
    if(isPower){
      const def = this.powerUpManager.activate(c.key, this);
      if(!def) return;
      this.particles.burst(sx,sy,{count:20,color:def.color,up:3,life:34,shape:'star'});
      this.floaters.add(sx,sy-30,def.name.toUpperCase()+'!',{color:def.color,size:18});
      AudioManager.powerup();
      return;
    }
    const combo = this.comboManager.bump();
    const scoreMult = this.comboManager.multiplier * (this.powerups.rush>0?1.5:1) * (this.powerups.golden>0?2:1);
    let base=0, label='', color=COLORS.limeDark, coinAdd=0;
    if(c.kind==='cane'){
      base = COLLECTIBLE_SCORE.cane[c.sub] ?? COLLECTIBLE_SCORE.cane.normal;
      label = c.sub==='golden' ? 'GOLDEN CANE!' : 'CANE!';
      color = c.sub==='golden'?COLORS.yellow:COLORS.limeDark;
      this.scoreManager.caneCount++;
      if(c.sub==='golden') this.scoreManager.goldenThisRun++;
      AudioManager.cane();
      this.particles.burst(sx,sy,{count:14,color,up:2,life:30});
    } else if(c.kind==='coin'){
      base = COLLECTIBLE_SCORE.coin; coinAdd=1; label='COIN!'; color=COLORS.orange;
      AudioManager.coin();
      this.particles.burst(sx,sy,{count:10,color:COLORS.yellow,up:2,life:26,shape:'circle'});
    } else {
      base = COLLECTIBLE_SCORE.fruit; label = 'FRUIT!'; color=COLORS.hotpink; this.scoreManager.fruitCount++;
      AudioManager.fruit();
      this.particles.burst(sx,sy,{count:14,color,up:2,life:28});
    }
    const gained = Math.round(base*scoreMult);
    this.scoreManager.add(gained); this.scoreManager.coins += coinAdd;
    this.floaters.add(sx,sy-20, label, {color, size:20});
    this.floaters.add(sx,sy+8, '+'+gained, {color:COLORS.ink, size:16, vy:-0.7});
    if(combo>=2) this.floaters.add(sx,sy+30, 'COMBO x'+combo, {color:COLORS.orange, size:15, vy:-0.5});
    AudioManager.combo(combo);
  }

  doNearMiss(){
    this.scoreManager.add(50);
    const sx = this.proj.x(this.player.laneF, JUDGE_Z), sy = this.proj.y(JUDGE_Z)-40;
    this.floaters.add(sx, sy, 'NEAR MISS! +50', {color:COLORS.purple, size:17});
    this.particles.burst(sx,sy,{count:12,color:COLORS.purple,up:2,life:24,shape:'star'});
    this.slowMoT = 0.12;
    this.shake(4,0.15);
    AudioManager.nearMiss();
  }

  shake(mag,t){ this.shakeMag=mag; this.shakeT=t; }

  crash(o){
    // Second Wind: a stored revive charge cancels the crash entirely instead
    // of ending the run — consumed once, silently tracked until then.
    if(this.powerups.secondWind>0){
      this.powerups.secondWind = 0;
      o.destroyed = true;
      this.player.hit = false;
      this.powerups.shield = 1.2; // brief grace window so the same obstacle can't re-trigger a crash
      const sx=this.proj.x(o.lane,JUDGE_Z), sy=this.proj.y(JUDGE_Z);
      this.particles.burst(sx,sy,{count:26,color:COLORS.lime,up:3,life:34,shape:'star'});
      this.floaters.add(sx,sy-30,'SECOND WIND!',{color:COLORS.lime,size:19});
      this.shake(8,0.25);
      AudioManager.powerup();
      return;
    }
    this.player.hit = true; this.player.hitT=0;
    this.shake(14,0.4);
    AudioManager.collision();
    const sx=this.proj.x(o.lane,JUDGE_Z), sy=this.proj.y(JUDGE_Z);
    this.particles.burst(sx,sy,{count:26,color:COLORS.red,up:3,life:36});
    this.comboManager.break_();
    setTimeout(()=>{ if(this.state==='playing') this.endRun(); }, 420);
  }

  /* ---------------- RENDER ---------------- */
  render(){
    const ctx=this.ctx, proj=this.proj;
    ctx.save();
    if(this.shakeT>0){
      ctx.translate(rand(-this.shakeMag,this.shakeMag), rand(-this.shakeMag,this.shakeMag));
    }
    const env = this.environmentManager.current, nextEnv = this.environmentManager.next;
    drawEnvironment(ctx, proj, env, nextEnv, this.environmentManager.blend, this.distance);

    if(this.specialActive==='juiceBoost'){
      ctx.save(); ctx.globalAlpha=0.18;
      ctx.fillStyle=COLORS.aqua;
      ctx.fillRect(0,proj.horizonY,proj.w,proj.groundY-proj.horizonY);
      ctx.restore();
    }

    const all = [];
    this.decos.forEach(d=>all.push({t:'deco', z:d.z, e:d}));
    all.sort((a,b)=>a.z-b.z);
    for(const item of all){
      const z = clamp(item.z,0,1.18);
      drawDeco(ctx, item.e, proj.x(item.e.lane,z), proj.y(z), proj.scale(z), item.e.kind);
    }

    // Obstacles, collectibles and power tokens are drawn together,
    // sorted far-to-near, so a nearby collectible never renders behind
    // a farther obstacle (or vice versa).
    const bob = this.distance*0.06;
    this._drawDepthSorted(ctx, proj, bob);

    this.renderPlayer();

    this.particles.draw(ctx);
    this.floaters.draw(ctx);

    if(this.debug) this.drawDebug(ctx, proj);

    ctx.restore();
  }

  _drawDepthSorted(ctx, proj, bob){
    // Build a flat list of drawable refs so obstacles/collectibles/tokens
    // interleave correctly by depth instead of drawing in three separate
    // back-to-front passes (which would let a near collectible render
    // behind a far obstacle).
    const items = [];
    this.obstacleManager.list.forEach(o=>{ if(!o.destroyed) items.push({z:o.z, kind:'obs', e:o}); });
    this.collectibleManager.list.forEach(c=>{ if(!c.collected) items.push({z:c.z, kind:'col', e:c}); });
    this.powerUpManager.tokens.forEach(p=>{ if(!p.collected) items.push({z:p.z, kind:'pow', e:p}); });
    items.sort((a,b)=>a.z-b.z);
    for(const it of items){
      const z = clamp(it.z,0,1.18);
      const scale = proj.scale(z);
      const sx = proj.x(it.e.lane,z), sy = proj.y(z);
      if(it.kind==='obs'){
        drawObstacle(ctx, it.e.type, sx, sy, scale, it.e.warning && it.e.z<JUDGE_Z && !it.e.judged);
        if(z>0.25){ ctx.save(); ctx.globalAlpha=0.18*z; ctx.fillStyle='#000';
          ctx.beginPath(); ctx.ellipse(sx, sy+4, 30*scale,8*scale,0,0,Math.PI*2); ctx.fill(); ctx.restore(); }
        if(it.e.warning && it.e.z<JUDGE_Z && !it.e.judged) drawWarningGlyph(ctx, it.e.action, sx, sy - 90*scale);
      } else if(it.kind==='col'){
        drawCollectible(ctx, it.e.kind, sx, sy, scale, bob+it.e.id*10, it.e.sub);
      } else if(it.kind==='pow'){
        drawPowerupToken(ctx, it.e.key, sx, sy, scale*1.1, bob*1.5+it.e.id*10);
      }
    }
  }

  renderPlayer(){
    const ctx=this.ctx, proj=this.proj, p=this.player;
    const z = PLAYER_Z;
    const baseX = proj.x(p.laneF, z);
    const baseY = proj.y(z);
    const scale = proj.scale(z)*0.62;

    // ---- Determine animation state ----
    let state = 'run';
    if(p.hit)                              state = 'hit';
    else if(p.jumping && this.powerups.superjump > 0) state = 'superjump';
    else if(p.jumping)                     state = 'jump';
    else if(p.sliding)                     state = 'slide';

    const jumpLift = p.airY;

    // Drop shadow (shrinks while airborne)
    ctx.save();
    ctx.globalAlpha = 0.28;
    ctx.fillStyle='#000';
    ctx.beginPath();
    ctx.ellipse(baseX, baseY+6, 34*scale*(1-jumpLift/300), 10*scale*(1-jumpLift/300), 0, 0, Math.PI*2);
    ctx.fill();
    ctx.restore();

    // Shield bubble
    if(this.powerups.shield > 0){
      ctx.save();
      ctx.globalAlpha = 0.35 + Math.sin(this.animPhase*20)*0.1;
      ctx.strokeStyle = COLORS.aqua; ctx.lineWidth = 4;
      ctx.beginPath(); ctx.arc(baseX, baseY - jumpLift - 60*scale, 55*scale, 0, Math.PI*2); ctx.stroke();
      ctx.restore();
    }

    // Super-jump golden glow
    if(state === 'superjump'){
      ctx.save();
      ctx.globalAlpha = 0.25 + Math.sin(this.animPhase*18)*0.15;
      const sjG = ctx.createRadialGradient(baseX, baseY - jumpLift - 30*scale, 5*scale, baseX, baseY - jumpLift - 30*scale, 70*scale);
      sjG.addColorStop(0, 'rgba(255,220,50,0.9)');
      sjG.addColorStop(1, 'rgba(255,140,0,0)');
      ctx.fillStyle = sjG;
      ctx.beginPath(); ctx.arc(baseX, baseY - jumpLift - 30*scale, 70*scale, 0, Math.PI*2); ctx.fill();
      ctx.restore();
    }

    // Compute timing fractions for per-frame animation
    const superJumpFrac = (this.powerups.superjump > 0 && p.jumping)
      ? clamp(1 - this.powerups.superjump / (POWERUP_CONFIG.superjump?.duration || 8), 0, 1)
      : 0;
    const airYFrac  = p.airY > 0
      ? (p.velY >= 0
          ? 0.5 + (1 - p.airY / (p.airY + 1)) * 0.5
          : p.airY / (PLAYER_PHYSICS.jumpVelocity * -0.05 + 1))
      : 0;
    const slideFrac = p.sliding ? p.slideT / PLAYER_PHYSICS.slideDuration : 0;
    const hitFrac   = p.hit    ? Math.min(1, p.hitT / 0.6) : 0;

    drawRunner(ctx, this.selectedChar, baseX, baseY - jumpLift, scale, state, this.animPhase, {
      bank:         p.lane - p.laneF,
      airYFrac,
      slideFrac,
      hitFrac,
      idlePhase:    this.idlePhase,
      superJumpFrac,
    });

    if(this.powerups.rush>0 && Math.random()<0.6){
      this.particles.burst(baseX, baseY-jumpLift-40*scale, {count:1,color:COLORS.orange,up:0,life:16,minSpd:0.2,maxSpd:0.6,minSize:4,maxSize:8});
    }

    if(this.debug){
      const hb = p.hitboxV;
      drawDebugHitbox(ctx, baseX, baseY-hb.bottom, baseY-hb.top, PLAYER_PHYSICS.fullWidth/2*scale, '#00E5FF');
    }
  }

  drawDebug(ctx, proj){
    ctx.save();
    ctx.strokeStyle='rgba(0,229,255,0.5)'; ctx.lineWidth=1.5;
    ctx.beginPath(); ctx.moveTo(0, proj.groundY); ctx.lineTo(proj.w, proj.groundY); ctx.stroke();
    LANES.forEach(l=>{
      ctx.beginPath();
      ctx.moveTo(proj.x(l-0.5,0), proj.horizonY); ctx.lineTo(proj.x(l-0.5,1), proj.groundY); ctx.stroke();
    });
    for(const o of this.obstacleManager.list){
      if(o.destroyed || o.judged) continue;
      const def = this.obstacleManager.registry[o.type];
      const z = clamp(o.z,0,1.18); const scale = proj.scale(z);
      const sx = proj.x(o.lane,z), sy = proj.y(z);
      if(def.action==='lane'){
        drawDebugHitbox(ctx, sx, sy, sy-def.box.height*scale, 50*scale, '#FF5A5F');
      } else {
        drawDebugHitbox(ctx, sx, sy-def.box.bottom*scale, sy-(def.box.bottom+def.box.height)*scale, 50*scale, '#FF5A5F');
      }
    }
    ctx.restore();
  }

  /* ---------------- LOOP ---------------- */
  loop(t){
    let dt = (t-this.lastT)/1000; this.lastT=t;
    dt = clamp(dt, 0, 0.05); // guards against tab-switch stalls without breaking physics
    this.update(dt);
    this.render();
    requestAnimationFrame(tt=>this.loop(tt));
  }

  /* ---------------- SCREEN FLOW ---------------- */
  startRun(){
    AudioManager.unlock();
    this.resetRun();
    this.state='playing';
    this.ui.showPlayingUI();
    this.ui.showBanner(this.environmentManager.current.name);
    AudioManager.startMusic();
  }

  endRun(){
    this.state='gameover';
    AudioManager.stopMusic();

    const isRecord = this.scoreManager.score > SAVE.bestScore;
    SAVE.bestScore = Math.max(SAVE.bestScore, Math.floor(this.scoreManager.score));
    SAVE.bestDistance = Math.max(SAVE.bestDistance, Math.floor(this.distance));
    SAVE.totalCoins += this.scoreManager.coins;
    SAVE.totalCane += this.scoreManager.caneCount;
    SAVE.totalFruit += this.scoreManager.fruitCount;
    SAVE.bestCombo = Math.max(SAVE.bestCombo, this.comboManager.best);
    SAVE.runs += 1;
    SAVE.goldenCaneCollected += this.scoreManager.goldenThisRun;
    StorageManager.save();

    const newly = this.achievementManager.check(SAVE);
    if(newly.length) StorageManager.save();

    this.ui.showGameOver({
      score:this.scoreManager.score, distance:this.distance, coins:this.scoreManager.coins,
      bestCombo:this.comboManager.best, caneCount:this.scoreManager.caneCount
    }, isRecord);
    if(isRecord) AudioManager.record();

    this.ui.updateMenuStats();
    this.ui.populateAchievements();

    if(newly.length){ setTimeout(()=>this.ui.showBanner('🏅 '+newly[0].name.toUpperCase()), 900); }
  }

  togglePause(){
    if(this.state==='playing'){ this.state='paused'; AudioManager.stopMusic(); this.ui.showPauseScreen(true); }
    else if(this.state==='paused'){ this.state='playing'; AudioManager.startMusic(); this.ui.showPauseScreen(false); }
  }

  goHome(){
    this.state='idle';
    AudioManager.stopMusic();
    this.ui.goHome();
    this.ui.updateMenuStats();
  }

  /* ---------------- UI BINDING ---------------- */
  bindUI(){
    document.getElementById('btnStart').addEventListener('click', ()=>{ AudioManager.click(); this.startRun(); });
    document.getElementById('btnAchievements').addEventListener('click', ()=>{ AudioManager.click(); this.ui.showPanel('achScreen'); });
    document.getElementById('btnSettings').addEventListener('click', ()=>{ AudioManager.click(); this.ui.showPanel('settingsScreen'); });
    document.querySelectorAll('[data-close]').forEach(b=>b.addEventListener('click', ()=>{ AudioManager.click(); this.ui.hidePanels(); }));

    document.getElementById('pauseBtn').addEventListener('click', ()=>this.togglePause());
    document.getElementById('btnResume').addEventListener('click', ()=>this.togglePause());
    document.getElementById('btnQuit').addEventListener('click', ()=>{ this.ui.showPauseScreen(false); this.goHome(); });

    document.getElementById('btnRunAgain').addEventListener('click', ()=>{ AudioManager.click(); this.startRun(); });
    document.getElementById('btnGoHome').addEventListener('click', ()=>{ AudioManager.click(); this.goHome(); });

    const st = document.getElementById('toggleSound');
    const mt = document.getElementById('toggleMusic');
    st.classList.toggle('on', SAVE.soundOn);
    mt.classList.toggle('on', SAVE.musicOn);
    st.addEventListener('click', ()=>{ SAVE.soundOn=!SAVE.soundOn; st.classList.toggle('on',SAVE.soundOn); StorageManager.save(); AudioManager.click(); });
    mt.addEventListener('click', ()=>{ SAVE.musicOn=!SAVE.musicOn; mt.classList.toggle('on',SAVE.musicOn); StorageManager.save(); if(!SAVE.musicOn) AudioManager.stopMusic(); else if(this.state==='playing') AudioManager.startMusic(); });
    document.getElementById('btnReset').addEventListener('click', ()=>{
      if(confirm('Reset all progress? This cannot be undone.')){
        StorageManager.reset(); Object.assign(SAVE, StorageManager.data); this.selectedChar='cane';
        this.ui.updateMenuStats(); this.ui.populateAchievements();
      }
    });

    // Optional on-screen touch buttons (item 12) — same input pipeline as swipes.
    document.querySelectorAll('#onscreenControls [data-action]').forEach(btn=>{
      btn.addEventListener('touchstart', e=>{ e.preventDefault(); this.input._fire(btn.dataset.action, 'BUTTON'); }, {passive:false});
      btn.addEventListener('click', e=>{ this.input._fire(btn.dataset.action, 'BUTTON'); });
    });
  }
}

/* ---------------- BOOTSTRAP ---------------- */
window.addEventListener('DOMContentLoaded', ()=>{
  window.__game = new Game();
});
