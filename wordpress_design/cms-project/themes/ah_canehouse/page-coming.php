<?php
/* ═══════════════════════════════════════════
   THE CANE HOUSE - CONTACT PAGE
   All editable values live here at the top
   ═══════════════════════════════════════════ */

$site_name   = CH_BRAND_NAME;
$moto        = CH_BRAND_TAGLINE;
$logo_url    = get_template_directory_uri() . '/assets/images/logo.png';
$phone       = CONTACT_NUMBER;
$email       = CONTACT_EMAIL;

$services = [
  [
    "title" => "Franchise Opportunities",
    "desc"  => "Open your own Cane House outlet. Contact our team to explore franchise locations and partnership models.",
    "icon"  => "home"
  ],
  [
    "title" => "Event Catering",
    "desc"  => "Fresh sugarcane juice at your wedding, corporate event, or festival. We set up, serve, and keep the juice flowing.",
    "icon"  => "users"
  ]
];

$launching_soon_text = "Order your favourite <b>sugarcane juice</b> flavours online, book event catering, and explore our full menu - all from your phone. We're almost ready.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $site_name; ?> – Contact</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0f2808;
  --card:rgba(28,65,10,0.55);
  --card-h:rgba(28,65,10,0.78);
  --lime:#d4f53c;
  --white:#f4ffea;
  --muted:rgba(210,255,175,0.45);
  --border:rgba(180,240,60,0.18);
  --ff:'Syne',sans-serif;
  --fb:'DM Sans',sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--white);font-family:var(--fb);font-weight:300;overflow-x:hidden;min-height:100vh}

/* ── KEYFRAMES ── */
@keyframes sway1{0%,100%{transform:rotate(-4deg)}50%{transform:rotate(4deg) translateX(5px)}}
@keyframes sway2{0%,100%{transform:rotate(3deg)}50%{transform:rotate(-3deg) translateX(-4px)}}
@keyframes sway3{0%,100%{transform:rotate(-2deg)}50%{transform:rotate(3deg)}}
@keyframes shimmer{0%,100%{opacity:1}50%{opacity:.72}}
@keyframes rise{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.55);opacity:.55}}
@keyframes bubble{0%{transform:translateY(0);opacity:.7}100%{transform:translateY(-38px);opacity:0}}

/* floating fruit icons - pop in once then float gently */
@keyframes fruit-pop{
  0%  {opacity:0;transform:scale(0) rotate(-20deg) translateY(0)}
  55% {opacity:1;transform:scale(1.15) rotate(6deg) translateY(-12px)}
  75% {transform:scale(.96) rotate(-3deg) translateY(-8px)}
  100%{opacity:1;transform:scale(1) rotate(0deg) translateY(-10px)}
}
@keyframes fruit-float{
  0%,100%{transform:translateY(-10px) rotate(var(--fr,0deg))}
  50%    {transform:translateY(-22px) rotate(var(--fr2,4deg))}
}
.fruit{
  position:fixed;
  font-size:var(--fs,2rem);
  z-index:111;
  pointer-events:none;
  opacity:0.25 !important;
  animation:
    fruit-pop   .7s cubic-bezier(.34,1.56,.64,1) var(--pd,.4s) forwards,
    fruit-float 3.5s ease-in-out var(--fd,1.2s) infinite;
}

/* ── CANE BG ── */
.bg-cane{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.bg-cane svg{width:100%;height:100%}
.sl1{transform-origin:bottom center;animation:sway1 4.2s ease-in-out infinite}
.sl2{transform-origin:bottom center;animation:sway2 5.5s ease-in-out infinite .6s}
.sl3{transform-origin:bottom center;animation:sway3 3.8s ease-in-out infinite .2s}
.sr1{transform-origin:bottom center;animation:sway2 4.8s ease-in-out infinite .3s}
.sr2{transform-origin:bottom center;animation:sway1 4s ease-in-out infinite .8s}
.sr3{transform-origin:bottom center;animation:sway3 5.6s ease-in-out infinite}

/* ── PAGE ── */
.page{position:relative;z-index:2;max-width:820px;margin:0 auto;padding:2.2rem 1.4rem 4rem;animation:rise .75s ease both}

/* ── HEADER ── */
.top{display:flex;flex-direction:column;align-items:center;text-align:center;padding-bottom:1.8rem;}
.logo{height:50vh;width:auto;object-fit:contain;animation:shimmer 3s ease-in-out infinite}
.site-name{font-family:var(--ff);font-size:1.05rem;font-weight:800;color:var(--white);margin-top:.55rem;letter-spacing:.04em}
.moto{font-size:11.5px;font-weight:300;color:var(--muted);letter-spacing:.06em;margin-top:.22rem;font-style:italic}
.fresh-tag{margin-top:.75rem;display:inline-flex;align-items:center;gap:6px;background:rgba(212,245,60,.11);border:1px solid rgba(212,245,60,.28);border-radius:50px;padding:4px 14px;font-size:10px;font-weight:500;letter-spacing:.16em;text-transform:uppercase;color:var(--lime)}

/* ── LABELS & TITLES ── */
.sec-lbl{font-size:10px;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:var(--lime);opacity:.8;margin-bottom:.4rem}
.sec-title{font-family:var(--ff);font-size:clamp(1.3rem,3vw,1.85rem);font-weight:800;color:var(--white);line-height:1.15;margin-bottom:1.3rem}
.sec-title em{color:var(--lime);font-style:normal}

/* ── FLAVOUR GRID ── */
.flavours{display:grid;grid-template-columns:repeat(auto-fill,minmax(148px,1fr));gap:.8rem}
.fcard{background:var(--card);border:1px solid var(--border);border-radius:18px;padding:1.2rem 1rem 1rem;display:flex;flex-direction:column;align-items:center;text-align:center;position:relative;overflow:hidden;transition:transform .22s,border-color .22s,background .22s;cursor:default}
.fcard::before{content:'';position:absolute;bottom:0;left:0;right:0;height:var(--lh,55%);background:var(--lc,rgba(212,245,60,.1));border-radius:0 0 18px 18px;transition:background .3s}
.fcard:hover{transform:translateY(-5px) scale(1.03);border-color:rgba(212,245,60,.42)}
.fcard:hover::before{filter:brightness(1.4)}
.fcard-inner{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center}
.glass-wrap{width:48px;height:62px;margin-bottom:.65rem}
.glass-wrap svg{width:100%;height:100%}
.bbl{animation:bubble 2.2s ease-in infinite var(--bd,0s)}
.fname{font-family:var(--ff);font-size:.8rem;font-weight:700;color:var(--white);line-height:1.2;margin-bottom:.2rem}
.ftag{font-size:10.5px;font-weight:300;color:var(--muted);line-height:1.5}

/* ── RULE ── */
.rule{border:none;height:1px;background:linear-gradient(90deg,transparent,rgba(180,240,60,.25) 40%,rgba(180,240,60,.25) 60%,transparent);margin:2.2rem 0 0}

/* ── CONTACT PILLS ── */
.pills{display:flex;flex-wrap:wrap;gap:.75rem;margin-top:1.1rem}
.pill{flex:1;min-width:190px;display:flex;align-items:center;gap:11px;background:rgba(212,245,60,.1);border:1px solid rgba(212,245,60,.26);border-radius:12px;padding:.85rem 1.1rem;text-decoration:none;color:var(--white);transition:background .2s,transform .2s}
.pill:hover{background:rgba(212,245,60,.18);transform:translateY(-2px)}
.pill svg{width:17px;height:17px;stroke:var(--lime);stroke-width:2;fill:none;flex-shrink:0}
.pill-lbl{font-size:9px;font-weight:500;letter-spacing:.14em;text-transform:uppercase;color:var(--lime);margin-bottom:2px}
.pill-val{font-size:13px;font-weight:500}

/* ── SERVICE CARDS ── */
.svc-note{font-size:12.5px;font-weight:300;color:var(--muted);line-height:1.75;margin-bottom:1.1rem}
.svc-note em{color:var(--lime);font-style:normal}
.svc-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(135px,1fr));gap:.18rem}
.svc{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:0.5rem;transition:background .22s,border-color .22s,transform .2s;opacity:0;transform:translateY(14px)}
.svc.vis{opacity:1;transform:none;transition:opacity .5s ease,transform .5s ease,background .22s,border-color .22s}
.svc:hover{background:var(--card-h);border-color:rgba(212,245,60,.38);transform:translateY(-2px)}
.svc-ico{width:36px;height:36px;border-radius:9px;background:rgba(212,245,60,.1);border:1px solid rgba(212,245,60,.2);display:flex;align-items:center;justify-content:center;margin-bottom:.75rem}
.svc-ico svg{width:17px;height:17px;stroke:var(--lime);stroke-width:2;fill:none}
.svc-name{font-family:var(--ff);font-size:.84rem;font-weight:700;color:var(--white);margin-bottom:.28rem}
.svc-txt{font-size:12px;font-weight:300;color:var(--muted);line-height:1.7}

/* ── LAUNCH BANNER ── */
.launch{border:1px solid rgba(212,245,60,.26);border-radius:18px;padding:1rem;text-align:center;background:rgba(22,55,8,.7);position:relative;overflow:hidden}
.launch::after{content:'';position:absolute;top:-50px;left:50%;transform:translateX(-50%);width:240px;height:120px;background:radial-gradient(ellipse,rgba(212,245,60,.08) 0%,transparent 70%);pointer-events:none}
.launch-badge{display:inline-flex;align-items:center;gap:7px;background:rgba(212,245,60,.1);border:1px solid rgba(212,245,60,.26);border-radius:50px;padding:4px 13px;font-size:10px;font-weight:500;letter-spacing:.15em;text-transform:uppercase;color:var(--lime);margin-bottom:.9rem}
.launch-dot{width:6px;height:6px;border-radius:50%;background:var(--lime);animation:pulse 1.8s ease-in-out infinite;flex-shrink:0}
.launch h2{font-family:var(--ff);font-size:clamp(1.15rem,3.5vw,1.7rem);font-weight:800;color:var(--white);margin-bottom:.45rem}
.launch h2 em{color:var(--lime);font-style:normal}
.launch p{font-size:13px;font-weight:300;color:var(--muted);line-height:1.8;max-width:380px;margin:0 auto 1.3rem}
.notify{display:flex;gap:8px;max-width:340px;margin:0 auto;flex-wrap:wrap;justify-content:center}
.n-in{flex:1;min-width:160px;background:rgba(28,65,10,.7);border:1px solid var(--border);border-radius:50px;padding:.6rem 1rem;font-family:var(--fb);font-size:12.5px;font-weight:300;color:var(--white);outline:none;transition:border-color .2s}
.n-in::placeholder{color:var(--muted)}
.n-in:focus{border-color:rgba(212,245,60,.4)}
.n-btn{background:var(--lime);color:#0f2808;border:none;border-radius:50px;padding:.6rem 1.3rem;font-family:var(--fb);font-size:12.5px;font-weight:600;cursor:pointer;transition:opacity .2s,transform .15s;white-space:nowrap}
.n-btn:hover{opacity:.86;transform:translateY(-1px)}

/* ── FOOTER ── */
footer{text-align:center;margin-top:2.4rem;font-size:11.5px;color:var(--muted)}
footer em{color:var(--lime);font-style:normal}

@media(max-width:500px){
  .flavours{grid-template-columns:repeat(2,1fr)}
}
</style>
</head>
<body>

<!-- ══ FLOATING FRUIT ICONS (pop in once, then gently float) ══ -->
<div class="fruit" style="left:5%; top:18%;--fs:2.2rem;--pd:.3s;--fd:1.1s;--fr:-8deg;--fr2:4deg">🍋</div>
<div class="fruit" style="left:88%;top:72%;--fs:2rem;  --pd:.5s;--fd:1.4s;--fr:6deg; --fr2:-5deg">🥭</div>
<div class="fruit" style="left:43%; top:52%;--fs:1.8rem;--pd:.7s;--fd:1.7s;--fr:-5deg;--fr2:7deg">🌿</div>
<div class="fruit" style="left:31%;top:45%;--fs:1.9rem;--pd:.6s;--fd:1.3s;--fr:7deg; --fr2:-4deg">🍍</div>
<div class="fruit" style="left:75%; top:80%;--fs:2rem;  --pd:.9s;--fd:1.8s;--fr:-6deg;--fr2:5deg">🍉</div>
<div class="fruit" style="left:15%;top:75%;--fs:2.1rem;--pd:.4s;--fd:1.5s;--fr:5deg; --fr2:-6deg">🫚</div>
<div class="fruit" style="left:93%;top:28%;--fs:1.7rem;--pd:1s; --fd:2s;  --fr:8deg; --fr2:-3deg">🌿</div>

<!-- ══ CANE BG ══ -->
<div class="bg-cane">
<svg viewBox="0 0 1080 900" preserveAspectRatio="xMidYMid slice">
  <g class="sl1"><rect x="18" y="0" width="13" height="900" rx="6" fill="#2d5a1b" opacity=".8"/><ellipse cx="24" cy="70" rx="34" ry="10" fill="#3a7020" opacity=".72" transform="rotate(-25,24,70)"/><ellipse cx="24" cy="250" rx="36" ry="10" fill="#3a7020" opacity=".62" transform="rotate(21,24,250)"/><ellipse cx="24" cy="450" rx="31" ry="9" fill="#3a7020" opacity=".52" transform="rotate(-18,24,450)"/><ellipse cx="24" cy="650" rx="28" ry="8" fill="#3a7020" opacity=".42" transform="rotate(15,24,650)"/></g>
  <g class="sl2"><rect x="57" y="100" width="10" height="800" rx="5" fill="#4a8c2a" opacity=".6"/><ellipse cx="62" cy="115" rx="26" ry="8" fill="#5aaa32" opacity=".6" transform="rotate(23,62,115)"/><ellipse cx="62" cy="310" rx="28" ry="8" fill="#5aaa32" opacity=".5" transform="rotate(-21,62,310)"/><ellipse cx="62" cy="520" rx="24" ry="7" fill="#5aaa32" opacity=".4" transform="rotate(18,62,520)"/></g>
  <g class="sl3"><rect x="3" y="300" width="8" height="600" rx="4" fill="#2d5a1b" opacity=".38"/><ellipse cx="7" cy="315" rx="20" ry="6" fill="#3a7020" opacity=".38" transform="rotate(-14,7,315)"/><ellipse cx="7" cy="510" rx="18" ry="5" fill="#3a7020" opacity=".28" transform="rotate(12,7,510)"/></g>
  <g class="sr1"><rect x="1049" y="0" width="13" height="900" rx="6" fill="#2d5a1b" opacity=".8"/><ellipse cx="1055" cy="60" rx="34" ry="10" fill="#3a7020" opacity=".72" transform="rotate(25,1055,60)"/><ellipse cx="1055" cy="240" rx="36" ry="10" fill="#3a7020" opacity=".62" transform="rotate(-21,1055,240)"/><ellipse cx="1055" cy="440" rx="31" ry="9" fill="#3a7020" opacity=".52" transform="rotate(18,1055,440)"/><ellipse cx="1055" cy="640" rx="28" ry="8" fill="#3a7020" opacity=".42" transform="rotate(-15,1055,640)"/></g>
  <g class="sr2"><rect x="1012" y="80" width="10" height="820" rx="5" fill="#4a8c2a" opacity=".6"/><ellipse cx="1017" cy="95" rx="26" ry="8" fill="#5aaa32" opacity=".6" transform="rotate(-23,1017,95)"/><ellipse cx="1017" cy="290" rx="28" ry="8" fill="#5aaa32" opacity=".5" transform="rotate(21,1017,290)"/><ellipse cx="1017" cy="500" rx="24" ry="7" fill="#5aaa32" opacity=".4" transform="rotate(-18,1017,500)"/></g>
  <g class="sr3"><rect x="1069" y="250" width="8" height="650" rx="4" fill="#2d5a1b" opacity=".38"/><ellipse cx="1073" cy="265" rx="20" ry="6" fill="#3a7020" opacity=".38" transform="rotate(14,1073,265)"/><ellipse cx="1073" cy="460" rx="18" ry="5" fill="#3a7020" opacity=".28" transform="rotate(-12,1073,460)"/></g>
</svg>
</div>

<!-- ══ PAGE ══ -->
<div class="page">

  <!-- HEADER -->
  <header class="top">
    <img class="logo" src="<?php echo $logo_url; ?>" alt="<?php echo $site_name; ?>">
    <div class="moto"><?php echo $moto; ?></div>
	 <div class="fresh-tag">
   <p>
	    Bringing Back Childhood Memories, Freshly pressed Sugarcane juice right before your eyes, enjoyed across the world for generations.</p>
    
</div>
  </header>



  <div class="launch">
    <div class="launch-badge"><span class="launch-dot"></span> Coming Soon</div>
    <h2>Website &amp; Online Ordering <em>Launching Soon</em></h2>
    <p><?php echo $launching_soon_text; ?></p>
  </div>
  <!-- LAUNCHING SOON -->

    <!-- CONTACT -->
  <div style="margin-top:2.2rem">
    <p class="sec-lbl">Contact Us</p>
    <div class="sec-title">Get in touch &amp; <em>place your order</em></div>

    <div class="pills">
      <a href="tel:<?php echo preg_replace('/\s+/','',$phone); ?>" class="pill">
        <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.9 12.22 19.79 19.79 0 0 1 1.83 3.68 2 2 0 0 1 3.81 1.5h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.22a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        <div>
          <div class="pill-lbl">Call Us</div>
          <div class="pill-val"><?php echo $phone; ?></div>
        </div>
      </a>
      <a href="mailto:<?php echo $email; ?>" class="pill">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <div>
          <div class="pill-lbl">Email Us</div>
          <div class="pill-val"><?php echo $email; ?></div>
        </div>
      </a>
    </div>
  </div>


</div><!-- .page -->

<script>
function handleNotify(btn){
  const inp=btn.previousElementSibling;
  if(!inp.value||!inp.value.includes('@')){inp.style.borderColor='rgba(220,80,80,.5)';inp.focus();return;}
  inp.style.borderColor='';
  btn.textContent='✓ You\'re on the list!';
  btn.style.background='#6abf3a';
  btn.disabled=true;inp.disabled=true;inp.style.opacity='.5';
}
const io=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add('vis');});
},{threshold:.1});
document.querySelectorAll('.svc').forEach((el,i)=>{
  el.style.transitionDelay=(i*.09)+'s';
  io.observe(el);
});
</script>
</body>
</html>