<?php
defined( 'ABSPATH' ) || exit;

$settings       = ah_get_settings();
$phone          = $settings['phone'] ?? '';
$logo_url       = get_template_directory_uri() . '/assets/images/logo.png';
$has_logo       = file_exists( get_template_directory() . '/assets/images/logo.png' );
$buying_topics  = ah_get_nav_buying_topics();
$finance_topics = ah_get_nav_finance_topics();
$legal_topics   = ah_get_nav_legal_topics();
$nav_vis        = ah_get_nav_visibility();
$nav_links      = ah_get_nav_static_links();
$nav_cta        = ah_get_nav_cta();
?>
<?php $global_banner = ah_get_html_block( 'global_banner' ); ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( $global_banner ) echo $global_banner; ?>

<!-- ── PRIMARY NAV ── -->
<nav class="nav" id="mainNav" role="navigation" aria-label="<?php esc_attr_e( 'Main Navigation', 'ah-theme' ); ?>">
  <div class="container">
    <div class="nav__inner">

      <!-- Logo -->
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__logo" aria-label="<?php bloginfo( 'name' ); ?> Home">
        <?php if ( $has_logo ) : ?>
          <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>" style="height:40px">
        <?php else : ?>
          <div class="nav__logo-mark">AH</div>
          <span>Advaith <em style="font-style:italic;font-family:var(--font-accent)">Homes</em></span>
        <?php endif; ?>
      </a>

      <!-- Desktop Menu -->
      <ul class="nav__menu" role="list">

        <!-- Buying Guides dropdown -->
        <?php if ( $buying_topics && ! empty( $nav_vis['buying'] ) ) : ?>
        <li class="nav__dropdown">
          <button class="nav__link nav__dropdown-toggle" aria-haspopup="true" aria-expanded="false">
            <?php esc_html_e( 'Buying', 'ah-theme' ); ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="nav__dropdown-menu" role="menu">
            <?php foreach ( $buying_topics as $t ) :
              $t   = is_object($t) ? (array) $t : $t;
              $url = home_url( '/guides/' . ( $t['slug'] ?? '' ) . '/' );
            ?>
              <a href="<?php echo esc_url( $url ); ?>" class="nav__dropdown-item" role="menuitem"
                 <?php if ( ! empty( $t['highlight'] ) ) echo 'style="background:var(--bg-alt);border-radius:8px"'; ?>>
                <div class="nav__dropdown-item-icon" <?php if ( ! empty( $t['highlight'] ) ) echo 'style="background:var(--accent);color:white"'; ?>>
                  <?php echo esc_html( $t['icon'] ?? '🏠' ); ?>
                </div>
                <div>
                  <div style="font-weight:<?php echo ! empty( $t['highlight'] ) ? 700 : 600; ?>;color:<?php echo ! empty( $t['highlight'] ) ? 'var(--accent)' : 'var(--slate-800)'; ?>;font-size:.85rem">
                    <?php echo esc_html( $t['title'] ); ?>
                  </div>
                  <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?php echo esc_html( $t['desc'] ?? '' ); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </li>
        <?php endif; ?>

        <!-- Finance dropdown -->
        <?php if ( $finance_topics && ! empty( $nav_vis['finance'] ) ) : ?>
        <li class="nav__dropdown">
          <button class="nav__link nav__dropdown-toggle" aria-haspopup="true" aria-expanded="false">
            <?php esc_html_e( 'Finance', 'ah-theme' ); ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="nav__dropdown-menu" role="menu">
            <?php foreach ( $finance_topics as $t ) :
              $t   = is_object($t) ? (array) $t : $t;
              $url = home_url( '/guides/' . ( $t['slug'] ?? '' ) . '/' );
            ?>
              <a href="<?php echo esc_url( $url ); ?>" class="nav__dropdown-item" role="menuitem"
                 <?php if ( ! empty( $t['highlight'] ) ) echo 'style="background:var(--bg-alt);border-radius:8px"'; ?>>
                <div class="nav__dropdown-item-icon" <?php if ( ! empty( $t['highlight'] ) ) echo 'style="background:var(--accent);color:white"'; ?>>
                  <?php echo esc_html( $t['icon'] ?? '🏦' ); ?>
                </div>
                <div>
                  <div style="font-weight:600;color:var(--slate-800);font-size:.85rem"><?php echo esc_html( $t['title'] ); ?></div>
                  <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?php echo esc_html( $t['desc'] ?? '' ); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </li>
        <?php endif; ?>

        <!-- Legal & Surveys dropdown -->
        <?php if ( $legal_topics && ! empty( $nav_vis['legal'] ) ) : ?>
        <li class="nav__dropdown">
          <button class="nav__link nav__dropdown-toggle" aria-haspopup="true" aria-expanded="false">
            <?php esc_html_e( 'Legal & Surveys', 'ah-theme' ); ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="nav__dropdown-menu" role="menu">
            <?php foreach ( $legal_topics as $t ) :
              $t   = is_object($t) ? (array) $t : $t;
              $url = home_url( '/guides/' . ( $t['slug'] ?? '' ) . '/' );
            ?>
              <a href="<?php echo esc_url( $url ); ?>" class="nav__dropdown-item" role="menuitem">
                <div class="nav__dropdown-item-icon"><?php echo esc_html( $t['icon'] ?? '⚖️' ); ?></div>
                <div>
                  <div style="font-weight:600;color:var(--slate-800);font-size:.85rem"><?php echo esc_html( $t['title'] ); ?></div>
                  <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?php echo esc_html( $t['desc'] ?? '' ); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </li>
        <?php endif; ?>

        <?php if ( ! empty( $nav_vis['news'] ) ) : ?>
        <li>
          <a href="<?php echo esc_url( home_url( $nav_links['news']['url'] ?? '/blog/' ) ); ?>" class="nav__link"
             <?php if ( is_home() || is_category() ) echo 'aria-current="page"'; ?>>
            <?php echo esc_html( $nav_links['news']['label'] ?? 'News & Guides' ); ?>
          </a>
        </li>
        <?php endif; ?>

        <?php if ( ! empty( $nav_vis['services'] ) ) : ?>
        <li>
          <a href="<?php echo esc_url( home_url( $nav_links['services']['url'] ?? '/services/' ) ); ?>" class="nav__link">
            <?php echo esc_html( $nav_links['services']['label'] ?? 'Services' ); ?>
          </a>
        </li>
        <?php endif; ?>

      </ul>

      <!-- Right actions -->
      <div class="nav__actions">
        <?php if ( $phone ) : ?>
        <a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"
           class="btn btn-sm btn-primary" aria-label="<?php esc_attr_e( 'Call us', 'ah-theme' ); ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.02 1.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
          </svg>
          Contact Us
        </a>
        <?php endif; ?>
        <a href="<?php echo esc_url( home_url( $nav_cta['url'] ?? '/contact/' ) ); ?>" class="btn btn-sm btn-primary">
          <?php echo esc_html( $nav_cta['label'] ?? 'Get Help' ); ?>
        </a>
        <button class="nav__hamburger" id="ahHamburger"
                aria-label="<?php esc_attr_e( 'Open menu', 'ah-theme' ); ?>"
                aria-expanded="false" aria-controls="ahMobileNav">
          <span></span><span></span><span></span>
        </button>
      </div>

    </div>
  </div>
</nav>

<!-- ── MOBILE NAV ── -->
<nav class="nav__mobile" id="ahMobileNav" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'ah-theme' ); ?>">
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__mobile-link">🏠 <?php esc_html_e( 'Home', 'ah-theme' ); ?></a>

  <?php if ( $buying_topics ) : ?>
  <details class="nav__mobile-details">
    <summary class="nav__mobile-summary">
      🏠 <?php esc_html_e( 'Buying', 'ah-theme' ); ?>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="nav__mobile-sub-menu">
      <?php foreach ( $buying_topics as $t ) :
        $t = is_object($t) ? (array) $t : $t;
      ?>
        <a href="<?php echo esc_url( home_url( '/guides/' . ( $t['slug'] ?? '' ) . '/' ) ); ?>"
           class="nav__mobile-link"
           <?php if ( ! empty( $t['highlight'] ) ) echo 'style="color:var(--accent);font-weight:700"'; ?>>
          <?php echo esc_html( ( $t['icon'] ?? '' ) . ' ' . ( $t['title'] ?? '' ) ); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </details>
  <?php endif; ?>

  <?php if ( $finance_topics ) : ?>
  <details class="nav__mobile-details">
    <summary class="nav__mobile-summary">
      🏦 <?php esc_html_e( 'Finance', 'ah-theme' ); ?>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="nav__mobile-sub-menu">
      <?php foreach ( $finance_topics as $t ) :
        $t = is_object($t) ? (array) $t : $t;
      ?>
        <a href="<?php echo esc_url( home_url( '/guides/' . ( $t['slug'] ?? '' ) . '/' ) ); ?>" class="nav__mobile-link">
          <?php echo esc_html( ( $t['icon'] ?? '' ) . ' ' . ( $t['title'] ?? '' ) ); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </details>
  <?php endif; ?>

  <?php if ( $legal_topics ) : ?>
  <details class="nav__mobile-details">
    <summary class="nav__mobile-summary">
      ⚖️ <?php esc_html_e( 'Legal & Surveys', 'ah-theme' ); ?>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="nav__mobile-sub-menu">
      <?php foreach ( $legal_topics as $t ) :
        $t = is_object($t) ? (array) $t : $t;
      ?>
        <a href="<?php echo esc_url( home_url( '/guides/' . ( $t['slug'] ?? '' ) . '/' ) ); ?>" class="nav__mobile-link">
          <?php echo esc_html( ( $t['icon'] ?? '' ) . ' ' . ( $t['title'] ?? '' ) ); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </details>
  <?php endif; ?>

  <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"     class="nav__mobile-link">📰 <?php esc_html_e( 'News & Guides', 'ah-theme' ); ?></a>
  <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="nav__mobile-link">✦ <?php esc_html_e( 'Services', 'ah-theme' ); ?></a>
  <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"  class="nav__mobile-link">📬 <?php esc_html_e( 'Get Help', 'ah-theme' ); ?></a>

  <div style="padding:16px">
    <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary btn-block" style="justify-content:center">
      <?php esc_html_e( 'Talk to an Expert', 'ah-theme' ); ?>
    </a>
  </div>
</nav>

<div id="page-content">

<!-- ── NEWS TICKER ── -->
<?php if ( ah_section_visible( 'global_news_ticker' ) ) : ?>
<?php get_template_part( 'components/news-ticker' ); ?>
<?php endif; ?>
