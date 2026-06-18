<?php
/**
 * components/sections/tools_search_bar.php - Calculators search strip.
 *
 * Props: $search { placeholder }
 * JS (calculators.js) hooks into #toolSearchInput.
 * Usage: adn_component( 'sections/tools_search_bar', array( 'search' => $ctx['search'] ) );
 */

defined( 'ABSPATH' ) || exit;

$search      = isset( $search ) && is_array( $search ) ? $search : array();
$placeholder = isset( $search['placeholder'] ) ? (string) $search['placeholder'] : '';
?>
<div class="tools-search-bar">
	<div class="tools-search-inner">
		<div class="search-input-wrap">
			<span class="search-icon" aria-hidden="true">🔍</span>
			<input
				type="search"
				id="toolSearchInput"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				aria-label="<?php echo esc_attr__( 'Search calculators', ADN_TEXT_DOMAIN ); ?>"
				autocomplete="off"
			/>
			<button class="search-btn" type="button" aria-label="<?php echo esc_attr__( 'Search', ADN_TEXT_DOMAIN ); ?>">›</button>
		</div>
	</div>
</div>

