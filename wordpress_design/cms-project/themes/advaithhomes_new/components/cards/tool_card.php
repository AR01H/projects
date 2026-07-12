<?php
/**
 * components/cards/tool_card.php - Component: Calculator Card
 * Props: $card { icon, name, url, thumbnail?, highlight? }
 */

defined( 'ABSPATH' ) || exit;

$card      = isset( $card ) && is_array( $card ) ? $card : array();
$thumbnail = isset( $card['thumbnail'] ) && '' !== $card['thumbnail'] ? (string) $card['thumbnail'] : '';

// Fallback to default calculator image if none is provided
if ( empty( $thumbnail ) ) {
    $thumbnail = get_template_directory_uri() . THEME_DEFAULT_CALC_IMG . '?v=' . LOCAL_CACHE_VERSION;
}
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="calc-card">
	<div class="calc-card-media">
		<?php if ( $thumbnail ) : 
			$fallback = esc_url( get_template_directory_uri() . THEME_DEFAULT_CALC_IMG . '?v=' . LOCAL_CACHE_VERSION );
			$alt_text = esc_attr( ! empty( $card['name'] ) ? $card['name'] : ( ! empty( $card['title'] ) ? $card['title'] : '' ) );
		?>
			<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo $alt_text; ?>" onerror="this.onerror=null;this.src='<?php echo $fallback; ?>';" class="calc-card-thumb" loading="lazy">
		<?php else : ?>
			<span class="calc-card-icon-wrap" aria-hidden="true">
				<?php echo adn_icon( isset( $card['icon'] ) ? $card['icon'] : '' ); ?>
			</span>
		<?php endif; ?>
		<?php if ( ! empty( $card['highlight'] ) ) : ?>
			<span class="calc-card-badge"><?php echo esc_html( $card['highlight'] ); ?></span>
		<?php endif; ?>
	</div>
	<div class="calc-card-body">
		<div class="calc-card-name"><?php echo esc_html( ! empty( $card['name'] ) ? $card['name'] : ( ! empty( $card['title'] ) ? $card['title'] : '' ) ); ?></div>
	</div>
</a>
