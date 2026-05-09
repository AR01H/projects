<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php require_once get_template_directory() . '/inc/seo.php'; ?>
  <link rel="icon" type="image/png" href="<?php echo esc_url( AH_LOGO_PATH ); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="home">
<?php wp_body_open(); ?>

<div id="nav-placeholder">
  <nav class="navbar" id="main-nav">
    <div class="container nav__inner">
      <a href="<?php echo esc_url( home_url('/') ); ?>" class="nav__logo">
        <?php if ( has_custom_logo() ):
            the_custom_logo();
          else: ?>
          <img src="<?php echo esc_url( AH_LOGO_PATH ); ?>" alt="<?php echo esc_attr( AH_SITE_NAME ); ?>" width="120" height="40">
        <?php endif; ?>
      </a>
      <ul class="nav__links" id="nav-links">
        <li><a href="#services">Services</a></li>
        <li><a href="#why-us">Why Us</a></li>
        <li><a href="#properties">Properties</a></li>
        <li><a href="#testimonials">Reviews</a></li>
        <li><a href="<?php echo esc_url( home_url('/contact') ); ?>" class="btn btn--primary btn--sm">Free Consultation</a></li>
      </ul>
      <button class="nav__burger" id="hamburger" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>
</div>
