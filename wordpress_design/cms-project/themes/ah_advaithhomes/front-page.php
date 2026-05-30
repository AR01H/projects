<?php
/**
 * Homepage — Editorial / Informational rebuild (MoveIQ-inspired).
 * All content from DB/CMS. Hardcoded labels in includes/common_terms.php only.
 */
defined( 'ABSPATH' ) || exit;
get_header();

// ── Data ──────────────────────────────────────────────────────────────────────
$guide_cats   = function_exists( 'ah_get_guide_categories' ) ? ah_get_guide_categories() : [];
$site_stats   = function_exists( 'ah_get_site_stats' )       ? ah_get_site_stats()       : [];
$hp_tiles     = function_exists( 'get_client_hp_tiles' )     ? get_client_hp_tiles()     : [];

// Parent taxonomy terms for hero chips
$parent_terms = [];
if ( class_exists( 'AH_Taxonomy_Parent_Model' ) ) {
	$_pt_model    = new AH_Taxonomy_Parent_Model();
	$parent_terms = $_pt_model->get_all_active();
}

// All posts — hero (1) + bento (6) + grid (6)
$all_posts  = get_posts( [
	'posts_per_page'      => 13,
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => false,
] );
$hero_post   = $all_posts[0] ?? null;
$bento_posts = array_slice( $all_posts, 1, 6 );
$grid_posts  = array_slice( $all_posts, 7, 6 );

// News bar items
$news_items = [];
if ( class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$_nb = AH_DB_Helper::table( 'news_bar_items' );
	$_td = current_time( 'Y-m-d' );
	$news_items = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$_nb}` WHERE status='active'
		 AND (start_date IS NULL OR start_date <= %s)
		 AND (end_date   IS NULL OR end_date   >= %s)
		 ORDER BY COALESCE(start_date,'1970-01-01') DESC, id DESC LIMIT 4",
		$_td, $_td
	) ) ?: [];
}

if ( ! function_exists( 'nhp_meta' ) ) {
	function nhp_meta( WP_Post $p ): array {
		$cats    = get_the_category( $p->ID );
		$cat     = $cats[0] ?? null;
		$thumb   = get_the_post_thumbnail_url( $p->ID, 'ah-card' )
			?: get_the_post_thumbnail_url( $p->ID, 'medium_large' ) ?: '';
		$excerpt = wp_trim_words( get_the_excerpt( $p->ID ) ?: $p->post_content, 22, '…' );
		$rt      = function_exists( 'ah_reading_time' ) ? ah_reading_time( $p->ID ) : '';
		return [ 'cat' => $cat, 'thumb' => $thumb, 'excerpt' => $excerpt, 'rt' => $rt,
		         'url' => get_permalink( $p->ID ), 'date' => get_the_date( 'M j, Y', $p->ID ) ];
	}
}
$hm = $hero_post ? nhp_meta( $hero_post ) : null;

// HP tiles as array
$tiles_arr = array_values( $hp_tiles ?: [] );

// Fallback images for cards with no thumbnail
$_img_base  = get_template_directory_uri() . '/assets/images/backgrounds/';
$_fb_news   = $_img_base . 'min_news.png';
$_fb_blog   = $_img_base . 'mini_blog.png';
$_fb_guides = $_img_base . 'mini_guides.png';
$_fb_review = $_img_base . 'min_reviews.png';

// ── Normalized bento cards (consistent shape for posts AND news) ──────────────
if ( ! function_exists( 'nhp_card_from_post' ) ) {
	function nhp_card_from_post( WP_Post $p ): array {
		$m = nhp_meta( $p );
		return [
			'title'   => get_the_title( $p->ID ),
			'thumb'   => $m['thumb'],
			'excerpt' => $m['excerpt'],
			'meta'    => $m['rt'],                       // read-time
			'url'     => $m['url'],
			'badge'   => $m['cat'] ? html_entity_decode( $m['cat']->name, ENT_QUOTES ) : 'Article',
			'slug'    => $m['cat'] ? $m['cat']->slug : '',
			'is_news' => false,
		];
	}
}
if ( ! function_exists( 'nhp_card_from_news' ) ) {
	function nhp_card_from_news( $item ): array {
		$thumb = ! empty( $item->image_id )
			? ( wp_get_attachment_image_url( (int) $item->image_id, 'ah-card' )
				?: wp_get_attachment_image_url( (int) $item->image_id, 'medium_large' ) )
			: '';
		$excerpt = ! empty( $item->content ) ? wp_trim_words( wp_strip_all_tags( $item->content ), 22, '…' ) : '';
		$url     = ! empty( $item->link_url ) ? $item->link_url : home_url( '/allnews/?item=' . (int) $item->id );
		$date    = ! empty( $item->start_date ) ? date_i18n( 'M j, Y', strtotime( $item->start_date ) ) : '';
		return [
			'title'   => $item->text ?? '',
			'thumb'   => $thumb,
			'excerpt' => $excerpt,
			'meta'    => $date,
			'url'     => $url,
			'badge'   => 'NEWS',
			'slug'    => 'news',
			'is_news' => true,
		];
	}
}

// Build queues: real news items, and blog posts
$nhp_news_q = array_map( 'nhp_card_from_news', $news_items );
$nhp_post_q = array_map( 'nhp_card_from_post', $bento_posts );

// Allocate bento slots — NEWS slots prefer real news, fall back to posts (with real category)
$card_wide = array_shift( $nhp_news_q ) ?: array_shift( $nhp_post_q );  // big top card
$card_dark = array_shift( $nhp_post_q );                                // dark feature (post)
$card_art  = array_shift( $nhp_post_q );                                // article (post)
$card_n1   = array_shift( $nhp_news_q ) ?: array_shift( $nhp_post_q );  // bottom card 1
$card_n2   = array_shift( $nhp_news_q ) ?: array_shift( $nhp_post_q );  // bottom card 2

// Accent colour from a category slug (news = green)
$nhp_slug_color = function ( $slug ) {
	$map = [ 'news' => '#16a34a', 'buying' => '#2ecc71', 'first' => '#3b8fd4',
	         'finance' => '#a855f7', 'legal' => '#6366f1', 'invest' => '#14b8a6',
	         'tips' => '#eab308', 'client' => '#d97706' ];
	foreach ( $map as $k => $c ) if ( $slug && stripos( $slug, $k ) !== false ) return $c;
	return 'var(--accent)';
};
$nhp_dots = '<span class="nhp-mq-dots" aria-hidden="true"><i></i><i></i><i class="is-on"></i></span>';
?>

<div class="nhp-wrap">

<!-- ══════════════════════════════════════════════════════════════
     § 1  HERO
     ══════════════════════════════════════════════════════════════ -->
<?php $hero_img = get_template_directory_uri() . '/assets/images/backgrounds/family_background.png'; ?>
<section class="nhp-banner nhp-banner--hero">
  <div class="nhp-banner__media" style="background-image:url('<?php echo esc_url( $hero_img ); ?>')" aria-hidden="true"></div>

  <div class="nhp-banner__body">
    <span class="nhp-banner__eyebrow">UK Property Resource</span>
    <h1 class="nhp-banner__title nhp-banner__title--hero">
      Your Complete Guide to <em>Buying Property</em> in the UK
    </h1>
    <p class="nhp-banner__sub">
      Independent, expert-written guides on rules, regulations, finance, and legal requirements — everything a buyer needs, explained clearly.
    </p>

    <div class="nhp-banner__chips">
      <?php if ( $parent_terms ) :
        foreach ( array_slice( $parent_terms, 0, 6 ) as $pt ) :
          $pt = is_object( $pt ) ? $pt : (object) $pt;
      ?>
      <a href="<?php echo esc_url( home_url( '/guides/?parent_term=' . urlencode( $pt->slug ?? '' ) ) ); ?>"
         class="nhp-banner__chip">
        <?php if ( ! empty( $pt->icon_emoji ) ) echo esc_html( $pt->icon_emoji ) . ' '; ?>
        <?php echo esc_html( $pt->name ?? '' ); ?>
      </a>
      <?php endforeach; endif; ?>
      <a href="<?php echo esc_url( home_url( '/allnews/' ) ); ?>" class="nhp-banner__chip">📰 Latest News</a>
      <a href="<?php echo esc_url( home_url( '/multiinfo/' ) ); ?>" class="nhp-banner__chip">🛠️ All Services</a>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="nhp-banner__chip nhp-banner__chip--accent">All Topics →</a>
    </div>

    <div class="nhp-banner__ctas">
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="nhp-banner__btn nhp-banner__btn--primary">
        <span>Browse Guides</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
      <a href="<?php echo esc_url( home_url( '/multiinfo/' ) ); ?>" class="nhp-banner__btn nhp-banner__btn--ghost">Explore Info Hub</a>
    </div>
  </div>

  <svg class="nhp-banner__rooftops" viewBox="0 0 1200 90" preserveAspectRatio="none" aria-hidden="true">
    <path d="M0,90 L0,60 L70,60 L70,44 L104,44 L104,32 L138,32 L138,60 L210,60 L255,28 L300,60 L390,60 L390,48 L450,48 L450,36 L486,36 L486,60 L560,60 L606,22 L652,60 L740,60 L740,46 L800,46 L800,60 L880,60 L926,30 L972,60 L1060,60 L1060,50 L1116,50 L1116,40 L1150,40 L1150,60 L1200,60 L1200,90 Z"/>
    <rect x="252" y="14" width="6" height="16"/>
    <rect x="603" y="8"  width="6" height="16"/>
    <rect x="923" y="16" width="6" height="16"/>
  </svg>
</section>

<!-- ══════════════════════════════════════════════════════════════
     § 2  MIXED BENTO GRID  (MoveIQ style)
     ══════════════════════════════════════════════════════════════ -->
<?php
$t0 = $tiles_arr[0] ?? null;
$t1 = $tiles_arr[1] ?? null;
$t2 = $tiles_arr[2] ?? null;

// Badge HTML for a card: real news → green NEWS, post → category-coloured pill
$nhp_badge = function ( $card ) {
	if ( ! empty( $card['is_news'] ) ) return '<span class="nhp-mq-badge nhp-mq-badge--green">NEWS</span>';
	if ( ! empty( $card['badge'] ) )   return '<span class="nhp-mq-badge nhp-mq-badge--cat">' . esc_html( strtoupper( $card['badge'] ) ) . '</span>';
	return '';
};
?>
<?php if ( $card_wide ) : ?>
<section class="nhp-bento-section">
  <div class="container">

    <div class="nhp-mq-grid">

      <!-- ROW 1 ── wide card (real news if available) -->
      <?php $b = $card_wide; ?>
      <?php $_wide_fb = $b['is_news'] ? $_fb_news : $_fb_blog; ?>
      <a href="<?php echo esc_url( $b['url'] ); ?>" class="nhp-mq-wide nhp-mq-area-wide"
         style="--ac:<?php echo esc_attr( $nhp_slug_color( $b['slug'] ) ); ?>" data-aos="fade-up">
        <div class="nhp-mq-wide__img" style="background-image:url('<?php echo esc_url( $b['thumb'] ?: $_wide_fb ); ?>')">
          <?php echo $nhp_badge( $b ); ?>
        </div>
        <div class="nhp-mq-wide__body">
          <h3 class="nhp-mq-wide__title"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php if ( $b['excerpt'] ) : ?><p class="nhp-mq-wide__excerpt"><?php echo esc_html( $b['excerpt'] ); ?></p><?php endif; ?>
          <div class="nhp-mq-foot">
            <?php echo $nhp_dots; ?>
            <?php if ( $b['meta'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
          </div>
        </div>
      </a>

      <!-- ROW 1 ── promo tile -->
      <?php if ( $t0 ) :
        $t0_img = ! empty( $t0['image'] ) ? get_template_directory_uri() . '/' . $t0['image'] : '';
      ?>
      <a href="<?php echo esc_url( home_url( $t0['url'] ) ); ?>" class="nhp-mq-promo nhp-mq-area-promo"
         style="--ac:<?php echo esc_attr( $t0['color'] ); ?>" data-aos="fade-up" data-delay="80">
        <div class="nhp-mq-tile__img" <?php if ( $t0_img ) echo 'style="background-image:url(' . esc_url( $t0_img ) . ')"'; ?>>
          <span class="nhp-mq-promo__icon"><?php echo esc_html( $t0['icon'] ); ?></span>
        </div>
        <div class="nhp-mq-promo__body">
          <h3 class="nhp-mq-promo__title"><?php echo esc_html( $t0['title'] ); ?></h3>
          <?php if ( $t0['desc'] ) : ?><p class="nhp-mq-promo__desc"><?php echo esc_html( $t0['desc'] ); ?></p><?php endif; ?>
          <span class="nhp-mq-go"><?php echo esc_html( $t0['cta'] ); ?> <span aria-hidden="true">→</span></span>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 2 ── dark featured (post) -->
      <?php if ( $card_dark ) : $b = $card_dark; ?>
      <a href="<?php echo esc_url( $b['url'] ); ?>" class="nhp-mq-dark nhp-mq-area-dark"
         style="--ac:<?php echo esc_attr( $nhp_slug_color( $b['slug'] ) ); ?>" data-aos="fade-up">
        <div class="nhp-mq-dark__img" style="background-image:url('<?php echo esc_url( $b['thumb'] ?: $_fb_guides ); ?>')">
        </div>
        <div class="nhp-mq-dark__body">
          <?php if ( $b['badge'] ) : ?><span class="nhp-mq-badge nhp-mq-badge--glass"><?php echo esc_html( strtoupper( $b['badge'] ) ); ?></span><?php endif; ?>
          <h3 class="nhp-mq-dark__title"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php if ( $b['excerpt'] ) : ?><p class="nhp-mq-dark__excerpt"><?php echo esc_html( $b['excerpt'] ); ?></p><?php endif; ?>
          <div class="nhp-mq-foot nhp-mq-foot--light">
            <?php echo $nhp_dots; ?>
            <?php if ( $b['meta'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
          </div>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 2 ── article (post) -->
      <?php if ( $card_art ) : $b = $card_art; ?>
      <a href="<?php echo esc_url( $b['url'] ); ?>" class="nhp-mq-card nhp-mq-area-art"
         style="--ac:<?php echo esc_attr( $nhp_slug_color( $b['slug'] ) ); ?>" data-aos="fade-up" data-delay="80">
        <div class="nhp-mq-card__img" style="background-image:url('<?php echo esc_url( $b['thumb'] ?: $_fb_blog ); ?>')">
          <?php echo $nhp_badge( $b ); ?>
        </div>
        <div class="nhp-mq-card__body">
          <h3 class="nhp-mq-card__title"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php if ( $b['excerpt'] ) : ?><p class="nhp-mq-card__excerpt"><?php echo esc_html( $b['excerpt'] ); ?></p><?php endif; ?>
          <div class="nhp-mq-foot">
            <?php echo $nhp_dots; ?>
            <?php if ( $b['meta'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
          </div>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 2 ── service tile -->
      <?php if ( $t1 ) :
        $t1_img = ! empty( $t1['image'] ) ? get_template_directory_uri() . '/' . $t1['image'] : '';
      ?>
      <a href="<?php echo esc_url( home_url( $t1['url'] ) ); ?>" class="nhp-mq-service nhp-mq-area-svc1"
         style="--ac:<?php echo esc_attr( $t1['color'] ); ?>" data-aos="fade-up" data-delay="160">
        <div class="nhp-mq-tile__img" <?php if ( $t1_img ) echo 'style="background-image:url(' . esc_url( $t1_img ) . ')"'; ?>>
          <span class="nhp-mq-service__icon"><?php echo esc_html( $t1['icon'] ); ?></span>
        </div>
        <div class="nhp-mq-service__body">
          <h3 class="nhp-mq-service__title"><?php echo esc_html( $t1['title'] ); ?></h3>
          <?php if ( $t1['desc'] ) : ?><p class="nhp-mq-service__desc"><?php echo esc_html( $t1['desc'] ); ?></p><?php endif; ?>
          <span class="nhp-mq-go"><?php echo esc_html( $t1['cta'] ); ?> <span aria-hidden="true">→</span></span>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 3 ── service tile -->
      <?php if ( $t2 ) :
        $t2_img = ! empty( $t2['image'] ) ? get_template_directory_uri() . '/' . $t2['image'] : '';
      ?>
      <a href="<?php echo esc_url( home_url( $t2['url'] ) ); ?>" class="nhp-mq-service nhp-mq-area-svc2"
         style="--ac:<?php echo esc_attr( $t2['color'] ); ?>" data-aos="fade-up">
        <div class="nhp-mq-tile__img" <?php if ( $t2_img ) echo 'style="background-image:url(' . esc_url( $t2_img ) . ')"'; ?>>
          <span class="nhp-mq-service__icon"><?php echo esc_html( $t2['icon'] ); ?></span>
        </div>
        <div class="nhp-mq-service__body">
          <h3 class="nhp-mq-service__title"><?php echo esc_html( $t2['title'] ); ?></h3>
          <?php if ( $t2['desc'] ) : ?><p class="nhp-mq-service__desc"><?php echo esc_html( $t2['desc'] ); ?></p><?php endif; ?>
          <span class="nhp-mq-go"><?php echo esc_html( $t2['cta'] ); ?> <span aria-hidden="true">→</span></span>
        </div>
      </a>
      <?php endif; ?>

      <!-- ROW 3 ── two cards (real news if available) -->
      <?php foreach ( [ 'svc3' => $card_n1, 'svc4' => $card_n2 ] as $area => $b ) :
        if ( ! $b ) continue;
        $_card_fb = $b['is_news'] ? $_fb_news : $_fb_review;
      ?>
      <a href="<?php echo esc_url( $b['url'] ); ?>" class="nhp-mq-card nhp-mq-area-<?php echo esc_attr( $area ); ?>"
         style="--ac:<?php echo esc_attr( $nhp_slug_color( $b['slug'] ) ); ?>" data-aos="fade-up">
        <div class="nhp-mq-card__img" style="background-image:url('<?php echo esc_url( $b['thumb'] ?: $_card_fb ); ?>')">
          <?php echo $nhp_badge( $b ); ?>
        </div>
        <div class="nhp-mq-card__body">
          <h3 class="nhp-mq-card__title"><?php echo esc_html( $b['title'] ); ?></h3>
          <?php if ( $b['excerpt'] ) : ?><p class="nhp-mq-card__excerpt"><?php echo esc_html( $b['excerpt'] ); ?></p><?php endif; ?>
          <div class="nhp-mq-foot">
            <?php echo $nhp_dots; ?>
            <?php if ( $b['meta'] ) : ?><span class="nhp-mq-rt"><?php echo esc_html( $b['meta'] ); ?></span><?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>

    </div><!-- /.nhp-mq-grid -->
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     § 4  BROWSE BY TOPIC
     ══════════════════════════════════════════════════════════════ -->
<?php if ( $guide_cats ) : ?>
<section class="nhp-topics">
  <div class="container">
    <div class="nhp-section-head" data-aos="fade-up">
      <div>
        <span class="nhp-eyebrow">Browse by Topic</span>
        <h2 class="nhp-section-title">Find Exactly What You Need</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="nhp-see-all">View all topics →</a>
    </div>
    <div class="nhp-topics__grid">
      <?php foreach ( array_slice( $guide_cats, 0, 6 ) as $i => $gc ) :
        $gc      = is_object( $gc ) ? (array) $gc : $gc;
        $img_url = ! empty( $gc['image_id'] ) ? wp_get_attachment_image_url( (int)$gc['image_id'], 'medium' ) : '';
        $color   = ! empty( $gc['parent_color'] ) ? $gc['parent_color'] : 'var(--accent)';
        $url     = home_url( '/guides/?category=' . urlencode( $gc['slug'] ?? '' ) );
      ?>
      <a href="<?php echo esc_url( $url ); ?>"
         class="nhp-topic-card"
         style="--tc:<?php echo esc_attr( $color ); ?>"
         data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 80; ?>">
        <div class="nhp-topic-card__header" <?php if ( $img_url ) echo 'style="background-image:url(' . esc_url( $img_url ) . ')"'; ?>>
          <?php if ( ! $img_url ) : ?>
          <span class="nhp-topic-card__icon"><?php echo esc_html( $gc['icon_emoji'] ?? '📖' ); ?></span>
          <?php endif; ?>
          <div class="nhp-topic-card__header-overlay" aria-hidden="true"></div>
        </div>
        <div class="nhp-topic-card__body">
          <h3 class="nhp-topic-card__title"><?php echo esc_html( $gc['title'] ?? '' ); ?></h3>
          <?php if ( ! empty( $gc['desc'] ) ) : ?><p class="nhp-topic-card__desc"><?php echo esc_html( $gc['desc'] ); ?></p><?php endif; ?>
        </div>
        <div class="nhp-topic-card__footer">
          <?php if ( ! empty( $gc['count'] ) ) : ?><span class="nhp-topic-card__count"><?php echo (int) $gc['count']; ?> guides</span><?php endif; ?>
          <span class="nhp-topic-card__arrow" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     § 5  LATEST GUIDES GRID
     ══════════════════════════════════════════════════════════════ -->
<?php if ( $grid_posts ) : ?>
<section class="nhp-articles">
  <div class="container">
    <div class="nhp-section-head" data-aos="fade-up">
      <div>
        <span class="nhp-eyebrow">Latest</span>
        <h2 class="nhp-section-title">Recent Guides & Articles</h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="nhp-see-all">See all →</a>
    </div>
    <div class="nhp-articles__grid">
      <?php foreach ( $grid_posts as $i => $p ) :
        $m = nhp_meta( $p );
      ?>
      <a href="<?php echo esc_url( $m['url'] ); ?>"
         class="nhp-article-card"
         data-cat="<?php echo esc_attr( $m['cat'] ? $m['cat']->slug : '' ); ?>"
         data-aos="fade-up" data-delay="<?php echo ( $i % 3 ) * 70; ?>">
        <div class="nhp-article-card__img-wrap">
          <?php if ( $m['thumb'] ) : ?>
          <img src="<?php echo esc_url( $m['thumb'] ); ?>" alt="<?php echo esc_attr( get_the_title( $p->ID ) ); ?>" loading="lazy" decoding="async">
          <?php else : ?>
          <img src="<?php echo esc_url( $_fb_blog ); ?>" alt="<?php echo esc_attr( get_the_title( $p->ID ) ); ?>" loading="lazy" decoding="async">
          <?php endif; ?>
          <?php if ( $m['cat'] ) : ?><span class="nhp-pill nhp-pill--sm nhp-article-card__badge"><?php echo esc_html( $m['cat']->name ); ?></span><?php endif; ?>
        </div>
        <div class="nhp-article-card__body">
          <h3 class="nhp-article-card__title"><?php echo esc_html( get_the_title( $p->ID ) ); ?></h3>
          <p class="nhp-article-card__excerpt"><?php echo esc_html( $m['excerpt'] ); ?></p>
          <div class="nhp-article-card__footer">
            <?php if ( $m['rt'] ) : ?><span class="nhp-article-card__time"><?php echo esc_html( $m['rt'] ); ?></span><?php endif; ?>
            <span class="nhp-article-card__read">Read guide <span aria-hidden="true">→</span></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════════
     § 7  SERVICES GRID  (purple gradient + white tiles)
     ══════════════════════════════════════════════════════════════ -->
<?php if ( $hp_tiles && FALSE ) : ?>
<section class="nhp-svc-grid" data-aos="fade-up">
  <div class="container">
    <h2 class="nhp-svc-grid__title">Find trusted services, save money</h2>
    <div class="nhp-svc-grid__grid">
      <?php foreach ( $hp_tiles as $tile ) : ?>
      <a href="<?php echo esc_url( home_url( $tile['url'] ) ); ?>" class="nhp-svc-tile">
        <span class="nhp-svc-tile__icon" style="--tc:<?php echo esc_attr( $tile['color'] ); ?>"><?php echo esc_html( $tile['icon'] ); ?></span>
        <span class="nhp-svc-tile__name"><?php echo esc_html( $tile['title'] ); ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="nhp-svc-grid__foot">
      <a href="<?php echo esc_url( home_url( '/multiinfo/' ) ); ?>" class="nhp-svc-grid__more">Show more services →</a>
    </div>
  </div>
</section>
<?php endif; ?>

</div><!-- /.nhp-wrap -->
<?php get_template_part( 'components/cta-section', null, [] ); ?>
<?php get_footer(); ?>
