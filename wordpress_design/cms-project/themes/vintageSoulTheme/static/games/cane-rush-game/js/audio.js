/* =========================================================
   CANE RUSH — audio.js
   All sound is synthesized with WebAudio (no audio files to load).
   Every public method checks SAVE.soundOn/musicOn itself, so callers
   never need to guard a call.
   ========================================================= */
'use strict';

const Audio1 = (function(){
  let ctx=null, musicTimer=null, musicStep=0, musicPlaying=false;
  function ensure(){
    if(!ctx){
      try{ ctx = new (window.AudioContext||window.webkitAudioContext)(); }catch(e){ ctx=null; }
    }
    if(ctx && ctx.state==='suspended') ctx.resume();
    return ctx;
  }
  function tone(freq, dur, type='sine', vol=0.18, delay=0, glideTo=null){
    if(!SAVE.soundOn) return;
    const c = ensure(); if(!c) return;
    const t0 = c.currentTime + delay;
    const osc = c.createOscillator();
    const gain = c.createGain();
    osc.type = type; osc.frequency.setValueAtTime(freq, t0);
    if(glideTo) osc.frequency.exponentialRampToValueAtTime(glideTo, t0+dur);
    gain.gain.setValueAtTime(0.0001, t0);
    gain.gain.exponentialRampToValueAtTime(vol, t0+0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, t0+dur);
    osc.connect(gain); gain.connect(c.destination);
    osc.start(t0); osc.stop(t0+dur+0.02);
  }
  function noiseBurst(dur, vol=0.15, delay=0){
    if(!SAVE.soundOn) return;
    const c = ensure(); if(!c) return;
    const bufferSize = c.sampleRate*dur;
    const buffer = c.createBuffer(1, bufferSize, c.sampleRate);
    const data = buffer.getChannelData(0);
    for(let i=0;i<bufferSize;i++) data[i] = (Math.random()*2-1) * (1-i/bufferSize);
    const src = c.createBufferSource(); src.buffer = buffer;
    const gain = c.createGain(); gain.gain.value = vol;
    src.connect(gain); gain.connect(c.destination);
    src.start(c.currentTime+delay);
  }
  return {
    unlock(){ ensure(); },
    coin(){ tone(1046,0.09,'square',0.12); tone(1568,0.09,'square',0.1,0.05); },
    cane(){ tone(660,0.12,'triangle',0.16); },
    fruit(){ tone(523,0.08,'sine',0.15); tone(784,0.1,'sine',0.13,0.06); },
    jump(){ tone(330,0.12,'sine',0.15,0,660); },
    slide(){ tone(220,0.1,'sawtooth',0.1,0,120); },
    powerup(){ tone(440,0.14,'triangle',0.18); tone(660,0.14,'triangle',0.16,0.08); tone(880,0.18,'triangle',0.15,0.16); },
    combo(n){ tone(500+n*40,0.12,'square',0.14); },
    nearMiss(){ tone(900,0.08,'sine',0.12); tone(1200,0.08,'sine',0.1,0.05); },
    collision(){ noiseBurst(0.3,0.2); tone(120,0.3,'sawtooth',0.16,0,60); },
    record(){ [523,659,784,1046].forEach((f,i)=>tone(f,0.16,'triangle',0.16,i*0.09)); },
    click(){ tone(700,0.05,'square',0.08); },
    startMusic(){
      if(musicPlaying || !SAVE.musicOn) return;
      musicPlaying = true;
      const notes = [523,587,659,523,784,659,587,523];
      musicStep=0;
      musicTimer = setInterval(()=>{
        if(!musicPlaying) return;
        if(SAVE.musicOn) tone(notes[musicStep%notes.length],0.16,'triangle',0.05);
        musicStep++;
      }, 260);
    },
    stopMusic(){ musicPlaying=false; if(musicTimer) clearInterval(musicTimer); }
  };
})();
