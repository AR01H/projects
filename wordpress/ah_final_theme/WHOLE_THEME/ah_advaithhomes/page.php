<?php
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$settings = ah_get_settings();
$consult  = $settings['consultation_url'] ?? home_url( '/free-consultation/' );
$phone    = $settings['phone'] ?? '+447747223762';

// Check if this page has actual content
$has_content = false;
if ( have_posts() ) {
	the_post();
	$has_content = ! empty( trim( get_the_content() ) );
	rewind_posts();
}
?>
<main id="main-content">

  <!-- Page Hero -->
  <section class="page-hero page-hero--sm">
    <div class="container">
      <h1><?php the_title(); ?></h1>
    </div>
  </section>

  <?php if ( $has_content ) : ?>
  <!-- Standard page content -->
  <section class="section">
    <div class="container">
      <div class="page-content">
        <?php
        while ( have_posts() ) :
          the_post();
          the_content();
        endwhile;
        ?>
      </div>
    </div>
  </section>

  <?php else : ?>
  <!-- Guide/topic page — no content yet. Show navigation + support cards -->
  <section class="section">
    <div class="container">
      <div class="guide-placeholder">

        <!-- Contextual support cards -->
        <div class="section-header">
          <h2><?php esc_html_e( 'How can we help?', 'ah-theme' ); ?></h2>
          <p><?php esc_html_e( 'Browse the guide sections below, or talk to an expert directly.', 'ah-theme' ); ?></p>
        </div>

        <div class="support-grid" style="grid-template-columns: repeat(3,1fr);margin-top:32px">

          <div class="support-card support-card--blue">
            <div class="support-card__icon">📖</div>
            <h3><?php esc_html_e( 'Read the Guide', 'ah-theme' ); ?></h3>
            <p><?php esc_html_e( 'Explore our full library of buying guides — mortgages, legal, surveys, negotiation and more.', 'ah-theme' ); ?></p>
            <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="support-card__btn"><?php esc_html_e( 'All Guides →', 'ah-theme' ); ?></a>
          </div>

          <div class="support-card support-card--purple">
            <div class="support-card__icon">💬</div>
            <h3><?php esc_html_e( 'Ask an Expert', 'ah-theme' ); ?></h3>
            <p><?php esc_html_e( 'Our buyer\'s agents answer questions on property, legal, surveys, and finance — free initial call.', 'ah-theme' ); ?></p>
            <a href="<?php echo esc_url( $consult ); ?>" class="support-card__btn"><?php esc_html_e( 'Book Free Call →', 'ah-theme' ); ?></a>
          </div>

          <div class="support-card support-card--gold">
            <div class="support-card__icon">📞</div>
            <h3><?php esc_html_e( 'Call Us Directly', 'ah-theme' ); ?></h3>
            <p><?php esc_html_e( 'Prefer to talk? Call us Mon–Sat 9am–6pm. No scripts, just expert help.', 'ah-theme' ); ?></p>
            <a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" class="support-card__btn support-card__btn--primary">
              <?php echo esc_html( $phone ); ?> →
            </a>
          </div>

        </div>

        <!-- Topic links grid -->
        <div style="margin-top:56px;padding-top:40px;border-top:1px solid var(--border)">
          <h3 style="margin-bottom:24px;font-size:1.15rem"><?php esc_html_e( 'Explore Related Guides', 'ah-theme' ); ?></h3>
          <div class="topic-grid" style="grid-template-columns:repeat(3,1fr)">
            <a href="<?php echo esc_url( home_url( '/guides/first-time-buyers/' ) ); ?>" class="topic-card topic-card--blue">
              <div class="topic-card__icon">🏠</div>
              <h3><?php esc_html_e( 'First-Time Buyers', 'ah-theme' ); ?></h3>
              <p><?php esc_html_e( 'The complete A–Z guide', 'ah-theme' ); ?></p>
              <span class="topic-card__arrow">→</span>
            </a>
            <a href="<?php echo esc_url( home_url( '/guides/mortgage-guide/' ) ); ?>" class="topic-card topic-card--green">
              <div class="topic-card__icon">🏦</div>
              <h3><?php esc_html_e( 'Mortgage Guide', 'ah-theme' ); ?></h3>
              <p><?php esc_html_e( 'Rates, types and best deals', 'ah-theme' ); ?></p>
              <span class="topic-card__arrow">→</span>
            </a>
            <a href="<?php echo esc_url( home_url( '/guides/legal-search/' ) ); ?>" class="topic-card topic-card--purple">
              <div class="topic-card__icon">⚖️</div>
              <h3><?php esc_html_e( 'Legal & Conveyancing', 'ah-theme' ); ?></h3>
              <p><?php esc_html_e( 'Searches, contracts, and the legal process', 'ah-theme' ); ?></p>
              <span class="topic-card__arrow">→</span>
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>
  <?php endif; ?>

</main>
<?php get_template_part( 'parts/footer' ); ?>
