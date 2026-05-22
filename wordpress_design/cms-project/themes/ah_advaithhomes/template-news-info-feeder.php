<?php
/**
 * Template Name: News & Info Feeder
 * Portal-style information hub with sectioned layout.
 * URL: /news-info-feeder/
 */

get_header();

$active_cat = sanitize_text_field( $_GET['category'] ?? '' );
$paged      = max( 1, get_query_var( 'paged' ) ?: ( get_query_var( 'page' ) ?: 1 ) );

// ── WP posts query ────────────────────────────────────────────────────────────
$wp_args = [
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 15,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
];
if ( $active_cat ) {
	$term = get_term_by( 'slug', $active_cat, 'category' );
	if ( $term ) $wp_args['cat'] = $term->term_id;
}
$blog_query = new WP_Query( $wp_args );
$wp_cats    = get_categories( [ 'hide_empty' => true ] );

// ── Plugin posts (page 1 only, no filter) ─────────────────────────────────────
$plugin_posts = [];
if ( class_exists( 'AH_DB_Helper' ) && $paged === 1 && ! $active_cat ) {
	global $wpdb;
	$p_tbl        = AH_DB_Helper::table( 'posts' );
	$plugin_posts = $wpdb->get_results(
		"SELECT * FROM `{$p_tbl}` WHERE status = 'active' ORDER BY created_at DESC LIMIT 4"
	);
}

// ── Static resource / tool cards ─────────────────────────────────────────────
$resource_cards = [
	[
		'icon'  => '🧮',
		'badge' => 'Free Tool',
		'title' => 'Stamp Duty Calculator',
		'desc'  => 'Calculate your exact SDLT liability — standard rate, first-time buyer relief, and additional property surcharge — in seconds.',
		'url'   => home_url( '/stamp-duty-2025-complete-guide/' ),
		'style' => 'accent',
	],
	[
		'icon'  => '📋',
		'badge' => 'Free Guide',
		'title' => 'Mortgage Readiness Checklist',
		'desc'  => 'Everything lenders check before approving your application. Know your position before you speak to a broker.',
		'url'   => home_url( '/getting-mortgage-in-principle-uk/' ),
		'style' => 'light',
	],
	[
		'icon'  => '🗓️',
		'badge' => 'Free — No Obligation',
		'title' => 'Book a Buyer Strategy Call',
		'desc'  => 'A 30-minute call with one of our buyer\'s agents. Straight, practical advice — no sales pitch.',
		'url'   => home_url( '/contact/' ),
		'style' => 'dark',
	],
	[
		'icon'  => '🗺️',
		'badge' => 'Area Guides',
		'title' => 'UK Property Market Guides',
		'desc'  => 'In-depth local insights for London, Bristol, Manchester, Leeds, Birmingham and beyond.',
		'url'   => home_url( '/area-guides/' ),
		'style' => 'accent',
	],
];

// ── Collect posts ─────────────────────────────────────────────────────────────
$posts_arr = [];
while ( $blog_query->have_posts() ) {
	$blog_query->the_post();
	$posts_arr[] = get_post();
}
wp_reset_postdata();

// ── Post data helper (shared across all NIF components) ───────────────────────
if ( ! function_exists( 'nif_get_post_data' ) ) {
	function nif_get_post_data( WP_Post $p ): array {
		$cats      = get_the_category( $p->ID );
		$cat       = $cats[0] ?? null;
		$thumb_url = get_the_post_thumbnail_url( $p->ID, 'ah-card' );
		$permalink = get_permalink( $p->ID );
		$excerpt   = wp_trim_words( get_the_excerpt( $p->ID ) ?: $p->post_content, 22, '…' );
		$read_time = function_exists( 'ah_reading_time' ) ? ah_reading_time( $p->ID ) : '';
		$emoji_map = [ 'buying' => '🏠', 'first' => '🔑', 'finance' => '💷', 'legal' => '⚖️', 'invest' => '📈', 'tips' => '💡' ];
		$emoji = '📰';
		if ( $cat ) {
			foreach ( $emoji_map as $k => $e ) {
				if ( stripos( $cat->slug, $k ) !== false ) { $emoji = $e; break; }
			}
		}
		return compact( 'cat', 'thumb_url', 'permalink', 'excerpt', 'read_time', 'emoji' );
	}
}
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => 'News, Guides & Resources',
	'title'      => 'Your Property Buyer\'s',
	'title_em'   => 'Information Hub',
	'desc'       => 'Market updates, step-by-step guides, mortgage insights, legal explainers and practical tools — everything you need to buy smarter.',
	'breadcrumb' => [
		[ 'Home', home_url( '/' ) ],
		[ 'News & Info', '' ],
	],
] ); ?>

<?php get_template_part( 'components/nif-filter-bar', null, [
	'cats'       => $wp_cats,
	'active_cat' => $active_cat,
	'permalink'  => get_permalink(),
] ); ?>

<?php if ( ! $active_cat && $paged === 1 && ! empty( $posts_arr ) ) :
	// ── PORTAL LAYOUT ─────────────────────────────────────────────────────────

	get_template_part( 'components/nif-hero-row', null, [
		'posts' => array_slice( $posts_arr, 0, 3 ),
	] );

	get_template_part( 'components/nif-feature-row', null, [
		'feat'  => $posts_arr[3] ?? null,
		'flank' => array_slice( $posts_arr, 4, 2 ),
	] );

	get_template_part( 'components/nif-compact-grid', null, [
		'posts'    => array_slice( $posts_arr, 6 ),
		'more_url' => get_permalink() . '?paged=2',
	] );

	get_template_part( 'components/nif-resource-strip', null, [
		'cards' => $resource_cards,
	] );

else : ?>
<!-- ── Filtered / paginated grid ──────────────────────────────────────────────  -->
<section class="section" aria-label="<?php esc_attr_e( 'News and information feed', 'ah-theme' ); ?>">
  <div class="container">

    <?php if ( ! empty( $posts_arr ) ) : ?>
    <div class="nif-grid">
      <?php foreach ( $posts_arr as $idx => $p ) :
        $d     = nif_get_post_data( $p );
        $delay = ( $idx % 3 ) * 80;
      ?>
      <article class="nif-grid-card" data-aos="fade-up" data-aos-delay="<?php echo esc_attr( $delay ); ?>">

        <?php if ( $d['thumb_url'] ) : ?>
          <div class="nif-grid-card__img">
            <a href="<?php echo esc_url( $d['permalink'] ); ?>" tabindex="-1" aria-hidden="true">
              <img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
                   alt="<?php echo esc_attr( get_the_title( $p->ID ) ); ?>"
                   loading="lazy" decoding="async">
            </a>
          </div>
        <?php else : ?>
          <div class="nif-grid-card__img nif-grid-card__img--placeholder" aria-hidden="true">
            <span><?php echo esc_html( $d['emoji'] ); ?></span>
          </div>
        <?php endif; ?>

        <div class="nif-grid-card__body">
          <?php if ( $d['cat'] ) : ?>
            <span class="nif-badge" data-slug="<?php echo esc_attr( $d['cat']->slug ); ?>">
              <?php echo esc_html( $d['cat']->name ); ?>
            </span>
          <?php endif; ?>
          <h3 class="nif-grid-card__title">
            <a href="<?php echo esc_url( $d['permalink'] ); ?>">
              <?php echo esc_html( get_the_title( $p->ID ) ); ?>
            </a>
          </h3>
          <p class="nif-grid-card__excerpt"><?php echo esc_html( $d['excerpt'] ); ?></p>
          <div class="nif-grid-card__footer">
            <span class="nif-meta-time">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?php echo $d['read_time'] ? esc_html( $d['read_time'] ) : 'Quick read'; ?>
            </span>
            <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-read-link nif-read-link--sm">
              <?php esc_html_e( 'Read', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>

      </article>
      <?php endforeach; ?>
    </div>

    <?php else : ?>
    <div class="nif-empty" data-aos="fade-up">
      <div class="nif-empty__icon">✍️</div>
      <h2 class="nif-empty__title"><?php esc_html_e( 'Nothing here yet', 'ah-theme' ); ?></h2>
      <p class="nif-empty__desc">
        <?php echo $active_cat
          ? esc_html__( 'No posts in this topic yet. Try another category or view everything.', 'ah-theme' )
          : esc_html__( 'We\'re working on great content — check back shortly.', 'ah-theme' ); ?>
      </p>
      <?php if ( $active_cat ) : ?>
        <a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-outline" style="margin-top:20px">
          <?php esc_html_e( 'View All Topics →', 'ah-theme' ); ?>
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ( $blog_query->max_num_pages > 1 ) :
      $base_url = $active_cat
        ? add_query_arg( 'category', $active_cat, get_permalink() )
        : get_permalink();
      $links = paginate_links( [
        'base'      => trailingslashit( $base_url ) . '%_%',
        'format'    => '?paged=%#%',
        'current'   => $paged,
        'total'     => $blog_query->max_num_pages,
        'prev_text' => '← Prev',
        'next_text' => 'Next →',
        'type'      => 'array',
      ] );
      if ( $links ) : ?>
    <nav class="pagination" aria-label="<?php esc_attr_e( 'Page navigation', 'ah-theme' ); ?>" style="margin-top:48px">
      <ul class="pagination__list">
        <?php foreach ( $links as $link ) echo '<li class="pagination__item">' . $link . '</li>'; ?>
      </ul>
    </nav>
    <?php endif; endif; ?>

  </div>
</section>
<?php endif; ?>

<!-- ── Plugin posts strip ────────────────────────────────────────────────────── -->
<?php if ( ! empty( $plugin_posts ) ) : ?>
<section class="section section--alt" aria-label="<?php esc_attr_e( 'More reads from our reports', 'ah-theme' ); ?>">
  <div class="container">
    <div class="nif-section-label" data-aos="fade-up">
      <span class="section__eyebrow"><?php esc_html_e( 'From Our Reports', 'ah-theme' ); ?></span>
      <h2 class="section__title" style="font-size:1.4rem;margin:6px 0 0"><?php esc_html_e( 'More', 'ah-theme' ); ?> <em><?php esc_html_e( 'Insights', 'ah-theme' ); ?></em></h2>
    </div>
    <div class="nif-plugin-strip">
      <?php foreach ( $plugin_posts as $pp ) :
        $pp_url     = home_url( '/' . $pp->slug . '/' );
        $pp_excerpt = wp_trim_words( $pp->excerpt ?: $pp->content, 16, '…' );
      ?>
      <article class="nif-plugin-card" data-aos="fade-up">
        <span class="nif-badge" data-type="<?php echo esc_attr( $pp->post_type ); ?>">
          <?php echo esc_html( ucfirst( $pp->post_type ) ); ?>
        </span>
        <h3 class="nif-plugin-card__title">
          <a href="<?php echo esc_url( $pp_url ); ?>"><?php echo esc_html( $pp->title ); ?></a>
        </h3>
        <?php if ( $pp_excerpt ) : ?>
          <p class="nif-plugin-card__excerpt"><?php echo esc_html( $pp_excerpt ); ?></p>
        <?php endif; ?>
        <a href="<?php echo esc_url( $pp_url ); ?>" class="nif-read-link nif-read-link--sm" style="margin-top:auto">
          <?php esc_html_e( 'Read →', 'ah-theme' ); ?>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── CTA ─────────────────────────────────────────────────────────────────── -->
<section class="section section--pattern" aria-label="<?php esc_attr_e( 'Free consultation', 'ah-theme' ); ?>">
  <div class="container container--sm">
    <div class="newsletter-block text-center" data-aos="fade-up">
      <span class="section__eyebrow"><?php esc_html_e( 'Free Advice', 'ah-theme' ); ?></span>
      <h2 class="section__title" style="font-size:1.75rem;margin-bottom:12px">
        <?php esc_html_e( 'Ready to talk strategy?', 'ah-theme' ); ?>
      </h2>
      <p style="color:var(--text-secondary);margin-bottom:28px">
        <?php esc_html_e( "Book a free 30-minute call with a buyer's agent. No commitment, no sales pitch — just practical guidance on your next move.", 'ah-theme' ); ?>
      </p>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary">
        <?php esc_html_e( 'Book Free Consultation →', 'ah-theme' ); ?>
      </a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
