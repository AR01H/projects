<?php
/**
 * Template Name: Guides Archive
 */
get_header();

$categories = ah_get_guide_categories();
$active_cat = sanitize_text_field( $_GET['category'] ?? '' );

// Pull recent posts (guides) — filter by category if set
$query_args = [
  'post_type'      => 'post',
  'posts_per_page' => 12,
  'post_status'    => 'publish',
  'orderby'        => 'date',
  'order'          => 'DESC',
];
if ( $active_cat ) {
  $term = get_term_by( 'slug', $active_cat, 'category' );
  if ( $term ) {
    $query_args['cat'] = $term->term_id;
  }
}
$guides_query = new WP_Query( $query_args );
?>

<!-- ── Page Header ───────────────────────────────────────────────────────── -->
<section class="page-hero page-hero--sm" aria-label="Guides">
  <div class="container">
    <div class="page-hero__copy text-center" style="max-width:680px;margin-inline:auto" data-aos="fade-up">
      <span class="section__eyebrow">Free Resources</span>
      <h1 class="page-hero__title">The Complete<br><em>Home Buying Library</em></h1>
      <p class="page-hero__desc">
        Guides written by buyer's agents — not marketers. Everything you need to buy with confidence,
        from mortgage basics to completion day.
      </p>
    </div>
  </div>
</section>

<!-- ── Category Filter ───────────────────────────────────────────────────── -->
<?php if ( $categories ) : ?>
<div class="section section--xs" style="border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="filter-tabs" role="tablist" aria-label="Guide categories">
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>"
         class="filter-tab<?php if ( ! $active_cat ) echo ' filter-tab--active'; ?>"
         role="tab">
        All Guides
      </a>
      <?php foreach ( $categories as $cat ) :
        $cat     = is_object($cat) ? (array) $cat : $cat;
        $is_active = ( $active_cat === ( $cat['slug'] ?? '' ) );
      ?>
      <a href="<?php echo esc_url( home_url( '/guides/?category=' . urlencode( $cat['slug'] ?? '' ) ) ); ?>"
         class="filter-tab<?php if ( $is_active ) echo ' filter-tab--active'; ?>"
         role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
        <?php echo esc_html( ( $cat['icon'] ?? '' ) . ' ' . ( $cat['title'] ?? '' ) ); ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── Guide Cards ───────────────────────────────────────────────────────── -->
<section class="section" aria-label="Guides listing">
  <div class="container">

    <?php if ( $guides_query->have_posts() ) : ?>
    <div class="post-grid">
      <?php while ( $guides_query->have_posts() ) :
        $guides_query->the_post();
      ?>
      <article class="post-card" data-aos="fade-up">
        <?php if ( has_post_thumbnail() ) : ?>
          <a href="<?php the_permalink(); ?>" class="post-card__img-wrap">
            <?php the_post_thumbnail( 'ah-card' ); ?>
          </a>
        <?php endif; ?>
        <div class="post-card__body">
          <?php $cats = get_the_category(); if ( $cats ) : ?>
          <div class="post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></div>
          <?php endif; ?>
          <div class="card__meta">
            <span><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></span>
            <span>·</span>
            <span><?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span>
          </div>
          <h2 class="post-card__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
          </h2>
          <p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
          <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-ghost">Read Guide →</a>
        </div>
      </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <?php ah_pagination(); ?>

    <?php else : ?>
    <div class="text-center section--sm">
      <div style="font-size:3rem;margin-bottom:16px">📚</div>
      <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">
        No guides published yet
      </h2>
      <p style="color:var(--text-secondary);margin-bottom:24px">
        Check back soon — our team is building out the full guide library.
      </p>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-outline">View all categories →</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── Guide Category Cards ──────────────────────────────────────────────── -->
<?php if ( $categories ) : ?>
<section class="section section--alt" aria-label="Browse by category">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Browse by Topic</span>
      <h2 class="section__title">Find Exactly What You Need</h2>
    </div>
    <div class="grid-4">
      <?php foreach ( $categories as $i => $cat ) :
        $cat = is_object($cat) ? (array) $cat : $cat;
      ?>
      <a href="<?php echo esc_url( home_url( '/guides/?category=' . urlencode( $cat['slug'] ?? '' ) ) ); ?>"
         class="guide-card" data-aos="fade-up" data-delay="<?php echo $i * 100; ?>"
         style="text-decoration:none;color:inherit">
        <div class="guide-card__icon"><?php echo esc_html( $cat['icon'] ?? '📖' ); ?></div>
        <div class="guide-card__title"><?php echo esc_html( $cat['title'] ); ?></div>
        <div class="guide-card__desc"><?php echo esc_html( $cat['desc'] ?? '' ); ?></div>
        <div class="guide-card__count"><?php echo esc_html( $cat['count'] ?? '' ); ?> GUIDES →</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
