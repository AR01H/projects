<?php
/**
 * components/cards/calc_card.php - Component: Calculator Card
 * Props: $card { icon, name, url, thumbnail?, highlight? }
 */

defined( 'ABSPATH' ) || exit;

$card      = isset( $card ) && is_array( $card ) ? $card : array();
$thumbnail = isset( $card['thumbnail'] ) && '' !== $card['thumbnail'] ? (string) $card['thumbnail'] : '';
$highlight = isset( $card['highlight'] ) && '' !== $card['highlight'] ? (string) $card['highlight'] : '';
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="calc-card">
	<?php if ( $highlight ) : ?>
		<span class="highlight-textblock"><?php echo esc_html( $highlight ); ?></span>
	<?php endif; ?>
	<div class="calc-card-icon" aria-hidden="true">
		<?php if ( $thumbnail ) : ?>
			<img src="<?php echo esc_url( $thumbnail ); ?>" alt="" class="calc-card-thumb" loading="lazy">
		<?php else : ?>
			<?php echo adn_icon( isset( $card['icon'] ) ? $card['icon'] : '' ); ?>
		<?php endif; ?>
	</div>
	<div class="calc-card-name"><?php echo esc_html( isset( $card['name'] ) ? $card['name'] : '' ); ?></div>
</a>
