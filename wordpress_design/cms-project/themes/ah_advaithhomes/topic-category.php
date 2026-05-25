<?php
/**
 * Template: Category Archive within a Parent Term
 * URL: /{parent-term-slug}/{category-slug}/
 * Routed here by the template_include filter in functions.php.
 */
defined( 'ABSPATH' ) || exit;

get_header();

// ── Parent term + category ────────────────────────────────────────────────────
$pt = $GLOBALS['ah_current_pt'] ?? null;
if ( ! $pt ) { wp_redirect( home_url( '/' ) ); exit; }

$pt_slug  = $pt->slug;
$pt_name  = $pt->name;
$cat_slug = sanitize_title( get_query_var( 'ah_cat_slug' ) );
$paged    = max( 1, absint( $_GET['pg'] ?? 1 ) );

// Resolve WP category for this slug
$current_cat = get_term_by( 'slug', $cat_slug, 'category' );
$cat_name    = $current_cat ? $current_cat->name : ucwords( str_replace( '-', ' ', $cat_slug ) );

// ── All child categories (for sidebar/filter strip) ───────────────────────────
$child_terms   = [];
$pt_child_cats = [];
if ( class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$tax_table   = AH_DB_Helper::table( 'taxonomies' );
	$child_terms = $wpdb->get_results( $wpdb->prepare(
		"SELECT id, slug, name FROM `{$tax_table}` WHERE parent_term_id = %d AND status = 1 ORDER BY name ASC",
		(int) $pt->id
	) ) ?: [];
	foreach ( $child_terms as $ct ) {
		$wc = get_term_by( 'slug', $ct->slug, 'category' );
		if ( $wc ) $pt_child_cats[] = $wc;
	}
}

// ── Posts query (filtered to this category) ───────────────────────────────────
$wp_args = [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 12,
	'paged'               => $paged,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
];
if ( $current_cat ) {
	$wp_args['cat'] = $current_cat->term_id;
} else {
	$wp_args['post__in'] = [ 0 ]; // category not found, show nothing
}
$blog_query = new WP_Query( $wp_args );
$posts_arr  = [];
while ( $blog_query->have_posts() ) { $blog_query->the_post(); $posts_arr[] = get_post(); }
wp_reset_postdata();

// ── Sidebar data ──────────────────────────────────────────────────────────────
$site_stats = function_exists( 'ah_get_site_stats' ) ? ah_get_site_stats() : [];

// Popular Now: scoped to this topic's categories, excluding featured posts and
// posts already shown in the main grid (so nothing appears twice on the page).
$popular_query = [
	'posts_per_page' => 5,
	'post_status'    => 'publish',
	'meta_query'     => [
		'relation' => 'AND',
		[ 'key' => '_ah_is_popular', 'value' => '1' ],
		[
			'relation' => 'OR',
			[ 'key' => '_ah_is_featured', 'compare' => 'NOT EXISTS' ],
			[ 'key' => '_ah_is_featured', 'value' => '1', 'compare' => '!=' ],
		],
	],
];
if ( $pt_child_cats ) {
	$popular_query['category__in'] = array_map( fn( $c ) => $c->term_id, $pt_child_cats );
}
$already_shown = array_map( fn( $p ) => $p->ID, $posts_arr );
if ( $already_shown ) {
	$popular_query['post__not_in'] = $already_shown;
}
$popular_posts = get_posts( $popular_query );

// ── Post data helper ──────────────────────────────────────────────────────────
if ( ! function_exists( 'nif_get_post_data' ) ) {
	function nif_get_post_data( WP_Post $p ): array {
		$cats      = get_the_category( $p->ID );
		$cat       = $cats[0] ?? null;
		$thumb_url = get_the_post_thumbnail_url( $p->ID, 'ah-card' )
			?: get_the_post_thumbnail_url( $p->ID, 'medium_large' )
			?: get_the_post_thumbnail_url( $p->ID, 'medium' )
			?: get_the_post_thumbnail_url( $p->ID, 'full' );
		$permalink = get_permalink( $p->ID );
		$excerpt   = wp_trim_words( get_the_excerpt( $p->ID ) ?: $p->post_content, 22, '…' );
		$read_time = function_exists( 'ah_reading_time' ) ? ah_reading_time( $p->ID ) : '';
		$emoji_map = [ 'buying' => '🏠', 'first' => '🔑', 'finance' => '💷', 'legal' => '⚖️', 'invest' => '📈', 'tips' => '💡' ];
		$emoji     = '📰';
		if ( $cat ) {
			foreach ( $emoji_map as $k => $e ) {
				if ( stripos( $cat->slug, $k ) !== false ) { $emoji = $e; break; }
			}
		}
		return compact( 'cat', 'thumb_url', 'permalink', 'excerpt', 'read_time', 'emoji' );
	}
}

$total_posts = $blog_query->found_posts;
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => esc_html( $pt_name ),
	'title'      => esc_html( $cat_name ),
	'badge'      => $total_posts ? sprintf( _n( '%s ' . AH_TERM_LOWER, '%s ' . AH_TERM_LOWER_PLURAL, $total_posts, 'ah-theme' ), number_format_i18n( $total_posts ) ) : '',
	'breadcrumb' => [
		[ TXT_HOME,   home_url( '/' ) ],
		[ $pt_name,                    home_url( '/' . $pt_slug . '/' ) ],
		[ $cat_name,                   '' ],
	],
] ); ?>

<?php if ( ! empty( $pt_child_cats ) ) : ?>
<div class="topic-filter-strip">
	<div class="container">
		<div class="topic-filter-strip__inner">
			<a href="<?php echo esc_url( home_url( '/' . $pt_slug . '/' ) ); ?>"
			   class="topic-chip topic-chip--all">
				<?php echo esc_html( TXT_ALL ); ?>
			</a>
			<?php foreach ( $pt_child_cats as $fc ) : ?>
				<a href="<?php echo esc_url( home_url( '/' . $pt_slug . '/' . $fc->slug . '/' ) ); ?>"
				   class="topic-chip<?php echo $fc->slug === $cat_slug ? ' topic-chip--active' : ''; ?>">
					<?php echo esc_html( $fc->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<div class="nif-portal-bg">
	<div class="container">
		<div class="nif-portal-wrap">

			<main class="nif-portal-main">

				<?php if ( ! empty( $posts_arr ) ) : ?>
				<div class="nif-grid">
					<?php foreach ( $posts_arr as $idx => $p ) :
						$d     = nif_get_post_data( $p );
						$delay = ( $idx % 3 ) * 80;
					?>
					<article class="nif-grid-card" data-aos="fade-up" data-aos-delay="<?php echo esc_attr( $delay ); ?>"
					         <?php if ( $d['cat'] ) echo esc_html( TXT_DATA_CAT_ESC_ATTR_D_CAT_SLUG ); ?>>

						<?php if ( $d['thumb_url'] ) : ?>
							<div class="nif-grid-card__img">
								<a href="<?php echo esc_url( $d['permalink'] ); ?>" tabindex="-1" aria-hidden="true">
									<img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
									     alt="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_GET_THE_TITLE_P_ID ); ?>"
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
									<?php echo esc_html( TXT_READ ); ?> <span aria-hidden="true">→</span>
								</a>
							</div>
						</div>
					</article>
					<?php endforeach; ?>
				</div>

				<?php else : ?>
				<div class="nif-empty" data-aos="fade-up">
					<div class="nif-empty__icon">✍️</div>
					<h2 class="nif-empty__title"><?php echo esc_html( TXT_NOTHING_HERE_YET ); ?></h2>
					<p class="nif-empty__desc"><?php printf( esc_html( TXT_NO_S_IN_THIS_CATEGORY_YET_TRY_ANOTHER_S ), AH_TERM_LOWER_PLURAL, AH_TERM_LOWER ); ?></p>
					<a href="<?php echo esc_url( home_url( '/' . $pt_slug . '/' ) ); ?>" class="btn btn-outline" style="margin-top:20px">
						← <?php echo esc_html( $pt_name ); ?>
					</a>
				</div>
				<?php endif; ?>

				<?php if ( $blog_query->max_num_pages > 1 ) :
					$pg_base = home_url( '/' . $pt_slug . '/' . $cat_slug . '/' );
					$links   = paginate_links( [
						'base'      => $pg_base . '?pg=%#%',
						'format'    => '',
						'current'   => $paged,
						'total'     => $blog_query->max_num_pages,
						'prev_text' => '← Prev',
						'next_text' => 'Next →',
						'type'      => 'array',
					] );
					if ( $links ) : ?>
				<nav class="pagination" aria-label="<?php echo esc_attr( TXT_PAGE_NAVIGATION ); ?>" style="margin-top:48px">
					<ul class="pagination__list">
						<?php foreach ( $links as $link ) echo ( '<li class="pagination__item">'. $link . '</li>' ); ?>
					</ul>
				</nav>
				<?php endif; endif; ?>

			</main>

			<aside class="nif-portal-sidebar" aria-label="<?php echo esc_attr( TXT_TOPIC_SIDEBAR ); ?>">
				<?php get_template_part( 'components/topic-sidebar', null, [
					'pt'            => $pt,
					'pt_slug'       => $pt_slug,
					'pt_child_cats' => $pt_child_cats,
					'active_cat'    => $cat_slug,
					'site_stats'    => $site_stats,
					'popular_posts' => $popular_posts,
				] ); ?>
			</aside>

		</div>
	</div>
</div>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
