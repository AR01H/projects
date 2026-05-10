<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- NAV -->
<nav id="main-nav">
  <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-logo">
    <div class="logo-icon">
      <?php if (has_custom_logo()) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/thecanehouselogo.png" alt="<?php bloginfo('name'); ?>" width="50" height="50">
      <?php endif; ?>
    </div>
    THE CANE <span>HOUSE</span>
  </a>
  <?php
  wp_nav_menu(array(
    'theme_location' => 'primary',
    'menu_class'     => 'nav-links',
    'container'      => false,
    'fallback_cb'    => false,
    'items_wrap'     => '<ul id="nav-links" class="nav-links">%3$s</ul>',
  ));
  ?>
  <button class="hamburger" id="hamburger" onclick="toggleNav()">
    <span></span><span></span><span></span>
  </button>
</nav>
