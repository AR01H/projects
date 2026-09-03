/* =========================================================
   CANE RUSH — utils.js
   Generic, stateless helpers: math, easing, random, canvas paths
   and color math. Nothing in here knows about game rules.
   ========================================================= */
'use strict';

function lerp(a,b,t){ return a+(b-a)*t; }
function clamp(v,mn,mx){ return Math.max(mn,Math.min(mx,v)); }
function easeOutCubic(t){ return 1-Math.pow(1-t,3); }
function easeInOutSine(t){ return -(Math.cos(Math.PI*t)-1)/2; }
function rand(a,b){ return a+Math.random()*(b-a); }
function randInt(a,b){ return Math.floor(rand(a,b+1)); }
function pick(arr){ return arr[Math.floor(Math.random()*arr.length)]; }

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

function animateCount(id, target){
  const el = document.getElementById(id);
  let cur=0; const step=Math.max(1,Math.round(target/40));
  const t=setInterval(()=>{
    cur+=step;
    if(cur>=target){ cur=target; clearInterval(t); }
    el.textContent = cur.toLocaleString();
  },16);
}
