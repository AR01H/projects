<?php
/**
 * 404 - Page Not Found
 * Self-contained template (no dependency on header.php / footer.php).
 * Refactor to use get_header()/get_footer() once those exist.
 */

defined( 'ABSPATH' ) || exit;

$site_name = get_bloginfo( 'name' );
$home_url  = home_url( '/' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>404 - Page Not Found | <?php echo esc_html( $site_name ); ?></title>
<meta name="robots" content="noindex,nofollow">
<?php wp_head(); ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --c1:#1a2c4e;--c2:#2a4a82;--c3:#3a6abf;--c4:#6e9ad9;
  --c5:#edf2f9;--accent:#f0a500;
  --text:#1a2c4e;--muted:#5a6e8a;
}
html,body{height:100%}
body{
  font-family:'Inter','Helvetica Neue',sans-serif;
  background:var(--c5);color:var(--text);
  display:flex;flex-direction:column;min-height:100vh;
}
.pt-err-page{
  flex:1;display:flex;align-items:center;justify-content:center;
  padding:3rem 1.5rem;
}
.pt-err-inner{
  text-align:center;max-width:540px;
}
.pt-err-code{
  font-size:clamp(5rem,18vw,9rem);
  font-weight:900;
  line-height:1;
  background:linear-gradient(135deg,var(--c2) 0%,var(--c4) 100%);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text;
  letter-spacing:-4px;
  margin-bottom:.25rem;
}
.pt-err-divider{
  width:48px;height:4px;
  background:var(--accent);
  border-radius:4px;
  margin:1.25rem auto;
}
.pt-err-heading{
  font-size:clamp(1.4rem,4vw,1.9rem);
  font-weight:800;
  color:var(--c1);
  margin-bottom:1rem;
}
.pt-err-desc{
  font-size:1rem;
  line-height:1.7;
  color:var(--muted);
  margin-bottom:2.5rem;
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
  cursor:pointer;border:2px solid transparent;
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

    <div class="pt-err-code" aria-hidden="true">404</div>
    <div class="pt-err-divider"></div>

    <h1 class="pt-err-heading">Page Not Found</h1>

    <p class="pt-err-desc">
      The page you're looking for doesn't exist or may have been moved.
      Let's get you back on track.
    </p>

    <div class="pt-err-actions">
      <a href="<?php echo esc_url( $home_url ); ?>" class="pt-err-btn pt-err-btn--primary">
        &#8592; Back to Home
      </a>
      <?php
      /* Show a "Go back" button only if there's a referrer */
      if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) :
      ?>
      <a href="javascript:history.back()" class="pt-err-btn pt-err-btn--outline">
        Previous Page
      </a>
      <?php endif; ?>
    </div>

  </div>
</div>

<footer class="pt-err-footer">
  <p>
    &copy; <?php echo esc_html( date( 'Y' ) ); ?>
    <a href="<?php echo esc_url( $home_url ); ?>"><?php echo esc_html( $site_name ); ?></a>
  </p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
