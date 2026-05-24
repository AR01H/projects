<?php
/**
 * Template Name: Guides Archive
 */
get_header();

$categories = ah_get_guide_categories();
$active_cat = sanitize_text_field( $_GET['category'] ?? '' );
$paged      = max( 1, absint( $_GET['pg'] ?? get_query_var( 'paged', 1 ) ) );
$base_url   = get_permalink();

// Active category object
$active_cat_obj = null;
if ( $active_cat && $categories ) {
	foreach ( $categories as $c ) {
		$c = is_object( $c ) ? (array) $c : $c;
		if ( ( $c['slug'] ?? '' ) === $active_cat ) {
			$active_cat_obj = $c;
			break;
		}
	}
}

// Query - only run when a category is selected
$guides_query = null;
if ( $active_cat ) {
	$query_args = [
		'post_type'      => 'post',
		'posts_per_page' => 12,
		'paged'          => $paged,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	];
	$term = get_term_by( 'slug', $active_cat, 'category' );
	if ( $term ) $query_args['cat'] = $term->term_id;
	$guides_query = new WP_Query( $query_args );
}
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => '',
	'title'      => 'The Complete',
	'title_em'   => 'Home Buying Library',
	'desc'       => 'Guides written by buyer\'s agents - not marketers. Everything you need to buy with confidence, from mortgage basics to completion day.',
	'breadcrumb' => array_filter( [
		[ 'Home',   home_url( '/' ) ],
		[ 'Guides', $active_cat ? esc_url( $base_url ) : '' ],
		$active_cat_obj ? [ esc_html( $active_cat_obj['title'] ?? $active_cat ), '' ] : null,
	] ),
] ); ?>

<?php if ( $active_cat && $guides_query ) : ?>
<!-- ── FILTERED: back link + posts + category cards below ──────────────── -->

<section class="section section--pattern" aria-label="<?php esc_attr_e( 'Guides listing', 'ah-theme' ); ?>">
  <div class="container">

    <!-- Back / filter indicator -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:32px">
      <a href="<?php echo esc_url( $base_url ); ?>"
         style="display:inline-flex;align-items:center;gap:6px;font-size:.85rem;font-weight:600;color:var(--accent);text-decoration:none">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <?php esc_html_e( 'All Topics', 'ah-theme' ); ?>
      </a>
      <?php if ( $active_cat_obj ) : ?>
      <span style="font-size:.82rem;color:var(--text-secondary)">
        <?php echo esc_html( $active_cat_obj['icon_emoji'] ?? '' ); ?>
        <?php echo esc_html( $active_cat_obj['title'] ?? $active_cat ); ?>
        <?php if ( ! empty( $active_cat_obj['count'] ) ) : ?>
        &mdash; <?php echo (int) $active_cat_obj['count']; ?> <?php esc_html_e( 'guides', 'ah-theme' ); ?>
        <?php endif; ?>
      </span>
      <?php endif; ?>
    </div>

    <?php if ( $guides_query->have_posts() ) : ?>
    <div class="post-grid">
      <?php while ( $guides_query->have_posts() ) :
        $guides_query->the_post();
        $cats     = get_the_category();
        $cat0     = $cats ? $cats[0] : null;
        $cat_name = $cat0 ? $cat0->name : '';
        $cat_slug = $cat0 ? $cat0->slug : '';
      ?>
      <a href="<?php the_permalink(); ?>" class="gc" data-cat="<?php echo esc_attr( $cat_slug ); ?>" data-aos="fade-up">
        <?php if ( has_post_thumbnail() ) : ?>
          <?php the_post_thumbnail( 'ah-card', [ 'class' => 'gc__img' ] ); ?>
        <?php else : ?>
          <div class="gc__img gc__img--fallback">📖</div>
        <?php endif; ?>
        <div class="gc__overlay">
          <div class="gc__top">
            <?php if ( $cat_name ) : ?>
              <span class="gc__cat"><?php echo esc_html( $cat_name ); ?></span>
            <?php endif; ?>
          </div>
          <div class="gc__bottom">
            <div class="gc__meta"><?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></div>
            <h2 class="gc__title"><?php the_title(); ?></h2>
            <span class="gc__btn">Read Guide →</span>
          </div>
        </div>
      </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <!-- Pagination -->
    <?php if ( $guides_query->max_num_pages > 1 ) :
      $sep   = strpos( $base_url, '?' ) !== false ? '&' : '?';
      $links = paginate_links( [
        'base'      => add_query_arg( 'category', $active_cat, $base_url ) . $sep . 'pg=%#%',
        'format'    => '',
        'current'   => $paged,
        'total'     => $guides_query->max_num_pages,
        'prev_text' => '← Prev',
        'next_text' => 'Next →',
        'type'      => 'array',
      ] );
      if ( $links ) :
    ?>
    <nav class="pagination" aria-label="<?php esc_attr_e( 'Guides navigation', 'ah-theme' ); ?>" style="margin-top:48px">
      <ul class="pagination__list">
        <?php foreach ( $links as $link ) echo '<li class="pagination__item">' . $link . '</li>'; ?>
      </ul>
    </nav>
    <?php endif; endif; ?>

    <?php else : ?>
    <div class="text-center section--sm">
      <div style="font-size:3rem;margin-bottom:16px">📚</div>
      <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">No guides in this topic yet</h2>
      <p style="color:var(--text-secondary);margin-bottom:24px">Check back soon.</p>
      <a href="<?php echo esc_url( $base_url ); ?>" class="btn btn-outline">← All Topics</a>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ── Category cards repeated below posts ───────────────────────────────── -->
<?php if ( $categories ) : ?>
<section class="section section--pattern" aria-label="<?php esc_attr_e( 'Browse by topic', 'ah-theme' ); ?>">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Browse by Topic</span>
      <h2 class="section__title">Explore More Topics</h2>
    </div>
    <div class="gcat-grid">
      <?php foreach ( $categories as $i => $cat ) :
        $cat = is_object( $cat ) ? (array) $cat : $cat;
        get_template_part( 'components/cards/guide-category-card', null, [ 'cat' => $cat, 'index' => $i ] );
      endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php else : ?>
<!-- ── HOME: category cards grid only ───────────────────────────────────── -->

<?php if ( $categories ) : ?>
<section class="section section--pattern" aria-label="<?php esc_attr_e( 'Guide topics', 'ah-theme' ); ?>">
  <div class="container">
    <div class="section__header text-center">
      <span class="section__eyebrow">Browse by Topic</span>
      <h2 class="section__title">Find Exactly What You Need</h2>
    </div>
    <div class="gcat-grid">
      <?php foreach ( $categories as $i => $cat ) :
        $cat = is_object( $cat ) ? (array) $cat : $cat;
        get_template_part( 'components/cards/guide-category-card', null, [ 'cat' => $cat, 'index' => $i ] );
      endforeach; ?>
    </div>
  </div>
</section>
<?php else : ?>
<div class="text-center section">
  <div style="font-size:3rem;margin-bottom:16px">📚</div>
  <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">No guide topics yet</h2>
  <p style="color:var(--text-secondary)">Check back soon.</p>
</div>
<?php endif; ?>

<?php endif; ?>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
