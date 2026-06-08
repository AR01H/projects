<!DOCTYPE html>
<!--
  500 — Server Error
  Pure static HTML. No PHP, no WordPress dependencies.

  WordPress doesn't load theme templates on fatal errors.
  To serve this file on a real 500:

  Apache (.htaccess):
      ErrorDocument 500 /wp-content/themes/project_theme/500.php

  Nginx (server block):
      error_page 500 502 503 504 /wp-content/themes/project_theme/500.php;
      location = /wp-content/themes/project_theme/500.php { internal; }
-->
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>500 – Server Error</title>
<meta name="robots" content="noindex,nofollow">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --c1:#1a2c4e;--c2:#2a4a82;--c3:#3a6abf;--c4:#6e9ad9;
  --c5:#edf2f9;--accent:#f0a500;
  --text:#1a2c4e;--muted:#5a6e8a;
  --err:#dc2626;--err-bg:#fef2f2;
}
html,body{height:100%}
body{
  font-family:'Inter','Helvetica Neue',Arial,sans-serif;
  background:var(--c5);color:var(--text);
  display:flex;flex-direction:column;min-height:100vh;
}
.pt-err-page{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:3rem 1.5rem;
}
.pt-err-inner{
  text-align:center;max-width:520px;
}
.pt-err-icon{
  width:80px;height:80px;
  background:var(--err-bg);
  border:2px solid rgba(220,38,38,.2);
  border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 1.5rem;
  font-size:2rem;
}
.pt-err-code{
  font-size:clamp(4.5rem,15vw,8rem);
  font-weight:900;
  line-height:1;
  background:linear-gradient(135deg,#991b1b 0%,var(--err) 100%);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text;
  letter-spacing:-3px;
  margin-bottom:.25rem;
}
.pt-err-divider{
  width:48px;height:4px;
  background:var(--err);
  border-radius:4px;
  margin:1.25rem auto;
}
.pt-err-heading{
  font-size:clamp(1.35rem,4vw,1.85rem);
  font-weight:800;
  color:var(--c1);
  margin-bottom:1rem;
}
.pt-err-desc{
  font-size:1rem;
  line-height:1.7;
  color:var(--muted);
  margin-bottom:.75rem;
}
.pt-err-note{
  display:inline-block;
  font-size:.8rem;
  color:var(--muted);
  background:rgba(110,154,217,.1);
  border:1px solid rgba(110,154,217,.2);
  border-radius:8px;
  padding:10px 18px;
  margin-bottom:2.25rem;
  line-height:1.55;
}
.pt-err-actions{
  display:flex;
  gap:12px;
  justify-content:center;
  flex-wrap:wrap;
}
.pt-err-btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:12px 28px;
  border-radius:100px;
  font-size:.9rem;font-weight:700;
  text-decoration:none;
  transition:all .25s ease;
  border:2px solid transparent;
}
.pt-err-btn--primary{
  background:var(--c2);color:#fff;border-color:var(--c2);
}
.pt-err-btn--primary:hover{
  background:var(--c1);border-color:var(--c1);
  transform:translateY(-2px);
  box-shadow:0 8px 24px rgba(26,44,78,.28);
}
.pt-err-btn--outline{
  background:transparent;color:var(--c2);border-color:var(--c2);
}
.pt-err-btn--outline:hover{
  background:var(--c2);color:#fff;
  transform:translateY(-2px);
}
.pt-err-footer{
  padding:1.25rem;
  text-align:center;
  font-size:.78rem;
  color:var(--muted);
  border-top:1px solid rgba(110,154,217,.2);
}
.pt-err-footer a{color:var(--c3);text-decoration:none;}
.pt-err-footer a:hover{text-decoration:underline;}
</style>
</head>
<body>

<div class="pt-err-page">
  <div class="pt-err-inner">

    <div class="pt-err-icon" aria-hidden="true">&#9888;</div>
    <div class="pt-err-code" aria-hidden="true">500</div>
    <div class="pt-err-divider"></div>

    <h1 class="pt-err-heading">Something Went Wrong</h1>

    <p class="pt-err-desc">
      Our server ran into an unexpected problem.
      We're aware and working to fix it as quickly as possible.
    </p>

    <span class="pt-err-note">
      This is a temporary issue &mdash; please try again in a few minutes.
    </span>

    <div class="pt-err-actions">
      <a href="/" class="pt-err-btn pt-err-btn--primary">
        &#8592; Back to Home
      </a>
      <a href="javascript:location.reload()" class="pt-err-btn pt-err-btn--outline">
        Try Again
      </a>
    </div>

  </div>
</div>

<footer class="pt-err-footer">
  <p>&copy; <span id="yr"></span> Project Theme</p>
</footer>

<script>document.getElementById('yr').textContent = new Date().getFullYear();</script>
</body>
</html>
