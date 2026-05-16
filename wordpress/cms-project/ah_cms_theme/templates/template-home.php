<?php
/**
 * Template Name: Home
 */
defined( 'ABSPATH' ) || exit;

$settings = ah_get_settings();
$consult  = $settings['consultation_url'] ?? home_url( '/free-consultation/' );
$phone    = $settings['phone'] ?? '+447747223762';

// Latest posts for the featured section
$featured_posts = ah_get_posts( 3, 1 );
if ( empty( $featured_posts ) ) {
	$featured_posts = [
		[ 'title' => __( "First-Time Buyer's Complete Guide 2025", 'ah-theme' ),     'excerpt' => __( 'Everything from deposit to keys — what to expect at each stage of buying your first home in the UK.', 'ah-theme' ),      'slug' => 'first-time-buyers-guide',  'category' => 'Buying',    'featured_image_id' => 0 ],
		[ 'title' => __( 'How to Negotiate House Price (And Win)', 'ah-theme' ),     'excerpt' => __( "Most buyers pay too much. Here's the data-driven approach professional agents use to save 5–10%.", 'ah-theme' ),          'slug' => 'house-price-negotiation',  'category' => 'Strategy',  'featured_image_id' => 0 ],
		[ 'title' => __( 'Stamp Duty 2025: The Complete Breakdown', 'ah-theme' ),   'excerpt' => __( 'Thresholds, exemptions, and how to plan your purchase around the updated rules.', 'ah-theme' ),                            'slug' => 'stamp-duty-2025',          'category' => 'Finance',   'featured_image_id' => 0 ],
	];
}

$unsplash = [
	ah_unsplash( '1560518883-ce09059eeffa', 600, 400 ),
	ah_unsplash( '1573497019940-1c28c88b4f3e', 600, 400 ),
	ah_unsplash( '1450101499163-c8848c66ca85', 600, 400 ),
];

get_template_part( 'parts/header' );
?>
<main id="main-content">

<!-- ══ HERO — editorial, not company pitch ══ -->
<section class="portal-hero">
  <div class="container">
    <div class="portal-hero__inner">
      <div class="portal-hero__content">
        <div class="portal-hero__eyebrow">
          <span class="portal-hero__badge">🇬🇧 UK Property</span>
          <?php esc_html_e( 'Independent guidance you can trust', 'ah-theme' ); ?>
        </div>
        <h1 class="portal-hero__headline">
          <?php esc_html_e( 'Everything you need to', 'ah-theme' ); ?><br>
          <em><?php esc_html_e( 'buy a home in the UK', 'ah-theme' ); ?></em>
        </h1>
        <p class="portal-hero__sub">
          <?php esc_html_e( 'Free guides, expert advice, and the right contacts — covering mortgages, legal, surveys, negotiation, and every stage of the buying journey.', 'ah-theme' ); ?>
        </p>
        <!-- Topic quick-filter pills -->
        <div class="portal-hero__pills">
          <a href="<?php echo esc_url( home_url( '/guides/first-time-buyers/' ) ); ?>" class="portal-pill">🏠 <?php esc_html_e( 'First-Time Buyers', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/guides/mortgage-guide/' ) ); ?>"    class="portal-pill">🏦 <?php esc_html_e( 'Mortgages', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/guides/legal-search/' ) ); ?>"     class="portal-pill">⚖️ <?php esc_html_e( 'Legal', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/guides/surveys/' ) ); ?>"          class="portal-pill">🔬 <?php esc_html_e( 'Surveys', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/guides/buy-to-let/' ) ); ?>"       class="portal-pill">🏘️ <?php esc_html_e( 'Buy-to-Let', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/guides/moving-guide/' ) ); ?>"     class="portal-pill">🚛 <?php esc_html_e( 'Moving', 'ah-theme' ); ?></a>
        </div>
      </div>
      <div class="portal-hero__stats">
        <div class="portal-stat-card">
          <div class="portal-stat-card__num">500+</div>
          <div class="portal-stat-card__label"><?php esc_html_e( 'Buyers helped', 'ah-theme' ); ?></div>
        </div>
        <div class="portal-stat-card">
          <div class="portal-stat-card__num">£18k</div>
          <div class="portal-stat-card__label"><?php esc_html_e( 'Avg. saving', 'ah-theme' ); ?></div>
        </div>
        <div class="portal-stat-card portal-stat-card--accent">
          <div class="portal-stat-card__icon">⭐⭐⭐⭐⭐</div>
          <div class="portal-stat-card__label"><?php esc_html_e( '4.9/5 rating', 'ah-theme' ); ?></div>
          <a href="<?php echo esc_url( $consult ); ?>" class="portal-stat-card__cta">
            <?php esc_html_e( 'Talk to an expert →', 'ah-theme' ); ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ TOPIC CATEGORY GRID ══ -->
<section class="section section--alt">
  <div class="container">
    <div class="section-header">
      <h2><?php esc_html_e( 'What do you need help with?', 'ah-theme' ); ?></h2>
      <p><?php esc_html_e( 'Choose a topic to get started — each guide is written by property experts with real buying experience.', 'ah-theme' ); ?></p>
    </div>
    <div class="topic-grid">

      <a href="<?php echo esc_url( home_url( '/guides/first-time-buyers/' ) ); ?>" class="topic-card topic-card--blue">
        <div class="topic-card__icon">🏠</div>
        <h3><?php esc_html_e( 'First-Time Buyers', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( 'The complete A–Z guide from deposit to keys', 'ah-theme' ); ?></p>
        <span class="topic-card__arrow">→</span>
      </a>

      <a href="<?php echo esc_url( home_url( '/guides/mortgage-guide/' ) ); ?>" class="topic-card topic-card--green">
        <div class="topic-card__icon">🏦</div>
        <h3><?php esc_html_e( 'Mortgages & Finance', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( 'Rates, types, brokers, and the best deals', 'ah-theme' ); ?></p>
        <span class="topic-card__arrow">→</span>
      </a>

      <a href="<?php echo esc_url( home_url( '/guides/legal-search/' ) ); ?>" class="topic-card topic-card--purple">
        <div class="topic-card__icon">⚖️</div>
        <h3><?php esc_html_e( 'Legal & Conveyancing', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( 'Searches, contracts, and the legal process', 'ah-theme' ); ?></p>
        <span class="topic-card__arrow">→</span>
      </a>

      <a href="<?php echo esc_url( home_url( '/guides/surveys/' ) ); ?>" class="topic-card topic-card--orange">
        <div class="topic-card__icon">🔬</div>
        <h3><?php esc_html_e( 'Surveys & Inspections', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( 'Which survey type and what to do with results', 'ah-theme' ); ?></p>
        <span class="topic-card__arrow">→</span>
      </a>

      <a href="<?php echo esc_url( home_url( '/guides/buy-to-let/' ) ); ?>" class="topic-card topic-card--gold">
        <div class="topic-card__icon">🏘️</div>
        <h3><?php esc_html_e( 'Buy-to-Let & Investment', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( 'Yields, financing, and portfolio strategy', 'ah-theme' ); ?></p>
        <span class="topic-card__arrow">→</span>
      </a>

      <a href="<?php echo esc_url( home_url( '/guides/moving-guide/' ) ); ?>" class="topic-card topic-card--teal">
        <div class="topic-card__icon">🚛</div>
        <h3><?php esc_html_e( 'Moving & Completion', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( 'Removal companies, utilities, and moving day', 'ah-theme' ); ?></p>
        <span class="topic-card__arrow">→</span>
      </a>

    </div>
  </div>
</section>

<!-- ══ FEATURED GUIDES ══ -->
<section class="section">
  <div class="container">
    <div class="section-header section-header--left">
      <h2><?php esc_html_e( 'Latest Guides & Articles', 'ah-theme' ); ?></h2>
      <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="section-header__link">
        <?php esc_html_e( 'View all →', 'ah-theme' ); ?>
      </a>
    </div>
    <div class="blog-grid">
      <?php foreach ( $featured_posts as $i => $p ) :
        $p_title   = ah_val( $p, 'title' );
        $p_excerpt = ah_val( $p, 'excerpt' );
        $p_slug    = ah_val( $p, 'slug' );
        $p_cat     = ah_val( $p, 'category', 'Guide' );
        $p_img_id  = ah_field( $p, 'featured_image_id', 0 );
        $p_img     = $p_img_id ? ah_media_url( (int) $p_img_id ) : ( $unsplash[ $i ] ?? $unsplash[0] );
        $p_url     = $p_slug ? home_url( '/blog/' . $p_slug . '/' ) : home_url( '/blog/' );
        $p_date    = ah_field( $p, 'created_at', '' );
        $p_date    = $p_date ? date_i18n( 'j M Y', strtotime( $p_date ) ) : '';
      ?>
        <article class="blog-card reveal">
          <a href="<?php echo esc_url( $p_url ); ?>" class="blog-card__img-wrap" tabindex="-1">
            <img src="<?php echo esc_url( $p_img ); ?>" alt="<?php echo esc_attr( $p_title ); ?>" loading="lazy" class="blog-card__img">
            <span class="blog-card__cat"><?php echo esc_html( $p_cat ); ?></span>
          </a>
          <div class="blog-card__body">
            <?php if ( $p_date ) : ?>
              <div class="blog-card__meta"><time class="blog-card__date"><?php echo esc_html( $p_date ); ?></time></div>
            <?php endif; ?>
            <h3 class="blog-card__title"><a href="<?php echo esc_url( $p_url ); ?>"><?php echo esc_html( $p_title ); ?></a></h3>
            <?php if ( $p_excerpt ) : ?>
              <p class="blog-card__excerpt"><?php echo esc_html( $p_excerpt ); ?></p>
            <?php endif; ?>
            <a href="<?php echo esc_url( $p_url ); ?>" class="blog-card__link">
              <?php esc_html_e( 'Read Article', 'ah-theme' ); ?>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══ CONTEXTUAL SUPPORT CARDS ══ -->
<section class="section section--alt">
  <div class="container">
    <div class="section-header">
      <h2><?php esc_html_e( 'Need more than a guide?', 'ah-theme' ); ?></h2>
      <p><?php esc_html_e( "Sometimes expert human help is what you need. Here's who to contact at each stage.", 'ah-theme' ); ?></p>
    </div>
    <div class="support-grid">

      <div class="support-card support-card--blue">
        <div class="support-card__icon">🏦</div>
        <h3><?php esc_html_e( 'Find a Mortgage Broker', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( 'An independent broker searches the whole market and can access exclusive rates not available on comparison sites.', 'ah-theme' ); ?></p>
        <a href="<?php echo esc_url( home_url( '/guides/mortgage-guide/' ) ); ?>" class="support-card__btn">
          <?php esc_html_e( 'Mortgage guide →', 'ah-theme' ); ?>
        </a>
      </div>

      <div class="support-card support-card--purple">
        <div class="support-card__icon">⚖️</div>
        <h3><?php esc_html_e( 'Find a Conveyancer', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( "A licensed conveyancer handles the legal transfer of the property. Don't skip on quality — a bad solicitor can cost you the deal.", 'ah-theme' ); ?></p>
        <a href="<?php echo esc_url( home_url( '/guides/conveyancing/' ) ); ?>" class="support-card__btn">
          <?php esc_html_e( 'Conveyancing guide →', 'ah-theme' ); ?>
        </a>
      </div>

      <div class="support-card support-card--orange">
        <div class="support-card__icon">🔬</div>
        <h3><?php esc_html_e( 'Book a Property Survey', 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( "A survey protects you from hidden defects. We explain which level you need and what to do when issues are found.", 'ah-theme' ); ?></p>
        <a href="<?php echo esc_url( home_url( '/guides/surveys/' ) ); ?>" class="support-card__btn">
          <?php esc_html_e( 'Survey guide →', 'ah-theme' ); ?>
        </a>
      </div>

      <div class="support-card support-card--gold">
        <div class="support-card__icon">🤝</div>
        <h3><?php esc_html_e( "Want a Buyer's Agent?", 'ah-theme' ); ?></h3>
        <p><?php esc_html_e( 'We search, negotiate, and complete on your behalf — saving you an average of £18,000 and months of stress. First call is free.', 'ah-theme' ); ?></p>
        <a href="<?php echo esc_url( $consult ); ?>" class="support-card__btn support-card__btn--primary">
          <?php esc_html_e( 'Book free call →', 'ah-theme' ); ?>
        </a>
      </div>

    </div>
  </div>
</section>

<!-- ══ STATS BAR ══ -->
<?php get_template_part( 'components/stats' ); ?>

<!-- ══ FAQ ══ -->
<?php get_template_part( 'components/faqs' ); ?>

<!-- ══ TESTIMONIALS ══ -->
<?php get_template_part( 'components/reviews' ); ?>

</main>
<?php get_template_part( 'parts/footer' ); ?>
