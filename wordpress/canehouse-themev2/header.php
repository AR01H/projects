<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php wp_head(); ?>
  <style>
    /* ── NAV LOGO FIX ── */
    .nav-logo {
      display: flex;
      align-items: center;
      gap: 0.7rem;
      font-family: 'Nunito', sans-serif;
      font-size: 1.35rem;
      font-weight: 900;
      color: var(--lime, #c8e830);
      text-decoration: none;
    }

    .nav-logo img {
      width: 38px !important;
      height: 38px !important;
      max-width: 38px !important;
      object-fit: contain;
      border-radius: 50%;
    }

    .nav-logo .nav-logo-icon {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      overflow: hidden;
      flex-shrink: 0;
    }

    .nav-logo .logo-text-cane {
      color: var(--lime, #c8e830);
    }

    .nav-logo .logo-text-house {
      color: #fff;
    }

    /* WordPress custom logo wraps in a figure — reset it */
    .nav-logo .custom-logo-link,
    .nav-logo .custom-logo-link figure {
      display: inline-flex;
      margin: 0;
      padding: 0;
    }

    .nav-logo .custom-logo-link img,
    .nav-logo .wp-custom-logo img {
      width: 38px !important;
      height: 38px !important;
      max-width: 38px !important;
    }

    /* ── PRIVACY POLICY NOTICE OFF ── */
    .wp-admin-bar {
      display: none;
    }

    .ch-announcement-bar {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      z-index: 1002 !important;
      height: 25px;
      line-height: 25px;
      padding: 0 10px;
    }

    #main-nav {
      position: fixed !important;
      top: 25px !important;
      left: 0 !important;
      right: 0 !important;
      z-index: 999 !important;
    }
  </style>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>

  <!-- NAV -->
  <nav id="main-nav">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-logo">
      <div class="nav-logo-icon">
        <?php if (has_custom_logo()):
          $logo = get_custom_logo();
          $logo = preg_replace('/<a[^>]*>/', '', $logo);
          $logo = str_replace('</a>', '', $logo);
          echo $logo;
        else: ?>
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/thecanehouselogo.png"
            alt="<?php bloginfo('name'); ?>" width="38" height="38">
        <?php endif; ?>
      </div>
      <span>
        <span class="logo-text-cane">THE CANE </span><span class="logo-text-house">HOUSE</span>
      </span>
    </a>
    <?php
    wp_nav_menu(array(
      'theme_location' => 'primary',
      'menu_class' => 'nav-links',
      'container' => false,
      'fallback_cb' => 'ch_fallback_nav',
      'items_wrap' => '<ul id="nav-links" class="nav-links">%3$s</ul>',
    ));
    ?>

    <button class="hamburger" id="hamburger" onclick="toggleNav()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </nav>