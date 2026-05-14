<?php
/**
 * inc/login-page.php
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS FILE EXISTS:
 *   The WordPress login page at /wp-login.php shows the default WordPress
 *   logo and grey styling. This file replaces it completely with The Cane
 *   House branded login — deep green background, lime accents, brand logo.
 *
 * WHAT IT CHANGES:
 *   - Background: deep forest green with sugarcane field texture
 *   - Logo: The Cane House name instead of WP logo
 *   - Logo link: goes to your website, not wordpress.org
 *   - Form: white card with green accents
 *   - Buttons: lime green
 *   - Footer links: hidden (no need to show WP links)
 *
 * HOW IT WORKS:
 *   login_enqueue_scripts → loads our login.css
 *   login_headerurl       → changes logo link destination
 *   login_headertext      → changes logo alt text
 *   login_head            → injects inline critical styles
 *   login_footer          → adds extra HTML to login page footer
 * ─────────────────────────────────────────────────────────────────────────────
 */

if (!defined('ABSPATH')) exit;


// ── 1. LOAD LOGIN CSS + FONTS ─────────────────────────────────────────────────
// WHY: login_enqueue_scripts is the correct hook for the login page.
//      wp_enqueue_scripts does NOT fire on the login page.
add_action('login_enqueue_scripts', function () {

    wp_enqueue_style(
        'ch-login-css',
        get_template_directory_uri() . '/admin/login.css',
        [],
        '1.0'
    );

    wp_enqueue_style(
        'ch-login-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap',
        [],
        null
    );

});


// ── 2. CHANGE LOGO LINK URL ───────────────────────────────────────────────────
// WHY: Default logo links to wordpress.org — useless. We link to the website.
add_filter('login_headerurl', function () {
    return home_url('/');
});


// ── 3. CHANGE LOGO ALT TEXT ───────────────────────────────────────────────────
add_filter('login_headertext', function () {
    return 'The Cane House — Admin Login';
});


// ── 4. INJECT INLINE LOGIN STYLES ────────────────────────────────────────────
// WHY: Some WP login elements need very specific targeting that's easier
//      inline. This handles the logo replacement and key structural styles.
add_action('login_head', function () { ?>
<style>
/* ── Full page background ───────────────────────────────── */
body.login {
    background: linear-gradient(155deg, #050e03 0%, #0d1f08 50%, #1a3a0a 100%) !important;
    font-family: 'DM Sans', sans-serif !important;
    position: relative;
}

/* Subtle texture overlay */
body.login::before {
    content: '';
    position: fixed; inset: 0;
    background-image: radial-gradient(circle at 20% 50%, rgba(200,232,48,0.06) 0%, transparent 50%),
                      radial-gradient(circle at 80% 20%, rgba(106,191,58,0.04) 0%, transparent 40%);
    pointer-events: none;
}

/* ── Logo area — replace WP logo with brand text ───────── */
#login h1 a {
    background-image: none !important;
    width: auto !important;
    height: auto !important;
    font-family: 'Cormorant Garamond', serif !important;
    font-size: 42px !important;
    font-weight: 300 !important;
    color: #ffffff !important;
    text-align: center !important;
    display: block !important;
    text-indent: 0 !important;
    line-height: 1.1 !important;
    margin-bottom: 8px !important;
    text-shadow: none !important;
}
/* Inject the brand name via content */
#login h1 a::before {
    content: '🌿';
    display: block;
    font-size: 36px;
    margin-bottom: 8px;
}
#login h1 a::after {
    content: 'The Cane House';
}
/* Hide the actual text node (the default alt text) */
#login h1 a span { display: none !important; }

/* Sub-tagline below logo */
#login h1::after {
    content: 'Admin Portal — Authorised Access Only';
    display: block;
    font-family: 'DM Sans', sans-serif;
    font-size: 10px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: rgba(200,232,48,0.5);
    text-align: center;
    margin-top: 8px;
}

/* ── Login form card ────────────────────────────────────── */
#login {
    width: 360px !important;
    padding: 0 !important;
}
#loginform {
    background: rgba(255,255,255,0.04) !important;
    border: 1px solid rgba(200,232,48,0.15) !important;
    border-radius: 4px !important;
    box-shadow: 0 24px 60px rgba(0,0,0,0.5) !important;
    padding: 36px 36px 28px !important;
    margin-top: 16px !important;
}

/* ── Labels ─────────────────────────────────────────────── */
#loginform label {
    color: rgba(255,255,255,0.6) !important;
    font-size: 11px !important;
    letter-spacing: 2px !important;
    text-transform: uppercase !important;
}

/* ── Inputs ─────────────────────────────────────────────── */
#loginform input[type="text"],
#loginform input[type="password"] {
    background: rgba(255,255,255,0.06) !important;
    border: 1px solid rgba(200,232,48,0.2) !important;
    border-radius: 4px !important;
    color: #ffffff !important;
    padding: 12px 14px !important;
    font-family: 'DM Sans', sans-serif !important;
    font-size: 14px !important;
    box-shadow: none !important;
    transition: border-color 0.3s !important;
}
#loginform input:focus {
    border-color: #c8e830 !important;
    box-shadow: 0 0 0 2px rgba(200,232,48,0.2) !important;
    outline: none !important;
    background: rgba(255,255,255,0.08) !important;
}

/* ── Submit button ──────────────────────────────────────── */
#loginform #wp-submit {
    background: #c8e830 !important;
    border: none !important;
    border-radius: 4px !important;
    color: #1a3a0a !important;
    font-family: 'DM Sans', sans-serif !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    letter-spacing: 2px !important;
    text-transform: uppercase !important;
    padding: 14px !important;
    width: 100% !important;
    cursor: pointer !important;
    transition: background 0.3s !important;
    box-shadow: none !important;
    height: auto !important;
}
#loginform #wp-submit:hover {
    background: #d4f040 !important;
}

/* ── Remember me checkbox ───────────────────────────────── */
#loginform .forgetmenot label {
    color: rgba(255,255,255,0.4) !important;
    font-size: 12px !important;
    letter-spacing: 0 !important;
    text-transform: none !important;
}

/* ── "Lost your password?" link ─────────────────────────── */
#nav, #nav a {
    color: rgba(200,232,48,0.5) !important;
    font-size: 12px !important;
    text-align: center !important;
    display: block !important;
}
#nav a:hover { color: #c8e830 !important; }

/* ── Back to blog / WP footer links ─────────────────────── */
#backtoblog { display: none !important; }

/* ── Error messages ──────────────────────────────────────── */
#login_error {
    background: rgba(192,57,43,0.15) !important;
    border: 1px solid rgba(192,57,43,0.4) !important;
    border-left: 3px solid #c0392b !important;
    color: #ff8a80 !important;
    border-radius: 4px !important;
    font-size: 13px !important;
}

/* ── Success / info messages ─────────────────────────────── */
.message {
    background: rgba(200,232,48,0.1) !important;
    border: 1px solid rgba(200,232,48,0.3) !important;
    border-left: 3px solid #c8e830 !important;
    border-radius: 4px !important;
    color: rgba(255,255,255,0.8) !important;
}
</style>
<?php });


// ── 5. LOGIN PAGE FOOTER ──────────────────────────────────────────────────────
// WHY: We add a small copyright / brand note at the bottom of the login page.
add_filter('login_footer', function () {
    echo '<div style="text-align:center;margin-top:32px">
        <p style="font-family:Georgia,serif;color:rgba(200,232,48,0.25);font-size:12px;letter-spacing:1px">
            🌿 &nbsp; The Cane House &nbsp;·&nbsp; Admin Portal
        </p>
    </div>';
});


// ── 6. DISABLE SHAKE ANIMATION ON WRONG PASSWORD ─────────────────────────────
// WHY: The default WP login form shakes when you get the password wrong.
//      It's outdated and doesn't fit our premium brand feel.
add_action('login_footer', function () {
    echo '<script>window.onload = function(){ var f = document.getElementById("loginform"); if(f) f.classList.remove("shake"); };</script>';
});
