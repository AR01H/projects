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
		$_nb_ctx               = adn_news_get_context();
		$_nb_ctx['breadcrumb'] = array();
		if ( isset( $_nb_ctx['meta'] ) ) {
			$_nb_ctx['meta']['title'] = isset( $_nb_item->text ) ? (string) $_nb_item->text : '';
		}

		$_nb_stamp   = ! empty( $_nb_item->start_date ) ? $_nb_item->start_date : ( isset( $_nb_item->created_at ) ? $_nb_item->created_at : '' );
		$_nb_title   = isset( $_nb_item->text ) ? (string) $_nb_item->text : '';
		$_nb_content = isset( $_nb_item->content ) ? (string) $_nb_item->content : '';
		$_nb_url     = function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $_nb_item->id ) : '';

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
				'breadcrumb' => array(),
			) ); ?>
		<?php endif; ?>

		<?php /* TWO-COLUMN LAYOUT */ ?>
		<div class="news-layout news-layout--single">

			<main class="news-main">
				<div class="news-single-article">

					<a href="<?php echo esc_url( trailingslashit( home_url( SITE_NEWS_URL ) ) ); ?>" class="news-single-back">
						&#8592; <?php esc_html_e( 'Back to News', ADN_TEXT_DOMAIN ); ?>
					</a>

					<div class="news-single-meta">
						<span class="news-single-tag"><?php echo esc_html( SITE_NEWS_NOUN ); ?></span>
						<?php if ( $_nb_stamp ) : ?>
							<span class="news-single-date"><?php echo esc_html( date_i18n( 'F j, Y', strtotime( $_nb_stamp ) ) ); ?></span>
						<?php endif; ?>
					</div>

					<h1 class="news-single-title"><?php echo esc_html( $_nb_title ); ?></h1>

					<?php /* Share buttons */ ?>
					<?php if ( $_nb_url ) : ?>
					<div class="news-single-share">
						<span class="nss-label"><?php esc_html_e( 'Share:', ADN_TEXT_DOMAIN ); ?></span>
						<button type="button" class="nss-btn" id="nssShare" data-title="<?php echo esc_attr( $_nb_title ); ?>" data-url="<?php echo esc_attr( $_nb_url ); ?>" aria-label="<?php esc_attr_e( 'Share this article', ADN_TEXT_DOMAIN ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
							<?php esc_html_e( 'Share', ADN_TEXT_DOMAIN ); ?>
						</button>
						<a href="<?php echo esc_url( 'https://api.whatsapp.com/send?text=' . rawurlencode( $_nb_title . ' ' . $_nb_url ) ); ?>" target="_blank" rel="noopener noreferrer" class="nss-btn nss-btn--whatsapp" aria-label="Share on WhatsApp">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A12 12 0 0 0 3.48 20.52L2 22l1.52-5.48A12 12 0 1 0 20.52 3.48zm-8.52 18a10 10 0 0 1-5.38-1.56l-.38-.23-3.94 1.03 1.05-3.84-.25-.4A10 10 0 1 1 12 21zm5.5-7.5c-.3-.15-1.77-.87-2.04-.97s-.47-.15-.67.15-.77.97-.94 1.17-.35.22-.65.07a8.13 8.13 0 0 1-2.38-1.47 8.93 8.93 0 0 1-1.65-2.05c-.17-.3 0-.46.13-.6s.3-.35.45-.52a2 2 0 0 0 .3-.5.55.55 0 0 0-.02-.52c-.07-.15-.67-1.62-.92-2.22s-.48-.5-.67-.51h-.57a1.1 1.1 0 0 0-.8.37 3.37 3.37 0 0 0-1.05 2.5 5.85 5.85 0 0 0 1.22 3.1c.15.2 2.12 3.23 5.14 4.53a17.32 17.32 0 0 0 1.72.63 4.13 4.13 0 0 0 1.9.12c.58-.09 1.77-.72 2.02-1.42s.25-1.29.17-1.42-.27-.2-.57-.35z"/></svg>
							WhatsApp
						</a>
						<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?text=' . rawurlencode( $_nb_title ) . '&url=' . rawurlencode( $_nb_url ) ); ?>" target="_blank" rel="noopener noreferrer" class="nss-btn nss-btn--twitter" aria-label="Share on X / Twitter">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.736-8.846L2 2.25h7.19l4.26 5.634L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>
							X
						</a>
						<button type="button" class="nss-btn" id="nssCopy" data-url="<?php echo esc_attr( $_nb_url ); ?>" aria-label="<?php esc_attr_e( 'Copy link', ADN_TEXT_DOMAIN ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
							<?php esc_html_e( 'Copy Link', ADN_TEXT_DOMAIN ); ?>
						</button>
					</div>
					<?php endif; ?>

					<?php /* Content */ ?>
					<?php if ( '' !== $_nb_content ) : ?>
						<div class="news-single-content">
							<?php echo wp_kses_post( $_nb_content ); ?>
						</div>
					<?php endif; ?>

					<?php /* Source link */ ?>
					<?php if ( ! empty( $_nb_item->link_url ) ) : ?>
						<div class="news-single-source">
							<span class="news-single-source-label"><?php esc_html_e( 'Read the original article at the source:', ADN_TEXT_DOMAIN ); ?></span>
							<a href="<?php echo esc_url( (string) $_nb_item->link_url ); ?>"
							   class="btn btn-outline"
							   target="<?php echo esc_attr( ! empty( $_nb_item->link_target ) ? (string) $_nb_item->link_target : '_blank' ); ?>"
							   rel="noopener noreferrer">
								<?php esc_html_e( 'Read Source ↗', ADN_TEXT_DOMAIN ); ?>
							</a>
						</div>
					<?php endif; ?>

					<?php /* More from us */ ?>
					<?php if ( ! empty( $_nb_related ) ) : ?>
					<div class="news-single-related">
						<h2 class="nsr-heading"><?php esc_html_e( 'More News', ADN_TEXT_DOMAIN ); ?></h2>
						<div class="nsr-grid">
							<?php foreach ( $_nb_related as $_ritem ) : ?>
								<?php adn_component( 'cards/news_item', array( 'item' => $_ritem ) ); ?>
							<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>

				</div>
			</main>

			<aside class="news-sidebar">

				<?php /* Related news in sidebar */ ?>
				<?php if ( ! empty( $_nb_related ) ) : ?>
					<?php adn_component( 'parts/sidebar_news_mini', array( 'news_mini' => array(
						'heading'  => __( 'More News', ADN_TEXT_DOMAIN ),
						'items'    => $_nb_related,
						'view_all' => array( 'label' => 'All ' . SITE_NEWS_NOUN . ' →', 'url' => SITE_NEWS_URL ),
					) ) ); ?>
				<?php endif; ?>

				<?php /* Browse categories */ ?>
				<?php if ( ! empty( $_nb_ctx['categories'] ) ) : ?>
					<?php adn_component( 'parts/sidebar_browse_cats', array( 'categories' => $_nb_ctx['categories'] ) ); ?>
				<?php endif; ?>

				<?php /* Newsletter */ ?>
				<?php if ( ! empty( $_nb_ctx['sidebar']['newsletter'] ) ) : ?>
					<?php adn_component( 'parts/sidebar_newsletter_signup', array( 'newsletter' => $_nb_ctx['sidebar']['newsletter'] ) ); ?>
				<?php endif; ?>

			</aside>

		</div>

		<?php /* Bottom newsletter banner */ ?>
		<?php if ( ! empty( $_nb_ctx['bottom_newsletter'] ) ) : ?>
		<section class="newsletter-cta">
			<div class="container">
				<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $_nb_ctx['bottom_newsletter'] ) ); ?>
			</div>
		</section>
		<?php endif; ?>

		<script>
		(function () {
			var shareBtn = document.getElementById('nssShare');
			var copyBtn  = document.getElementById('nssCopy');
			if (shareBtn) {
				shareBtn.addEventListener('click', function () {
					var t = shareBtn.dataset.title || document.title;
					var u = shareBtn.dataset.url   || location.href;
					if (navigator.share) {
						navigator.share({ title: t, url: u });
					} else if (copyBtn) {
						copyBtn.click();
					}
				});
			}
			if (copyBtn) {
				copyBtn.addEventListener('click', function () {
					var u = copyBtn.dataset.url || location.href;
					navigator.clipboard.writeText(u).then(function () {
						var orig = copyBtn.innerHTML;
						copyBtn.textContent = '✓ Copied!';
						setTimeout(function () { copyBtn.innerHTML = orig; }, 2000);
					});
				});
			}
		})();
		</script>

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
				<?php echo esc_html__( 'Load More Stories', ADN_TEXT_DOMAIN ); ?>
			</button>
		</div>

	</main>

	<aside class="news-sidebar">
		<?php if ( ! empty( $ctx['categories'] ) ) : ?>
			<?php adn_component( 'parts/sidebar_browse_cats', array( 'categories' => $ctx['categories'] ) ); ?>
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
