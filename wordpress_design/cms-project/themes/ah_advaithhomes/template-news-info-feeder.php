<?php

get_header();

$page_url           = get_permalink();
$active_cat         = sanitize_text_field( $_GET['category']    ?? '' );
$active_parent_term = sanitize_title(      $_GET['parent_term'] ?? '' );
$news_cat           = sanitize_text_field( $_GET['news_cat']    ?? '' );
$paged              = max( 1, absint( $_GET['pg'] ?? 1 ) );

// ── Parent Terms (CMS taxonomy groups - shown as colored filter tabs) ─────────
$parent_terms = [];
if ( class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$pt_table     = AH_DB_Helper::table( 'taxonomy_parent_terms' );
	$parent_terms = $wpdb->get_results(
		"SELECT id, name, slug, color FROM {$pt_table} WHERE status = 1 ORDER BY name ASC"
	) ?: [];
}
$use_parent_terms = count( $parent_terms ) >= 2;

// ── Categories (fallback for sidebar / news-hero when parent terms not used) ──
$wp_cats = get_categories( [ 'hide_empty' => true ] );

// ── News posts (hero + side cards) - sticky-aware, separate from guides ───────
$news_query_args = [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 4,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => false,
];
if ( $news_cat ) {
	$news_term = get_term_by( 'slug', $news_cat, 'category' );
	if ( $news_term ) $news_query_args['cat'] = $news_term->term_id;
}
$news_query = new WP_Query( $news_query_args );
$news_posts = [];
while ( $news_query->have_posts() ) {
	$news_query->the_post();
	$news_posts[] = get_post();
}
wp_reset_postdata();

// ── Resolve active parent term + child terms (shared by WP_Query and sidebar) ─
$pt_obj        = null;   // active parent term object
$child_terms   = [];     // CMS taxonomy term objects (id, slug)
$pt_child_cats = [];     // WP_Term objects matching child term slugs (sidebar chips)
if ( $use_parent_terms && $active_parent_term && class_exists( 'AH_DB_Helper' ) ) {
	foreach ( $parent_terms as $_pt ) {
		if ( $_pt->slug === $active_parent_term ) { $pt_obj = $_pt; break; }
	}
	if ( $pt_obj ) {
		$_tax_table  = AH_DB_Helper::table( 'taxonomies' );
		$child_terms = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, slug FROM {$_tax_table} WHERE parent_term_id = %d AND status = 1",
			(int) $pt_obj->id
		) ) ?: [];
		foreach ( $child_terms as $_ct ) {
			$_wc = get_term_by( 'slug', $_ct->slug, 'category' );
			if ( $_wc ) $pt_child_cats[] = $_wc;
		}
	}
}

// ── All posts (guide tiles + brief list) - sticky posts shown once, not at top
$wp_args = [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 12,
	'paged'               => $paged,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
];
if ( $use_parent_terms && $pt_obj ) {
	if ( $active_cat ) {
		// Specific child category selected - narrow to that WP category only
		$_specific = get_term_by( 'slug', $active_cat, 'category' );
		if ( $_specific ) {
			$wp_args['cat'] = $_specific->term_id;
		} else {
			$wp_args['post__in'] = [ 0 ];
		}
	} else {
		// Whole parent term - show all posts across child categories
		$post_ids = [];
		if ( $child_terms && class_exists( 'AH_DB_Helper' ) ) {
			$_ct_table = AH_DB_Helper::table( 'content_taxonomies' );
			$_ids      = implode( ',', array_map( fn( $t ) => (int) $t->id, $child_terms ) );
			$post_ids  = $wpdb->get_col(
				"SELECT DISTINCT object_id FROM {$_ct_table} WHERE object_type = 'post' AND taxonomy_id IN ({$_ids})"
			) ?: [];
		}
		if ( $post_ids ) {
			$wp_args['post__in'] = array_map( 'intval', $post_ids );
		} elseif ( $pt_child_cats ) {
			$wp_args['category__in'] = array_map( fn( $c ) => $c->term_id, $pt_child_cats );
		} else {
			$wp_args['post__in'] = [ 0 ];
		}
	}
} elseif ( $use_parent_terms && $active_parent_term ) {
	$wp_args['post__in'] = [ 0 ]; // slug given but no matching DB term
} elseif ( $active_cat ) {
	$term = get_term_by( 'slug', $active_cat, 'category' );
	if ( $term ) $wp_args['cat'] = $term->term_id;
}
$blog_query = new WP_Query( $wp_args );
$posts_arr  = [];
while ( $blog_query->have_posts() ) {
	$blog_query->the_post();
	$posts_arr[] = get_post();
}
wp_reset_postdata();

// ── Sidebar data ──────────────────────────────────────────────────────────────
$site_stats     = ah_get_site_stats();
$news_bar_items = function_exists( 'ah_get_news_bar_items' ) ? ah_get_news_bar_items() : [];
$popular_posts  = get_posts( [
	'posts_per_page' => 5,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
	'meta_key'       => '_ah_is_popular',
	'meta_value'     => '1',
] );

// ── Featured posts (top "Featured Guides" section) ───────────────────────────
$featured_posts = get_posts( [
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
	'meta_key'       => '_ah_is_featured',
	'meta_value'     => '1',
] );

// ── Post data helper (shared across all NIF components in this request) ───────
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
      <!-- ══ MAIN CONTENT ════════════════════════════════════════════════════ -->
      <main class="nif-portal-main">
         
         <?php
          get_template_part( 'components/nif-background-imagecard', null, [] );
        ?>

        <?php if ( ! $active_cat && ! $active_parent_term && $paged === 1 ) {
          // ── PORTAL HOME LAYOUT ─────────────────────────────────────────────

          // Featured Guides: big hero card + 3 side cards - only _ah_is_featured posts
          get_template_part( 'components/nif-news-hero', null, [
            'posts'     => $featured_posts,
            'eyebrow'   => TXT_FEATURED_GUIDES,
            'see_all'   => home_url( '/guides/' ),
            'cats'      => $wp_cats,
            'news_cat'  => $news_cat,
            'permalink' => $page_url,
          ] );

          // Latest Guides: dark 3-column tiles
          get_template_part( 'components/nif-guide-tiles', null, [
            'posts'   => array_slice( $posts_arr, 0, 6 ),
            'eyebrow' => TXT_LATEST_GUIDES,
            'see_all' => home_url( '/guides/' ),
          ] );

          // Latest News: hero + side cards - links to /allnews/ for full listing
          if ( ! empty( $news_posts ) ) {
            get_template_part( 'components/nif-news-hero', null, [
              'posts'     => $news_posts,
              'eyebrow'   => TXT_LATEST_NEWS,
              'see_all'   => home_url( '/allnews/' ),
              'cats'      => $wp_cats,
              'news_cat'  => $news_cat,
              'permalink' => $page_url,
            ] );
          }

          // In Brief: remaining posts as a horizontal list
          get_template_part( 'components/nif-brief-list', null, [
            'posts'     => array_slice( $posts_arr, 0, 6 ),
            'max_pages' => $blog_query->max_num_pages,
            'paged'     => $paged,
            'base_url'  => $page_url,
          ] );

        } else {
          // ── FILTERED / PAGINATED GRID VIEW ────────────────────────────────
        ?>
        <section class="section" style="padding-top:28px" aria-label="<?php echo esc_attr( TXT_ARTICLES ); ?>">

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
            <p class="nif-empty__desc">
              <?php echo $active_cat
                ? esc_html( TXT_NO_POSTS_IN_THIS_TOPIC_YET_TRY_ANOTHER_CATEGORY )
                : esc_html( TXT_WE_RE_WORKING_ON_GREAT_CONTENT_CHECK_BACK_SHORTLY ); ?>
            </p>
            <?php if ( $active_cat || $active_parent_term ) : ?>
              <a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-outline" style="margin-top:20px">
                <?php echo esc_html( TXT_VIEW_ALL_TOPICS ); ?>
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Pagination - ?pg=X avoids WordPress redirect_canonical intercept -->
          <?php if ( $blog_query->max_num_pages > 1 ) :
            $pg_base = $active_parent_term
              ? add_query_arg( 'parent_term', $active_parent_term, $page_url )
              : ( $active_cat ? add_query_arg( 'category', $active_cat, $page_url ) : $page_url );
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
          <nav class="pagination" aria-label="<?php echo esc_attr( TXT_PAGE_NAVIGATION ); ?>" style="margin-top:48px">
            <ul class="pagination__list">
              <?php foreach ( $links as $link ) echo ( '<li class="pagination__item">'. $link . '</li>' ); ?>
            </ul>
          </nav>
          <?php endif; endif; ?>

        </section>
        <?php } ?>

      </main><!-- /.nif-portal-main -->

      <!-- ══ SIDEBAR ════════════════════════════════════════════════════════ -->
      <aside class="nif-portal-sidebar" aria-label="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_TXT_MARKET_INFORMATION_AND_TOOLS ); ?>">
        <?php get_template_part( 'components/nif-sidebar', null, [
          'site_stats'         => $site_stats,
          'news_bar_items'     => $news_bar_items,
          'popular_posts'      => $popular_posts,
          'cats'               => $wp_cats,
          'active_cat'         => $active_cat,
          'permalink'          => $page_url,
          'parent_terms'       => $parent_terms,
          'active_parent_term' => $active_parent_term,
          'active_pt_obj'      => $pt_obj,
          'pt_child_cats'      => $pt_child_cats,
        ] ); ?>
      </aside>

    </div><!-- /.nif-portal-wrap -->
  </div><!-- /.container -->
</div><!-- /.nif-portal-bg -->


<?php get_template_part( 'components/cta-section' ); ?>

<?php get_footer(); ?>
