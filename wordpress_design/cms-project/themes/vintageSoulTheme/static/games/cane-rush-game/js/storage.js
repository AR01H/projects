/* =========================================================
   CANE RUSH — storage.js
   Save-game persistence (localStorage). SAVE is the single mutable
   save object every other file reads/writes; call writeSave() after
   mutating it.
   ========================================================= */
'use strict';

const SAVE_KEY = 'caneRushSave_v1';

function defaultSave(){
  return {
    bestScore:0, bestDistance:0, totalCoins:0, totalCane:0, totalFruit:0,
    bestCombo:1, runs:0, goldenCaneCollected:0,
    unlockedChars:['cane'], selectedChar:'cane',
    unlockedBottles:['original'], selectedBottle:'original',
    achievements:[], soundOn:true, musicOn:true
  };
}

function loadSave(){
  try{
    const raw = localStorage.getItem(SAVE_KEY);
    if(!raw) return defaultSave();
    const parsed = JSON.parse(raw);
    return Object.assign(defaultSave(), parsed);
  }catch(e){ return defaultSave(); }
}

function writeSave(){
  try{ localStorage.setItem(SAVE_KEY, JSON.stringify(SAVE)); }catch(e){}
}

let SAVE = loadSave();
