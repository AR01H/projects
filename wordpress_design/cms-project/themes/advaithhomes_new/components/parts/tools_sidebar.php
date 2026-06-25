<?php
/**
 * components/parts/tools_sidebar.php - Calculators sidebar: category list + help CTA.
 *
 * Props: $sidebar { categories[] { key, label, count }, help { title, text, button_label, button_url } }
 * JS syncs active state with the filter tabs via data-category attributes.
 * Usage: adn_component( 'parts/tools_sidebar', array( 'sidebar' => $ctx['sidebar'] ) );
 */

defined( 'ABSPATH' ) || exit;

$sidebar    = isset( $sidebar )  && is_array( $sidebar )  ? $sidebar  : array();
$categories = isset( $sidebar['categories'] ) ? (array) $sidebar['categories'] : array();
$help       = isset( $sidebar['help'] )       ? (array) $sidebar['help']       : array();
$sections   = isset( $sidebar['sections'] )   ? (array) $sidebar['sections']   : array();
?>
<aside class="tools-sidebar">

	<?php if ( ! empty( $categories ) ) : ?>
		<div class="tools-sidebar-section mini_card_container_design">
			<h3><?php echo esc_html( SITE_SIDEBAR_BROWSE_CAT ); ?></h3>
			<div class="tools-cat-list">
				<?php foreach ( $categories as $cat ) :
					$key      = isset( $cat['key'] )   ? sanitize_key( $cat['key'] )   : '';
					$label    = isset( $cat['label'] )  ? (string) $cat['label']        : '';
					$count    = isset( $cat['count'] )  ? (int)    $cat['count']        : 0;
					$cat_url  = isset( $cat['url'] )    ? (string) $cat['url']          : '';
					$cls      = 'tools-cat-item' . ( 'all' === $key ? ' active' : '' );
				?>
					<?php if ( '' !== $cat_url ) : ?>
						<a
							href="<?php echo esc_url( $cat_url ); ?>"
							class="<?php echo esc_attr( $cls ); ?>"
							data-category="<?php echo esc_attr( $key ); ?>"
						>
							<?php echo esc_html( $label ); ?>
							<span class="tools-cat-count"><?php echo esc_html( (string) $count ); ?></span>
						</a>
					<?php else : ?>
						<button
							class="<?php echo esc_attr( $cls ); ?>"
							data-category="<?php echo esc_attr( $key ); ?>"
							type="button"
						>
							<?php echo esc_html( $label ); ?>
							<span class="tools-cat-count"><?php echo esc_html( (string) $count ); ?></span>
						</button>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php foreach ( $sections as $_sec ) :
		$_sec_heading = isset( $_sec['heading'] ) ? (string) $_sec['heading'] : '';
		$_sec_links   = isset( $_sec['links'] )   ? (array)  $_sec['links']   : array();
		if ( '' === $_sec_heading || empty( $_sec_links ) ) { continue; }
		// Map links → sidebar_link_list item shape.
		$_sec_items = array();
		foreach ( $_sec_links as $_sl ) {
			$_lbl = isset( $_sl['label'] ) ? (string) $_sl['label'] : '';
			if ( '' === $_lbl ) { continue; }
			$_sec_items[] = array(
				'icon'  => isset( $_sl['icon'] ) ? (string) $_sl['icon'] : '',
				'label' => $_lbl,
				'url'   => isset( $_sl['url'] )  ? (string) $_sl['url']  : '',
			);
		}
		if ( empty( $_sec_items ) ) { continue; }
		adn_component( 'parts/sidebar_link_list', array( 'list' => array(
			'heading' => $_sec_heading,
			'items'   => $_sec_items,
		) ) );
	endforeach; ?>

	<?php if ( ! empty( $help['title'] ) || ! empty( $help['text'] ) || ! empty( $help['button_label'] ) ) : ?>
		<div class="tools-sidebar-help">
			<?php if ( ! empty( $help['title'] ) ) : ?>
				<h4><?php echo esc_html( $help['title'] ); ?></h4>
			<?php endif; ?>
			<?php if ( ! empty( $help['text'] ) ) : ?>
				<p><?php echo esc_html( $help['text'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $help['button_label'] ) ) : ?>
				<a
					href="<?php echo esc_url( adn_link( isset( $help['button_url'] ) ? $help['button_url'] : '' ) ); ?>"
					class="btn btn-primary tools-help-btn"
				>
					<?php echo esc_html( $help['button_label'] ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</aside>

