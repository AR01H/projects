<?php
/**
 * Template Name: Category Guide
 *
 * pages/page-category_guide.php — Generic category landing page.
 *
 * Handles any slug-based category (buying, selling, house-movers…).
 * The page slug drives which JSON is loaded via the service layer, so
 * no content is hardcoded here.  When the plugin is ready to take over,
 * replace adn_service_category_data() internals — nothing else changes.
 *
 * Architecture:
 *   data/json/{slug}.json
 *     → apis/services.php  adn_service_category_data($slug)
 *       → intermediate/page_category_logical.php  adn_category_get_context()
 *         → THIS FILE  (structure only)
 *           → components/sections/*  components/parts/*  components/cards/*
 *
 * RULE: No hardcoded content and no data reads here — only structure.
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_category_logical.php';
$ctx = adn_category_get_context();

adn_page_open( $ctx );
?>

<?php /* ============================== CATEGORY HERO ============================== */ ?>
<section class="category-hero">
	<?php adn_component( 'sections/category_hero', array( 'hero' => $ctx['hero'] ) ); ?>
</section>

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

		<main style="display:grid;gap:10px;">

			<?php /* ── Guides Grid ── */ ?>
			<?php if ( ! empty( $ctx['guides']['items'] ) ) : ?>
			<section class="category-section category-guides">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['guides']['heading'] ) ? $ctx['guides']['heading'] : array(),
					'tag'     => 'h2',
				) );
				?>
				<div class="guides-grid guides-grid--5col">
					<?php foreach ( (array) $ctx['guides']['items'] as $card ) : ?>
						<?php adn_component( 'cards/guide_card', array( 'card' => $card ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endif; ?>

			<?php /* ── Latest News ── */ ?>
			<?php if ( ! empty( $ctx['news']['items'] ) ) : ?>
			<section class="category-section category-news mini_card_container_design">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['news']['heading'] ) ? $ctx['news']['heading'] : array(),
					'tag'     => 'h3',
				) );
				foreach ( (array) $ctx['news']['items'] as $item ) :
					adn_component( 'cards/news_item', array( 'item' => $item ) );
				endforeach;
				?>
			</section>
			<?php endif; ?>

			<?php /* ── Latest Regulations ── */ ?>
			<?php if ( ! empty( $ctx['regulations']['items'] ) ) : ?>
			<section class="category-section category-regulations mini_card_container_design">
				<?php
				adn_component( 'parts/section_headers/section_header', array(
					'heading' => isset( $ctx['regulations']['heading'] ) ? $ctx['regulations']['heading'] : array(),
					'tag'     => 'h3',
				) );
				foreach ( (array) $ctx['regulations']['items'] as $item ) :
					adn_component( 'cards/regulation_item', array( 'item' => $item ) );
				endforeach;
				?>
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
			<?php
			if ( ! empty( $ctx['sidebar']['quick_tools'] ) ) :
				adn_component( 'parts/sidebar_quick_tools', array( 'quick_tools' => $ctx['sidebar']['quick_tools'] ) );
			endif;
			if ( ! empty( $ctx['sidebar']['hot_topics'] ) ) :
				adn_component( 'parts/sidebar_hot_topics', array( 'hot_topics' => $ctx['sidebar']['hot_topics'] ) );
			endif;
			if ( ! empty( $ctx['sidebar']['expert_help'] ) ) :
				adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $ctx['sidebar']['expert_help'] ) );
			endif;
			?>
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
