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
			<?php if ( ! empty( $ctx['resources'] ) ) : ?>
			<div class="category-section">
				<?php adn_component( 'sections/category_resources', array( 'resources' => $ctx['resources'] ) ); ?>
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

			<?php /* ── Quick Links ── */ ?>
			<?php
			$_cat_all  = class_exists( 'AH_Category_Settings' ) ? AH_Category_Settings::get_all( $ctx['slug'] ) : array();
			$_ql_d     = isset( $_cat_all['quick_links'] ) && is_array( $_cat_all['quick_links'] ) ? $_cat_all['quick_links'] : array();
			if ( ! empty( $_ql_d['items'] ) ) :
				adn_component( 'parts/quick_links_widget', array( 'quick_links' => $_ql_d ) );
			endif; ?>

			<?php /* ── Spotlights (stacked, sidebar-native) ── */ ?>
			<?php
			$_cat_sp_all   = $_cat_all;
			$_cat_sp_terms = isset( $_cat_sp_all['spotlights']['terms'] ) && is_array( $_cat_sp_all['spotlights']['terms'] )
				? array_filter( array_map( 'sanitize_key', $_cat_sp_all['spotlights']['terms'] ) )
				: array();
			foreach ( $_cat_sp_terms as $_cat_sp_slug ) :
				adn_component( 'parts/spotlights_widget', array( 'term_slug' => $_cat_sp_slug ) );
			endforeach;
			?>

		</aside>

	</div>
</div>

<?php /* ============================== FAQs ============================== */ ?>
<?php if ( ! empty( $ctx['faqs']['items'] ) ) : ?>
<div class="section-faqs container">
	<?php adn_component( 'parts/faq_list', array(
		'faqs'    => $ctx['faqs']['items'],
		'heading' => $ctx['faqs']['heading'] ?? '',
	) ); ?>
</div>
<?php endif; ?>

<?php /* ============================== CTA BANNER ============================== */ ?>

<?php /* ============================== NEWSLETTER ============================== */ ?>
<?php if ( ! empty( $ctx['newsletter'] ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
