<?php
/**
 * components/parts/list_widget.php - Reusable headed list widget.
 *
 * Accepts a heading (with optional "view all" link), a list of mini_card
 * items, and an optional bottom CTA button. Works as a drop-in for any
 * "title + item list" pattern across the site.
 *
 * Props via $widget array:
 *   heading  array  { title, link_label?, link_url? }
 *   items    array  - each entry is a mini_card $card array:
 *                     { icon|badge|img, title, meta?, tag?, url? }
 *   cta      array  { label, url }  - optional bottom link button
 *   tag      string - heading HTML tag: 'h2' | 'h3' (default) | 'h4'
 *
 * Usage:
 *   adn_component( 'parts/list_widget', array( 'widget' => array(
 *       'heading' => array(
 *           'title'      => 'Latest News',
 *           'link_label' => 'View all →',
 *           'link_url'   => '/news/',
 *       ),
 *       'items' => array(
 *           array( 'img' => 'linear-gradient(135deg,#1d5c8e,#2d7dd2)', 'title' => 'Article title', 'meta' => 'Jan 12 2026', 'url' => '/news/article/' ),
 *           array( 'icon' => '🏠', 'title' => 'First-time buyer guide', 'url' => '/guides/first-time-buyer/' ),
 *       ),
 *       'cta' => array( 'label' => 'See all guides', 'url' => '/guides/' ),
 *   ) ) );
 */

defined( 'ABSPATH' ) || exit;

$widget  = isset( $widget ) && is_array( $widget ) ? $widget : array();
$heading = isset( $widget['heading'] ) && is_array( $widget['heading'] ) ? $widget['heading'] : array();
$items   = isset( $widget['items'] )   && is_array( $widget['items'] )   ? $widget['items']   : array();
$cta     = isset( $widget['cta'] )     && is_array( $widget['cta'] )     ? $widget['cta']     : array();

$allowed_tags = array( 'h2', 'h3', 'h4' );
$htag         = isset( $widget['tag'] ) && in_array( $widget['tag'], $allowed_tags, true ) ? $widget['tag'] : 'h3';

$title      = isset( $heading['title'] )      ? (string) $heading['title']      : '';
$link_label = isset( $heading['link_label'] ) ? (string) $heading['link_label'] : '';
$link_url   = isset( $heading['link_url'] )   ? (string) $heading['link_url']   : '';
$cta_label  = isset( $cta['label'] )          ? (string) $cta['label']          : '';
$cta_url    = isset( $cta['url'] )            ? (string) $cta['url']            : '';

if ( empty( $items ) && '' === $title ) { return; }
?>
<div class="list-widget">

	<?php if ( '' !== $title || ( '' !== $link_label && '' !== $link_url ) ) : ?>
	<div class="list-widget-header">
		<?php if ( '' !== $title ) : ?>
			<<?php echo $htag; ?>><?php echo esc_html( $title ); ?></<?php echo $htag; ?>>
		<?php endif; ?>
		<?php if ( '' !== $link_label && '' !== $link_url ) : ?>
			<a href="<?php echo esc_url( adn_link( $link_url ) ); ?>" class="list-widget-view-all">
				<?php echo esc_html( $link_label ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $items ) ) : ?>
	<div class="list-widget-items">
		<?php foreach ( $items as $_card ) : ?>
			<?php adn_component( 'cards/mini_card', array( 'card' => (array) $_card ) ); ?>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( '' !== $cta_label && '' !== $cta_url ) : ?>
		<a href="<?php echo esc_url( adn_link( $cta_url ) ); ?>" class="list-widget-cta btn btn-outline btn-sm">
			<?php echo esc_html( $cta_label ); ?>
		</a>
	<?php endif; ?>

</div>
