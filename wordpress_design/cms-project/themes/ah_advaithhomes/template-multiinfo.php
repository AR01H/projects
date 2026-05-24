<?php
/**
 * Template Name: MultiInfo Portal
 *
 * Routes:
 *   /multiinfo/            → all parent terms, full home layout
 *   /multiinfo/<pt-slug>/  → posts scoped to that parent term only
 *
 * The second route is served by template_include (see functions.php).
 * $GLOBALS['ah_multiinfo_pt'] is set there; on the WP page it is null (= show all).
 */
defined( 'ABSPATH' ) || exit;

get_header();

// ── Active parent term ────────────────────────────────────────────────────────
$active_pt   = $GLOBALS['ah_multiinfo_pt'] ?? null;
$active_slug = $active_pt ? $active_pt->slug : '';
$base_url    = $active_slug
	? home_url( '/multiinfo/' . $active_slug . '/' )
	: home_url( '/multiinfo/' );

// ── Sub-category filter (within a parent term) ────────────────────────────────
$active_cat = sanitize_title( $_GET['cat'] ?? '' );
$paged      = max( 1, absint( $_GET['pg'] ?? 1 ) );

// ── All parent terms (for "All Topics" chip strip on root page) ───────────────
$parent_terms = [];
if ( class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$pt_table     = AH_DB_Helper::table( 'taxonomy_parent_terms' );
	$parent_terms = $wpdb->get_results(
		"SELECT id, name, slug, color, icon_emoji FROM `{$pt_table}` WHERE status = 1 ORDER BY name ASC"
	) ?: [];
}

// ── Child categories for active parent term ───────────────────────────────────
$child_terms   = [];
$pt_child_cats = []; // WP_Term objects

if ( $active_pt && class_exists( 'AH_DB_Helper' ) ) {
	$tax_table   = AH_DB_Helper::table( 'taxonomies' );
	$child_terms = $wpdb->get_results( $wpdb->prepare(
		"SELECT id, slug, name FROM `{$tax_table}` WHERE parent_term_id = %d AND status = 1 ORDER BY name ASC",
		(int) $active_pt->id
	) ) ?: [];
	foreach ( $child_terms as $ct ) {
		$wc = get_term_by( 'slug', $ct->slug, 'category' );
		if ( $wc ) $pt_child_cats[] = $wc;
	}
}

// ── Resolve the specific sub-category WP term (if ?cat= is active) ────────────
$active_cat_term = $active_cat ? get_term_by( 'slug', $active_cat, 'category' ) : null;

// ── Category IDs to scope queries to the parent term ─────────────────────────
$scope_cat_ids = [];
if ( $active_pt ) {
	if ( $active_cat_term ) {
		$scope_cat_ids = [ $active_cat_term->term_id ];
	} elseif ( $pt_child_cats ) {
		$scope_cat_ids = array_map( fn( $c ) => $c->term_id, $pt_child_cats );
	} elseif ( $child_terms && class_exists( 'AH_DB_Helper' ) ) {
		// Fallback: use content_taxonomies join
		$ct_table = AH_DB_Helper::table( 'content_taxonomies' );
		$ids      = implode( ',', array_map( fn( $t ) => (int) $t->id, $child_terms ) );
		$post_ids = $wpdb->get_col(
			"SELECT DISTINCT object_id FROM `{$ct_table}` WHERE object_type = 'post' AND taxonomy_id IN ({$ids})"
		) ?: [];
	}
}

// ── Featured posts ─────────────────────────────────────────────────────────────
$feat_query = [
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
	'meta_key'       => '_ah_is_featured',
	'meta_value'     => '1',
];
if ( $scope_cat_ids ) $feat_query['category__in'] = $scope_cat_ids;
elseif ( ! empty( $post_ids ) ) $feat_query['post__in'] = array_map( 'intval', $post_ids ) ?: [ 0 ];
$featured_posts = get_posts( $feat_query );

// ── Main posts grid ────────────────────────────────────────────────────────────
$wp_args = [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 12,
	'paged'               => $paged,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
];
if ( $scope_cat_ids ) {
	$wp_args['category__in'] = $scope_cat_ids;
} elseif ( ! empty( $post_ids ) ) {
	$wp_args['post__in'] = array_map( 'intval', $post_ids ) ?: [ 0 ];
} elseif ( $active_pt ) {
	$wp_args['post__in'] = [ 0 ]; // parent term found but no child cats mapped
}
$blog_query = new WP_Query( $wp_args );
$posts_arr  = [];
while ( $blog_query->have_posts() ) { $blog_query->the_post(); $posts_arr[] = get_post(); }
wp_reset_postdata();

// ── Sidebar: popular posts scoped to parent term ───────────────────────────────
$pop_query = [
	'posts_per_page' => 5,
	'post_status'    => 'publish',
	'meta_key'       => '_ah_is_popular',
	'meta_value'     => '1',
];
if ( $scope_cat_ids ) $pop_query['category__in'] = $scope_cat_ids;
$popular_posts = get_posts( $pop_query );

$site_stats     = function_exists( 'ah_get_site_stats' )     ? ah_get_site_stats()     : [];
$news_bar_items = function_exists( 'ah_get_news_bar_items' ) ? ah_get_news_bar_items() : [];

// ── Post data helper ───────────────────────────────────────────────────────────
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
?>

<div class="nif-portal-bg">
  <div class="container">
    <div class="nif-portal-wrap">

      <!-- ══ MAIN CONTENT ═══════════════════════════════════════════════════ -->
      <main class="nif-portal-main">

        <?php 
        $extra_heading ='';
        if(isset($active_pt->name)){
          $extra_heading = 'on '.$active_pt->name;
        }
        get_template_part( 'components/nif-background-imagecard',null,[
          'exta_heading'=> $extra_heading,
        ] ); 
        
        ?>

        <?php if ( $active_pt ) : ?>
        <!-- ── Parent term header + subcategory filter strip ─────────────── -->
        <div class="mi-pt-header">
          <div class="mi-pt-header__top-row">
            <div>
              <div class="mi-pt-header__eyebrow"><?php echo esc_html( TXT_TOPIC ); ?></div>
              <h1 class="mi-pt-header__title">
                <?php if ( ! empty( $active_pt->icon_emoji ) ) echo esc_html( $active_pt->icon_emoji ) . ' '; ?>
                <?php echo esc_html( $active_pt->name ); ?>
              </h1>
            </div>
            <a href="<?php echo esc_url( home_url( '/' . $active_slug . '/' ) ); ?>"
               class="mi-pt-header__full-link">
              <?php echo esc_html( TXT_FULL_TOPIC_PAGE ); ?>
              <span aria-hidden="true">→</span>
            </a>
          </div>
          <?php if ( ! empty( $pt_child_cats ) ) : ?>
          <div class="mi-chip-strip">
            <a href="<?php echo esc_url( $base_url ); ?>"
               class="mi-chip<?php echo ! $active_cat ? ' mi-chip--active' : ''; ?>">
              <?php echo esc_html( TXT_ALL ); ?>
            </a>
            <?php foreach ( $pt_child_cats as $fc ) : ?>
            <a href="<?php echo esc_url( add_query_arg( 'cat', $fc->slug, $base_url ) ); ?>"
               class="mi-chip<?php echo $active_cat === $fc->slug ? ' mi-chip--active' : ''; ?>">
              <?php echo esc_html( $fc->name ); ?>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <?php else : ?>
        <!-- ── Root /multiinfo/ - parent term chip strip ──────────────────── -->
        <?php if ( ! empty( $parent_terms ) ) : ?>
        <div class="mi-chip-strip mi-chip-strip--pts">
          <?php foreach ( $parent_terms as $_pt ) :
            $color = ! empty( $_pt->color ) ? $_pt->color : 'var(--accent)';
            $label = ( ! empty( $_pt->icon_emoji ) ? $_pt->icon_emoji . ' ' : '' ) . $_pt->name;
          ?>
          <a href="<?php echo esc_url( home_url( '/multiinfo/' . $_pt->slug . '/' ) ); ?>"
             class="mi-chip"
             style="--ptc:<?php echo esc_attr( $color ); ?>">
            <?php echo esc_html( $label ); ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ( $paged === 1 && ( empty( $featured_posts ) || ! empty( $featured_posts ) ) ) : ?>
        <!-- ── Featured section ──────────────────────────────────────────── -->
        <?php get_template_part( 'components/nif-news-hero', null, [
          'posts'    => $featured_posts,
          'eyebrow'  => $active_pt
            ? sprintf( TXT_FEATURED_S, $active_pt->name )
            : TXT_FEATURED_GUIDES,
          'see_all'  => $active_pt
            ? home_url( '/multiinfo/' . $active_slug . '/' )
            : home_url( '/multiinfo/' ),
          'cats'     => $pt_child_cats ?: get_categories( [ 'hide_empty' => true ] ),
          'news_cat' => $active_cat,
          'permalink'=> $base_url,
        ] ); ?>

        <!-- ── Guide tiles ───────────────────────────────────────────────── -->
        <?php get_template_part( 'components/nif-guide-tiles', null, [
          'posts'   => array_slice( $posts_arr, 0, 6 ),
          'eyebrow' => $active_pt
            ? sprintf( TXT_LATEST_IN_S, $active_pt->name )
            : TXT_LATEST_GUIDES,
          'see_all' => $active_pt
            ? home_url( '/multiinfo/' . $active_slug . '/' )
            : home_url( '/multiinfo/' ),
        ] ); ?>

        <!-- ── In brief / more posts ─────────────────────────────────────── -->
        <?php get_template_part( 'components/nif-brief-list', null, [
          'posts'     => array_slice( $posts_arr, 0, 6 ),
          'max_pages' => $blog_query->max_num_pages,
          'paged'     => $paged,
          'base_url'  => $base_url,
        ] ); ?>

        <?php else : ?>
        <!-- ── Paginated grid (pg > 1) ───────────────────────────────────── -->
        <section class="section" style="padding-top:28px">
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
                <span class="nif-badge"><?php echo esc_html( $d['cat']->name ); ?></span>
                <?php endif; ?>
                <h3 class="nif-grid-card__title">
                  <a href="<?php echo esc_url( $d['permalink'] ); ?>"><?php echo esc_html( get_the_title( $p->ID ) ); ?></a>
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
          <div class="nif-empty">
            <div class="nif-empty__icon">✍️</div>
            <h2 class="nif-empty__title"><?php echo esc_html( TXT_NOTHING_HERE_YET ); ?></h2>
            <p class="nif-empty__desc"><?php echo esc_html( TXT_WE_RE_WORKING_ON_GREAT_CONTENT_CHECK_BACK_SOON ); ?></p>
            <a href="<?php echo esc_url( home_url( '/multiinfo/' ) ); ?>" class="btn btn-outline" style="margin-top:20px">
              ← <?php echo esc_html( TXT_ALL_TOPICS ); ?>
            </a>
          </div>
          <?php endif; ?>

          <?php if ( $blog_query->max_num_pages > 1 ) :
            $pg_base = $active_cat ? add_query_arg( 'cat', $active_cat, $base_url ) : $base_url;
            $sep     = strpos( $pg_base, '?' ) !== false ? '&' : '?';
            $links   = paginate_links( [
              'base'      => $pg_base . $sep . 'pg=%#%',
              'format'    => '',
              'current'   => $paged,
              'total'     => $blog_query->max_num_pages,
              'prev_text' => '← Prev',
              'next_text' => 'Next →',
              'type'      => 'array',
            ] );
            if ( $links ) : ?>
          <nav class="pagination" aria-label="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_TXT_PAGE_NAVIGATION ); ?>" style="margin-top:48px">
            <ul class="pagination__list">
              <?php foreach ( $links as $link ) echo ( '<li class="pagination__item">'. $link . '</li>' ); ?>
            </ul>
          </nav>
          <?php endif; endif; ?>
        </section>
        <?php endif; ?>

      </main>

      <!-- ══ SIDEBAR ═══════════════════════════════════════════════════════ -->
      <aside class="nif-portal-sidebar" aria-label="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_TXT_TOPIC_INFORMATION ); ?>">
        <?php get_template_part( 'components/multiinfo-sidebar', null, [
          'active_pt'      => $active_pt,
          'active_slug'    => $active_slug,
          'active_cat'     => $active_cat,
          'pt_child_cats'  => $pt_child_cats,
          'parent_terms'   => $parent_terms,
          'popular_posts'  => $popular_posts,
          'site_stats'     => $site_stats,
          'news_bar_items' => $news_bar_items,
          'base_url'       => $base_url,
        ] ); ?>
      </aside>

    </div>
  </div>
</div>

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
