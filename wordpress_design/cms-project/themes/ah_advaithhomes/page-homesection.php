<?php get_header(); ?>

<?php if ( ah_section_visible( 'home_hero' ) ) : ?>
<?php get_template_part( 'components/hero' ); ?>
<?php endif; ?>

<?php if ( ah_section_visible( 'global_trust_bar' ) ) : ?>
<?php get_template_part( 'components/trust-bar' ); ?>
<?php endif; ?>

<?php if ( ah_section_visible( 'home_guide_cards' ) ) : ?>
<?php get_template_part( 'components/guide-cards' ); ?>
<?php endif; ?>

<!-- ── How It Works ─────────────────────────────────────────────────────── -->
<?php if ( ah_section_visible( 'home_process' ) ) : ?>
<?php $steps = ah_get_process_steps(); if ( $steps ) : ?>
<section class="section" aria-label="<?php echo esc_attr( TXT_HOW_WE_WORK ); ?>">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">The Process</span>
      <h2 class="section__title">How We Help You Buy</h2>
      <p class="section__desc" style="margin-inline:auto">
        A clear, structured process from brief to completion - with you in control at every step.
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
<?php endif; ?>



<?php if ( ah_section_visible( 'home_services' ) ) : ?>
<?php get_template_part( 'components/services-section' ); ?>
<?php endif; ?>

<?php if ( ah_section_visible( 'home_properties' ) ) : ?>
<?php get_template_part( 'components/property-showcase' ); ?>
<?php endif; ?>

<?php if ( ah_section_visible( 'home_team' ) ) : ?>
<?php get_template_part( 'components/team-section' ); ?>
<?php endif; ?>

<?php if ( ah_section_visible( 'home_faq' ) ) : ?>
<?php get_template_part( 'components/faq-section' ); ?>
<?php endif; ?>

<!-- ── Latest Blog Posts ─────────────────────────────────────────────────── -->
<?php if ( ah_section_visible( 'home_blog' ) ) : ?>
<?php
$blog_posts = get_posts( [ 'numberposts' => 4, 'post_status' => 'publish' ] );
if ( $blog_posts ) :
?>
<section class="section" aria-label="<?php echo esc_attr( TXT_LATEST_FROM_THE_BLOG ); ?>" style="background-color: var(--bg-alt);">
  <div class="container">
    <div class="section__header flex justify-between items-center flex-wrap gap-16">
      <div>
        <span class="section__eyebrow">News & Insights</span>
        <h2 class="section__title" style="margin-bottom:0">Latest from the Blog</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="btn btn-outline btn-sm">All Articles →</a>
    </div>
    <div class="post-grid">
      <?php foreach ( $blog_posts as $idx => $post ) :
        setup_postdata( $post );
        $is_featured = $idx === 0 || get_post_meta( $post->ID, '_ah_featured', true );
        $cats = get_the_category( $post->ID );
        $cat_name = ! empty( $cats ) ? $cats[0]->name : '';
         setup_postdata( $post );
        get_template_part( 'components/mini-blog-card', null, [
            'post'  => $post,
            'idx'   => $idx,
        ] );
      ?>
      <?php endforeach; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php endif; ?>

<?php if ( ah_section_visible( 'home_cta' ) ) : ?>
<?php
get_template_part( 'components/cta-section', null, [] );
?>
<?php endif; ?>

<?php get_footer(); ?>
