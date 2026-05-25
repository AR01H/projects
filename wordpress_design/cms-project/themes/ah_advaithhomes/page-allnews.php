<?php
/**
 * Template Name: All News
 *
 * Listing:  /allnews/
 * Detail:   /allnews/?item=ID
 */
defined( 'ABSPATH' ) || exit;

get_header();

$base_url = get_permalink();
$today    = current_time( 'Y-m-d' );
$item_id  = absint( $_GET['item'] ?? 0 );

// ══ SINGLE ITEM DETAIL VIEW ══════════════════════════════════════════════════
if ( $item_id && class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$nb_table   = AH_DB_Helper::table( 'news_bar_items' );
	$single     = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM `{$nb_table}` WHERE id = %d AND status = 'active' LIMIT 1",
		$item_id
	) );

	if ( $single ) {
		$s_title  = $single->text ?? '';
		$s_date   = ! empty( $single->start_date ) ? date_i18n( 'd M Y', strtotime( $single->start_date ) ) : '';
		$s_content = ! empty( $single->content ) ? $single->content : '';
		$s_thumb  = ! empty( $single->image_id )
			? ( wp_get_attachment_image_url( (int) $single->image_id, 'large' )
			  ?: wp_get_attachment_image_url( (int) $single->image_id, 'medium_large' ) )
			: '';
		$s_terms  = [];
		if ( class_exists( 'AH_Theme_Content_Taxonomy' ) ) {
			$_td     = AH_Theme_Content_Taxonomy::get_terms_for_items( [ $single ], 'news_bar_item' );
			$s_terms = $_td['item_terms'][ $single->id ] ?? [];
		}
		$s_cat = ! empty( $s_terms ) ? $s_terms[0]->name : 'News';
		?>

		<?php get_template_part( 'components/page-header', null, [
			'eyebrow'    => esc_html( $s_cat ),
			'title'      => '',
			'title_em'   => esc_html( $s_title ),
			'desc'       => $s_date,
			'breadcrumb' => [
				[ 'Home',     home_url( '/' ) ],
				[ 'All News', esc_url( $base_url ) ],
				[ esc_html( wp_trim_words( $s_title, 6, '…' ) ), '' ],
			],
		] ); ?>

		<article class="section" aria-label="<?php echo esc_attr( TXT_NEWS_ARTICLE ); ?>">
		  <div class="container">

		    <!-- Back link -->
		    <a href="<?php echo esc_url( $base_url ); ?>" class="news-single__back" style="display:inline-flex;align-items:center;gap:6px;font-size:.85rem;font-weight:600;color:var(--accent);text-decoration:none;margin-bottom:32px">
		      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
		      <?php echo esc_html( TXT_BACK_TO_ALL_NEWS ); ?>
		    </a>

			
		    <div class="news-single__meta" style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
				<span class="nif-tile-badge" style="font-size:.75rem"><?php echo esc_html( strtoupper( $s_cat ) ); ?></span>
				<?php if ( $s_date ) : ?>
					<span style="font-size:.82rem;color:var(--text-secondary)"><?php echo esc_html( $s_date ); ?></span>
					<?php endif; ?>
				</div>
				
				<div style="display:flex;flex-wrap:wrap;justify-content: space-between;">
					<h1 class="news-single__title" style="font-family:var(--font-display);font-size:clamp(1.5rem,3vw,2.25rem);font-weight:700;color:var(--text-primary);margin-bottom:24px;line-height:1.25">
						<?php echo esc_html( $s_title ); ?>
					</h1>
					<?php if ( $s_thumb ) : ?>
					<div class="news-single__hero" style="margin-bottom:32px;border-radius:var(--r-lg);overflow:hidden;aspect-ratio:16/7;max-width:400px">
					  <img src="<?php echo esc_url( $s_thumb ); ?>"
						alt="<?php echo ( 'News' ); ?>"
						   style="width:100%;height:100%;object-fit:cover"
						   loading="eager" decoding="async">
					</div>
					<?php endif; ?>
				</div>

		    <?php if ( $s_content ) : ?>
		    <div class="news-single__body prose" style="color:var(--text-secondary);font-size:1rem;line-height:1.8;">
		      <?php echo wp_kses_post( wpautop( $s_content ) ); ?>
		    </div>
		    <?php endif; ?>
		  </div>
		</article>

		<?php
	} else {
		// Item not found - redirect to listing
		wp_safe_redirect( $base_url );
		exit;
	}
}else{


// ══ LISTING VIEW ════════════════════════════════════════════════════════════
$per_page   = 12;
$active_cat = sanitize_text_field( $_GET['category'] ?? '' );
$paged      = max( 1, absint( $_GET['pg'] ?? 1 ) );
$news_items  = [];
$total_items = 0;
$max_pages   = 1;

if ( class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$nb_table = AH_DB_Helper::table( 'news_bar_items' );
	$offset   = ( $paged - 1 ) * $per_page;

	$where = $wpdb->prepare(
		"WHERE status = 'active'
		   AND (start_date IS NULL OR start_date <= %s)
		   AND (end_date   IS NULL OR end_date   >= %s)",
		$today, $today
	);

	$cat_term_id = 0;
	if ( $active_cat ) {
		$_tax_table = AH_DB_Helper::table( 'taxonomies' );
		$_tax_row   = $wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM `{$_tax_table}` WHERE slug = %s AND status = 1 LIMIT 1",
			$active_cat
		) );
		if ( $_tax_row ) $cat_term_id = (int) $_tax_row->id;
	}

	if ( $cat_term_id ) {
		$ct_table    = AH_DB_Helper::table( 'content_taxonomies' );
		$total_items = (int) $wpdb->get_var(
			"SELECT COUNT(DISTINCT n.id) FROM `{$nb_table}` n
			 INNER JOIN `{$ct_table}` ct ON ct.object_id = n.id AND ct.object_type = 'news_bar_item'
			 {$where} AND ct.taxonomy_id = {$cat_term_id}"
		);
		$news_items  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT n.* FROM `{$nb_table}` n
				 INNER JOIN `{$ct_table}` ct ON ct.object_id = n.id AND ct.object_type = 'news_bar_item'
				 {$where} AND ct.taxonomy_id = {$cat_term_id}
				 ORDER BY COALESCE(n.start_date, '1970-01-01') DESC, n.id DESC
				 LIMIT %d OFFSET %d",
				$per_page, $offset
			)
		) ?: [];
	} else {
		$total_items = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM `{$nb_table}` {$where}"
		);
		$news_items  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$nb_table}` {$where}
				 ORDER BY COALESCE(start_date, '1970-01-01') DESC, id DESC
				 LIMIT %d OFFSET %d",
				$per_page, $offset
			)
		) ?: [];
	}

	$max_pages = $total_items > 0 ? (int) ceil( $total_items / $per_page ) : 1;
}

$item_terms = [];
if ( ! empty( $news_items ) && class_exists( 'AH_Theme_Content_Taxonomy' ) ) {
	$_tax_data  = AH_Theme_Content_Taxonomy::get_terms_for_items( $news_items, 'news_bar_item' );
	$item_terms = $_tax_data['item_terms'] ?? [];
}

$unique_terms = [];
if ( class_exists( 'AH_Theme_Content_Taxonomy' ) && class_exists( 'AH_DB_Helper' ) ) {
	$_nb2       = AH_DB_Helper::table( 'news_bar_items' );
	$_all_items = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id FROM `{$_nb2}` WHERE status = 'active'
			   AND (start_date IS NULL OR start_date <= %s)
			   AND (end_date   IS NULL OR end_date   >= %s)",
			$today, $today
		)
	) ?: [];
	if ( ! empty( $_all_items ) ) {
		$unique_terms = AH_Theme_Content_Taxonomy::get_unique_terms( $_all_items, 'news_bar_item' );
	}
}
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => TXT_STAY_INFORMED,
	'title'      => TXT_ALL,
	'title_em'   => TXT_NEWS,
	'desc'       => TXT_MARKET_UPDATES_PROPERTY_INSIGHTS_AND_BUYING_TIPS_E,
	'breadcrumb' => [
		[ 'Home', home_url( '/' ) ],
		[ 'All News', '' ],
	],
] ); ?>

<!-- ── Category filter ───────────────────────────────────────────────────── -->
<?php if ( ! empty( $unique_terms ) ) : ?>
<div style="border-bottom:1px solid var(--border);background:var(--bg-alt)">
  <div class="container" style="padding-top:4px;padding-bottom:4px">
    <div class="filter-tabs" role="tablist" aria-label="<?php echo esc_attr( TXT_FILTER_BY_CATEGORY ); ?>">
      <a href="<?php echo esc_url( $base_url ); ?>"
         class="filter-tab<?php echo ! $active_cat ? ' filter-tab--active' : ''; ?>"
         role="tab" aria-selected="<?php echo ! $active_cat ? 'true' : 'false'; ?>">
        <?php echo esc_html( TXT_ALL ); ?>
      </a>
      <?php foreach ( $unique_terms as $term ) :
        $is_active = ( $active_cat === $term->slug );
      ?>
      <a href="<?php echo esc_url( add_query_arg( 'category', $term->slug, $base_url ) ); ?>"
         class="filter-tab<?php echo $is_active ? ' filter-tab--active' : ''; ?>"
         role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
        <?php echo esc_html( $term->name ); ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── News-card grid ─────────────────────────────────────────────────────── -->
<section class="section" aria-label="<?php echo esc_attr( TXT_NEWS_ARTICLE ); ?>">
  <div class="container">

    <?php if ( ! empty( $news_items ) ) : ?>
    <div class="post-grid">
      <?php foreach ( $news_items as $idx => $item ) :
        $terms       = $item_terms[ $item->id ] ?? [];
        $cat_label   = ! empty( $terms ) ? $terms[0]->name : 'News';
        // External link goes direct; empty link uses internal detail page
        $item_link   = ( ! empty( $item->link_url ) && filter_var( $item->link_url, FILTER_VALIDATE_URL ) )
          ? $item->link_url
          : esc_url( add_query_arg( 'item', $item->id, $base_url ) );
        $ext_target  = ( ! empty( $item->link_url ) && filter_var( $item->link_url, FILTER_VALIDATE_URL ) && ! empty( $item->link_target ) )
          ? 'target="' . esc_attr( $item->link_target ) . '"'
          : '';
        $item_title  = $item->text ?? '';
        $excerpt     = ! empty( $item->content )
          ? wp_trim_words( wp_strip_all_tags( $item->content ), 18, '…' )
          : '';
        $thumb_url   = ! empty( $item->image_id )
          ? ( wp_get_attachment_image_url( (int) $item->image_id, 'ah-card' )
            ?: wp_get_attachment_image_url( (int) $item->image_id, 'medium_large' ) )
          : '';
        $date_str    = ! empty( $item->start_date )
          ? date_i18n( 'd M Y', strtotime( $item->start_date ) )
          : '';
        $delay       = min( $idx % 3, 2 ) * 80;
      ?>
      <article class="blog-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
        <div class="blog-card__img-wrap">

          <a href="<?php echo esc_url( $item_link ); ?>" <?php echo $ext_target; ?>
             class="blog-card__img-link" tabindex="-1" aria-hidden="true">
            <?php if ( $thumb_url ) : ?>
              <img src="<?php echo esc_url( $thumb_url ); ?>"
                   alt="<?php echo esc_attr( $item_title ); ?>"
                   class="blog-card__img" loading="lazy" decoding="async">
            <?php else : ?>
              <div class="blog-card__img-placeholder">📰</div>
            <?php endif; ?>
          </a>

          <div class="blog-card__overlay">
            <div class="blog-card__badges">
              <span class="blog-card__cat"><?php echo esc_html( $cat_label ); ?></span>
              <?php if ( $date_str ) : ?>
              <span class="blog-card__read-time"><?php echo esc_html( $date_str ); ?></span>
              <?php endif; ?>
            </div>
            <h2 class="blog-card__title">
              <a href="<?php echo esc_url( $item_link ); ?>" <?php echo $ext_target; ?>>
                <?php echo esc_html( $item_title ); ?>
              </a>
            </h2>
            <?php if ( $excerpt ) : ?>
            <div class="blog-card__desc-wrap">
              <div>
                <p class="blog-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
                <a href="<?php echo esc_url( $item_link ); ?>" <?php echo $ext_target; ?>
                   class="blog-card__read-btn">
                  <?php echo esc_html( TXT_READ_MORE ); ?> <span aria-hidden="true">→</span>
                </a>
              </div>
            </div>
            <?php endif; ?>
          </div>

        </div>
      </article>
      <?php endforeach; ?>
    </div><!-- /.post-grid -->

    <!-- ── Pagination ──────────────────────────────────────────── -->
    <?php if ( $max_pages > 1 ) :
      $pg_base = $active_cat
        ? add_query_arg( 'category', $active_cat, $base_url )
        : $base_url;
      $sep   = strpos( $pg_base, '?' ) !== false ? '&' : '?';
      $links = paginate_links( [
        'base'      => $pg_base . $sep . 'pg=%#%',
        'format'    => '',
        'current'   => $paged,
        'total'     => $max_pages,
        'prev_text' => '← Prev',
        'next_text' => 'Next →',
        'type'      => 'array',
      ] );
      if ( $links ) :
    ?>
    <nav class="pagination" aria-label="<?php echo esc_attr( TXT_NEWS_NAVIGATION ); ?>" style="margin-top:48px">
      <ul class="pagination__list">
        <?php foreach ( $links as $link ) echo ( '<li class="pagination__item">'. $link . '</li>' ); ?>
      </ul>
    </nav>
    <?php endif; endif; ?>

    <?php else : ?>
    <div class="text-center" style="padding:80px 24px">
      <div style="font-size:3rem;margin-bottom:12px">📰</div>
      <h2 style="font-size:1.25rem;margin-bottom:8px"><?php echo esc_html( TXT_NO_NEWS_YET ); ?></h2>
      <p style="color:var(--text-secondary)">
        <?php echo $active_cat
          ? esc_html( TXT_NOTHING_IN_THIS_CATEGORY_YET )
          : esc_html( TXT_CHECK_BACK_SOON_FOR_UPDATES ); ?>
      </p>
      <?php if ( $active_cat ) : ?>
      <a href="<?php echo esc_url( $base_url ); ?>" class="btn btn-outline" style="margin-top:16px">
        <?php echo esc_html( TXT_VIEW_ALL ); ?>
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php }

get_template_part( 'components/cta-section', null, [] );
get_template_part( 'components/scroll-to-top' );
get_footer(); ?>
