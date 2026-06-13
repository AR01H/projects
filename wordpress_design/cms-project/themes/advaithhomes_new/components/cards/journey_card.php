<?php
/**
 * components/cards/journey_card.php - Component: Journey Card
 * Props: $card { image, icon, gradient, title, description, link_label, url }
 *   image    — real photo URL (preferred); if absent falls back to gradient+icon
 */

defined( 'ABSPATH' ) || exit;

$card       = isset( $card ) && is_array( $card ) ? $card : array();
$_has_image = ! empty( $card['image'] );
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="journey-card">

	<div class="journey-card-img<?php echo $_has_image ? ' journey-card-img--photo' : ''; ?>"
	     <?php if ( ! $_has_image ) : ?>style="background:<?php echo esc_attr( isset( $card['gradient'] ) ? $card['gradient'] : '' ); ?>;"<?php endif; ?>>
		<?php if ( $_has_image ) : ?>
			<img src="<?php echo esc_url( $card['image'] ); ?>" alt="<?php echo esc_attr( isset( $card['title'] ) ? $card['title'] : '' ); ?>" loading="lazy" />
		<?php else : ?>
			<?php echo adn_icon( isset( $card['icon'] ) ? $card['icon'] : '' ); ?>
		<?php endif; ?>
	</div>

	<div class="journey-card-body">
		<h3><?php echo esc_html( isset( $card['title'] ) ? $card['title'] : '' ); ?></h3>
		<span class="card-desc-text"><?php echo esc_html( isset( $card['description'] ) ? $card['description'] : '' ); ?></span>
		<div class="journey-card-link"><?php echo esc_html( isset( $card['link_label'] ) ? $card['link_label'] : '' ); ?></div>
	</div>

</a>
