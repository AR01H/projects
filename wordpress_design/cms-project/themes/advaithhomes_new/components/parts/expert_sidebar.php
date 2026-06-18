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

	<?php /* ── Contact for Help ─────────────────────────────────── */ ?>
	<?php if ( ! empty( $_ch ) ) :
		$_ch_h   = esc_html( isset( $_ch['heading'] )      ? (string) $_ch['heading']      : '' );
		$_ch_d   = esc_html( isset( $_ch['desc'] )         ? (string) $_ch['desc']         : '' );
		$_ch_btn = esc_html( isset( $_ch['button_label'] ) ? (string) $_ch['button_label'] : SITE_SIDEBAR_CONTACT_BTN );
		$_ch_url = esc_url( adn_link( isset( $_ch['button_url'] ) ? (string) $_ch['button_url'] : SITE_CONTACT_URL ) );
	?>
	<div class="sidebar-card expert-need-help">
		<?php if ( '' !== $_ch_h ) : ?>
			<div class="sidebar-card-title"><?php echo $_ch_h; ?></div>
		<?php endif; ?>
		<?php if ( '' !== $_ch_d ) : ?>
			<p class="sb-contact-desc"><?php echo $_ch_d; ?></p>
		<?php endif; ?>
		<a href="<?php echo $_ch_url; ?>" class="btn btn-primary sidebar-cta"><?php echo $_ch_btn; ?></a>
	</div>
	<?php endif; ?>

	<?php /* ── Latest News (sidebar_news_mini) ──────────────────── */ ?>
	<?php if ( ! empty( $_ln['items'] ) ) : ?>
		<?php adn_component( 'parts/sidebar_news_mini', array( 'news_mini' => $_ln ) ); ?>
	<?php endif; ?>

	<?php /* ── Quick Tools (sidebar_quick_tools) ─────────── */ ?>
	<?php if ( ! empty( $_tools['items'] ) ) : ?>
		<?php adn_component( 'parts/sidebar_quick_tools', array( 'quick_tools' => $_tools ) ); ?>
	<?php endif; ?>

	<?php /* ── Browse Guides (sidebar_guide_parents) ───────────── */ ?>
	<?php if ( ! empty( $_gt['items'] ) ) : ?>
		<?php adn_component( 'parts/sidebar_guide_parents', array( 'guide_parents' => $_gt ) ); ?>
	<?php endif; ?>

</aside>
