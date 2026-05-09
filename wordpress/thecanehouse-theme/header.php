<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php require_once get_template_directory() . '/inc/seo.php'; ?>
  <link rel="icon" type="image/png" href="<?php echo esc_url( TCH_LOGO_PATH ); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- NAV -->
<nav id="main-nav">
  <a href="<?php echo esc_url( home_url('/') ); ?>" class="nav-logo">
    <div class="logo-icon">
      <?php if ( has_custom_logo() ):
          the_custom_logo();
        else: ?>
        <img src="<?php echo esc_url( TCH_LOGO_PATH ); ?>" alt="<?php echo esc_attr( TCH_SITE_NAME ); ?>" width="50" height="50">
      <?php endif; ?>
    </div>
    THE CANE <span>HOUSE</span>
  </a>
  <ul class="nav-links" id="nav-links">
    <li><a href="#how-to-order">How to Order</a></li>
    <li><a href="#reviews">Reviews</a></li>
    <li><a href="#build">Our Juices</a></li>
    <li><a href="#faq">FAQ</a></li>
    <li><a href="#hire">Events</a></li>
    <li><a href="#franchise">Franchise</a></li>
    <li><a href="#contact" class="nav-cta-btn">Contact</a></li>
  </ul>
  <button class="hamburger" id="hamburger" onclick="toggleNav()">
    <span></span><span></span><span></span>
  </button>
</nav>
