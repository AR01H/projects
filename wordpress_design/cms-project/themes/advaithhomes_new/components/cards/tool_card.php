<?php
/**
 * components/cards/tool_card.php - Component: Calculator Card
 * Props: $card { icon, name, url, thumbnail?, highlight? }
 */

defined( 'ABSPATH' ) || exit;

$card      = isset( $card ) && is_array( $card ) ? $card : array();
$thumbnail = isset( $card['thumbnail'] ) && '' !== $card['thumbnail'] ? (string) $card['thumbnail'] : '';
$data_category = isset( $card['data_category'] ) ? (string) $card['data_category'] : '';
$data_index    = isset( $card['data_index'] ) ? (int) $card['data_index'] : 0;

// Fallback to default calculator image if none is provided
if ( empty( $thumbnail ) ) {
    $thumbnail = get_template_directory_uri() . THEME_DEFAULT_CALC_IMG . '?v=' . LOCAL_CACHE_VERSION;
}

$extra_attrs = '';
if ( $data_category ) {
    $extra_attrs .= ' data-category="' . esc_attr( $data_category ) . '"';
}
if ( isset( $card['data_index'] ) ) {
    $extra_attrs .= ' data-index="' . esc_attr( (string) $data_index ) . '"';
}
?>
<a href="<?php echo esc_url( adn_link( isset( $card['url'] ) ? $card['url'] : '' ) ); ?>" class="calc-card"<?php echo $extra_attrs; ?>>
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
		<?php if ( ! empty( $card['desc'] ) ) : ?>
			<p class="card-desc-text"><?php echo esc_html( (string) $card['desc'] ); ?></p>
		<?php endif; ?>
	</div>
</a>
