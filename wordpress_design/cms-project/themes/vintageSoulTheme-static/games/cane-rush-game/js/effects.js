/* =========================================================
   CANE RUSH — effects.js
   Purely visual, stateless-per-frame systems: particle bursts and
   floating score/label text. Neither one knows any game rules —
   other files call .burst()/.add() and these just animate to zero.
   ========================================================= */
'use strict';

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
  update(){
    for(let i=this.list.length-1;i>=0;i--){
      const p=this.list[i];
      p.x+=p.vx; p.y+=p.vy; p.vy+=p.g; p.life--;
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
function drawStar(ctx,x,y,r,points){
  ctx.save(); ctx.translate(x,y); ctx.beginPath();
  for(let i=0;i<points*2;i++){
    const rad = i%2===0?r:r*0.45;
    const ang = (Math.PI/points)*i - Math.PI/2;
    ctx.lineTo(Math.cos(ang)*rad, Math.sin(ang)*rad);
  }
  ctx.closePath(); ctx.fill(); ctx.restore();
}

class Floaters{
  constructor(){ this.list=[]; }
  add(x,y,text,opts={}){
    this.list.push({ x,y,text, life:opts.life||55, maxLife:opts.life||55,
      color:opts.color||COLORS.ink, size:opts.size||20, vy:opts.vy!=null?opts.vy:-1.1 });
  }
  update(){
    for(let i=this.list.length-1;i>=0;i--){
      const f=this.list[i]; f.y+=f.vy; f.life--; if(f.life<=0) this.list.splice(i,1);
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
      ctx.font = `800 ${f.size}px 'Baloo 2', Poppins, sans-serif`;
      ctx.lineWidth=4; ctx.strokeStyle='rgba(255,253,247,0.95)';
      ctx.strokeText(f.text,0,0);
      ctx.fillStyle=f.color;
      ctx.fillText(f.text,0,0);
      ctx.restore();
    }
  }
}
