<html><head><style>
:root {
  --ch-green-deep: #2d5a1b;
  --ch-green-mid: #4a8c2a;
  --ch-green-bright: #6abf3a;
  --ch-green-light: #a8d96e;
  --ch-lime: #c8e830;
  --ch-white: #fdfff8;
  --ch-bg-dark: #1a3a0f;
  --ch-text-muted: #6a8c50;
  --ch-font-body: 'Poppins', sans-serif;
  --ch-font-display: 'Nunito', sans-serif;
}

body{
    padding:0;
    margin:0;
    overflow: hidden;
}

@keyframes sway1 { 0%,100%{transform:rotate(-3deg) translateX(0)} 50%{transform:rotate(3deg) translateX(4px)} }
@keyframes sway2 { 0%,100%{transform:rotate(2deg) translateX(0)} 50%{transform:rotate(-2deg) translateX(-3px)} }
@keyframes sway3 { 0%,100%{transform:rotate(-1.5deg)} 50%{transform:rotate(2.5deg)} }
@keyframes rise  { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
@keyframes shimmer { 0%,100%{opacity:1} 50%{opacity:0.7} }
@keyframes particle-float {
  0%   { transform: translateY(0px) translateX(0px); opacity: 0; }
  20%  { opacity: 0.6; }
  80%  { opacity: 0.3; }
  100% { transform: translateY(-120px) translateX(var(--dx, 20px)); opacity: 0; }
}
.ch-wrap {
  background: var(--ch-bg-dark);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  font-family: var(--ch-font-body);
}
.bg-svg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}
.stalk-l1 { transform-origin: bottom center; animation: sway1 4s ease-in-out infinite; }
.stalk-l2 { transform-origin: bottom center; animation: sway2 5s ease-in-out infinite 0.5s; }
.stalk-l3 { transform-origin: bottom center; animation: sway3 3.5s ease-in-out infinite 0.2s; }
.stalk-r1 { transform-origin: bottom center; animation: sway2 4.5s ease-in-out infinite 0.3s; }
.stalk-r2 { transform-origin: bottom center; animation: sway1 3.8s ease-in-out infinite 0.7s; }
.stalk-r3 { transform-origin: bottom center; animation: sway3 5.2s ease-in-out infinite 0.1s; }
.particle {
  position: absolute;
  width: 4px; height: 4px;
  border-radius: 50%;
  background: var(--ch-lime);
  animation: particle-float var(--dur, 4s) ease-in-out infinite var(--delay, 0s);
}
.content {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  animation: rise 1s ease both;
}
.logo-wrap {
  margin-bottom: 0.25rem;
  animation: shimmer 3s ease-in-out infinite;
}
.logo-wrap img {
  width: auto;
  object-fit: contain;
  /* filter: brightness(0) invert(1); */
  min-height: 70vh;
}
.main-title {
  font-family: var(--ch-font-display);
  font-size: clamp(2.2rem, 5vw, 3.2rem);
  font-weight: 800;
  color: var(--ch-white);
  line-height: 1.15;
  margin: 0 0 1rem;
  letter-spacing: -0.5px;
}
.main-title .accent {
  color: var(--ch-lime);
}
.divider {
  width: 60px;
  height: 3px;
  background: var(--ch-lime);
  border-radius: 2px;
  margin: 0 auto 1.2rem;
  opacity: 0.8;
}
.sub-text {
  font-size: 15px;
  font-weight: 300;
  color: rgba(232,245,224,0.65);
  line-height: 1.8;
  margin: 0;
}
.done-badge {
  margin-top: 2.2rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(200,232,48,0.12);
  border: 1px solid rgba(200,232,48,0.35);
  border-radius: 50px;
  padding: 8px 20px;
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--ch-lime);
}
.done-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: var(--ch-lime);
  box-shadow: 0 0 6px var(--ch-lime);
  animation: shimmer 1.5s ease-in-out infinite;
}
</style>

<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@800&amp;family=Poppins:wght@300;500&amp;display=swap" rel="stylesheet">

</head><body><div class="ch-wrap">

  <!-- Particles -->
  <div class="particle" style="left:15%;bottom:20%;--dur:5s;--delay:0s;--dx:15px;opacity:0.5"></div>
  <div class="particle" style="left:25%;bottom:15%;--dur:6s;--delay:1s;--dx:-10px;opacity:0.4"></div>
  <div class="particle" style="left:70%;bottom:25%;--dur:4.5s;--delay:0.5s;--dx:12px;opacity:0.5"></div>
  <div class="particle" style="left:80%;bottom:18%;--dur:5.5s;--delay:1.5s;--dx:-8px;opacity:0.3"></div>
  <div class="particle" style="left:50%;bottom:10%;--dur:7s;--delay:2s;--dx:20px;opacity:0.4"></div>
  <div class="particle" style="left:40%;bottom:22%;--dur:4s;--delay:0.8s;--dx:-15px;opacity:0.3"></div>

  <!-- Animated sugarcane background -->
  <svg class="bg-svg" viewBox="0 0 680 560" preserveAspectRatio="xMidYMid slice">

    <!-- Left stalks -->
    <g class="stalk-l1">
      <rect x="30" y="80" width="10" height="480" rx="5" fill="#2d5a1b" opacity="0.9"></rect>
      <ellipse cx="35" cy="90" rx="28" ry="9" fill="#3a7020" opacity="0.8" transform="rotate(-25,35,90)"></ellipse>
      <ellipse cx="35" cy="200" rx="30" ry="9" fill="#3a7020" opacity="0.7" transform="rotate(20,35,200)"></ellipse>
      <ellipse cx="35" cy="320" rx="26" ry="8" fill="#3a7020" opacity="0.6" transform="rotate(-18,35,320)"></ellipse>
      <ellipse cx="35" cy="430" rx="24" ry="7" fill="#3a7020" opacity="0.5" transform="rotate(15,35,430)"></ellipse>
    </g>
    <g class="stalk-l2">
      <rect x="65" y="120" width="8" height="440" rx="4" fill="#4a8c2a" opacity="0.7"></rect>
      <ellipse cx="69" cy="130" rx="22" ry="7" fill="#5aaa32" opacity="0.7" transform="rotate(22,69,130)"></ellipse>
      <ellipse cx="69" cy="250" rx="24" ry="7" fill="#5aaa32" opacity="0.6" transform="rotate(-20,69,250)"></ellipse>
      <ellipse cx="69" cy="380" rx="20" ry="6" fill="#5aaa32" opacity="0.5" transform="rotate(18,69,380)"></ellipse>
    </g>
    <g class="stalk-l3">
      <rect x="10" y="200" width="7" height="360" rx="3" fill="#2d5a1b" opacity="0.5"></rect>
      <ellipse cx="13" cy="210" rx="18" ry="6" fill="#3a7020" opacity="0.5" transform="rotate(-15,13,210)"></ellipse>
      <ellipse cx="13" cy="340" rx="16" ry="5" fill="#3a7020" opacity="0.4" transform="rotate(12,13,340)"></ellipse>
    </g>

    <!-- Right stalks -->
    <g class="stalk-r1">
      <rect x="640" y="60" width="10" height="500" rx="5" fill="#2d5a1b" opacity="0.9"></rect>
      <ellipse cx="645" cy="70" rx="28" ry="9" fill="#3a7020" opacity="0.8" transform="rotate(25,645,70)"></ellipse>
      <ellipse cx="645" cy="190" rx="30" ry="9" fill="#3a7020" opacity="0.7" transform="rotate(-20,645,190)"></ellipse>
      <ellipse cx="645" cy="310" rx="26" ry="8" fill="#3a7020" opacity="0.6" transform="rotate(18,645,310)"></ellipse>
      <ellipse cx="645" cy="420" rx="24" ry="7" fill="#3a7020" opacity="0.5" transform="rotate(-15,645,420)"></ellipse>
    </g>
    <g class="stalk-r2">
      <rect x="608" y="100" width="8" height="460" rx="4" fill="#4a8c2a" opacity="0.7"></rect>
      <ellipse cx="612" cy="110" rx="22" ry="7" fill="#5aaa32" opacity="0.7" transform="rotate(-22,612,110)"></ellipse>
      <ellipse cx="612" cy="240" rx="24" ry="7" fill="#5aaa32" opacity="0.6" transform="rotate(20,612,240)"></ellipse>
      <ellipse cx="612" cy="370" rx="20" ry="6" fill="#5aaa32" opacity="0.5" transform="rotate(-18,612,370)"></ellipse>
    </g>
    <g class="stalk-r3">
      <rect x="663" y="180" width="7" height="380" rx="3" fill="#2d5a1b" opacity="0.5"></rect>
      <ellipse cx="666" cy="190" rx="18" ry="6" fill="#3a7020" opacity="0.5" transform="rotate(15,666,190)"></ellipse>
      <ellipse cx="666" cy="320" rx="16" ry="5" fill="#3a7020" opacity="0.4" transform="rotate(-12,666,320)"></ellipse>
    </g>

    <!-- Ground line -->
    <rect x="0" y="540" width="680" height="20" rx="0" fill="#1a3a0f" opacity="0.8"></rect>
    <rect x="0" y="535" width="680" height="8" rx="0" fill="#2d5a1b" opacity="0.4"></rect>

  </svg>

  <!-- Main content -->
  <div class="content">
    <div class="logo-wrap">
      <img src="<?php echo (get_template_directory_uri() . '/assets/images/logo.png') ?>" alt="The Cane House">
    </div>

    <div>
        <h1 class="main-title">
          <span class="accent">Lauching</span> Soon <br>with something <span class="accent">fresh</span>
        </h1>
    </div>
  </div>

</div>
</body></html>