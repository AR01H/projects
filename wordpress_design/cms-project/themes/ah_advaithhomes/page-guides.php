<?php
/**
 * Template Name: Guides Archive
 */
get_header();

$categories = ah_get_guide_categories();
$_raw_cat   = sanitize_text_field( $_GET['category'] ?? '' );
$active_cat = sanitize_title( strtok( $_raw_cat, '?' ) ); // strip any ?pg=N pollution
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
	'title_em'   => 'Library',
	'desc'       => 'Guides written by buyer\'s agents - not marketers. Everything you need to buy with confidence, from mortgage basics to completion day.',
	'breadcrumb' => array_filter( [
		[ 'Home',   home_url( '/' ) ],
		[ 'Guides', $active_cat ? esc_url( $base_url ) : '' ],
		$active_cat_obj ? [ esc_html( $active_cat_obj['title'] ?? $active_cat ), '' ] : null,
	] ),
] ); ?>

<?php if ( $active_cat && $guides_query ) : ?>
<!-- ── FILTERED: back link + posts + category cards below ──────────────── -->

<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_GUIDES_LISTING ); ?>">
  <div class="container">

    <!-- Category banner -->
    <?php
      $cat_img_url = ! empty( $active_cat_obj['image_id'] ) ? wp_get_attachment_image_url( $active_cat_obj['image_id'], 'medium_large' ) : '';
    ?>
    <div class="gc-cat-banner" style="<?php if($cat_img_url) echo '--gc-cat-img:url(' . esc_url($cat_img_url) . ')'; ?>">
      <div class="gc-cat-banner__left">
        <a href="<?php echo esc_url( $base_url ); ?>" class="gc-cat-banner__back">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          <?php echo esc_html( TXT_ALL_TOPICS ); ?>
        </a>
        <div class="gc-cat-banner__icon"><?php echo esc_html( $active_cat_obj['icon_emoji'] ?? '📖' ); ?></div>
        <h1 class="gc-cat-banner__title"><?php echo esc_html( $active_cat_obj['title'] ?? $active_cat ); ?></h1>
        <?php if ( ! empty( $active_cat_obj['desc'] ) ) : ?>
          <p class="gc-cat-banner__desc"><?php echo esc_html( $active_cat_obj['desc'] ); ?></p>
        <?php endif; ?>
        <?php if ( ! empty( $active_cat_obj['count'] ) ) : ?>
          <span class="gc-cat-banner__count"><?php echo (int) $active_cat_obj['count']; ?> <?php echo esc_html( TXT_GUIDES ); ?></span>
        <?php endif; ?>
      </div>
      <?php if ( $cat_img_url ) : ?>
      <div class="gc-cat-banner__img-wrap">
        <img src="<?php echo esc_url( $cat_img_url ); ?>" alt="<?php echo esc_attr( $active_cat_obj['title'] ?? '' ); ?>" class="gc-cat-banner__img">
      </div>
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
        <div class="gc__img-wrap">
          <?php if ( has_post_thumbnail() ) : ?>
            <?php the_post_thumbnail( 'ah-card', [ 'class' => 'gc__img' ] ); ?>
          <?php else : ?>
            <div class="gc__img gc__img--fallback">📖</div>
          <?php endif; ?>
          <?php if ( $cat_name ) : ?>
            <span class="gc__cat"><?php echo esc_html( $cat_name ); ?></span>
          <?php endif; ?>
        </div>
        <div class="gc__body">
          <div class="gc__meta">
            <span class="gc__read-time">⏱ <?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span>
          </div>
          <h2 class="gc__title"><?php the_title(); ?></h2>
          <?php $excerpt = get_the_excerpt(); if ( $excerpt ) : ?>
            <p class="gc__excerpt"><?php echo wp_trim_words( $excerpt, 18, '…' ); ?></p>
          <?php endif; ?>
          <span class="gc__btn">Read Guide <span class="gc__arrow">→</span></span>
        </div>
      </a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <!-- Pagination -->
    <?php if ( $guides_query->max_num_pages > 1 ) :
      $links = paginate_links( [
        'base'      => add_query_arg( 'category', $active_cat, $base_url ) . '&pg=%#%',
        'format'    => '',
        'current'   => $paged,
        'total'     => $guides_query->max_num_pages,
        'prev_text' => '← Prev',
        'next_text' => 'Next →',
        'type'      => 'array',
      ] );
      if ( $links ) :
    ?>
    <nav class="pagination" aria-label="<?php echo esc_attr( TXT_GUIDES_NAVIGATION ); ?>" style="margin-top:48px">
      <ul class="pagination__list">
        <?php foreach ( $links as $link ) echo ( '<li class="pagination__item">'. $link . '</li>' ); ?>
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
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_BROWSE_BY_TOPIC_1 ); ?>">
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
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_GUIDE_TOPICS ); ?>">
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
