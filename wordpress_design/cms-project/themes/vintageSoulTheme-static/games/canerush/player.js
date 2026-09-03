/* =========================================================
   CANE RUSH — player.js
   The player is a small physics object: a lane index, a vertical
   position driven by real gravity, and a slide timer. Its collision
   hitbox is derived every frame from that state — never guessed.
   ========================================================= */
'use strict';

class Player{
  constructor(){ this.reset(); }

  reset(){
    this.lane = 0;
    this.laneF = 0;
    this.grounded = true;
    this.airY = 0;           // height above ground, virtual units (0 = grounded)
    this.velY = 0;           // vertical velocity, virtual units/s
    this.sliding = false;
    this.slideT = 0;
    this.hit = false;
    this.hitT = 0;
    this.lastLandParticles = false;
  }

  /** True while airborne (used to block a second jump — no accidental double jump). */
  get jumping(){ return !this.grounded; }

  moveLane(dir){
    this.lane = clamp(this.lane+dir, -1, 1);
  }

  /** Returns false if the jump could not start (already airborne/sliding/hit). */
  jump(superJump){
    if(!this.grounded || this.sliding || this.hit) return false;
    this.grounded = false;
    this.velY = superJump ? PLAYER_PHYSICS.superJumpVelocity : PLAYER_PHYSICS.jumpVelocity;
    return true;
  }

  /** Returns false if the slide could not start (airborne/already sliding/hit). */
  slide(){
    if(!this.grounded || this.sliding || this.hit) return false;
    this.sliding = true; this.slideT = 0;
    return true;
  }

  /** Advance real gravity-based vertical physics. Returns 'landed' if this frame was a landing. */
  update(dt){
    let justLanded = false;
    if(!this.grounded){
      this.velY += PLAYER_PHYSICS.gravity*dt;
      this.airY -= this.velY*dt; // velY negative = moving up = airY increasing
      if(this.airY <= 0){
        this.airY = 0; this.velY = 0; this.grounded = true;
        justLanded = true;
      }
    }
    if(this.sliding){
      this.slideT += dt;
      if(this.slideT >= PLAYER_PHYSICS.slideDuration){ this.sliding=false; this.slideT=0; }
    }
    if(this.hit){ this.hitT += dt; }
    return justLanded;
  }

  /** Smoothly interpolate the visual/collision lane position toward the target lane. Frame-rate independent. */
  updateLane(dt){
    this.laneF = smoothTo(this.laneF, this.lane, dt, PLAYER_PHYSICS.laneLerpRate);
  }

  /** Current collision height in virtual units (shrinks while sliding). */
  get collisionHeight(){
    return this.sliding ? PLAYER_PHYSICS.fullHeight*PLAYER_PHYSICS.slideHeightFactor : PLAYER_PHYSICS.fullHeight;
  }

  /** Vertical hitbox span [bottom, top] in virtual units, ground-relative. */
  get hitboxV(){
    const bottom = this.airY;
    const top = bottom + this.collisionHeight;
    return {bottom, top};
  }

  get roundedLane(){ return Math.round(this.laneF); }
}
