<?php
/**
 * Template Name: Category Guide
 *
 * pages/page-category_guide.php - Generic category landing page.
 *
 * Handles any slug-based category (buying, selling, house-movers…).
 * The page slug drives which JSON is loaded via the service layer, so
 * no content is hardcoded here.  When the plugin is ready to take over,
 * replace adn_service_category_data() internals - nothing else changes.
 *
 * Architecture:
 *   data/json/{slug}.json
 *     → apis/services.php  adn_service_category_data($slug)
 *       → intermediate/page_category_logical.php  adn_category_get_context()
 *         → THIS FILE  (structure only)
 *           → components/sections/*  components/parts/*  components/cards/*
 *
 * RULE: No hardcoded content and no data reads here - only structure.
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_category_logical.php';
$ctx = adn_category_get_context();

if ( ! empty( $ctx['faqs']['items'] ) ) {
	wp_enqueue_style( 'adn-page-faqs-style', get_template_directory_uri() . '/assets/css/faqs.css', array(), ADN_THEME_VERSION );
	wp_enqueue_script( 'adn-page-faqs-script', get_template_directory_uri() . '/assets/js/faqs.js', array(), ADN_THEME_VERSION, true );
}

// Breadcrumb renders inside the hero banner - skip it from adn_page_open().
$_open_ctx                = $ctx;
$_open_ctx['breadcrumb']  = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php adn_component( 'sections/page_hero', array(
	'hero'       => $ctx['hero'],
	'breadcrumb' => $ctx['breadcrumb'],
) ); ?>

<?php /* ============================== JOURNEY STEPS ============================== */ ?>
<?php if ( ! empty( $ctx['journey'] ) ) : ?>
<section class="category-journey">
	<div class="container">
		<?php adn_component( 'sections/category_journey', array( 'journey' => $ctx['journey'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== MAIN + SIDEBAR ============================== */ ?>
<div class="container">
	<div class="page-with-sidebar">

		<main class="cat-guide-main">

			<?php /* ── Guides Carousel ── */ ?>
			<?php if ( ! empty( $ctx['guides']['items'] ) ) : ?>
			<div class="category-section category-guides">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['guides']['heading'] ) ? $ctx['guides']['heading'] : array(),
					'tag'     => 'h2',
				) );
				adn_component( 'sections/guides', array( 'items' => $ctx['guides']['items'] ) );
				?>
			</div>
			<?php endif; ?>

			<?php /* ── Popular Posts (admin-curated) ── */ ?>
			<?php if ( ! empty( $ctx['popular_posts']['items'] ) ) : ?>
			<div class="category-section category-popular">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['popular_posts']['heading'] ) ? $ctx['popular_posts']['heading'] : array(),
					'tag'     => 'h2',
				) );
				adn_component( 'sections/guides', array( 'items' => $ctx['popular_posts']['items'] ) );
				?>
			</div>
			<?php endif; ?>

			<?php /* ── Latest News + Regulations (side by side) ── */ ?>
			<?php $_has_news = ! empty( $ctx['news']['items'] ); $_has_regs = ! empty( $ctx['regulations']['items'] ); ?>
			<?php if ( $_has_news || $_has_regs ) : ?>
			<div class="category-section category-news-regs">
				<div class="cat-news-regs-grid">

					<?php if ( $_has_news ) : ?>
					<div class="cat-news-col">
						<?php adn_component( 'parts/news_widget', array( 'widget' => array(
							'heading' => $ctx['news']['heading'],
							'items'   => $ctx['news']['items'],
						) ) ); ?>
					</div>
					<?php endif; ?>

					<?php if ( $_has_regs ) : ?>
					<div class="cat-regs-col mini_card_container_design">
						<?php
						adn_component( 'parts/section_headers/section_header', array(
							'heading' => isset( $ctx['regulations']['heading'] ) ? $ctx['regulations']['heading'] : array(),
							'tag'     => 'h3',
						) );
						foreach ( (array) $ctx['regulations']['items'] as $item ) :
							adn_component( 'cards/regulation_item', array( 'item' => $item ) );
						endforeach;
						?>
					</div>
					<?php endif; ?>

				</div>
			</div>
			<?php endif; ?>

			<?php /* ── Tools ── */ ?>
			<?php if ( ! empty( $ctx['calculators']['items'] ) ) : ?>
			<div class="category-section category-tools">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['calculators']['heading'] ) ? $ctx['calculators']['heading'] : array(),
					'tag'     => 'h3',
				) );
				?>
				<div class="tool-grid tool-grid--7col">
					<?php foreach ( (array) $ctx['calculators']['items'] as $card ) : ?>
						<?php adn_component( 'cards/tool_card', array( 'card' => $card ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php /* ── Resources (PDFs / Links / Videos) ── */ ?>
			<?php
			$_res = isset( $ctx['resources'] ) ? $ctx['resources'] : array();

			// Pre-filter: only items with real data.
			$_pdfs  = array_filter( (array) ( $_res['pdfs']   ?? array() ), function( $p ) { return ! empty( $p['file_url'] ) && ! empty( $p['title'] ); } );
			$_links = array_filter( (array) ( $_res['links']  ?? array() ), function( $l ) { return ! empty( $l['title'] ); } );
			$_vids  = array_filter( (array) ( $_res['videos'] ?? array() ), function( $v ) { return ! empty( $v['url'] ); } );

			if ( $_pdfs || $_links || $_vids ) :
			?>
			<div class="category-section category-resources">

				<?php adn_component( 'parts/section_headers/section_header', array(
					'heading' => array( 'title' => esc_html__( 'Useful Resources', ADN_TEXT_DOMAIN ), 'link_label' => '', 'link_url' => '' ),
					'tag'     => 'h3',
				) ); ?>

				<?php /* ── PDFs ── */ ?>
				<?php if ( $_pdfs ) : ?>
				<div class="res-subsection">
					<p class="res-sub-label"><?php esc_html_e( 'PDF Documents', ADN_TEXT_DOMAIN ); ?></p>
					<div class="res-grid">
						<?php foreach ( $_pdfs as $pdf ) : ?>
						<a href="<?php echo esc_url( $pdf['file_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="res-card">
							<div class="res-card-icon res-icon--pdf">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
							</div>
							<div class="res-card-body">
								<strong class="res-card-title"><?php echo esc_html( $pdf['title'] ); ?></strong>
								<?php if ( ! empty( $pdf['desc'] ) ) : ?>
								<p class="res-card-desc"><?php echo esc_html( $pdf['desc'] ); ?></p>
								<?php endif; ?>
							</div>
							<span class="res-card-cta"><?php esc_html_e( 'Download', ADN_TEXT_DOMAIN ); ?> ↓</span>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<?php /* ── External Links ── */ ?>
				<?php if ( $_links ) : ?>
				<div class="res-subsection">
					<p class="res-sub-label"><?php esc_html_e( 'External Links', ADN_TEXT_DOMAIN ); ?></p>
					<div class="res-grid">
						<?php foreach ( $_links as $lnk ) :
							$_raw_icon = isset( $lnk['icon'] ) ? trim( $lnk['icon'] ) : '';
						?>
						<a href="<?php echo esc_url( $lnk['url'] ); ?>" target="_blank" rel="noopener noreferrer" class="res-card">
							<div class="res-card-icon res-icon--link">
								<?php if ( '' !== $_raw_icon ) : ?>
									<span class="res-icon-emoji"><?php echo esc_html( $_raw_icon ); ?></span>
								<?php else : ?>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
								<?php endif; ?>
							</div>
							<div class="res-card-body">
								<strong class="res-card-title"><?php echo esc_html( $lnk['title'] ); ?></strong>
								<?php if ( ! empty( $lnk['desc'] ) ) : ?>
								<p class="res-card-desc"><?php echo esc_html( $lnk['desc'] ); ?></p>
								<?php endif; ?>
							</div>
							<span class="res-card-cta"><?php esc_html_e( 'Visit', ADN_TEXT_DOMAIN ); ?> ↗</span>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<?php /* ── YouTube Videos - embedded player only ── */ ?>
				<?php if ( $_vids ) : ?>
				<div class="res-subsection">
					<p class="res-sub-label"><?php esc_html_e( 'Videos', ADN_TEXT_DOMAIN ); ?></p>
					<div class="res-grid res-grid-youtube">
						<?php foreach ( $_vids as $vid ) : ?>
						<div class="res-video-item">
							<div class="res-video-embed ">
								<iframe
									src="<?php echo esc_url( 'https://www.youtube.com/embed/' . $vid['vid_id'] . '?rel=0&modestbranding=1' ); ?>"
									title="<?php echo esc_attr( $vid['title'] ); ?>"
									loading="lazy"
									allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
									allowfullscreen></iframe>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

			</div>
			<?php endif; ?>

		</main>

		<aside class="sidebar-col">

			<?php /* ── Quick Tools / Related Calculators (dark card) ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['quick_tools'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_quick_tools', array( 'quick_tools' => $ctx['sidebar']['quick_tools'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Hot Topics ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['hot_topics'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_hot_topics', array( 'hot_topics' => $ctx['sidebar']['hot_topics'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Featured Topics ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['featured_topics'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_featured_topics', array( 'featured_topics' => $ctx['sidebar']['featured_topics'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Expert Help / Need Help From a Professional? ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['expert_help'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $ctx['sidebar']['expert_help'] ) ); ?>
			<?php endif; ?>

		</aside>

	</div>
</div>

<?php /* ============================== FAQs ============================== */ ?>
<?php if ( ! empty( $ctx['faqs']['items'] ) ) : ?>
<div class="section-faqs">
	<div class="faqs-main">
		<?php if ( ! empty( $ctx['faqs']['heading'] ) ) : ?>
			<h2 class="faqs-section-heading"><?php echo esc_html( $ctx['faqs']['heading'] ); ?></h2>
		<?php endif; ?>
		<div class="faqs-list">
			<?php foreach ( $ctx['faqs']['items'] as $_faq ) :
				$_fq  = is_array( $_faq ) ? (string) ( $_faq['question']  ?? '' ) : (string) ( $_faq->question  ?? '' );
				$_fa  = is_array( $_faq ) ? (string) ( $_faq['answer']    ?? '' ) : (string) ( $_faq->answer    ?? '' );
				$_flu = is_array( $_faq ) ? (string) ( $_faq['link_url']  ?? '' ) : (string) ( $_faq->link_url  ?? '' );
				$_flt = is_array( $_faq ) ? (string) ( $_faq['link_text'] ?? '' ) : (string) ( $_faq->link_text ?? '' );
				if ( '' === trim( $_fq ) ) { continue; }
			?>
				<details class="faq-item">
					<summary class="faq-q">
						<span class="faq-q-text"><?php echo esc_html( $_fq ); ?></span>
					</summary>
					<div class="faq-a">
						<?php if ( '' !== trim( $_fa ) ) : ?>
							<div class="faq-a-body"><?php echo wp_kses_post( wpautop( wp_trim_words( $_fa, 500, '' ) ) ); ?></div>
						<?php endif; ?>
						<?php if ( '' !== trim( $_flu ) ) : ?>
							<p class="faq-link"><a href="<?php echo esc_url( adn_link( $_flu ) ); ?>"><?php echo esc_html( $_flt ?: $_flu ); ?></a></p>
						<?php endif; ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<?php /* ============================== PERSONALISED GUIDANCE CTA ============================== */ ?>
<?php if ( ! empty( $ctx['cta_banner'] ) ) : ?>
<div class="">
	<?php adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) ); ?>
</div>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
