<?php
/**
 * Template Name: News & Insights
 *
 * pages/page-newsall.php - News listing page.
 *
 * All content comes from data/json/news.json via the service layer.
 * No content is hardcoded here - only structure.
 *
 * Architecture:
 *   data/json/news.json
 *     → apis/services.php  adn_service_news_data()
 *       → intermediate/page_news_logical.php  adn_news_get_context()
 *         → THIS FILE  (structure only)
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_news_logical.php';

/* ── Single news item view ──────────────────────────────────────────────── */
$_ah_news_id = isset( $_GET['ah_news_id'] ) ? absint( $_GET['ah_news_id'] ) : 0;
if ( $_ah_news_id > 0 && function_exists( 'adn_cms_newsbar_items' ) ) {
	global $wpdb;
	$_nb_table = $wpdb->prefix . 'ah_news_bar_items';
	$_nb_item  = null;
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $_nb_table ) ) === $_nb_table ) {
		$_nb_item = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$_nb_table}` WHERE id = %d LIMIT 1",
			$_ah_news_id
		) );
	}

	if ( $_nb_item ) {
		wp_enqueue_style( 'adn-single-style', get_template_directory_uri() . '/assets/css/single.css', array(), ADN_THEME_VERSION );
		$_nb_ctx               = adn_news_get_context();
		$_nb_ctx['breadcrumb'] = array(); // suppressed from adn_page_open; passed to hero directly below
		if ( isset( $_nb_ctx['meta'] ) ) {
			$_nb_ctx['meta']['title'] = isset( $_nb_item->text ) ? (string) $_nb_item->text : '';
		}
		if ( ! empty( $_nb_item->image_id ) ) {
			$_nb_ctx['hero']['image_id'] = (int) $_nb_item->image_id;
		}

		$_nb_stamp   = ! empty( $_nb_item->start_date ) ? $_nb_item->start_date : ( isset( $_nb_item->created_at ) ? $_nb_item->created_at : '' );
		$_nb_label   = isset( $_nb_item->label )   ? (string) $_nb_item->label   : '';
		$_nb_title   = isset( $_nb_item->text )    ? (string) $_nb_item->text    : '';
		$_nb_excerpt = isset( $_nb_item->excerpt ) ? (string) $_nb_item->excerpt : '';
		$_nb_content = isset( $_nb_item->content ) ? (string) $_nb_item->content : '';
		$_nb_url     = function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $_nb_item->id ) : '';

		$_nb_breadcrumb = array(
			array( 'label' => PAGE_TITLE_HOME, 'url' => home_url( '/' ) ),
			array( 'label' => SITE_NEWS_NOUN,  'url' => home_url( SITE_NEWS_URL ) ),
			array( 'label' => $_nb_title,      'url' => null ),
		);

		// Related news: other active items excluding current.
		$_nb_related = array();
		if ( function_exists( 'adn_cms_newsbar_items' ) && function_exists( 'adn_cms_gradient' ) ) {
			$_rni = 0;
			foreach ( adn_cms_newsbar_items( 10 ) as $_rn ) {
				if ( (int) $_rn->id === $_ah_news_id ) { continue; }
				if ( $_rni >= 4 ) { break; }
				$_rn_stamp     = ! empty( $_rn->start_date ) ? $_rn->start_date : ( isset( $_rn->created_at ) ? $_rn->created_at : '' );
				$_nb_related[] = array(
					'gradient' => adn_cms_gradient( $_rni ),
					'title'    => isset( $_rn->text ) ? (string) $_rn->text : '',
					'date'     => $_rn_stamp ? date_i18n( 'M j, Y', strtotime( $_rn_stamp ) ) : '',
					'tag'      => 'NEWS',
					'url'      => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $_rn->id ) : '',
				);
				$_rni++;
			}
		}

		adn_page_open( $_nb_ctx );
		?>

		<?php /* HERO */ ?>
		<?php if ( ! empty( $_nb_ctx['hero'] ) ) : ?>
			<?php adn_component( 'sections/page_hero', array(
				'hero'       => $_nb_ctx['hero'],
				'breadcrumb' => $_nb_breadcrumb,
			) ); ?>
		<?php endif; ?>

		<?php /* TWO-COLUMN LAYOUT */ ?>
		<section class="news-layout news-layout--single">

			<main class="news-main">
				<div class="news-single-article">

					<div class="news-single-meta">
						<?php if ( '' !== $_nb_label ) : ?>
							<span class="news-single-label"><?php echo esc_html( $_nb_label ); ?></span>
						<?php endif; ?>
					</div>

					<h1 class="news-single-title"><?php echo esc_html( $_nb_title ); ?></h1>

					<?php /* Content */ ?>
					<?php if ( '' !== $_nb_content ) : ?>
						<div class="news-single-content">
							<?php echo wp_kses_post( $_nb_content ); ?>
						</div>
					<?php endif; ?>

					<?php /* Source link - excerpt used as the description text */ ?>
					<?php if ( ! empty( $_nb_item->link_url ) ) : ?>
						<div class="news-single-source">
							<span class="news-single-source-label">
								<?php if ( '' !== $_nb_excerpt ) : ?>
									<?php echo esc_html( $_nb_excerpt ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Read the original article at the source:', ADN_TEXT_DOMAIN ); ?>
								<?php endif; ?>
							</span>
							<a href="<?php echo esc_url( (string) $_nb_item->link_url ); ?>"
							   class="btn btn-outline"
							   target="<?php echo esc_attr( ! empty( $_nb_item->link_target ) ? (string) $_nb_item->link_target : '_blank' ); ?>"
							   rel="noopener noreferrer">
								<?php esc_html_e( 'Read Source', ADN_TEXT_DOMAIN ); ?> <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
							</a>
						</div>
					<?php endif; ?>

					<?php /* Share bar - after content */ ?>
					<?php if ( $_nb_url ) : ?>
						<?php adn_component( 'sections/post_feedback', array(
							'share'        => array( 'url' => $_nb_url, 'title' => $_nb_title ),
							'hide_helpful' => true,
						) ); ?>
					<?php endif; ?>

					<?php /* More from us */ ?>
					<!-- <?php if ( ! empty( $_nb_related ) ) : ?>
					<div class="news-single-related">
						<h2 class="nsr-heading"><?php esc_html_e( 'More News', ADN_TEXT_DOMAIN ); ?></h2>
						<div class="nsr-grid">
							<?php foreach ( $_nb_related as $_ritem ) : ?>
								<?php adn_component( 'cards/news_item', array( 'item' => $_ritem ) ); ?>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?> -->

				</div>
			</main>

			<aside class="news-sidebar">

				<?php /* Browse topics */ ?>
				<?php if ( ! empty( $_nb_ctx['sidebar']['topics'] ) ) : ?>
					<?php adn_component( 'parts/sidebar_link_list', array( 'list' => array(
						'heading' => __( 'Browse Topics', ADN_TEXT_DOMAIN ),
						'items'   => $_nb_ctx['sidebar']['topics'],
					) ) ); ?>
				<?php endif; ?>

				<?php /* Newsletter */ ?>
				<?php if ( ! empty( $_nb_ctx['sidebar']['newsletter'] ) ) : ?>
					<?php adn_component( 'parts/sidebar_newsletter_signup', array( 'newsletter' => $_nb_ctx['sidebar']['newsletter'] ) ); ?>
				<?php endif; ?>

			</aside>

		</section>

		<?php /* Bottom newsletter banner */ ?>
		<?php if ( ! empty( $_nb_ctx['bottom_newsletter'] ) ) : ?>
		<section class="newsletter-cta">
			<div class="container">
				<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $_nb_ctx['bottom_newsletter'] ) ); ?>
			</div>
		</section>
		<?php endif; ?>

		<?php
		adn_page_close( $_nb_ctx );
		return;
	}
}

/* ── Default: news listing view ─────────────────────────────────────────── */
$ctx = adn_news_get_context();

$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php if ( ! empty( $ctx['hero'] ) ) : ?>
	<?php adn_component( 'sections/page_hero', array(
		'hero'       => $ctx['hero'],
		'breadcrumb' => $ctx['breadcrumb'],
	) ); ?>
<?php endif; ?>

<?php /* ============================== CATEGORY TABS ============================== */ ?>
<?php if ( ! empty( $ctx['categories'] ) ) : ?>
	<?php adn_component( 'sections/news_cats_strip', array( 'categories' => $ctx['categories'] ) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN LAYOUT ============================== */ ?>
<div class="news-layout">

	<main class="news-main" id="newsMain">

			<?php if ( ! empty( $ctx['featured'] ) || ! empty( $ctx['sections'] ) ) : ?>
				<?php /* FEATURED */ ?>
				<?php if ( ! empty( $ctx['featured'] ) ) : ?>
					<?php adn_component( 'sections/news_featured', array( 'featured' => $ctx['featured'] ) ); ?>
				<?php endif; ?>

				<?php /* SECTIONS (grid / list) */ ?>
				<?php if ( ! empty( $ctx['sections'] ) ) : ?>
					<?php foreach ( $ctx['sections'] as $sec ) : ?>
						<?php adn_component( 'sections/news_section', array( 'section' => $sec ) ); ?>
					<?php endforeach; ?>
				<?php endif; ?>

				<div class="load-more-wrap">
					<button class="load-more-btn" id="loadMoreBtn" type="button">
						<?php echo esc_html( SITE_BTN_LOAD_MORE ); ?>
					</button>
				</div>
			<?php else : ?>
				<div class="news-empty">
					<p class="muted"><?php esc_html_e( 'No news available yet. Please check back soon.', ADN_TEXT_DOMAIN ); ?></p>
				</div>
			<?php endif; ?>

	</main>

	<aside class="news-sidebar">

		<?php /* Browse topics */ ?>
		<?php if ( ! empty( $ctx['sidebar']['topics'] ) ) : ?>
			<?php adn_component( 'parts/sidebar_link_list', array( 'list' => array(
				'heading' => __( 'Browse Topics', ADN_TEXT_DOMAIN ),
				'items'   => $ctx['sidebar']['topics'],
			) ) ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $ctx['sidebar']['newsletter'] ) ) : ?>
			<?php adn_component( 'parts/sidebar_newsletter_signup', array( 'newsletter' => $ctx['sidebar']['newsletter'] ) ); ?>
		<?php endif; ?>

	</aside>

</div>

<?php /* ============================== BOTTOM NEWSLETTER BANNER ============================== */ ?>
<?php if ( ! empty( $ctx['bottom_newsletter'] ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['bottom_newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
