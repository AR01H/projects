<?php
/**
 * Homepage - bento grid layout.
 * Different card sizes: blog=2×2, service=1×1, news=1×1.
 *
 * ── EDIT ZONE (change text / urls / icons here) ──────────────────────────────
 */
defined( 'ABSPATH' ) || exit;

/*
 * Service / link cards - edit freely.
 * 'color' = CSS color for the top accent strip.
 */
$hp_tiles = [
	'contact' => [
		'icon'   => '💬',
		'title'  => 'Contact Us',
		'desc'   => 'Speak to our team - free, no obligation.',
		'url'    => '/contact/',
		'cta'    => 'Get in touch',
		'color'  => '#0f172a',
	],
	'support' => [
		'icon'   => '🛡️',
		'title'  => 'Get Support',
		'desc'   => 'Existing client? Raise a query or request assistance.',
		'url'    => '/contact/?enquiry_type=support',
		'cta'    => 'Get help',
		'color'  => '#1e3a8a',
	],
	'services' => [
		'icon'   => '🛠️',
		'title'  => 'Our Services',
		'desc'   => 'From surveys to conveyancing - see everything we offer.',
		'url'    => '/services/',
		'cta'    => 'View services',
		'color'  => '#14532d',
	],
	'multiinfo' => [
		'icon'   => '📚',
		'title'  => 'Info Hub',
		'desc'   => 'In-depth guides on buying, selling, renting & more.',
		'url'    => '/multiinfo/',
		'cta'    => 'Explore topics',
		'color'  => '#78350f',
	],
	'guides' => [
		'icon'   => '📖',
		'title'  => 'Guides to Know',
		'desc'   => 'Explore detailed step-by-step guidance designed to help you confidently navigate every stage of your property journey, from searching for the right home to securing the perfect deal and moving in successfully.',
		'url'    => '/guides/',
		'cta'    => 'Browse guides',
		'color'  => '#581c87',
	],
];
/* ── END EDIT ZONE ────────────────────────────────────────────────────────── */

get_header();

// ── Post data helper ──────────────────────────────────────────────────────────
if ( ! function_exists( 'nif_get_post_data' ) ) {
	function nif_get_post_data( WP_Post $p ): array {
		$cats      = get_the_category( $p->ID );
		$cat       = $cats[0] ?? null;
		$thumb_url = get_the_post_thumbnail_url( $p->ID, 'ah-card' )
			?: get_the_post_thumbnail_url( $p->ID, 'medium_large' )
			?: get_the_post_thumbnail_url( $p->ID, 'medium' )
			?: '';
		$permalink = get_permalink( $p->ID );
		$excerpt   = wp_trim_words( get_the_excerpt( $p->ID ) ?: $p->post_content, 18, '…' );
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

// ── 1 latest blog post (sticky-aware) ────────────────────────────────────────
$blog_pool = get_posts( [
	'posts_per_page'      => 1,
	'post_status'         => 'publish',
	'ignore_sticky_posts' => false,
] );
$blog_post = $blog_pool[0] ?? null;

// ── 3 news items: prefer wp_ah_news_bar_items, fall back to latest blog posts ─
$news_items      = [];   // newsbar objects
$news_item_terms = [];
$news_fallback   = [];   // WP_Post objects used only when newsbar is empty

if ( class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$_nb_table  = AH_DB_Helper::table( 'news_bar_items' );
	$_today     = current_time( 'Y-m-d' );
	$news_items = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$_nb_table}`
		 WHERE status = 'active'
		   AND (start_date IS NULL OR start_date <= %s)
		   AND (end_date   IS NULL OR end_date   >= %s)
		 ORDER BY COALESCE(start_date, '1970-01-01') DESC, id DESC
		 LIMIT 3",
		$_today, $_today
	) ) ?: [];
}
if ( ! empty( $news_items ) && class_exists( 'AH_Theme_Content_Taxonomy' ) ) {
	$_tax_data       = AH_Theme_Content_Taxonomy::get_terms_for_items( $news_items, 'news_bar_item' );
	$news_item_terms = $_tax_data['item_terms'] ?? [];
}
// Fill remaining slots with blog posts when newsbar has fewer than 3 items
$_news_needed = 3 - count( $news_items );
if ( $_news_needed > 0 ) {
	$news_fallback = get_posts( [
		'posts_per_page'      => $_news_needed,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'post__not_in'        => $blog_post ? [ $blog_post->ID ] : [],
	] );
}

// ── Parent terms for Guidance section ────────────────────────────────────────
$parent_terms = [];
if ( class_exists( 'AH_DB_Helper' ) ) {
	$pt_table     = AH_DB_Helper::table( 'taxonomy_parent_terms' );
	$parent_terms = $wpdb->get_results(
		"SELECT id, name, slug, color, icon_emoji FROM `{$pt_table}` WHERE status = 1 ORDER BY name ASC"
	) ?: [];
}

$bd = $blog_post ? nif_get_post_data( $blog_post ) : null;
$blog_bg = ( $bd && $bd['thumb_url'] )
	? 'style="--blog-bg:url(' . esc_url( $bd['thumb_url'] ) . ')"'
	: '';
$blog_cats = $blog_post ? get_the_category( $blog_post->ID ) : [];
$blog_cat  = $blog_cats[0] ?? null;
?>

<div class="hp-wrap whole-page-card">

  <!-- ══ HERO ══════════════════════════════════════════════════════════════ -->
  <?php get_template_part( 'components/nif-background-imagecard' ); ?>

  <!-- ══ BENTO GRID ════════════════════════════════════════════════════════ -->
  <section class="hp-grid-section" aria-label="<?php esc_attr_e( 'Explore', 'ah-theme' ); ?>">
    <div class="container">
      <div class="hp-bento">

        <!-- ─ BLOG (big 2×2) ─────────────────────────────────────────────── -->
        <?php if ( $blog_post && $bd ) : ?>
        <article class="hp-bento__item hp-bento__blog" <?php echo $blog_bg; ?>>
          <div class="hp-bento__blog-overlay" aria-hidden="true"></div>
          <a href="<?php echo esc_url( $bd['permalink'] ); ?>" class="hp-bento__cover-link" tabindex="-1" aria-hidden="true"></a>
          <div class="hp-bento__blog-body">
            <div class="hp-bento__blog-top">
              <?php if ( $blog_cat ) : ?>
              <span class="nif-tile-badge"><?php echo esc_html( strtoupper( $blog_cat->name ) ); ?></span>
              <?php endif; ?>
              <span class="hp-bento__blog-tag"><?php esc_html_e( 'Latest Article', 'ah-theme' ); ?></span>
            </div>
            <h2 class="hp-bento__blog-title">
              <a href="<?php echo esc_url( $bd['permalink'] ); ?>"><?php echo esc_html( get_the_title( $blog_post->ID ) ); ?></a>
            </h2>
            <p class="hp-bento__blog-excerpt"><?php echo esc_html( $bd['excerpt'] ); ?></p>
            <div class="hp-bento__blog-meta">
              <?php if ( $bd['read_time'] ) : ?>
              <span class="nif-meta-pill">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo esc_html( $bd['read_time'] ); ?>
              </span>
              <?php endif; ?>
              <a href="<?php echo esc_url( $bd['permalink'] ); ?>" class="hp-bento__blog-cta">
                <?php esc_html_e( 'Continue reading', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>
        </article>
        <?php endif; ?>

        <!-- ─ SERVICE TILES (5 tiles: svc1–svc5) ────────────────────────── -->
        <?php foreach ( $hp_tiles as $key => $tile ) : ?>
        <a href="<?php echo esc_url( home_url( $tile['url'] ) ); ?>"
           class="hp-bento__item hp-bento__svc hp-bento__svc--<?php echo esc_attr( $key ); ?>"
           style="--tile-color:<?php echo esc_attr( $tile['color'] ); ?>">
          <div class="hp-bento__svc-banner" aria-hidden="true">
            <span class="hp-bento__svc-icon"><?php echo esc_html( $tile['icon'] ); ?></span>
            <span class="hp-bento__svc-title"><?php echo esc_html( $tile['title'] ); ?></span>
          </div>
          <div class="hp-bento__svc-body">
            <p class="hp-bento__svc-desc"><?php echo esc_html( $tile['desc'] ); ?></p>
            <span class="hp-bento__svc-cta"><?php echo esc_html( $tile['cta'] ); ?> <span aria-hidden="true">→</span></span>
          </div>
        </a>
        <?php endforeach; ?>

        <!-- ─ NEWS CARDS (newsbar first, then blogs fill remaining slots) ── -->
        <?php
        $_news_areas  = [ 'news1', 'news2', 'news3' ];
        $_slot        = 0;

        // ── Newsbar items ──────────────────────────────────────────────────
        foreach ( $news_items as $item ) :
          $_terms     = $news_item_terms[ $item->id ] ?? [];
          $news_label = 'NEWS';
          $news_area  = $_news_areas[ $_slot ] ?? '';
          $news_link  = ! empty( $item->link_url ) ? $item->link_url : home_url( '/allnews/?item=' . (int) $item->id );
          $news_title = $item->text ?? '';
          $news_thumb = ( ! empty( $item->image_id ) )
            ? ( wp_get_attachment_image_url( (int) $item->image_id, 'ah-card' )
              ?: wp_get_attachment_image_url( (int) $item->image_id, 'medium_large' ) )
            : '';
          $news_meta  = ! empty( $item->start_date )
            ? date_i18n( 'd M Y', strtotime( $item->start_date ) )
            : '';
          $target_attr = ! empty( $item->link_target )
            ? 'target="' . esc_attr( $item->link_target ) . '"'
            : '';
        ?>
        <article class="hp-bento__item hp-bento__news"
                 <?php if ( $news_area ) echo 'style="grid-area:' . esc_attr( $news_area ) . '"'; ?>
                 data-aos="fade-up" data-aos-delay="<?php echo $_slot * 60; ?>">
          <a href="<?php echo esc_url( $news_link ); ?>"
             class="hp-bento__news-img" tabindex="-1" aria-hidden="true" <?php echo $target_attr; ?>>
            <?php if ( $news_thumb ) : ?>
              <img src="<?php echo esc_url( $news_thumb ); ?>"
                   alt="<?php echo esc_attr( $news_title ); ?>"
                   loading="lazy" decoding="async">
            <?php else : ?>
              <div class="hp-bento__news-placeholder">📰</div>
            <?php endif; ?>
            <span class="nif-tile-badge hp-bento__news-badge hp-bento__news-badge-news-card"><?php echo esc_html( $news_label ); ?></span>
          </a>
          <div class="hp-bento__news-body">
            <h3 class="hp-bento__news-title">
              <a href="<?php echo esc_url( $news_link ); ?>" <?php echo $target_attr; ?>>
                <?php echo esc_html( $news_title ); ?>
              </a>
            </h3>
            <div class="hp-bento__news-meta">
              <?php if ( $news_meta ) : ?>
              <span class="nif-meta-time">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo esc_html( $news_meta ); ?>
              </span>
              <?php endif; ?>
              <a href="<?php echo esc_url( $news_link ); ?>" class="hp-bento__news-link" <?php echo $target_attr; ?>>
                <?php esc_html_e( 'Read more', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>
        </article>
        <?php $_slot++; endforeach;

        // ── Blog posts fill any remaining slots ────────────────────────────
        foreach ( $news_fallback as $p ) :
          $fb_d     = nif_get_post_data( $p );
          $fb_cats  = get_the_category( $p->ID );
          $fb_cat   = $fb_cats[0] ?? null;
          $fb_label = $fb_cat ? strtoupper( $fb_cat->name ) : 'NEWS';
          $fb_area  = $_news_areas[ $_slot ] ?? '';
        ?>
        <article class="hp-bento__item hp-bento__news"
                 <?php if ( $fb_area ) echo 'style="grid-area:' . esc_attr( $fb_area ) . '"'; ?>
                 data-aos="fade-up" data-aos-delay="<?php echo $_slot * 60; ?>">
          <a href="<?php echo esc_url( $fb_d['permalink'] ); ?>"
             class="hp-bento__news-img" tabindex="-1" aria-hidden="true">
            <?php if ( $fb_d['thumb_url'] ) : ?>
              <img src="<?php echo esc_url( $fb_d['thumb_url'] ); ?>"
                   alt="<?php echo esc_attr( get_the_title( $p->ID ) ); ?>"
                   loading="lazy" decoding="async">
            <?php else : ?>
              <div class="hp-bento__news-placeholder">📰</div>
            <?php endif; ?>
            <span class="nif-tile-badge hp-bento__news-badge"><?php echo esc_html( $fb_label ); ?></span>
          </a>
          <div class="hp-bento__news-body">
            <h3 class="hp-bento__news-title">
              <a href="<?php echo esc_url( $fb_d['permalink'] ); ?>">
                <?php echo esc_html( get_the_title( $p->ID ) ); ?>
              </a>
            </h3>
            <div class="hp-bento__news-meta">
              <?php if ( $fb_d['read_time'] ) : ?>
              <span class="nif-meta-time">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo esc_html( $fb_d['read_time'] ); ?>
              </span>
              <?php endif; ?>
              <a href="<?php echo esc_url( $fb_d['permalink'] ); ?>" class="hp-bento__news-link">
                <?php esc_html_e( 'Read more', 'ah-theme' ); ?> <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>
        </article>
        <?php $_slot++; endforeach; ?>

      </div><!-- /.hp-bento -->
    </div>
  </section>

  <!-- ══ PERSONALISED GUIDANCE ══════════════════════════════════════════════ -->
  <?php get_template_part( 'components/hp-guidance' ); ?>

</div><!-- /.hp-wrap -->

<?php get_template_part( 'components/cta-section', null, [] ); ?>
<?php get_footer(); ?>
