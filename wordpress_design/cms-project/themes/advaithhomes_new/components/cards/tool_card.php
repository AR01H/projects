<?php
/**
 * components/cards/tool_card.php - Component: Calculator Card
 * Props: $card { icon, name, url, thumbnail?, highlight? }
 */

defined( 'ABSPATH' ) || exit;

$card      = isset( $card ) && is_array( $card ) ? $card : array();
$thumbnail = isset( $card['thumbnail'] ) && '' !== $card['thumbnail'] ? (string) $card['thumbnail'] : '';
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="calc-card">
	<div class="calc-card-media">
		<?php if ( $thumbnail ) : ?>
			<img src="<?php echo esc_url( $thumbnail ); ?>" alt="" class="calc-card-thumb" loading="lazy">
		<?php else : ?>
			<span class="calc-card-icon-wrap" aria-hidden="true">
				<?php echo adn_icon( isset( $card['icon'] ) ? $card['icon'] : '' ); ?>
			</span>
		<?php endif; ?>
	</div>
	<div class="calc-card-body">
		<?php if ( ! empty( $card['highlight'] ) ) : ?>
			<span class="calc-card-badge"><?php echo esc_html( $card['highlight'] ); ?></span>
		<?php endif; ?>
		<div class="calc-card-name"><?php echo esc_html( isset( $card['name'] ) ? $card['name'] : '' ); ?></div>
	</div>
</a>
