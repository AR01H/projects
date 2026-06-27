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

// Enqueue the buying page design CSS when viewing the 'buying' parent-term.
if ( isset( $ctx['slug'] ) && 'buying' === $ctx['slug'] ) {
	wp_enqueue_style( 'adn-buying-design', get_template_directory_uri() . '/assets/css/buying-design.css', array(), ADN_THEME_VERSION );
}

if ( ! empty( $ctx['faqs']['items'] ) ) {
	wp_enqueue_style( 'adn-page-faqs-style', get_template_directory_uri() . '/assets/css/faqs.css', array(), ADN_THEME_VERSION );
	wp_enqueue_script( 'adn-page-faqs-script', get_template_directory_uri() . '/assets/js/faqs.js', array(), ADN_THEME_VERSION, true );
}
wp_enqueue_style( 'adn-resources', get_template_directory_uri() . '/assets/css/resources.css', array(), ADN_THEME_VERSION );

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

<?php /* ============================== CONTROL CENTRE ============================== */ ?>
<?php if ( ! empty( $ctx['journey'] ) || ! empty( $ctx['spotlights']['terms'] ) ) : ?>
	<?php adn_component( 'sections/category_control_center', array(
		'journey'    => $ctx['journey'],
		'spotlights' => isset( $ctx['spotlights'] ) ? $ctx['spotlights'] : array(),
	) ); ?>
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

			<?php /* ── Tools ── */ ?>
			<?php if ( ! empty( $ctx['calculators']['items'] ) ) : ?>
			<div class="category-section category-tools">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['calculators']['heading'] ) ? $ctx['calculators']['heading'] : array(),
					'tag'     => 'h3',
				) );
				?>
				<div class="tool-grid">
					<?php foreach ( (array) $ctx['calculators']['items'] as $card ) : ?>
						<?php adn_component( 'cards/tool_card', array( 'card' => $card ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php /* ── Resources (library items) ── */ ?>
			<?php if ( ! empty( $ctx['resources']['items'] ) ) : ?>
			<div class="category-section">
				<?php adn_component( 'sections/category_resources', array( 'resources' => $ctx['resources'] ) ); ?>
			</div>
			<?php endif; ?>

		</main>

		<aside class="sidebar-col">

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

		</aside>

	</div>
</div>

<?php /* ============================== CTA BANNER (interruption after guides) ============================== */ ?>
<?php if ( ! empty( $ctx['cta_banner']['title'] ) ) : ?>
<div class="cat-cta-wrap cat-cta-wrap--inline">
	<?php adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) ); ?>
</div>
<?php endif; ?>

<?php /* ============================== LATEST NEWS + UPDATES (full-width) ============================== */ ?>
<?php if ( ! empty( $ctx['news']['items'] ) || ! empty( $ctx['regulations']['items'] ) ) : ?>
<section class="news-three-col">
	<div class="container">
		<?php adn_component( 'sections/news_three_col', array(
			'news'        => $ctx['news'],
			'regulations' => $ctx['regulations'],
		) ); ?>
	</div>
</section>
<?php endif; ?>

<?php /* ============================== FAQs ============================== */ ?>
<?php if ( ! empty( $ctx['faqs']['items'] ) ) : ?>
<div class="section-faqs">
	<?php adn_component( 'parts/faq_list', array(
		'faqs'    => $ctx['faqs']['items'],
		'heading' => $ctx['faqs']['heading'] ?? '',
	) ); ?>
</div>
<?php endif; ?>

<?php /* ============================== NEWSLETTER ============================== */ ?>
<?php if ( ! empty( $ctx['newsletter'] ) ) : ?>
<section class="newsletter-cta">
	<div class="container">
		<?php adn_component( 'sections/newsletter_cta', array( 'newsletter' => $ctx['newsletter'] ) ); ?>
	</div>
</section>
<?php endif; ?>

<?php
$_fi_cat_d   = isset( $ctx['slug'] ) ? AH_Category_Settings::get_all( sanitize_key( $ctx['slug'] ) ) : array();
$_fi_cat_sec = ( isset( $_fi_cat_d['featured_in']['section'] ) && '' !== $_fi_cat_d['featured_in']['section'] )
	? sanitize_key( $_fi_cat_d['featured_in']['section'] ) : '';
adn_component( 'parts/featured_in', array( 'section' => $_fi_cat_sec ) );
?>

<?php adn_page_close( $ctx ); ?>
