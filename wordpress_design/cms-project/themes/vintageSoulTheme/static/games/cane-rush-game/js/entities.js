/* =========================================================
   CANE RUSH — entities.js
   Gameplay simulation: player input, spawning, per-frame update,
   and collision/pickup judging. Attached to CaneRush.prototype so
   the class stays one cohesive object (game.js owns its identity)
   while the rules that drive it live in their own file.
   ========================================================= */
'use strict';

Object.assign(CaneRush.prototype, {

  /* ---------------- INPUT ---------------- */
  bindInput(){
    window.addEventListener('keydown', e=>{
      if(this.state!=='playing') return;
      switch(e.key){
        case 'ArrowLeft': case 'a': case 'A': this.moveLane(-1); break;
        case 'ArrowRight': case 'd': case 'D': this.moveLane(1); break;
        case 'ArrowUp': case 'w': case 'W': case ' ': e.preventDefault(); this.jump(); break;
        case 'ArrowDown': case 's': case 'S': this.slide(); break;
        case 'Escape': case 'p': case 'P': this.togglePause(); break;
      }
    });
    let tsx=0, tsy=0, tst=0, tracking=false;
    const opts = {passive:false};
    this.canvas.addEventListener('touchstart', e=>{
      if(this.state!=='playing') return;
      const t=e.touches[0]; tsx=t.clientX; tsy=t.clientY; tst=Date.now(); tracking=true;
    }, opts);
    this.canvas.addEventListener('touchmove', e=>{ if(this.state==='playing') e.preventDefault(); }, opts);
    this.canvas.addEventListener('touchend', e=>{
      if(!tracking) return; tracking=false;
      if(this.state!=='playing') return;
      const t=e.changedTouches[0];
      const dx=t.clientX-tsx, dy=t.clientY-tsy, dt=Date.now()-tst;
      if(dt>700) return;
      if(Math.abs(dx)>Math.abs(dy)){
        if(Math.abs(dx)>28){ this.moveLane(dx>0?1:-1); }
      } else {
        if(dy<-24) this.jump();
        else if(dy>24) this.slide();
      }
    }, opts);
    document.body.addEventListener('touchmove', e=>{ if(this.state==='playing') e.preventDefault(); }, opts);
  },

  moveLane(dir){
    const p=this.player;
    p.lane = clamp(p.lane+dir, -1, 1);
  },
  jump(){
    const p=this.player;
    if(p.jumping || p.sliding || p.hit) return;
    p.jumping=true; p.jumpT=0;
    p.jumpDur = this.powerups.superjump>0 ? 0.8 : 0.62;
    p.jumpHeight = this.powerups.superjump>0 ? 130 : 74;
    Audio1.jump();
  },
  slide(){
    const p=this.player;
    if(p.sliding || p.jumping || p.hit) return;
    p.sliding=true; p.slideT=0;
    Audio1.slide();
  },

  /* ---------------- SPAWNING ---------------- */
  speedFactor(){
    let f = 1 + clamp(this.distance/1500, 0, 1.5);
    if(this.powerups.rush>0) f*=1.55;
    return f;
  },
  travelTime(){ return clamp(1.85/this.speedFactor(), 0.85, 1.85); },

  spawnWave(){
    const patterns = [
      ['jump','', ''], ['', 'jump', ''], ['', '', 'jump'],
      ['slide','', ''], ['', '', 'slide'],
      ['lane','', ''], ['', '', 'lane'], ['lane','','lane'],
      ['jump','', 'slide'], ['slide','','jump'],
      ['', 'lane', ''],
      ['', '', ''], ['', '', ''], // clear waves for breathing room
    ];
    const pat = pick(patterns);
    const jumpKinds=['fallenCane','crate','puddle','coconutPile'];
    const slideKinds=['barrier','archway'];
    const laneKinds=['cart','truck','stall','bottleStack'];
    pat.forEach((req, idx)=>{
      const lane = LANES[idx];
      if(req===''){
        // chance of collectible in clear lane
        if(Math.random()<0.55) this.spawnCollectibleAt(lane);
        return;
      }
      let type;
      if(req==='jump') type=pick(jumpKinds);
      else if(req==='slide') type=pick(slideKinds);
      else type=pick(laneKinds);
      this.obstacles.push({ type, lane, z:0, judged:false, req, id:Math.random() });
    });
  },

  spawnCollectibleAt(lane, opts={}){
    const roll = Math.random();
    if(this.specialActive==='goldenRush' || roll<0.12){
      this.collectibles.push({kind:'cane', sub: this.specialActive==='goldenRush'? 'golden':'normal', lane, z:0, id:Math.random()});
    } else if(roll<0.22){
      this.collectibles.push({kind:'cane', sub:'fresh', lane, z:0, id:Math.random()});
    } else if(roll<0.34){
      const fruit = pick(['mango','pineapple','lemon','coconut','watermelon']);
      this.collectibles.push({kind:fruit, lane, z:0, id:Math.random()});
    } else {
      this.collectibles.push({kind:'coin', lane, z:0, id:Math.random()});
    }
  },

  spawnPowerToken(){
    const lane = pick(LANES);
    const key = pick(Object.keys(POWERUP_META));
    this.powerTokens.push({ key, lane, z:0, id:Math.random() });
  },

  spawnDeco(){
    const side = Math.random()<0.5 ? -1.75 : 1.75;
    this.decos.push({ lane:side, z:0, id:Math.random(), kind: ENVIRONMENTS[this.envIndex].deco });
  },

  triggerSpecial(){
    const kinds = ['goldenRush','fruitRain','juiceBoost','tropicalRush'];
    const kind = pick(kinds);
    this.specialActive = kind; this.specialT = 6.5;
    const labels = {
      goldenRush:'GOLDEN CANE RUSH!', fruitRain:'FRUIT RAIN!',
      juiceBoost:'JUICE BOOST!', tropicalRush:'TROPICAL RUSH!'
    };
    this.showBanner(labels[kind]);
    if(kind==='juiceBoost' || kind==='tropicalRush'){ this.powerups.rush = Math.max(this.powerups.rush, 6.5); }
    Audio1.powerup();
  },

  /* ---------------- UPDATE ---------------- */
  update(dtRaw){
    const dt = dtRaw * (this.slowMoT>0 ? 0.35 : 1);
    if(this.slowMoT>0) this.slowMoT -= dtRaw;
    if(this.shakeT>0) this.shakeT -= dtRaw;

    if(this.state!=='playing'){ this.updateIdleAnim(dt); this.particles.update(); this.floaters.update(); return; }

    const sf = this.speedFactor();
    this.distance += sf * 34 * dt; // meters
    this.animPhase += dt * (2.6*sf);
    if(this.animPhase>1) this.animPhase-=Math.floor(this.animPhase);

    // environment progression
    const envPos = this.distance / ENV_DISTANCE;
    const idx = Math.floor(envPos) % ENVIRONMENTS.length;
    const blend = envPos - Math.floor(envPos);
    if(idx !== this.envIndex && blend<0.02){
      this.showBanner(ENVIRONMENTS[idx].name);
    }
    this.envIndex = idx; this.envBlend = blend;

    // player physics
    const p=this.player;
    p.laneF = lerp(p.laneF, p.lane, 1-Math.pow(0.001,dt));
    if(p.jumping){
      p.jumpT += dt;
      if(p.jumpT>=p.jumpDur){ p.jumping=false; p.jumpT=0; }
    }
    if(p.sliding){
      p.slideT += dt;
      if(p.slideT>=p.slideDur){ p.sliding=false; p.slideT=0; }
    }
    if(p.hit){ p.hitT += dt; }

    // powerup timers
    for(const k in this.powerups){ if(this.powerups[k]>0) this.powerups[k]=Math.max(0,this.powerups[k]-dt); }
    if(this.powerups.blast>0){
      // destroy obstacles within blast window as they approach
      this.obstacles.forEach(o=>{
        if(!o.judged && o.z>0.35 && o.z<0.85 && !o.destroyed){
          o.destroyed = true;
          const sx = this.proj.x(o.lane, o.z), sy = this.proj.y(o.z);
          this.particles.burst(sx,sy,{count:16,color:COLORS.orange,up:2,life:30});
        }
      });
    }

    // combo timer
    if(this.comboTimer>0){ this.comboTimer -= dt; if(this.comboTimer<=0){ this.combo=1; } }

    // special moments
    this.specialTimer -= dt;
    if(this.specialTimer<=0 && !this.specialActive){ this.triggerSpecial(); this.specialTimer = rand(18,26); }
    if(this.specialActive){
      this.specialT -= dt;
      if(this.specialT<=0){ this.specialActive=null; }
    }

    // spawns
    this.spawnTimer -= dt;
    const interval = clamp(1.05/sf, 0.5, 1.05) * (this.specialActive==='fruitRain'?0.55:1);
    if(this.spawnTimer<=0){ this.spawnWave(); this.spawnTimer = interval; }

    this.collectTimer -= dt;
    if(this.collectTimer<=0){
      if(this.specialActive==='fruitRain'){
        LANES.forEach(l=>{ if(Math.random()<0.7) this.collectibles.push({kind:pick(['mango','pineapple','lemon','coconut','watermelon']), lane:l, z:0, id:Math.random()}); });
      } else if(Math.random()<0.5){
        this.spawnCollectibleAt(pick(LANES));
      }
      this.collectTimer = rand(0.35,0.6);
    }

    this.powerTimer -= dt;
    if(this.powerTimer<=0){ this.spawnPowerToken(); this.powerTimer = rand(8,13); }

    this.decoSpawnAcc -= dt;
    if(this.decoSpawnAcc<=0){ this.spawnDeco(); this.decoSpawnAcc = rand(0.5,0.9)/sf; }

    // advance entities
    const dz = dt / this.travelTime();
    this.updateEntities(this.obstacles, dz, true);
    this.updateEntities(this.collectibles, dz, false);
    this.updateEntities(this.powerTokens, dz, false);
    this.decos.forEach(d=>d.z += dz*0.9);
    this.decos = this.decos.filter(d=>d.z<1.25);

    this.particles.update(); this.floaters.update();

    // score trickle for distance
    this.score += Math.round(sf*0.6);

    this.updateHUD();
  },

  updateEntities(list, dz, isObstacle){
    for(let i=list.length-1;i>=0;i--){
      const e = list[i];
      // magnet effect pulls collectibles toward player lane
      if(!isObstacle && this.powerups.magnet>0 && e.z>0.45 && e.kind){
        e.lane = lerp(e.lane, this.player.lane, 0.14);
      }
      e.z += dz;
      if(isObstacle){
        if(!e.judged && e.z>=JUDGE_Z){ e.judged=true; this.judgeObstacle(e); }
      } else {
        if(!e.collected && e.z>=JUDGE_Z-0.02 && !e._checked){ e._checked=true; this.judgeCollectible(e, list===this.powerTokens); }
      }
      if(e.z>1.2 || e.collected || e.destroyed){ list.splice(i,1); }
    }
  },

  judgeObstacle(o){
    if(o.destroyed) return;
    const pLane = Math.round(this.player.laneF);
    if(pLane !== o.lane) return; // different lane, nothing happens
    const p=this.player;
    let safe=false, nearMiss=false;
    if(o.req==='jump'){ safe = p.jumping; nearMiss=safe; }
    else if(o.req==='slide'){ safe = p.sliding; nearMiss=safe; }
    else if(o.req==='lane'){ safe=false; } // if still in this lane at judge time, it's a hit

    if(safe){
      if(nearMiss) this.doNearMiss();
      return;
    }
    // unsafe collision
    if(this.powerups.shield>0){
      this.powerups.shield = 0;
      o.destroyed = true;
      const sx=this.proj.x(o.lane,o.z), sy=this.proj.y(o.z);
      this.particles.burst(sx,sy,{count:22,color:COLORS.aqua,up:3,life:34});
      this.floaters.add(sx,sy-30,'SHIELD BROKEN!',{color:COLORS.aqua,size:18});
      this.shake(6,0.2);
      Audio1.powerup();
      return;
    }
    this.crash(o);
  },

  judgeCollectible(c, isPower){
    const pLane = Math.round(this.player.laneF);
    if(pLane !== c.lane){ return; }
    c.collected = true;
    const sx=this.proj.x(c.lane, JUDGE_Z), sy=this.proj.y(JUDGE_Z);
    if(isPower){
      this.activatePower(c.key);
      this.particles.burst(sx,sy,{count:20,color:POWERUP_META[c.key].color,up:3,life:34,shape:'star'});
      this.floaters.add(sx,sy-30,POWERUP_META[c.key].name.toUpperCase()+'!',{color:POWERUP_META[c.key].color,size:18});
      Audio1.powerup();
      return;
    }
    this.combo = Math.min(this.combo+1, 20);
    this.bestComboThisRun = Math.max(this.bestComboThisRun, this.combo);
    this.comboTimer = 2.6;
    const mult = 1 + (this.combo-1)*0.12;
    const scoreMult = mult * (this.powerups.rush>0?1.5:1) * (this.powerups.golden>0?2:1);
    let base=0, label='', color=COLORS.limeDark, coinAdd=0;
    if(c.kind==='cane'){
      base = c.sub==='golden'?100:(c.sub==='fresh'?25:10);
      label = c.sub==='golden' ? 'GOLDEN CANE!' : 'CANE!';
      color = c.sub==='golden'?COLORS.yellow:COLORS.limeDark;
      this.caneCount++;
      if(c.sub==='golden') this.goldenThisRun++;
      Audio1.cane();
      this.particles.burst(sx,sy,{count:14,color,up:2,life:30});
    } else if(c.kind==='coin'){
      base = 5; coinAdd=1; label='COIN!'; color=COLORS.orange;
      Audio1.coin();
      this.particles.burst(sx,sy,{count:10,color:COLORS.yellow,up:2,life:26,shape:'circle'});
    } else {
      base = 15; label = 'FRUIT!'; color=COLORS.hotpink; this.fruitCount++;
      Audio1.fruit();
      this.particles.burst(sx,sy,{count:14,color,up:2,life:28});
    }
    const gained = Math.round(base*scoreMult);
    this.score += gained; this.coins += coinAdd;
    this.floaters.add(sx,sy-20, label, {color, size:20});
    this.floaters.add(sx,sy+8, '+'+gained, {color:COLORS.ink, size:16, vy:-0.7});
    if(this.combo>=2){
      this.floaters.add(sx,sy+30, 'COMBO x'+this.combo, {color:COLORS.orange, size:15, vy:-0.5});
    }
    Audio1.combo(this.combo);
  },

  activatePower(key){
    if(key==='magnet') this.powerups.magnet = 8;
    else if(key==='rush') this.powerups.rush = 6;
    else if(key==='shield') this.powerups.shield = 1;
    else if(key==='golden') this.powerups.golden = 10;
    else if(key==='blast') this.powerups.blast = 1.4;
    else if(key==='superjump') this.powerups.superjump = 8;
  },

  doNearMiss(){
    this.score += 50;
    const sx = this.proj.x(this.player.laneF, JUDGE_Z), sy = this.proj.y(JUDGE_Z)-40;
    this.floaters.add(sx, sy, 'NEAR MISS! +50', {color:COLORS.purple, size:17});
    this.particles.burst(sx,sy,{count:12,color:COLORS.purple,up:2,life:24,shape:'star'});
    this.slowMoT = 0.12;
    this.shake(4,0.15);
    Audio1.nearMiss();
  },

  shake(mag,t){ this.shakeMag=mag; this.shakeT=t; },

  crash(o){
    this.player.hit = true; this.player.hitT=0;
    this.shake(14,0.4);
    Audio1.collision();
    const sx=this.proj.x(o.lane,JUDGE_Z), sy=this.proj.y(JUDGE_Z);
    this.particles.burst(sx,sy,{count:26,color:COLORS.red,up:3,life:36});
    this.combo=1;
    setTimeout(()=>{ if(this.state==='playing') this.endRun(); }, 420);
  },

  updateIdleAnim(dt){ this.animPhase += dt*1.6; if(this.animPhase>1) this.animPhase-=Math.floor(this.animPhase); },

});
