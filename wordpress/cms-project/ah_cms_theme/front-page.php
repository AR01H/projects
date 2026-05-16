<?php get_header(); ?>

<?php get_template_part( 'components/hero' ); ?>
<?php get_template_part( 'components/trust-bar' ); ?>
<?php get_template_part( 'components/guide-cards' ); ?>

<!-- ── How It Works ─────────────────────────────────────────────────────── -->
<?php $steps = ah_get_process_steps(); if ( $steps ) : ?>
<section class="section" aria-label="How we work">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">The Process</span>
      <h2 class="section__title">How We Help You Buy</h2>
      <p class="section__desc" style="margin-inline:auto">
        A clear, structured process from brief to completion — with you in control at every step.
      </p>
    </div>
    <div class="process-grid">
      <?php foreach ( $steps as $i => $step ) :
        $step = is_object($step) ? (array) $step : $step;
      ?>
      <div class="process-card" data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 80; ?>">
        <div class="process-card__num"><?php echo esc_html( $step['num'] ?? sprintf('%02d', $i + 1) ); ?></div>
        <div class="process-card__title"><?php echo esc_html( $step['title'] ); ?></div>
        <p class="process-card__desc"><?php echo esc_html( $step['desc'] ?? $step['description'] ?? '' ); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Stats Strip ──────────────────────────────────────────────────────── -->
<?php $stats = ah_get_site_stats(); if ( $stats ) : ?>
<div class="section section--sm">
  <div class="container">
    <div class="stats-strip">
      <?php foreach ( $stats as $i => $stat ) :
        $stat = is_object($stat) ? (array) $stat : $stat;
      ?>
      <div class="stats-strip__item" data-aos="zoom-in" data-delay="<?php echo $i * 100; ?>">
        <div class="stats-strip__num"><?php echo esc_html( $stat['num'] ?? '' ); ?></div>
        <div class="stats-strip__label"><?php echo esc_html( $stat['label'] ?? '' ); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php get_template_part( 'components/services-section' ); ?>
<?php get_template_part( 'components/testimonials' ); ?>
<?php get_template_part( 'components/team-section' ); ?>
<?php get_template_part( 'components/faq-section' ); ?>

<!-- ── Latest Blog Posts ─────────────────────────────────────────────────── -->
<?php
$blog_posts = get_posts( [ 'numberposts' => 3, 'post_status' => 'publish' ] );
if ( $blog_posts ) :
?>
<section class="section" aria-label="Latest from the blog">
  <div class="container">
    <div class="section__header flex justify-between items-center flex-wrap gap-16">
      <div>
        <span class="section__eyebrow">News & Insights</span>
        <h2 class="section__title" style="margin-bottom:0">Latest from the Blog</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="btn btn-outline">All Articles →</a>
    </div>
    <div class="post-grid">
      <?php foreach ( $blog_posts as $post ) :
        setup_postdata( $post );
      ?>
      <article class="post-card" data-aos="fade-up">
        <?php if ( has_post_thumbnail( $post ) ) : ?>
          <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="post-card__img-wrap">
            <?php echo get_the_post_thumbnail( $post, 'ah-card' ); ?>
          </a>
        <?php endif; ?>
        <div class="post-card__body">
          <div class="card__meta">
            <span><?php echo esc_html( get_the_date( 'j M Y', $post ) ); ?></span>
            <span>·</span>
            <span><?php echo esc_html( ah_reading_time( $post->ID ) ); ?></span>
          </div>
          <h3 class="post-card__title">
            <a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
          </h3>
          <p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 20, '…' ) ); ?></p>
          <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="btn btn-sm btn-ghost">Read →</a>
        </div>
      </article>
      <?php endforeach; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php
get_template_part( 'components/cta-section', null, [
	'title'     => 'Your Ideal Home Is Out There.<br><em>Let\'s Find It Together.</em>',
	'desc'      => "Join 500+ buyers who saved time, stress, and thousands of pounds. Book a free, no-obligation consultation with one of our buyer's agents today.",
	'cta_label' => 'Book a Free Call →',
	'cta_url'   => home_url( '/contact/' ),
	'sec_label' => 'Read Our Guides First',
	'sec_url'   => home_url( '/guides/' ),
] );
?>

<?php get_footer(); ?>
