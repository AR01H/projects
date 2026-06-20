<?php
/**
 * components/sections/expert_cats_strip.php
 * Props: $categories[] { key, label, icon?, active? }
 */
defined( 'ABSPATH' ) || exit;

$_cats = ( isset( $categories ) && is_array( $categories ) ) ? $categories : array();
if ( empty( $_cats ) ) return;
?>
<div class="expert-cats-strip">

	<?php /* ── Search row ─────────────────────────────────────────── */ ?>
	<!-- <div class="expert-search-row container">
		<div class="search-input-wrap">
			<span class="search-icon" aria-hidden="true">🔍</span>
			<input
				type="search"
				id="expertSearch"
				placeholder="<?php esc_attr_e( 'Search experts by name or specialism…', ADN_TEXT_DOMAIN ); ?>"
				autocomplete="off"
				aria-label="<?php esc_attr_e( 'Search experts', ADN_TEXT_DOMAIN ); ?>"
			>
			<button type="button" id="expertSearchClear" class="search-btn expert-search-clear" aria-label="<?php esc_attr_e( 'Clear search', ADN_TEXT_DOMAIN ); ?>" hidden>×</button>
		</div>
	</div> -->

	<?php /* ── Category tabs row ────────────────────────────────── */ ?>
	<div class="expert-cats-inner container" role="tablist" aria-label="<?php esc_attr_e( 'Filter experts by category', ADN_TEXT_DOMAIN ); ?>">
		<?php foreach ( $_cats as $_c ) :
			$_ck     = esc_attr( sanitize_key( isset( $_c['key'] )   ? (string) $_c['key']   : 'all' ) );
			$_cl     = esc_html( isset( $_c['label'] ) ? (string) $_c['label'] : '' );
			$_ci     = isset( $_c['icon'] ) ? (string) $_c['icon'] : '';
			$_active = ! empty( $_c['active'] );
		?>
			<button
				type="button"
				class="expert-cat-tab<?php echo $_active ? ' active' : ''; ?>"
				data-cat="<?php echo $_ck; ?>"
				role="tab"
				aria-selected="<?php echo $_active ? 'true' : 'false'; ?>"
			><?php echo $_cl; ?></button>
		<?php endforeach; ?>
	</div>

</div>
