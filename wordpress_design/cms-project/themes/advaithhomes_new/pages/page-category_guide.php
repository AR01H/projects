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
<section class="buying-journey">
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
			<section class="category-section category-guides">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['guides']['heading'] ) ? $ctx['guides']['heading'] : array(),
					'tag'     => 'h2',
				) );
				adn_component( 'sections/guides', array( 'items' => $ctx['guides']['items'] ) );
				?>
			</section>
			<?php endif; ?>

			<?php /* ── Popular Posts (admin-curated) ── */ ?>
			<?php if ( ! empty( $ctx['popular_posts']['items'] ) ) : ?>
			<section class="category-section category-popular">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['popular_posts']['heading'] ) ? $ctx['popular_posts']['heading'] : array(),
					'tag'     => 'h2',
				) );
				adn_component( 'sections/guides', array( 'items' => $ctx['popular_posts']['items'] ) );
				?>
			</section>
			<?php endif; ?>

			<?php /* ── Latest News + Regulations (side by side) ── */ ?>
			<?php $_has_news = ! empty( $ctx['news']['items'] ); $_has_regs = ! empty( $ctx['regulations']['items'] ); ?>
			<?php if ( $_has_news || $_has_regs ) : ?>
			<section class="category-section category-news-regs">
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
			</section>
			<?php endif; ?>

			<?php /* ── Calculators ── */ ?>
			<?php if ( ! empty( $ctx['calculators']['items'] ) ) : ?>
			<section class="category-section category-calculators">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['calculators']['heading'] ) ? $ctx['calculators']['heading'] : array(),
					'tag'     => 'h3',
				) );
				?>
				<div class="calc-grid calc-grid--7col">
					<?php foreach ( (array) $ctx['calculators']['items'] as $card ) : ?>
						<?php adn_component( 'cards/calc_card', array( 'card' => $card ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
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

			<?php /* ── External Links ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['external_links'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_external_links', array( 'external_links' => $ctx['sidebar']['external_links'] ) ); ?>
			<?php endif; ?>

			<?php /* ── Expert Help / Need Help From a Professional? ── */ ?>
			<?php if ( ! empty( $ctx['sidebar']['expert_help'] ) ) : ?>
				<?php adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $ctx['sidebar']['expert_help'] ) ); ?>
			<?php endif; ?>

		</aside>

	</div>
</div>

<?php /* ============================== PERSONALISED GUIDANCE CTA ============================== */ ?>
<?php if ( ! empty( $ctx['cta_banner'] ) ) : ?>
<div class="container">
	<?php adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) ); ?>
</div>
<?php endif; ?>

<?php adn_page_close( $ctx ); ?>
