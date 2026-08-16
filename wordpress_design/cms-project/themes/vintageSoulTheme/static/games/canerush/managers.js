/* =========================================================
   CANE RUSH — managers.js
   One class per responsibility. Each manager owns its own state
   and exposes a small update()/query surface — the Game class
   wires them together but never reaches into their internals.
   ========================================================= */
'use strict';

/* ---------------- INPUT MANAGER ----------------
   Reliable keyboard + swipe input. Swipes need a minimum travel
   distance and a maximum duration to count, and a short cooldown
   prevents one physical gesture from firing twice. Every accepted
   input immediately calls onFeedback() so the game can show the
   player their gesture was received (item 12). */
class InputManager{
  constructor(canvas, handlers){
    this.canvas = canvas;
    this.handlers = handlers; // {left,right,jump,slide,pause}
    this.enabled = ()=>true;
    this.cooldown = 0;
    this.cooldownDuration = 0.18; // seconds — blocks accidental double-fire from one gesture
    this.lastInputLabel = '';
    this.onFeedback = null;

    // Swipe thresholds scale gently with screen size (small phones need a
    // slightly smaller absolute distance to feel equally responsive).
    this.minSwipeDistance = clamp(Math.min(window.innerWidth, window.innerHeight)*0.09, 35, 60);
    this.maxSwipeDuration = 650; // ms

    this._bindKeyboard();
    this._bindTouch();
  }

  update(dt){ if(this.cooldown>0) this.cooldown -= dt; }
  _ready(){ return this.cooldown<=0; }
  _fire(action, label){
    if(!this.enabled() || !this._ready()) return;
    this.cooldown = this.cooldownDuration;
    this.lastInputLabel = label;
    if(this.handlers[action]) this.handlers[action]();
    if(this.onFeedback) this.onFeedback(action);
  }

  _bindKeyboard(){
    window.addEventListener('keydown', e=>{
      if(e.key==='F3'){ e.preventDefault(); if(this.handlers.debugToggle) this.handlers.debugToggle(); return; }
      if(!this.enabled()) return;
      switch(e.key){
        case 'ArrowLeft': case 'a': case 'A': this._fire('left','KEY LEFT'); break;
        case 'ArrowRight': case 'd': case 'D': this._fire('right','KEY RIGHT'); break;
        case 'ArrowUp': case 'w': case 'W': case ' ': e.preventDefault(); this._fire('jump','KEY JUMP'); break;
        case 'ArrowDown': case 's': case 'S': this._fire('slide','KEY SLIDE'); break;
        case 'Escape': case 'p': case 'P': if(this.handlers.pause) this.handlers.pause(); break;
      }
    });
  }

  _bindTouch(){
    let tsx=0, tsy=0, tst=0, tracking=false;
    const opts = {passive:false};
    this.canvas.addEventListener('touchstart', e=>{
      if(!this.enabled()) return;
      const t=e.touches[0]; tsx=t.clientX; tsy=t.clientY; tst=Date.now(); tracking=true;
    }, opts);
    this.canvas.addEventListener('touchmove', e=>{ if(this.enabled()) e.preventDefault(); }, opts);
    this.canvas.addEventListener('touchend', e=>{
      if(!tracking) return; tracking=false;
      if(!this.enabled()) return;
      const t=e.changedTouches[0];
      const dx=t.clientX-tsx, dy=t.clientY-tsy, dt=Date.now()-tst;
      if(dt>this.maxSwipeDuration) return; // too slow to be an intentional swipe
      const adx=Math.abs(dx), ady=Math.abs(dy);
      if(Math.max(adx,ady) < this.minSwipeDistance) return; // tiny jitter, ignore
      if(adx>ady){
        this._fire(dx>0?'right':'left', dx>0?'SWIPE →':'SWIPE ←');
      } else {
        if(dy<0) this._fire('jump','SWIPE ↑'); else this._fire('slide','SWIPE ↓');
      }
    }, opts);
    document.body.addEventListener('touchmove', e=>{ if(this.enabled()) e.preventDefault(); }, opts);
  }
}

/* ---------------- LANE MANAGER ----------------
   Thin wrapper so obstacle/collision logic never has to know how
   lane switching is implemented — just "what lane is the player
   logically in / visually at". */
class LaneManager{
  constructor(player){ this.player = player; }
  move(dir){ this.player.moveLane(dir); }
  update(dt){ this.player.updateLane(dt); }
  get current(){ return this.player.roundedLane; }
  get visual(){ return this.player.laneF; }
}

/* ---------------- DIFFICULTY MANAGER ----------------
   Single source of truth for "how hard is the game right now".
   Everything else (spawn rate, obstacle speed, pattern complexity,
   reaction distance) reads from here instead of computing its own
   version of difficulty. */
class DifficultyManager{
  constructor(){ this.distance = 0; this.rushActive = false; }
  update(distance, rushActive){ this.distance = distance; this.rushActive = rushActive; }

  get tier(){
    let t = DIFFICULTY_TIERS[0];
    for(const d of DIFFICULTY_TIERS){ if(this.distance>=d.minDistance) t=d; }
    return t;
  }
  get speedFactor(){
    let f = 1 + clamp(this.distance/1500, 0, this.tier.maxSpeedMult-1);
    if(this.rushActive) f *= 1.55;
    return f;
  }
  /** Seconds for an obstacle to travel from spawn (z=0) to the player (z=1). */
  get travelTime(){ return clamp(1.85/this.speedFactor, 0.85, 1.85); }
  /** Seconds between obstacle waves — never below what's needed to safely resolve a jump/slide. */
  get waveInterval(){
    const raw = clamp(1.05/this.speedFactor, 0.62, 1.05);
    return raw;
  }
  get patternTier(){ return this.tier.patternTier; }
}

/* ---------------- COLLISION MANAGER ----------------
   Real hitbox intersection instead of boolean state guessing.
   Works entirely in virtual (resolution-independent) units. A
   `forgiveness` shrink is applied to the obstacle's box so a player
   who visibly clears an obstacle is never punished for a technical
   graze (item 7). */
class CollisionManager{
  constructor(forgiveness=0.82){ this.forgiveness = forgiveness; }

  /** obstacleDef.box = {bottom, height} in virtual units. action = 'jump'|'slide'|'lane'. */
  isHit(player, obstacleDef){
    if(obstacleDef.action==='lane') return true; // full-lane obstacle: only escape is not being in this lane
    const pb = player.hitboxV;
    const box = obstacleDef.box;
    const shrink = box.height*(1-this.forgiveness)/2;
    const b = box.bottom+shrink, t = box.bottom+box.height-shrink;
    return pb.top > b && pb.bottom < t;
  }
}

/* ---------------- OBSTACLE MANAGER ----------------
   Data-driven: new obstacle kinds are added via register(), no
   engine changes needed. Generates only pre-validated safe patterns
   (see SAFE_PATTERNS) so an unsolvable combination can never spawn,
   and never spawns a new wave sooner than the player could possibly
   resolve the previous one. Also owns the warning system: obstacles
   flip `warning=true` a fixed amount of *time* (not distance) before
   they reach the judge line, so reaction time stays fair as speed
   ramps up. */
class ObstacleManager{
  constructor(collisionManager){
    this.registry = {};
    Object.entries(OBSTACLE_CONFIG).forEach(([id,def])=>this.register(Object.assign({id},def)));
    this.list = [];
    this.collision = collisionManager;
    this.spawnTimer = 0.4;
    this.reactionTime = 0.55; // seconds of guaranteed warning before judge point
  }

  register(def){ this.registry[def.id] = def; }
  byAction(action){ return Object.values(this.registry).filter(o=>o.action===action); }

  reset(){ this.list = []; this.spawnTimer = 0.5; }

  update(dt, difficulty, judgeCallback, slowFactor=1){
    this.spawnTimer -= dt;
    if(this.spawnTimer<=0){
      this.spawnWave(difficulty);
      this.spawnTimer = difficulty.waveInterval;
    }
    const effectiveTravelTime = difficulty.travelTime*slowFactor;
    const dz = dt/effectiveTravelTime;
    // Warning window expressed in z-space so it corresponds to a constant
    // real-world reaction time even as travelTime shrinks with speed.
    const warnZ = JUDGE_Z - clamp(this.reactionTime/effectiveTravelTime, 0.12, 0.6);
    for(let i=this.list.length-1;i>=0;i--){
      const o = this.list[i];
      o.z += dz;
      if(!o.warning && o.z>=warnZ) o.warning = true;
      if(!o.judged && o.z>=JUDGE_Z){ o.judged=true; judgeCallback(o); }
      if(o.z>1.2 || o.destroyed) this.list.splice(i,1);
    }
  }

  spawnWave(difficulty){
    const tier = difficulty.patternTier;
    const pool = [].concat(SAFE_PATTERNS[0], tier>=1?SAFE_PATTERNS[1]:[], tier>=2?SAFE_PATTERNS[2]:[]);
    const pattern = pick(pool);
    const result = [];
    pattern.forEach((action, idx)=>{
      const lane = LANES[idx];
      if(action===''){ result.push({lane, clear:true}); return; }
      const choices = this.byAction(action);
      const def = pick(choices);
      this.list.push({ type:def.id, lane, z:0, judged:false, warning:false, action, id:Math.random() });
      result.push({lane, clear:false});
    });
    return result;
  }

  draw(ctx, proj){
    for(const o of this.list){
      if(o.destroyed) continue;
      const z = clamp(o.z,0,1.18);
      const scale = proj.scale(z);
      const sx = proj.x(o.lane,z), sy = proj.y(z);
      drawObstacle(ctx, o.type, sx, sy, scale, o.warning && o.z<JUDGE_Z);
      if(z>0.25){
        ctx.save(); ctx.globalAlpha=0.18*z; ctx.fillStyle='#000';
        ctx.beginPath(); ctx.ellipse(sx, sy+4, 30*scale,8*scale,0,0,Math.PI*2); ctx.fill();
        ctx.restore();
      }
      if(o.warning && o.z<JUDGE_Z && !o.judged){
        drawWarningGlyph(ctx, o.action, sx, sy - 90*scale);
      }
    }
  }
}

/* ---------------- COLLECTIBLE MANAGER ---------------- */
class CollectibleManager{
  constructor(){ this.list=[]; this.timer=0.3; }
  reset(){ this.list=[]; this.timer=0.3; }

  spawnAt(lane, opts={}){
    const roll = Math.random();
    if(opts.forceGolden || roll<0.12){
      this.list.push({kind:'cane', sub:opts.forceGolden?'golden':'normal', lane, z:0, id:Math.random()});
    } else if(roll<0.22){
      this.list.push({kind:'cane', sub:'fresh', lane, z:0, id:Math.random()});
    } else if(roll<0.34){
      const fruit = pick(['mango','pineapple','lemon','coconut','watermelon']);
      this.list.push({kind:fruit, lane, z:0, id:Math.random()});
    } else {
      this.list.push({kind:'coin', lane, z:0, id:Math.random()});
    }
  }

  update(dt, difficulty, playerLaneF, magnetActive, specialActive, judgeCallback, slowFactor=1){
    this.timer -= dt;
    if(this.timer<=0){
      if(specialActive==='fruitRain'){
        LANES.forEach(l=>{ if(Math.random()<0.7) this.list.push({kind:pick(['mango','pineapple','lemon','coconut','watermelon']), lane:l, z:0, id:Math.random()}); });
      } else if(Math.random()<0.5){
        this.spawnAt(pick(LANES), {forceGolden: specialActive==='goldenRush'});
      }
      this.timer = rand(0.35,0.6);
    }
    const dz = dt/(difficulty.travelTime*slowFactor);
    for(let i=this.list.length-1;i>=0;i--){
      const c = this.list[i];
      if(magnetActive && c.z>0.45) c.lane = lerp(c.lane, playerLaneF, 0.14);
      c.z += dz;
      if(!c.collected && c.z>=JUDGE_Z-0.02 && !c._checked){ c._checked=true; judgeCallback(c); }
      if(c.z>1.2 || c.collected) this.list.splice(i,1);
    }
  }

  draw(ctx, proj, bob){
    for(const c of this.list){
      if(c.collected) continue;
      const z = clamp(c.z,0,1.18);
      const scale = proj.scale(z);
      drawCollectible(ctx, c.kind, proj.x(c.lane,z), proj.y(z), scale, bob+c.id*10, c.sub);
      if(z>0.25){
        ctx.save(); ctx.globalAlpha=0.14*z; ctx.fillStyle='#000';
        ctx.beginPath(); ctx.ellipse(proj.x(c.lane,z), proj.y(z)+4, 22*scale,6*scale,0,0,Math.PI*2); ctx.fill();
        ctx.restore();
      }
    }
  }
}

/* ---------------- POWER-UP MANAGER ----------------
   register() accepts the exact shape described in the spec:
   {id, duration, icon, activate(state), deactivate(state)}. Active
   power-up timers live in `state.powerups`, a plain {key: secondsLeft}
   bag, so HUD / collision code can check them without knowing the
   registry. */
class PowerUpManager{
  constructor(){
    this.registry = {};
    Object.entries(POWERUP_CONFIG).forEach(([id,def])=>this.register(Object.assign({id},def)));
    this.tokens = [];
    this.timer = rand(7,10);
  }
  register(def){ this.registry[def.id] = def; }
  reset(){ this.tokens=[]; this.timer=rand(7,10); }

  spawnToken(){
    const key = pick(Object.keys(this.registry));
    this.tokens.push({ key, lane: pick(LANES), z:0, id:Math.random() });
  }

  activate(key, state){
    const def = this.registry[key];
    if(!def) return null;
    def.activate(state);
    return def;
  }

  tickTimers(state, dt){
    for(const k in state.powerups){
      const def = this.registry[k];
      if(def && def.persistent) continue; // stored charges (e.g. Second Wind) don't count down — they're consumed on use
      if(state.powerups[k]>0) state.powerups[k]=Math.max(0,state.powerups[k]-dt);
    }
  }

  update(dt, difficulty, judgeCallback, slowFactor=1){
    this.timer -= dt;
    if(this.timer<=0){ this.spawnToken(); this.timer = rand(8,13); }
    const dz = dt/(difficulty.travelTime*slowFactor);
    for(let i=this.tokens.length-1;i>=0;i--){
      const t = this.tokens[i];
      t.z += dz;
      if(!t.collected && t.z>=JUDGE_Z-0.02 && !t._checked){ t._checked=true; judgeCallback(t); }
      if(t.z>1.2 || t.collected) this.tokens.splice(i,1);
    }
  }

  draw(ctx, proj, bob){
    for(const t of this.tokens){
      if(t.collected) continue;
      const z = clamp(t.z,0,1.18);
      drawPowerupToken(ctx, t.key, proj.x(t.lane,z), proj.y(z), proj.scale(z)*1.1, bob*1.5+t.id*10);
    }
  }
}

/* ---------------- SCORE MANAGER ---------------- */
class ScoreManager{
  constructor(){ this.reset(); }
  reset(){ this.score=0; this.coins=0; this.caneCount=0; this.fruitCount=0; this.goldenThisRun=0; }
  addDistanceTrickle(sf, dt){ this.score += sf*36*dt; } // ~sf*0.6 per frame at 60fps, now frame-rate independent
  add(amount){ this.score += amount; }
}

/* ---------------- COMBO MANAGER ---------------- */
class ComboManager{
  constructor(){ this.reset(); }
  reset(){ this.combo=1; this.timer=0; this.best=1; }
  update(dt){ if(this.timer>0){ this.timer-=dt; if(this.timer<=0) this.combo=1; } }
  bump(){ this.combo=Math.min(this.combo+1,20); this.best=Math.max(this.best,this.combo); this.timer=2.6; return this.combo; }
  break_(){ this.combo=1; this.timer=0; }
  get multiplier(){ return 1+(this.combo-1)*0.12; }
}

/* ---------------- ENVIRONMENT MANAGER ---------------- */
class EnvironmentManager{
  constructor(){ this.index=0; this.blend=0; }
  update(distance, onChange){
    const envPos = distance/ENV_DISTANCE;
    const idx = Math.floor(envPos)%ENVIRONMENT_CONFIG.length;
    const blend = envPos-Math.floor(envPos);
    if(idx!==this.index && blend<0.02 && onChange) onChange(ENVIRONMENT_CONFIG[idx]);
    this.index=idx; this.blend=blend;
  }
  get current(){ return ENVIRONMENT_CONFIG[this.index]; }
  get next(){ return ENVIRONMENT_CONFIG[(this.index+1)%ENVIRONMENT_CONFIG.length]; }
}

/* ---------------- ACHIEVEMENT MANAGER ---------------- */
class AchievementManager{
  check(save){
    const newly=[];
    ACHIEVEMENT_CONFIG.forEach(a=>{
      if(!save.achievements.includes(a.id) && a.check(save)){
        save.achievements.push(a.id); newly.push(a);
      }
    });
    return newly;
  }
}
