/* =========================================================
   CANE RUSH — ui.js
   Everything that touches the DOM: HUD updates, banners, the menu
   mascot preview, and the character/shop/achievements panels.
   Attached to CaneRush.prototype (see entities.js for why).
   ========================================================= */
'use strict';

Object.assign(CaneRush.prototype, {

  /* ---------------- HUD / UI ---------------- */
  updateHUD(){
    document.getElementById('hudScore').textContent = Math.floor(this.score).toLocaleString();
    document.getElementById('hudCoins').textContent = this.coins;
    document.getElementById('hudDistance').textContent = 'DISTANCE ' + Math.floor(this.distance) + 'm';
    const comboEl = document.getElementById('hudCombo');
    if(this.combo>1){ comboEl.textContent='COMBO x'+this.combo; comboEl.classList.add('show'); }
    else comboEl.classList.remove('show');

    const tray = document.getElementById('powerupTray');
    tray.innerHTML='';
    Object.keys(this.powerups).forEach(k=>{
      if(this.powerups[k]>0){
        const meta = POWERUP_META[k] || {icon:'🛡'};
        const iconPath = ASSET_MANIFEST['powerup-'+k];
        const div = document.createElement('div');
        div.className='pu-chip';
        const iconHtml = iconPath ? `<img src="${iconPath}" alt="">` : `<span>${meta.icon}</span>`;
        div.innerHTML = `${iconHtml}<div class="bar" style="width:${clamp(this.powerups[k]/10,0,1)*100}%"></div>`;
        tray.appendChild(div);
      }
    });
  },

  updateMenuStats(){
    document.getElementById('pillBest').textContent = SAVE.bestScore.toLocaleString();
    document.getElementById('pillCoins').textContent = SAVE.totalCoins.toLocaleString();
  },

  showBanner(text){
    const el = document.getElementById('banner');
    const div = document.createElement('div');
    div.className='banner-text pop';
    div.textContent = text;
    div.style.color = COLORS.limeDark;
    el.innerHTML=''; el.appendChild(div);
    setTimeout(()=>{ if(div.parentNode) div.remove(); }, 1650);
  },

  /* ---------------- POPULATE PANELS ----------------
     Note: the start-screen mascot (#menuMascot) is a static illustrated
     SVG (assets/ui/mascot-cane.svg), not canvas-drawn — see index.html.
     The character-select cards below still use the canvas rig so every
     unlockable skin gets an accurate live preview. */
  populateCharacters(){
    const grid = document.getElementById('charGrid');
    grid.innerHTML='';
    CHARACTERS.forEach(ch=>{
      const unlocked = SAVE.unlockedChars.includes(ch.id);
      const div = document.createElement('div');
      div.className='char-card'+(SAVE.selectedChar===ch.id?' selected':'');
      div.innerHTML = `<canvas width="120" height="90"></canvas><div class="char-name">${ch.name}</div><div style="font-size:11px;color:#7a7362;">${ch.desc}</div>${unlocked?'':`<div class="buy-badge">🪙 ${ch.cost}</div>`}${unlocked?'':'<div class="char-lock">LOCKED</div>'}`;
      grid.appendChild(div);
      const cvs = div.querySelector('canvas');
      const cctx = cvs.getContext('2d');
      cctx.save(); if(!unlocked){cctx.globalAlpha=0.45;}
      drawRunner(cctx, ch.id, 60, 78, 0.62, 'idle', 0, {});
      cctx.restore();
      div.addEventListener('click', ()=>{
        Audio1.click();
        if(unlocked){
          SAVE.selectedChar = ch.id; this.selectedChar = ch.id; writeSave();
          this.populateCharacters();
        } else if(SAVE.totalCoins>=ch.cost){
          SAVE.totalCoins -= ch.cost; SAVE.unlockedChars.push(ch.id);
          SAVE.selectedChar = ch.id; this.selectedChar = ch.id;
          writeSave(); this.populateCharacters(); this.updateMenuStats();
        } else {
          this.showBanner('NEED MORE COINS');
        }
      });
    });
  },

  populateShop(){
    const grid = document.getElementById('shopGrid');
    grid.innerHTML='';
    BOTTLES.forEach(b=>{
      const unlocked = SAVE.unlockedBottles.includes(b.id);
      const div = document.createElement('div');
      div.className='shop-card'+(SAVE.selectedBottle===b.id?' selected':'');
      div.innerHTML = `<canvas width="120" height="90"></canvas><div class="shop-name">${b.name}</div>${unlocked?'':`<div class="buy-badge">🪙 ${b.cost}</div>`}${unlocked?'':'<div class="shop-lock">LOCKED</div>'}`;
      grid.appendChild(div);
      const cvs = div.querySelector('canvas'); const cctx = cvs.getContext('2d');
      drawShopBottle(cctx, b.color, 60, 78, unlocked?1:0.45);
      div.addEventListener('click', ()=>{
        Audio1.click();
        if(unlocked){ SAVE.selectedBottle=b.id; writeSave(); this.populateShop(); }
        else if(SAVE.totalCoins>=b.cost){
          SAVE.totalCoins-=b.cost; SAVE.unlockedBottles.push(b.id); SAVE.selectedBottle=b.id;
          writeSave(); this.populateShop(); this.updateMenuStats();
        } else { this.showBanner('NEED MORE COINS'); }
      });
    });
  },

  populateAchievements(){
    const list = document.getElementById('achList');
    list.innerHTML='';
    ACHIEVEMENTS.forEach(a=>{
      const unlocked = SAVE.achievements.includes(a.id);
      const div = document.createElement('div');
      div.className='ach-item'+(unlocked?' unlocked':'');
      div.innerHTML = `<div class="ach-icon">${unlocked?a.icon:'🔒'}</div><div><div class="ach-name">${a.name}</div><div class="ach-desc">${a.desc}</div></div>`;
      list.appendChild(div);
    });
  },

  /* ---------------- UI BINDING ---------------- */
  bindUI(){
    document.getElementById('btnStart').addEventListener('click', ()=>{ Audio1.click(); this.startRun(); });
    document.getElementById('btnCharacters').addEventListener('click', ()=>{ Audio1.click(); this.showPanel('charScreen'); });
    document.getElementById('btnShop').addEventListener('click', ()=>{ Audio1.click(); this.showPanel('shopScreen'); });
    document.getElementById('btnAchievements').addEventListener('click', ()=>{ Audio1.click(); this.showPanel('achScreen'); });
    document.getElementById('btnSettings').addEventListener('click', ()=>{ Audio1.click(); this.showPanel('settingsScreen'); });
    document.querySelectorAll('[data-close]').forEach(b=>b.addEventListener('click', ()=>{ Audio1.click(); this.hidePanels(); }));

    document.getElementById('pauseBtn').addEventListener('click', ()=>this.togglePause());
    document.getElementById('btnResume').addEventListener('click', ()=>this.togglePause());
    document.getElementById('btnQuit').addEventListener('click', ()=>{ document.getElementById('pauseScreen').classList.add('hidden'); this.goHome(); });

    document.getElementById('btnRunAgain').addEventListener('click', ()=>{ Audio1.click(); this.startRun(); });
    document.getElementById('btnGoHome').addEventListener('click', ()=>{ Audio1.click(); this.goHome(); });
    document.getElementById('btnGoShop').addEventListener('click', ()=>{ Audio1.click(); document.getElementById('gameOverScreen').classList.add('hidden'); this.showPanel('shopScreen'); });

    const st = document.getElementById('toggleSound');
    const mt = document.getElementById('toggleMusic');
    st.classList.toggle('on', SAVE.soundOn);
    mt.classList.toggle('on', SAVE.musicOn);
    st.addEventListener('click', ()=>{ SAVE.soundOn=!SAVE.soundOn; st.classList.toggle('on',SAVE.soundOn); writeSave(); Audio1.click(); });
    mt.addEventListener('click', ()=>{ SAVE.musicOn=!SAVE.musicOn; mt.classList.toggle('on',SAVE.musicOn); writeSave(); if(!SAVE.musicOn) Audio1.stopMusic(); else if(this.state==='playing') Audio1.startMusic(); });
    document.getElementById('btnReset').addEventListener('click', ()=>{
      if(confirm('Reset all progress? This cannot be undone.')){
        SAVE = defaultSave(); writeSave(); this.selectedChar='cane';
        this.updateMenuStats(); this.populateCharacters(); this.populateShop(); this.populateAchievements();
      }
    });
  },
  showPanel(id){
    this.hidePanels();
    document.getElementById(id).classList.remove('hidden');
  },
  hidePanels(){
    ['charScreen','shopScreen','achScreen','settingsScreen'].forEach(id=>document.getElementById(id).classList.add('hidden'));
  },

});

/* ---------------- SHOP BOTTLE PREVIEW (canvas art, not a game entity) ---------------- */
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
