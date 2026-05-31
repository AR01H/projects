<?php
/**
 * Template Name: Guides Archive
 */
get_header();

$categories     = ah_get_guide_categories();
$_raw_cat       = sanitize_text_field( $_GET['category'] ?? '' );
$active_cat     = sanitize_title( strtok( $_raw_cat, '?' ) );
$active_pt_slug = sanitize_title( $_GET['parent_term'] ?? '' );
$paged          = max( 1, absint( $_GET['pg'] ?? get_query_var( 'paged', 1 ) ) );
$base_url       = get_permalink();

// ── Resolve parent term filter ────────────────────────────────────────────────
$active_pt        = null;
$pt_child_cat_ids = [];
$_child_slugs     = [];
if ( $active_pt_slug && class_exists( 'AH_Taxonomy_Parent_Model' ) ) {
	global $wpdb;
	$_ptm = new AH_Taxonomy_Parent_Model();
	foreach ( $_ptm->get_all_active() as $_pt ) {
		if ( ( $_pt->slug ?? '' ) === $active_pt_slug ) { $active_pt = $_pt; break; }
	}
	if ( $active_pt ) {
		$_tax_table   = AH_DB_Helper::table( 'taxonomies' );
		$_child_slugs = $wpdb->get_col( $wpdb->prepare(
			"SELECT slug FROM `{$_tax_table}` WHERE parent_term_id = %d AND status = 1",
			(int) $active_pt->id
		) ) ?: [];
		foreach ( $_child_slugs as $_cs ) {
			$_wc = get_term_by( 'slug', $_cs, 'category' );
			if ( $_wc ) $pt_child_cat_ids[] = $_wc->term_id;
		}
	}
}

// Categories to display — filtered to parent term's children when ?parent_term= is active
$display_cats = ( $active_pt_slug && ! empty( $_child_slugs ) )
	? array_values( array_filter( $categories, function( $c ) use ( $_child_slugs ) {
		$c = is_object( $c ) ? (array) $c : $c;
		return in_array( $c['slug'] ?? '', $_child_slugs, true );
	} ) )
	: $categories;

// Active category object (for ?category= filter)
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

// ── Sidebar: all parent terms + their children from DB ───────────────────────
$sidebar_pts = [];
if ( class_exists( 'AH_Taxonomy_Parent_Model' ) && class_exists( 'AH_DB_Helper' ) ) {
	if ( ! isset( $wpdb ) ) global $wpdb;
	$_ptm_sb   = new AH_Taxonomy_Parent_Model();
	$_tax_sb   = AH_DB_Helper::table( 'taxonomies' );
	foreach ( $_ptm_sb->get_all_active() as $_sb_pt ) {
		$_sb_children = $wpdb->get_results( $wpdb->prepare(
			"SELECT slug, name FROM `{$_tax_sb}` WHERE parent_term_id = %d AND status = 1 ORDER BY name ASC",
			(int) $_sb_pt->id
		) ) ?: [];
		$sidebar_pts[] = [ 'pt' => $_sb_pt, 'children' => $_sb_children ];
	}
}

// Pick a meaningful icon for a guide topic. Uses the term's own icon_emoji
// when set, otherwise matches keywords in the name/slug to a sensible default.
if ( ! function_exists( 'ah_guide_topic_icon' ) ) {
	function ah_guide_topic_icon( $name = '', $slug = '', $explicit = '' ) {
		$explicit = trim( (string) $explicit );
		// Respect an explicit icon only if it's a real emoji/symbol, not a plain
		// letter or word (some terms store just the first initial, e.g. "B").
		if ( $explicit !== '' && preg_match( '/[^\x00-\x7F]/u', $explicit ) ) return $explicit;
		$h = strtolower( $name . ' ' . $slug );
		$map = [
			'first-time'  => '🔑', 'first time' => '🔑',
			'mortgage'    => '🏦', 'finance' => '💷', 'remortgage' => '🏦',
			'calculat'    => '🧮', 'stamp duty' => '🧾',
			'legal'       => '⚖️', 'conveyanc' => '⚖️',
			'invest'      => '📈', 'btl' => '📈', 'buy-to-let' => '📈',
			'market'      => '📊', 'news' => '📰',
			'reloc'       => '✈️', 'international' => '🌍',
			'luxury'      => '💎',
			'tip'         => '💡', 'advice' => '💡',
			'sell'        => '🏷️',
			'buying'         => '🏡', 'home' => '🏡', 'purchase' => '🏡',
			'rent'        => '🔑', 'landlord' => '🏘️',
		];
		foreach ( $map as $needle => $icon ) {
			if ( strpos( $h, $needle ) !== false ) return $icon;
		}
		return '📂';
	}
}

// Define early so subsequent queries can use it
$is_filtered = $active_cat || $active_pt_slug;

// Build category-slug → parent-term lookup (from sidebar data already fetched)
$cat_pt_map = [];
foreach ( $sidebar_pts as $_sb ) {
	foreach ( $_sb['children'] as $_sbc ) {
		$cat_pt_map[ $_sbc->slug ] = $_sb['pt'];
	}
}

// Latest guides (for default home view)
$latest_guides = ! $is_filtered ? get_posts( [
	'posts_per_page' => 8,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
] ) : [];

// Popular guides (for default home view)
$popular_guides = ! $is_filtered ? get_posts( [
	'posts_per_page' => 4,
	'post_status'    => 'publish',
	'meta_key'       => '_ah_is_popular',
	'meta_value'     => '1',
	'orderby'        => 'date',
	'order'          => 'DESC',
] ) : [];

// ── Query ─────────────────────────────────────────────────────────────────────
// Runs when either ?category= or ?parent_term= is present
$guides_query = null;
if ( $is_filtered ) {
	$query_args = [
		'post_type'      => 'post',
		'posts_per_page' => 12,
		'paged'          => $paged,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	];
	if ( $active_cat ) {
		$term = get_term_by( 'slug', $active_cat, 'category' );
		if ( $term ) $query_args['cat'] = $term->term_id;
	} elseif ( $pt_child_cat_ids ) {
		$query_args['category__in'] = $pt_child_cat_ids;
	} else {
		$query_args['post__in'] = [ 0 ]; // parent term found but no child cats
	}
	$guides_query = new WP_Query( $query_args );
}
?>

<?php
// ── Dynamic header based on active filter ────────────────────────────────────
if ( $active_pt && ! $active_cat ) {
	// Filtered by parent term
	$_ph_eyebrow  = 'Guides';
	$_ph_title    = esc_html( $active_pt->name );
	$_ph_title_em = '';
	$_ph_desc     = ! empty( $active_pt->description ) ? esc_html( $active_pt->description ) : '';
} elseif ( $active_cat_obj ) {
	// Filtered by category
	$_ph_eyebrow  = ! empty( $active_pt ) ? esc_html( $active_pt->name ) : 'Guides';
	$_ph_title    = esc_html( $active_cat_obj['title'] ?? $active_cat );
	$_ph_title_em = '';
	$_ph_desc     = ! empty( $active_cat_obj['desc'] ) ? esc_html( $active_cat_obj['desc'] ) : '';
} else {
	// Default guides home
	$_ph_eyebrow  = '';
	$_ph_title    = 'The Complete';
	$_ph_title_em = 'Library';
	$_ph_desc     = 'Guides written by buyer\'s agents - not marketers. Everything you need to buy with confidence, from mortgage basics to completion day.';
}
get_template_part( 'components/page-header', null, [
	'eyebrow'    => $_ph_eyebrow,
	'title'      => $_ph_title,
	'title_em'   => $_ph_title_em,
	'desc'       => $_ph_desc,
	'breadcrumb' => array_filter( [
		[ 'Home',   home_url( '/' ) ],
		[ 'Guides', $is_filtered ? esc_url( $base_url ) : '' ],
		$active_cat_obj ? [ esc_html( $active_cat_obj['title'] ?? $active_cat ), '' ] : null,
		$active_pt && ! $active_cat ? [ esc_html( $active_pt->name ?? $active_pt_slug ), '' ] : null,
	] ),
] ); ?>

<div class="gc-portal-bg">
<div class="container">
<div class="gc-portal-layout<?php echo ! $is_filtered ? ' gc-portal-layout--full' : ''; ?>">

<!-- ── MAIN ─────────────────────────────────────────────── -->
<main class="gc-portal-main">

<?php if ( $is_filtered && $guides_query ) : ?>

  <?php
    $cat_img_url  = ! empty( $active_cat_obj['image_id'] ) ? wp_get_attachment_image_url( $active_cat_obj['image_id'], 'medium_large' ) : '';
    $banner_title = $active_cat_obj['title'] ?? ( $active_pt ? $active_pt->name : $active_cat );
    $banner_icon  = ! empty( $active_cat_obj['icon_emoji'] )
        ? $active_cat_obj['icon_emoji']
        : ( $active_pt
            ? ah_guide_topic_icon( $active_pt->name ?? '', $active_pt->slug ?? '', $active_pt->icon_emoji ?? '' )
            : ah_guide_topic_icon( $banner_title, $active_cat, '' ) );
    $banner_desc  = $active_cat_obj['desc'] ?? ( $active_pt ? ( $active_pt->description ?? '' ) : '' );
    $banner_count = $active_cat_obj['count'] ?? ( $guides_query ? $guides_query->found_posts : 0 );
  ?>
  <div class="gc-cat-banner" style="<?php if($cat_img_url) echo '--gc-cat-img:url(' . esc_url($cat_img_url) . ')'; ?>">
    <div class="gc-cat-banner__left">
      <a href="<?php echo esc_url( $base_url ); ?>" class="gc-cat-banner__back">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <?php echo esc_html( TXT_ALL_TOPICS ); ?>
      </a>
      <div class="gc-cat-banner__icon"><?php echo esc_html( $banner_icon ); ?></div>
      <?php if ( $banner_desc ) : ?><p class="gc-cat-banner__desc"><?php echo esc_html( $banner_desc ); ?></p><?php endif; ?>
      <?php if ( $banner_count ) : ?><span class="gc-cat-banner__count"><?php echo (int) $banner_count; ?> <?php echo esc_html( TXT_GUIDES ); ?></span><?php endif; ?>
    </div>
    <?php if ( $cat_img_url ) : ?>
    <div class="gc-cat-banner__img-wrap"><img src="<?php echo esc_url( $cat_img_url ); ?>" alt="<?php echo esc_attr( $active_cat_obj['title'] ?? '' ); ?>" class="gc-cat-banner__img"></div>
    <?php endif; ?>
  </div>

  <?php if ( $guides_query->have_posts() ) : ?>
  <div class="post-grid" style="margin-top:28px">
    <?php while ( $guides_query->have_posts() ) : $guides_query->the_post();
      $cats = get_the_category(); $cat0 = $cats ? $cats[0] : null;
      $cat_name = $cat0 ? $cat0->name : ''; $cat_slug = $cat0 ? $cat0->slug : '';
    ?>
    <?php $gc_pt = $cat_slug ? ( $cat_pt_map[ $cat_slug ] ?? null ) : null; ?>
    <a href="<?php the_permalink(); ?>" class="gc" data-cat="<?php echo esc_attr( $cat_slug ); ?>" data-aos="fade-up">
      <div class="gc__img-wrap">
        <?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'ah-card', [ 'class' => 'gc__img' ] );
        else : ?><div class="gc__img gc__img--fallback">📖</div><?php endif; ?>
        <?php if ( $cat_name ) : ?><span class="gc__cat"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
      </div>
      <div class="gc__body">
        <?php if ( $gc_pt ) : ?>
        <span class="gc__pt-badge" style="--ptc:<?php echo esc_attr( $gc_pt->color ?? 'var(--accent)' ); ?>">
          <?php echo esc_html( $gc_pt->name ); ?>
        </span>
        <?php endif; ?>
        <div class="gc__meta"><span class="gc__read-time">⏱ <?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span></div>
        <h2 class="gc__title"><?php the_title(); ?></h2>
        <?php $excerpt = get_the_excerpt(); if ( $excerpt ) : ?><p class="gc__excerpt"><?php echo wp_trim_words( $excerpt, 18, '…' ); ?></p><?php endif; ?>
        <span class="gc__btn">Read Guide <span class="gc__arrow">→</span></span>
      </div>
    </a>
    <?php endwhile; wp_reset_postdata(); ?>
  </div>

  <?php if ( $guides_query->max_num_pages > 1 ) :
    $links = paginate_links( [ 'base' => add_query_arg( 'category', $active_cat, $base_url ) . '&pg=%#%', 'format' => '', 'current' => $paged, 'total' => $guides_query->max_num_pages, 'prev_text' => '← Prev', 'next_text' => 'Next →', 'type' => 'array' ] );
    if ( $links ) : ?>
  <nav class="pagination" style="margin-top:40px"><ul class="pagination__list"><?php foreach ( $links as $l ) echo '<li class="pagination__item">' . $l . '</li>'; ?></ul></nav>
  <?php endif; endif; ?>

  <?php else : ?>
  <div class="text-center" style="padding:48px 0">
    <div style="font-size:3rem;margin-bottom:16px">📚</div>
    <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">No guides in this topic yet</h2>
    <p style="color:var(--text-secondary);margin-bottom:24px">Check back soon.</p>
    <a href="<?php echo esc_url( $base_url ); ?>" class="btn btn-outline">← All Topics</a>
  </div>
  <?php endif; ?>

  <?php if ( $display_cats ) : ?>
  <div style="margin-top:48px">
    <div class="section__header"><span class="section__eyebrow">Browse by Topic</span>
      <h2 class="section__title"><?php echo $active_pt_slug ? esc_html( $active_pt->name ?? 'Topics' ) . ' Topics' : 'Explore More Topics'; ?></h2>
    </div>
    <div class="gcat-grid">
      <?php foreach ( $display_cats as $i => $cat ) :
        $cat = is_object( $cat ) ? (array) $cat : $cat;
        get_template_part( 'components/cards/guide-category-card', null, [ 'cat' => $cat, 'index' => $i ] );
      endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

<?php else : ?>

  <!-- § A  Browse by Parent Term -->
  <?php if ( $sidebar_pts ) : ?>
  <div class="gc-home-section">
    <div class="gc-home-section__head">
      <span class="gc-home-section__eyebrow">Explore by Topic</span>
      <h2 class="gc-home-section__title">What Are You Looking For?</h2>
    </div>
    <div class="gc-pt-grid">
      <?php foreach ( $sidebar_pts as $sb ) :
        $sb_pt    = $sb['pt'];
        $sb_count = count( $sb['children'] );
        $sb_color = ! empty( $sb_pt->color ) ? $sb_pt->color : 'var(--accent)';
      ?>
      <a href="<?php echo esc_url( home_url( '/guides/?parent_term=' . urlencode( $sb_pt->slug ) ) ); ?>"
         class="gc-pt-card" style="--ptc:<?php echo esc_attr( $sb_color ); ?>">
        <span class="gc-pt-card__icon"><?php echo esc_html( ah_guide_topic_icon( $sb_pt->name ?? '', $sb_pt->slug ?? '', $sb_pt->icon_emoji ?? '' ) ); ?></span>
        <span class="gc-pt-card__name"><?php echo esc_html( $sb_pt->name ); ?></span>
        <?php if ( $sb_count ) : ?>
        <span class="gc-pt-card__meta"><?php echo $sb_count; ?> topics</span>
        <?php endif; ?>
        <span class="gc-pt-card__arrow">Browse →</span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- § B  Browse by Sub-category -->
  <?php if ( $display_cats ) : ?>
  <div class="gc-home-section">
    <div class="gc-home-section__head">
      <span class="gc-home-section__eyebrow">All Categories</span>
      <h2 class="gc-home-section__title">Find Exactly What You Need</h2>
    </div>
    <div class="gcat-grid">
      <?php foreach ( $display_cats as $i => $cat ) :
        $cat = is_object( $cat ) ? (array) $cat : $cat;
        get_template_part( 'components/cards/guide-category-card', null, [ 'cat' => $cat, 'index' => $i ] );
      endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- § C  Latest Guides -->
  <?php if ( $latest_guides ) : ?>
  <div class="gc-home-section">
    <div class="gc-home-section__head">
      <span class="gc-home-section__eyebrow">Fresh Content</span>
      <h2 class="gc-home-section__title">Latest Guides</h2>
    </div>
    <div class="post-grid">
      <?php foreach ( $latest_guides as $lg ) :
        $lg_cats  = get_the_category( $lg->ID );
        $lg_cat   = $lg_cats ? $lg_cats[0] : null;
        $lg_slug  = $lg_cat ? $lg_cat->slug : '';
        $lg_name  = $lg_cat ? $lg_cat->name : '';
        $lg_thumb = get_the_post_thumbnail_url( $lg->ID, 'ah-card' ) ?: '';
        $lg_pt    = $lg_slug ? ( $cat_pt_map[ $lg_slug ] ?? null ) : null;
      ?>
      <a href="<?php echo esc_url( get_permalink( $lg->ID ) ); ?>" class="gc" data-cat="<?php echo esc_attr( $lg_slug ); ?>" data-aos="fade-up">
        <div class="gc__img-wrap">
          <?php if ( $lg_thumb ) : ?><img src="<?php echo esc_url( $lg_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $lg->ID ) ); ?>" class="gc__img" loading="lazy">
          <?php else : ?><div class="gc__img gc__img--fallback">📖</div><?php endif; ?>
          <?php if ( $lg_name ) : ?><span class="gc__cat"><?php echo esc_html( $lg_name ); ?></span><?php endif; ?>
        </div>
        <div class="gc__body">
          <?php if ( $lg_pt ) : ?>
          <span class="gc__pt-badge" style="--ptc:<?php echo esc_attr( $lg_pt->color ?? 'var(--accent)' ); ?>"><?php echo esc_html( $lg_pt->name ); ?></span>
          <?php endif; ?>
          <div class="gc__meta"><span class="gc__read-time">⏱ <?php echo esc_html( ah_reading_time( $lg->ID ) ); ?></span></div>
          <h2 class="gc__title"><?php echo esc_html( get_the_title( $lg->ID ) ); ?></h2>
          <?php $lg_exc = get_the_excerpt( $lg->ID ); if ( $lg_exc ) : ?><p class="gc__excerpt"><?php echo wp_trim_words( $lg_exc, 15, '…' ); ?></p><?php endif; ?>
          <span class="gc__btn">Read Guide <span class="gc__arrow">→</span></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- § D  Popular Guides -->
  <?php if ( $popular_guides ) : ?>
  <div class="gc-home-section">
    <div class="gc-home-section__head">
      <span class="gc-home-section__eyebrow">Readers' Favourites</span>
      <h2 class="gc-home-section__title">Popular Right Now</h2>
    </div>
    <div class="post-grid">
      <?php foreach ( $popular_guides as $pg ) :
        $pg_cats  = get_the_category( $pg->ID );
        $pg_cat   = $pg_cats ? $pg_cats[0] : null;
        $pg_slug  = $pg_cat ? $pg_cat->slug : '';
        $pg_name  = $pg_cat ? $pg_cat->name : '';
        $pg_thumb = get_the_post_thumbnail_url( $pg->ID, 'ah-card' ) ?: '';
        $pg_pt    = $pg_slug ? ( $cat_pt_map[ $pg_slug ] ?? null ) : null;
      ?>
      <a href="<?php echo esc_url( get_permalink( $pg->ID ) ); ?>" class="gc" data-cat="<?php echo esc_attr( $pg_slug ); ?>" data-aos="fade-up">
        <div class="gc__img-wrap">
          <?php if ( $pg_thumb ) : ?><img src="<?php echo esc_url( $pg_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $pg->ID ) ); ?>" class="gc__img" loading="lazy">
          <?php else : ?><div class="gc__img gc__img--fallback">⭐</div><?php endif; ?>
          <?php if ( $pg_name ) : ?><span class="gc__cat"><?php echo esc_html( $pg_name ); ?></span><?php endif; ?>
        </div>
        <div class="gc__body">
          <?php if ( $pg_pt ) : ?>
          <span class="gc__pt-badge" style="--ptc:<?php echo esc_attr( $pg_pt->color ?? 'var(--accent)' ); ?>"><?php echo esc_html( $pg_pt->name ); ?></span>
          <?php endif; ?>
          <div class="gc__meta"><span class="gc__read-time">⏱ <?php echo esc_html( ah_reading_time( $pg->ID ) ); ?></span></div>
          <h2 class="gc__title"><?php echo esc_html( get_the_title( $pg->ID ) ); ?></h2>
          <?php $pg_exc = get_the_excerpt( $pg->ID ); if ( $pg_exc ) : ?><p class="gc__excerpt"><?php echo wp_trim_words( $pg_exc, 15, '…' ); ?></p><?php endif; ?>
          <span class="gc__btn">Read Guide <span class="gc__arrow">→</span></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

<?php endif; ?>

</main>

<!-- ── SIDEBAR ───────────────────────────────────────────── -->
<aside class="gc-portal-sidebar">
  <?php if ( $is_filtered && $sidebar_pts ) :
    // Which parent term is currently active — expand only that one
    $sidebar_active_pt_id = null;
    if ( $active_pt ) {
      $sidebar_active_pt_id = $active_pt->id;
    } elseif ( $active_cat && isset( $cat_pt_map[ $active_cat ] ) ) {
      $sidebar_active_pt_id = $cat_pt_map[ $active_cat ]->id ?? null;
    }
  ?>
  <div class="gc-see-more">
    <div class="gc-see-more__title">See More</div>
    <?php foreach ( $sidebar_pts as $sb ) :
      $sb_pt     = $sb['pt'];
      $sb_color  = ! empty( $sb_pt->color ) ? $sb_pt->color : 'var(--accent)';
      $is_open   = $sidebar_active_pt_id && ( (int) $sb_pt->id === (int) $sidebar_active_pt_id );
    ?>
    <div class="gc-see-more__group<?php echo $is_open ? ' is-open' : ''; ?>" style="--gc-group-color:<?php echo esc_attr( $sb_color ); ?>">
      <a href="<?php echo esc_url( home_url( '/guides/?parent_term=' . urlencode( $sb_pt->slug ) ) ); ?>"
         class="gc-see-more__pt-header" style="background:<?php echo esc_attr( $sb_color ); ?>">
        <span class="gc-see-more__pt-icon" aria-hidden="true"><?php echo esc_html( ah_guide_topic_icon( $sb_pt->name ?? '', $sb_pt->slug ?? '', $sb_pt->icon_emoji ?? '' ) ); ?></span>
        <?php echo esc_html( $sb_pt->name ); ?>
      </a>
      <?php if ( $is_open && $sb['children'] ) : ?>
      <ul class="gc-see-more__children">
        <?php foreach ( $sb['children'] as $sb_child ) :
          $is_active_child = ( $sb_child->slug === $active_cat );
        ?>
        <li>
          <a href="<?php echo esc_url( home_url( '/guides/?category=' . urlencode( $sb_child->slug ) ) ); ?>"
             class="<?php echo $is_active_child ? 'is-active' : ''; ?>">
            <?php echo esc_html( $sb_child->name ); ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</aside>

</div><!-- /.gc-portal-layout -->
</div><!-- /.container -->
</div><!-- /.gc-portal-bg -->

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
