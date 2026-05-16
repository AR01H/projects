<?php
defined( 'ABSPATH' ) || exit;

$settings = ah_get_settings();
$phone    = $settings['phone'] ?? '+447747223762';
$logo_url = get_template_directory_uri() . '/assets/images/logo.png';
$has_logo = file_exists( get_template_directory() . '/assets/images/logo.png' );
?>
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

      <!-- Desktop Menu — content-topic focused -->
      <ul class="nav__menu" role="list">

        <!-- Buying Guides -->
        <li class="nav__dropdown">
          <button class="nav__link nav__dropdown-toggle" aria-haspopup="true" aria-expanded="false">
            <?php esc_html_e( 'Buying', 'ah-theme' ); ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="nav__dropdown-menu" role="menu">
            <?php
            $buying_topics = [
              [ 'icon' => '🏠', 'title' => __( 'First-Time Buyers',   'ah-theme' ), 'desc' => __( 'Complete step-by-step guide',    'ah-theme' ), 'slug' => 'first-time-buyers' ],
              [ 'icon' => '🔑', 'title' => __( 'Moving Home',          'ah-theme' ), 'desc' => __( 'What changes when you upsize',    'ah-theme' ), 'slug' => 'moving-home' ],
              [ 'icon' => '🏘️', 'title' => __( 'Buy-to-Let',           'ah-theme' ), 'desc' => __( 'Investor buying strategy',        'ah-theme' ), 'slug' => 'buy-to-let' ],
              [ 'icon' => '🔍', 'title' => __( 'Off-Market Properties','ah-theme' ), 'desc' => __( 'Homes not on Rightmove',          'ah-theme' ), 'slug' => 'off-market' ],
              [ 'icon' => '🏗️', 'title' => __( 'New Builds',           'ah-theme' ), 'desc' => __( 'Developer deals & pitfalls',      'ah-theme' ), 'slug' => 'new-builds' ],
              [ 'icon' => '🤝', 'title' => __( 'Using a Buyer\'s Agent','ah-theme' ), 'desc' => __( 'What we do & why it works',      'ah-theme' ), 'slug' => 'buyers-agent', 'highlight' => true ],
            ];
            foreach ( $buying_topics as $t ) :
              $url = home_url( '/guides/' . $t['slug'] . '/' );
            ?>
              <a href="<?php echo esc_url( $url ); ?>" class="nav__dropdown-item" role="menuitem"
                 <?php if ( ! empty( $t['highlight'] ) ) echo 'style="background:var(--bg-alt);border-radius:8px"'; ?>>
                <div class="nav__dropdown-item-icon" <?php if ( ! empty( $t['highlight'] ) ) echo 'style="background:var(--accent);color:white"'; ?>>
                  <?php echo $t['icon']; ?>
                </div>
                <div>
                  <div style="font-weight:<?php echo ! empty( $t['highlight'] ) ? 700 : 600; ?>;color:<?php echo ! empty( $t['highlight'] ) ? 'var(--accent)' : 'var(--slate-800)'; ?>;font-size:.85rem">
                    <?php echo esc_html( $t['title'] ); ?>
                  </div>
                  <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?php echo esc_html( $t['desc'] ); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </li>

        <!-- Finance & Mortgages -->
        <li class="nav__dropdown">
          <button class="nav__link nav__dropdown-toggle" aria-haspopup="true" aria-expanded="false">
            <?php esc_html_e( 'Finance', 'ah-theme' ); ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="nav__dropdown-menu" role="menu">
            <?php
            $finance_topics = [
              [ 'icon' => '🏦', 'title' => __( 'Mortgage Guide',      'ah-theme' ), 'desc' => __( 'Rates, types & best deals',       'ah-theme' ), 'slug' => 'mortgage-guide' ],
              [ 'icon' => '💰', 'title' => __( 'Deposit Guide',       'ah-theme' ), 'desc' => __( 'How much do you really need?',    'ah-theme' ), 'slug' => 'deposit-guide' ],
              [ 'icon' => '📋', 'title' => __( 'Stamp Duty Guide',    'ah-theme' ), 'desc' => __( '2025 rates & exemptions',         'ah-theme' ), 'slug' => 'stamp-duty' ],
              [ 'icon' => '🧮', 'title' => __( 'Cost Calculator',     'ah-theme' ), 'desc' => __( 'Hidden costs of buying',          'ah-theme' ), 'slug' => 'price-calculator', 'highlight' => true ],
            ];
            foreach ( $finance_topics as $t ) :
              $url = home_url( '/guides/' . $t['slug'] . '/' );
            ?>
              <a href="<?php echo esc_url( $url ); ?>" class="nav__dropdown-item" role="menuitem"
                 <?php if ( ! empty( $t['highlight'] ) ) echo 'style="background:var(--bg-alt);border-radius:8px"'; ?>>
                <div class="nav__dropdown-item-icon" <?php if ( ! empty( $t['highlight'] ) ) echo 'style="background:var(--accent);color:white"'; ?>>
                  <?php echo $t['icon']; ?>
                </div>
                <div>
                  <div style="font-weight:600;color:var(--slate-800);font-size:.85rem"><?php echo esc_html( $t['title'] ); ?></div>
                  <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?php echo esc_html( $t['desc'] ); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </li>

        <!-- Legal & Surveys -->
        <li class="nav__dropdown">
          <button class="nav__link nav__dropdown-toggle" aria-haspopup="true" aria-expanded="false">
            <?php esc_html_e( 'Legal & Surveys', 'ah-theme' ); ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
          </button>
          <div class="nav__dropdown-menu" role="menu">
            <?php
            $legal_topics = [
              [ 'icon' => '⚖️', 'title' => __( 'Legal Search Packs',  'ah-theme' ), 'desc' => __( "What's hidden in the paperwork", 'ah-theme' ), 'slug' => 'legal-search' ],
              [ 'icon' => '📄', 'title' => __( 'Conveyancing Guide',  'ah-theme' ), 'desc' => __( 'The legal process explained',     'ah-theme' ), 'slug' => 'conveyancing' ],
              [ 'icon' => '🔬', 'title' => __( 'Survey Types',        'ah-theme' ), 'desc' => __( 'Which survey do you need?',       'ah-theme' ), 'slug' => 'surveys' ],
              [ 'icon' => '📊', 'title' => __( 'Property Research',   'ah-theme' ), 'desc' => __( 'Deep analysis before you buy',    'ah-theme' ), 'slug' => 'property-research' ],
            ];
            foreach ( $legal_topics as $t ) :
              $url = home_url( '/guides/' . $t['slug'] . '/' );
            ?>
              <a href="<?php echo esc_url( $url ); ?>" class="nav__dropdown-item" role="menuitem">
                <div class="nav__dropdown-item-icon"><?php echo $t['icon']; ?></div>
                <div>
                  <div style="font-weight:600;color:var(--slate-800);font-size:.85rem"><?php echo esc_html( $t['title'] ); ?></div>
                  <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px"><?php echo esc_html( $t['desc'] ); ?></div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </li>

        <!-- News & Blog -->
        <li>
          <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"
             class="nav__link"
             <?php if ( is_page( 'blog' ) ) echo 'aria-current="page"'; ?>>
            <?php esc_html_e( 'News & Guides', 'ah-theme' ); ?>
          </a>
        </li>

        <!-- Moving Guide -->
        <li>
          <a href="<?php echo esc_url( home_url( '/guides/moving-guide/' ) ); ?>"
             class="nav__link">
            <?php esc_html_e( 'Moving', 'ah-theme' ); ?>
          </a>
        </li>

      </ul>

      <!-- Right actions — minimal, not company-pitch -->
      <div class="nav__actions">
        <a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"
           class="nav__phone-link" aria-label="<?php esc_attr_e( 'Call us', 'ah-theme' ); ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.02 1.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
          </svg>
          <?php echo esc_html( $phone ); ?>
        </a>
        <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-sm btn-primary">
          <?php esc_html_e( 'Get Help', 'ah-theme' ); ?>
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

  <details class="nav__mobile-details">
    <summary class="nav__mobile-summary">
      🏠 <?php esc_html_e( 'Buying', 'ah-theme' ); ?>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="nav__mobile-sub-menu">
      <a href="<?php echo esc_url( home_url( '/guides/first-time-buyers/' ) ); ?>" class="nav__mobile-link">🏠 <?php esc_html_e( 'First-Time Buyers', 'ah-theme' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/guides/moving-home/' ) ); ?>"       class="nav__mobile-link">🔑 <?php esc_html_e( 'Moving Home', 'ah-theme' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/guides/buy-to-let/' ) ); ?>"        class="nav__mobile-link">🏘️ <?php esc_html_e( 'Buy-to-Let', 'ah-theme' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/guides/buyers-agent/' ) ); ?>"      class="nav__mobile-link" style="color:var(--accent);font-weight:700">🤝 <?php esc_html_e( "Buyer's Agent Guide", 'ah-theme' ); ?></a>
    </div>
  </details>

  <details class="nav__mobile-details">
    <summary class="nav__mobile-summary">
      🏦 <?php esc_html_e( 'Finance', 'ah-theme' ); ?>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="nav__mobile-sub-menu">
      <a href="<?php echo esc_url( home_url( '/guides/mortgage-guide/' ) ); ?>"  class="nav__mobile-link">🏦 <?php esc_html_e( 'Mortgage Guide', 'ah-theme' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/guides/deposit-guide/' ) ); ?>"   class="nav__mobile-link">💰 <?php esc_html_e( 'Deposit Guide', 'ah-theme' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/guides/stamp-duty/' ) ); ?>"      class="nav__mobile-link">📋 <?php esc_html_e( 'Stamp Duty', 'ah-theme' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/guides/price-calculator/' ) ); ?>" class="nav__mobile-link">🧮 <?php esc_html_e( 'Cost Calculator', 'ah-theme' ); ?></a>
    </div>
  </details>

  <details class="nav__mobile-details">
    <summary class="nav__mobile-summary">
      ⚖️ <?php esc_html_e( 'Legal & Surveys', 'ah-theme' ); ?>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="nav__mobile-sub-menu">
      <a href="<?php echo esc_url( home_url( '/guides/legal-search/' ) ); ?>"    class="nav__mobile-link">⚖️ <?php esc_html_e( 'Legal Search Packs', 'ah-theme' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/guides/conveyancing/' ) ); ?>"    class="nav__mobile-link">📄 <?php esc_html_e( 'Conveyancing Guide', 'ah-theme' ); ?></a>
      <a href="<?php echo esc_url( home_url( '/guides/surveys/' ) ); ?>"         class="nav__mobile-link">🔬 <?php esc_html_e( 'Survey Types', 'ah-theme' ); ?></a>
    </div>
  </details>

  <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"                     class="nav__mobile-link">📰 <?php esc_html_e( 'News & Guides', 'ah-theme' ); ?></a>
  <a href="<?php echo esc_url( home_url( '/guides/moving-guide/' ) ); ?>"      class="nav__mobile-link">🚛 <?php esc_html_e( 'Moving', 'ah-theme' ); ?></a>
  <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"                  class="nav__mobile-link">📬 <?php esc_html_e( 'Get Help', 'ah-theme' ); ?></a>

  <div style="padding:16px">
    <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary btn-block" style="justify-content:center">
      <?php esc_html_e( 'Talk to an Expert', 'ah-theme' ); ?>
    </a>
  </div>
</nav>

<!-- ── NEWS TICKER ── -->
<?php get_template_part( 'components/news-ticker' ); ?>

<div id="page-content">
