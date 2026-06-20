<?php
/**
 * components/cards/journey_card.php - Component: Journey Card
 * Props: $card { image, icon, gradient, title, link_label, url }
 *   image    - real photo URL; if absent falls back to gradient+icon
 *   icon     - emoji / FA class shown centred on gradient cards
 *   gradient - CSS gradient string for the background
 */

defined( 'ABSPATH' ) || exit;

$card       = isset( $card ) && is_array( $card ) ? $card : array();
$_has_image = ! empty( $card['image'] );
$_gradient  = isset( $card['gradient'] )    ? $card['gradient']    : '';
$_icon      = isset( $card['icon'] )        ? $card['icon']        : '';
$_title     = isset( $card['title'] )       ? $card['title']       : '';
$_desc      = isset( $card['description'] ) ? $card['description'] : '';
$_label     = isset( $card['link_label'] )  ? $card['link_label']  : '';
$_url       = isset( $card['url'] )         ? $card['url']         : '';
?>
<a href="<?php echo esc_url( adn_link( $_url ) ); ?>" class="journey-card">

	<div class="journey-card-bg"<?php if ( ! $_has_image && '' !== $_gradient ) : ?> style="background:<?php echo esc_attr( $_gradient ); ?>;"<?php endif; ?>>
		<?php if ( $_has_image ) : ?>
			<img class="journey-card-photo" src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( $_title ); ?>" loading="lazy">
		<?php elseif ( '' !== $_icon ) : ?>
			<span class="journey-card-icon" aria-hidden="true"><?php echo adn_icon( $_icon ); ?></span>
		<?php endif; ?>
	</div>

	<div class="journey-card-body">
		<?php if ( '' !== $_title ) : ?>
			<h3><?php echo esc_html( $_title ); ?></h3>
		<?php endif; ?>
		<?php if ( '' !== $_desc ) : ?>
			<p class="journey-card-desc"><?php echo esc_html( $_desc ); ?></p>
		<?php endif; ?>
		<span class="journey-card-link"><?php echo esc_html( '' !== $_label ? $_label : adn_term( 'buttons.explore_arrow', 'Explore →' ) ); ?></span>
	</div>

</a>
