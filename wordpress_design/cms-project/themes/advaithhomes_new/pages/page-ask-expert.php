<?php
/**
 * Template Name: Ask an Expert
 *
 * pages/page-ask-expert.php - Expert directory listing page.
 * Category filter tabs + expert cards grid + sidebar.
 *
 * Architecture:
 *   DB (AH_Expert_DB) + WP page + admin banner option
 *     → intermediate/page_ask_expert_logical.php  adn_ask_expert_get_context()
 *       → THIS FILE (structure only)
 *
 * RULE: No hardcoded content or data reads here - only structure.
 * RULE: Header/footer come from header.php / footer.php via get_header() / get_footer().
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/page_ask_expert_logical.php';
$ctx = adn_ask_expert_get_context();

// Pass AJAX url and contact nonce to ask_expert.js on the listing page.
// The script handle auto-registered in explode_function.php is 'adn-page-ask-expert-script'.
wp_localize_script( 'adn-page-ask-expert-script', 'adnExpert', array(
	'ajaxUrl' => isset( $ctx['ajax_url'] )      ? $ctx['ajax_url']      : admin_url( 'admin-ajax.php' ),
	'nonce'   => isset( $ctx['contact_nonce'] ) ? $ctx['contact_nonce'] : '',
) );

$_open_ctx               = $ctx;
$_open_ctx['breadcrumb'] = array();
adn_page_open( $_open_ctx );
?>

<?php /* ============================== HERO ============================== */ ?>
<?php adn_component( 'sections/page_hero', array(
	'hero'       => $ctx['hero'],
	'breadcrumb' => $ctx['breadcrumb'],
	'stats'      => $ctx['stats'],
) ); ?>

<?php /* ============================== CATEGORY TABS STRIP ============================== */ ?>
<?php if ( ! empty( $ctx['categories'] ) ) : ?>
	<?php adn_component( 'sections/expert_cats_strip', array( 'categories' => $ctx['categories'] ) ); ?>
<?php endif; ?>

<?php /* ============================== MAIN LAYOUT: CARDS + SIDEBAR ============================== */ ?>
<div class="expert-main-layout">

	<?php /* MAIN - expert cards */ ?>
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

<?php adn_page_close( $ctx ); ?>
