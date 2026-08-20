/* =========================================================
   CANE RUSH — ui.js
   All DOM/HUD reads & writes live here so Game.js stays about
   simulation, not markup.
   ========================================================= */
'use strict';

class UIManager{
  constructor(game){
    this.game = game;
    this.el = {
      hud: document.getElementById('hud'),
      hudScore: document.getElementById('hudScore'),
      hudCoins: document.getElementById('hudCoins'),
      hudDistance: document.getElementById('hudDistance'),
      hudCombo: document.getElementById('hudCombo'),
      powerupTray: document.getElementById('powerupTray'),
      pauseBtn: document.getElementById('pauseBtn'),
      banner: document.getElementById('banner'),
      swipeHint: document.getElementById('swipeHint'),
      startScreen: document.getElementById('startScreen'),
      gameOverScreen: document.getElementById('gameOverScreen'),
      pauseScreen: document.getElementById('pauseScreen'),
      pillBest: document.getElementById('pillBest'),
      pillCoins: document.getElementById('pillCoins'),
      goScore: document.getElementById('goScore'),
      goDistance: document.getElementById('goDistance'),
      goCoins: document.getElementById('goCoins'),
      goCombo: document.getElementById('goCombo'),
      goCane: document.getElementById('goCane'),
      goRecord: document.getElementById('goRecord'),
      debugPanel: document.getElementById('debugPanel'),
      laneWarnRow: document.getElementById('laneWarnRow'),
      inputFeedback: document.getElementById('inputFeedback'),
    };
  }

  /* ---------------- HUD ---------------- */
  updateHUD(score, coins, distance, combo, powerups, registry){
    this.el.hudScore.textContent = Math.floor(score).toLocaleString();
    this.el.hudCoins.textContent = coins;
    this.el.hudDistance.textContent = 'DISTANCE ' + Math.floor(distance) + 'm';
    if(combo>1){ this.el.hudCombo.textContent='COMBO x'+combo; this.el.hudCombo.classList.add('show'); }
    else this.el.hudCombo.classList.remove('show');

    const tray = this.el.powerupTray;
    tray.innerHTML='';
    Object.keys(powerups).forEach(k=>{
      if(powerups[k]>0){
        const def = registry[k] || {icon:'🛡', duration:10};
        const div = document.createElement('div');
        div.className='pu-chip';
        div.title = def.name||k;
        const iconHTML = def.asset && ASSET_MANIFEST[def.asset]
          ? `<img src="${ASSET_MANIFEST[def.asset]}" alt="${def.name||k}">`
          : `<span>${def.icon||'●'}</span>`;
        const pct = def.persistent ? 100 : clamp(powerups[k]/(def.duration||10),0,1)*100;
        div.innerHTML = `${iconHTML}<div class="bar" style="width:${pct}%"></div>`;
        tray.appendChild(div);
      }
    });
  }

  /* ---------------- Lane warning strip (item 3 ASCII-style readout) ---------------- */
  updateLaneWarnings(states){ // states: {left,center,right} each 'safe'|'blocked'|null
    if(!this.el.laneWarnRow) return;
    const anyWarning = states.left||states.center||states.right;
    this.el.laneWarnRow.classList.toggle('show', !!anyWarning);
    if(!anyWarning) return;
    ['left','center','right'].forEach(k=>{
      const cell = this.el.laneWarnRow.querySelector(`[data-lane="${k}"]`);
      if(!cell) return;
      cell.textContent = states[k]==='blocked' ? 'BLOCKED' : 'SAFE';
      cell.className = 'lane-cell '+(states[k]==='blocked'?'blocked':'safe');
    });
  }

  /* ---------------- Input feedback (item 12) ---------------- */
  showInputFeedback(action){
    const el = this.el.inputFeedback; if(!el) return;
    const glyphs = {left:'⬅', right:'➡', jump:'⬆', slide:'⬇'};
    el.textContent = glyphs[action]||'';
    el.classList.remove('pulse'); void el.offsetWidth; el.classList.add('pulse');
  }

  /* ---------------- Debug overlay (item 22) ---------------- */
  updateDebug(info){
    if(!this.el.debugPanel) return;
    this.el.debugPanel.innerHTML =
      `FPS ${info.fps}<br>SPEED ${info.speed}x<br>DIST ${info.distance}m<br>` +
      `LANE ${info.lane}<br>STATE ${info.state}<br>POWERUP ${info.powerup}<br>` +
      `INPUT ${info.input}<br>DIFFICULTY ${info.difficulty}`;
  }
  setDebugVisible(v){ if(this.el.debugPanel) this.el.debugPanel.classList.toggle('hidden', !v); }

  /* ---------------- Banners ---------------- */
  showBanner(text){
    const el = this.el.banner;
    const div = document.createElement('div');
    div.className='banner-text pop';
    div.textContent = text;
    div.style.color = COLORS.limeDark;
    el.innerHTML=''; el.appendChild(div);
    setTimeout(()=>{ if(div.parentNode) div.remove(); }, 1650);
  }

  /* ---------------- Screen flow ---------------- */
  showPlayingUI(){
    this.el.startScreen.classList.add('hidden');
    this.el.gameOverScreen.classList.add('hidden');
    this.el.hud.classList.remove('hidden');
    this.el.pauseBtn.classList.remove('hidden');
    this.el.swipeHint.classList.remove('hidden');
    setTimeout(()=>this.el.swipeHint.classList.add('hidden'), 3000);
  }
  showGameOver(stats, isRecord){
    this.el.hud.classList.add('hidden');
    this.el.pauseBtn.classList.add('hidden');
    this.el.goScore.textContent = Math.floor(stats.score).toLocaleString();
    this.el.goDistance.textContent = Math.floor(stats.distance)+'m';
    this.el.goCoins.textContent = stats.coins;
    this.el.goCombo.textContent = 'x'+stats.bestCombo;
    this.el.goCane.textContent = stats.caneCount;
    if(isRecord){ this.el.goRecord.classList.add('show'); } else this.el.goRecord.classList.remove('show');
    this.el.gameOverScreen.classList.remove('hidden');
    this.animateCount(this.el.goScore, Math.floor(stats.score));
  }
  showPauseScreen(v){ this.el.pauseScreen.classList.toggle('hidden', !v); }
  goHome(){
    ['hud','pauseBtn'].forEach(k=>this.el[k].classList.add('hidden'));
    this.el.pauseScreen.classList.add('hidden');
    this.el.gameOverScreen.classList.add('hidden');
    this.hidePanels();
    this.el.startScreen.classList.remove('hidden');
  }
  showPanel(id){ this.hidePanels(); document.getElementById(id).classList.remove('hidden'); }
  hidePanels(){ ['achScreen','settingsScreen'].forEach(id=>document.getElementById(id).classList.add('hidden')); }

  updateMenuStats(){
    this.el.pillBest.textContent = SAVE.bestScore.toLocaleString();
    this.el.pillCoins.textContent = SAVE.totalCoins.toLocaleString();
  }

  animateCount(el, target){
    let cur=0; const step=Math.max(1,Math.round(target/40));
    const t=setInterval(()=>{
      cur+=step;
      if(cur>=target){ cur=target; clearInterval(t); }
      el.textContent = cur.toLocaleString();
    },16);
  }

  populateAchievements(){
    const list = document.getElementById('achList');
    list.innerHTML='';
    ACHIEVEMENT_CONFIG.forEach(a=>{
      const unlocked = SAVE.achievements.includes(a.id);
      const div = document.createElement('div');
      div.className='ach-item'+(unlocked?' unlocked':'');
      div.innerHTML = `<div class="ach-icon">${unlocked?a.icon:'🔒'}</div><div><div class="ach-name">${a.name}</div><div class="ach-desc">${a.desc}</div></div>`;
      list.appendChild(div);
    });
  }

  initMenuMascot(getSelectedChar){
    const c = document.getElementById('menuMascot');
    const ctx = c.getContext('2d');
    const resizeM = ()=>{ const r=c.getBoundingClientRect(); c.width=r.width*(window.devicePixelRatio||1); c.height=r.height*(window.devicePixelRatio||1); ctx.setTransform(window.devicePixelRatio||1,0,0,window.devicePixelRatio||1,0,0); };
    resizeM(); window.addEventListener('resize', resizeM);
    let phase=0;
    const loop=()=>{
      const r=c.getBoundingClientRect();
      ctx.clearRect(0,0,r.width,r.height);
      phase += 0.02;
      if(phase>1) phase-=1;
      const sc = Math.min(r.width,r.height)/170;
      ctx.save(); ctx.globalAlpha=0.15; ctx.fillStyle=COLORS.limeDark;
      ctx.beginPath(); ctx.ellipse(r.width/2, r.height*0.86, 46*sc,10*sc,0,0,Math.PI*2); ctx.fill(); ctx.restore();
      drawRunner(ctx, getSelectedChar(), r.width/2, r.height*0.86, sc*0.55, 'run', phase, {});
      requestAnimationFrame(loop);
    };
    loop();
  }
}
