/* =========================================================
   CANE RUSH — projector.js
   Fake-3D lane projection: turns (lane, z) game coordinates into
   real screen (x, y, scale). z runs 0 (far/horizon) -> 1 (near/player).
   ========================================================= */
'use strict';

class Projector{
  constructor(canvas){ this.canvas = canvas; this.recalc(); }
  recalc(){
    const w = this.canvas.width, h = this.canvas.height;
    this.w=w; this.h=h;
    this.horizonY = h*HORIZON_Y_RATIO;
    this.groundY = h*0.98;
    this.centerX = w/2;
    this.topHalfLane = w*0.03;
    this.bottomHalfLane = w*0.34;
  }
  laneHalfWidth(z){ return lerp(this.topHalfLane, this.bottomHalfLane, z); }
  x(lane, z){ return this.centerX + lane*this.laneHalfWidth(z); }
  y(z){ return lerp(this.horizonY, this.groundY, z); }
  scale(z){ return lerp(0.16, 1.0, z); }
}
