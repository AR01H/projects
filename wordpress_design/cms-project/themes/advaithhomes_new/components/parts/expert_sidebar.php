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
			$_card   = (array) $_catalog[$_key];
			$_c_icon = isset( $_card['icon'] ) ? (string) $_card['icon'] : 'fa-solid fa-circle-info';
			$_c_head = isset( $_card['heading'] ) ? (string) $_card['heading'] : '';
			$_c_desc = isset( $_card['description'] ) ? (string) $_card['description'] : '';
			$_c_btn  = isset( $_card['button_label'] ) ? (string) $_card['button_label'] : '';
			$_c_url  = isset( $_card['url'] ) ? (string) $_card['url'] : '';
			$_c_cls  = isset( $_card['class'] ) ? (string) $_card['class'] : '';
	?>
		<div class="contact-alt-box<?php echo $_c_cls ? ' ' . esc_attr( $_c_cls ) : ''; ?>" style="margin-bottom: 24px;">
			<div class="contact-alt-head">
				<div class="contact-alt-icon" aria-hidden="true"><i class="<?php echo esc_attr( $_c_icon ); ?>"></i></div>
				<h3><?php echo esc_html( $_c_head ); ?></h3>
			</div>
			<p class="contact-guidance-text"><?php echo esc_html( $_c_desc ); ?></p>
			<a href="<?php echo esc_url( home_url( $_c_url ) ); ?>" class="btn btn-primary contact-alt-btn">
				<?php echo esc_html( $_c_btn ); ?> →
			</a>
		</div>
	<?php 
		endforeach;
	endif; 
	?>

	<?php /* ── Browse Guides (sidebar_guide_parents) ───────────── */ ?>
	<?php if ( ! empty( $_gt['items'] ) ) : ?>
		<?php adn_component( 'parts/sidebar_guide_parents', array( 'guide_parents' => $_gt ) ); ?>
	<?php endif; ?>

</aside>
