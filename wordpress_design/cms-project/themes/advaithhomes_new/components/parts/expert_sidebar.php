<?php
/**
 * components/parts/expert_sidebar.php
 *
 * Sidebar for the Ask an Expert listing page.
 * Reuses existing site sidebar components so the design is consistent.
 *
 * Props: $sidebar {
 *   contact_help   { heading, desc, button_label, button_url }
 *   latest_news    sidebar_news_mini shape   { heading, items[], view_all{} }
 *   calculators    sidebar_quick_tools shape { heading, items[], cta{} }
 *   guide_topics   sidebar_guide_parents shape { heading, items[] }
 *   newsletter_cta sidebar_newsletter_signup shape { heading, description, placeholder, button_label, note }
 * }
 */
defined( 'ABSPATH' ) || exit;

$_sb    = isset( $sidebar ) ? (array) $sidebar : array();
$_ch    = isset( $_sb['contact_help'] )   ? (array) $_sb['contact_help']   : array();
$_ln    = isset( $_sb['latest_news'] )    ? (array) $_sb['latest_news']    : array();
$_tools = isset( $_sb['tools'] )          ? (array) $_sb['tools']          : array();
$_gt    = isset( $_sb['guide_topics'] )   ? (array) $_sb['guide_topics']   : array();
$_nl    = isset( $_sb['newsletter_cta'] ) ? (array) $_sb['newsletter_cta'] : array();
?>
<aside class="expert-sidebar">

	<?php /* Dynamic Guidance & Support Cards from JSON catalog */ ?>
	<?php 
	$_catalog = array();
	if ( class_exists( 'ADN_Real_Loader' ) ) {
		$_catalog = ADN_Real_Loader::json( 'sidebar_cards' );
	}
	$_cards_to_show = array( 'guidance', 'contact' );
	if ( ! empty( $_catalog ) ) :
		foreach ( $_cards_to_show as $_key ) :
			if ( ! isset( $_catalog[$_key] ) ) { continue; }
			adn_component( 'cards/sidebar_contact_card', array(
				'card'         => (array) $_catalog[$_key],
				'inline_style' => 'margin-bottom: 24px;'
			) );
		endforeach;
	endif; 
	?>

	<?php /* ── Browse Guides (sidebar_guide_parents) ───────────── */ ?>
	<?php if ( ! empty( $_gt['items'] ) ) : ?>
		<?php adn_component( 'parts/sidebar_guide_parents', array( 'guide_parents' => $_gt ) ); ?>
	<?php endif; ?>

</aside>
