<?php
/**
 * Template Name: Ask an Expert
 *
 * pages/page-ask-expert.php — Expert directory listing page.
 * Category filter tabs + expert cards grid + sidebar.
 *
 * Architecture:
 *   data/json/ask-expert.json
 *     → apis/services.php  adn_service_ask_expert_data()
 *       → intermediate/page_ask_expert_logical.php  adn_ask_expert_get_context()
 *         → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here — only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_ask_expert_logical.php';
$ctx = adn_ask_expert_get_context();

get_header();
?>

<?php adn_component( 'parts/main_header', array( 'chrome' => $ctx['chrome'] ) ); ?>

<?php /* ============================== BREADCRUMB ============================== */ ?>
<?php if ( ! empty( $ctx['breadcrumb'] ) ) : ?>
	<?php adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) ); ?>
<?php endif; ?>

<?php /* ============================== HERO + STATS BAR ============================== */ ?>
<?php adn_component( 'sections/expert_hero', array(
	'hero'  => $ctx['hero'],
	'stats' => $ctx['stats'],
) ); ?>

<?php /* ============================== CATEGORY TABS STRIP ============================== */ ?>
<?php if ( ! empty( $ctx['categories'] ) ) : ?>
	<?php adn_component( 'sections/expert_cats_strip', array( 'categories' => $ctx['categories'] ) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN LAYOUT: CARDS + SIDEBAR ============================== */ ?>
<div class="expert-main-layout">

	<?php /* MAIN — expert cards */ ?>
	<main>
		<div class="expert-cards-grid" id="expertGrid">
			<?php foreach ( $ctx['experts'] as $_expert ) : ?>
				<?php adn_component( 'cards/expert_card', array( 'item' => (array) $_expert ) ); ?>
			<?php endforeach; ?>
		</div>

		<?php /* "Can't find the right expert?" banner */ ?>
		<?php if ( ! empty( $ctx['cant_find_cta'] ) ) : ?>
			<div style="margin-top:28px;">
				<?php adn_component( 'sections/expert_cant_find', array( 'cant_find_cta' => $ctx['cant_find_cta'] ) ); ?>
			</div>
		<?php endif; ?>
	</main>

	<?php /* SIDEBAR */ ?>
	<?php if ( ! empty( $ctx['sidebar'] ) ) : ?>
		<?php adn_component( 'parts/expert_sidebar', array( 'sidebar' => $ctx['sidebar'] ) ); ?>
	<?php endif; ?>

</div>

<?php /* ============================== FOOTER ============================== */ ?>
<?php
adn_component( 'parts/pre_footer' );
adn_component( 'parts/main_footer', array( 'footer' => isset( $ctx['chrome']['footer'] ) ? $ctx['chrome']['footer'] : array() ) );
adn_component( 'parts/post_footer' );
adn_component( 'parts/post_footer_notice' );

get_footer();
?>
