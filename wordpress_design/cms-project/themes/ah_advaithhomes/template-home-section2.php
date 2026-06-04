<?php
/**
 * Template Name: Homepage - Bento Layout (v1)
 *
 * The original bento-grid homepage preserved as a standalone page template.
 * Assign this template to any WordPress page to restore the old layout.
 */
defined( 'ABSPATH' ) || exit;

$hp_tiles = function_exists('get_client_hp_tiles') ? get_client_hp_tiles() : [];

get_header();

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

$blog_pool = get_posts( [ 'posts_per_page' => 1, 'post_status' => 'publish', 'ignore_sticky_posts' => false ] );
$blog_post = $blog_pool[0] ?? null;

$news_items = $news_item_terms = $news_fallback = [];
if ( class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$_nb_table  = AH_DB_Helper::table( 'news_bar_items' );
	$_today     = current_time( 'Y-m-d' );
	$news_items = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$_nb_table}` WHERE status = 'active'
		 AND (start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s)
		 ORDER BY COALESCE(start_date,'1970-01-01') DESC, id DESC LIMIT 3", $_today, $_today ) ) ?: [];
}
if ( ! empty( $news_items ) && class_exists( 'AH_Theme_Content_Taxonomy' ) ) {
	$_tax_data       = AH_Theme_Content_Taxonomy::get_terms_for_items( $news_items, 'news_bar_item' );
	$news_item_terms = $_tax_data['item_terms'] ?? [];
}
$_news_needed = 3 - count( $news_items );
if ( $_news_needed > 0 ) {
	$news_fallback = get_posts( [ 'posts_per_page' => $_news_needed, 'post_status' => 'publish',
		'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true,
		'post__not_in' => $blog_post ? [ $blog_post->ID ] : [] ] );
}

$parent_terms = [];
if ( class_exists( 'AH_DB_Helper' ) ) {
	if ( ! isset( $wpdb ) ) global $wpdb;
	$pt_table     = AH_DB_Helper::table( 'taxonomy_parent_terms' );
	$parent_terms = $wpdb->get_results( "SELECT id, name, slug, color, icon_emoji FROM `{$pt_table}` WHERE status = 1 ORDER BY name ASC" ) ?: [];
}

$bd       = $blog_post ? nif_get_post_data( $blog_post ) : null;
$blog_bg  = ( $bd && $bd['thumb_url'] ) ? 'style="--blog-bg:url(' . esc_url( $bd['thumb_url'] ) . ')"' : '';
$blog_cat = $blog_post ? ( get_the_category( $blog_post->ID )[0] ?? null ) : null;
?>
<div class="hp-wrap whole-page-card">
  <?php get_template_part( 'components/nif-background-imagecard' ); ?>
  <section class="hp-grid-section" aria-label="<?php echo esc_attr( TXT_EXPLORE ); ?>">
    <div class="container">
      <div class="hp-bento">
        <?php if ( $blog_post && $bd ) : ?>
        <article class="hp-bento__item hp-bento__blog" <?php echo $blog_bg; ?>>
          <div class="hp-bento__blog-overlay" aria-hidden="true"></div>
          <a href="<?php echo esc_url( $bd['permalink'] ); ?>" class="hp-bento__cover-link" tabindex="-1" aria-hidden="true"></a>
          <div class="hp-bento__blog-body">
            <div class="hp-bento__blog-top">
              <?php if ( $blog_cat ) : ?><span class="nif-tile-badge"><?php echo esc_html( strtoupper( $blog_cat->name ) ); ?></span><?php endif; ?>
              <span class="hp-bento__blog-tag"><?php echo esc_html( TXT_LATEST_ARTICLE ); ?></span>
            </div>
            <h2 class="hp-bento__blog-title"><a href="<?php echo esc_url( $bd['permalink'] ); ?>"><?php echo esc_html( get_the_title( $blog_post->ID ) ); ?></a></h2>
            <p class="hp-bento__blog-excerpt"><?php echo esc_html( $bd['excerpt'] ); ?></p>
            <div class="hp-bento__blog-meta">
              <?php if ( $bd['read_time'] ) : ?><span class="nif-meta-pill"><?php echo esc_html( $bd['read_time'] ); ?></span><?php endif; ?>
              <a href="<?php echo esc_url( $bd['permalink'] ); ?>" class="hp-bento__blog-cta"><?php echo esc_html( TXT_CONTINUE_READING ); ?> →</a>
            </div>
          </div>
        </article>
        <?php endif; ?>
        <?php foreach ( $hp_tiles as $key => $tile ) : ?>
        <a href="<?php echo esc_url( home_url( $tile['url'] ) ); ?>"
           class="hp-bento__item hp-bento__svc hp-bento__svc--<?php echo esc_attr( $key ); ?>"
           style="--tile-color:<?php echo esc_attr( $tile['color'] ); ?>;--tile-bg:url('<?php echo esc_url( get_template_directory_uri() . '/' . $tile['image'] ); ?>')">
          <div class="hp-bento__svc-banner"><span class="hp-bento__svc-icon"><?php echo esc_html( $tile['icon'] ); ?></span><span class="hp-bento__svc-title"><?php echo esc_html( $tile['title'] ); ?></span></div>
          <div class="hp-bento__svc-body"><p class="hp-bento__svc-desc"><?php echo esc_html( $tile['desc'] ); ?></p><span class="hp-bento__svc-cta"><?php echo esc_html( $tile['cta'] ); ?> →</span></div>
        </a>
        <?php endforeach; ?>
        <?php $_news_areas = [ 'news1','news2','news3' ]; $_slot = 0;
        foreach ( $news_items as $item ) :
          $news_area = $_news_areas[ $_slot ] ?? ''; $news_link = ! empty( $item->link_url ) ? $item->link_url : home_url( '/allnews/?item=' . (int)$item->id );
          $news_thumb = ! empty( $item->image_id ) ? ( wp_get_attachment_image_url( (int)$item->image_id, 'ah-card' ) ?: wp_get_attachment_image_url( (int)$item->image_id, 'medium_large' ) ) : '';
          $news_meta = ! empty( $item->start_date ) ? date_i18n( 'd M Y', strtotime( $item->start_date ) ) : '';
          $target_attr = ! empty( $item->link_target ) ? 'target="' . esc_attr( $item->link_target ) . '"' : '';
        ?>
        <article class="hp-bento__item hp-bento__news" <?php if ( $news_area ) echo 'style="grid-area:' . esc_attr( $news_area ) . '"'; ?> data-aos="fade-up">
          <a href="<?php echo esc_url( $news_link ); ?>" class="hp-bento__news-img" tabindex="-1" aria-hidden="true" <?php echo $target_attr; ?>>
            <?php if ( $news_thumb ) : ?><img src="<?php echo esc_url( $news_thumb ); ?>" alt="<?php echo esc_attr( $item->text ?? '' ); ?>" loading="lazy"><?php else : ?><div class="hp-bento__news-placeholder">📰</div><?php endif; ?>
            <span class="nif-tile-badge hp-bento__news-badge hp-bento__news-badge-news-card">NEWS</span>
          </a>
          <div class="hp-bento__news-body"><h3 class="hp-bento__news-title"><a href="<?php echo esc_url( $news_link ); ?>" <?php echo $target_attr; ?>><?php echo esc_html( $item->text ?? '' ); ?></a></h3>
          <div class="hp-bento__news-meta"><?php if ( $news_meta ) echo '<span class="nif-meta-time">' . esc_html( $news_meta ) . '</span>'; ?><a href="<?php echo esc_url( $news_link ); ?>" class="hp-bento__news-link" <?php echo $target_attr; ?>><?php echo esc_html( TXT_READ_MORE ); ?> →</a></div></div>
        </article>
        <?php $_slot++; endforeach;
        foreach ( $news_fallback as $p ) :
          $fb_d = nif_get_post_data( $p ); $fb_cats = get_the_category( $p->ID ); $fb_cat = $fb_cats[0] ?? null;
          $fb_label = $fb_cat ? strtoupper( $fb_cat->name ) : 'NEWS'; $fb_area = $_news_areas[ $_slot ] ?? '';
        ?>
        <article class="hp-bento__item hp-bento__news" <?php if ( $fb_area ) echo 'style="grid-area:' . esc_attr( $fb_area ) . '"'; ?> data-aos="fade-up">
          <a href="<?php echo esc_url( $fb_d['permalink'] ); ?>" class="hp-bento__news-img" tabindex="-1" aria-hidden="true">
            <?php if ( $fb_d['thumb_url'] ) : ?><img src="<?php echo esc_url( $fb_d['thumb_url'] ); ?>" alt="<?php echo esc_attr( get_the_title( $p->ID ) ); ?>" loading="lazy"><?php else : ?><div class="hp-bento__news-placeholder">📰</div><?php endif; ?>
            <span class="nif-tile-badge hp-bento__news-badge"><?php echo esc_html( $fb_label ); ?></span>
          </a>
          <div class="hp-bento__news-body"><h3 class="hp-bento__news-title"><a href="<?php echo esc_url( $fb_d['permalink'] ); ?>"><?php echo esc_html( get_the_title( $p->ID ) ); ?></a></h3>
          <div class="hp-bento__news-meta"><?php if ( $fb_d['read_time'] ) echo '<span class="nif-meta-time">' . esc_html( $fb_d['read_time'] ) . '</span>'; ?><a href="<?php echo esc_url( $fb_d['permalink'] ); ?>" class="hp-bento__news-link"><?php echo esc_html( TXT_READ_MORE ); ?> →</a></div></div>
        </article>
        <?php $_slot++; endforeach; ?>
      </div>
    </div>
  </section>
  <?php get_template_part( 'components/hp-guidance' ); ?>
</div>
<?php get_template_part( 'components/cta-section', null, [] ); ?>
<?php get_footer(); ?>
