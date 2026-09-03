/* =========================================================
   CANE RUSH — assets.js
   Loads every external SVG asset once at boot and hands back
   ready-to-draw <img> elements by name. Nothing else in the
   codebase touches the network or the DOM Image API directly.
   If an image isn't ready yet (or fails to load), get() returns
   null and callers fall back to their own vector drawing — the
   game never shows a blank token while art streams in.
   ========================================================= */
'use strict';

const AssetManager = (function(){
  const cache = {};
  let total = 0, loaded = 0;

  function load(name, path){
    total++;
    const img = new Image();
    cache[name] = { img, ready:false };
    img.onload = ()=>{ cache[name].ready = true; loaded++; };
    img.onerror = ()=>{ loaded++; console.warn('CaneRush: failed to load asset', path); };
    img.src = path;
    return img;
  }

  return {
    /** Kick off loading every asset referenced in ASSET_MANIFEST (config.js). */
    preload(manifest){
      Object.entries(manifest).forEach(([name, path])=>load(name, path));
    },
    get(name){
      const entry = cache[name];
      return entry && entry.ready ? entry.img : null;
    },
    get progress(){ return total===0 ? 1 : loaded/total; },
    get isReady(){ return total>0 && loaded>=total; }
  };
})();
