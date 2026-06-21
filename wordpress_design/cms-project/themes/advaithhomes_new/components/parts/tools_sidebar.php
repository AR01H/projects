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
$hl_heading = isset( $sidebar['hl_heading'] ) ? (string) $sidebar['hl_heading'] : '';
$hl_links   = isset( $sidebar['hl_links'] )   ? (array)  $sidebar['hl_links']   : array();
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

	<?php if ( ! empty( $hl_heading ) && ! empty( $hl_links ) ) : ?>
		<div class="tools-sidebar-section mini_card_container_design">
			<h3><?php echo esc_html( $hl_heading ); ?></h3>
			<div class="tools-cat-list">
				<?php foreach ( $hl_links as $hl_item ) :
					$_hl_icon  = isset( $hl_item['icon'] )  ? (string) $hl_item['icon']  : '';
					$_hl_label = isset( $hl_item['label'] ) ? (string) $hl_item['label'] : '';
					$_hl_url   = isset( $hl_item['url'] )   ? (string) $hl_item['url']   : '';
					if ( '' === $_hl_label ) { continue; }
				?>
					<?php if ( '' !== $_hl_url ) : ?>
						<a href="<?php echo esc_url( adn_link( $_hl_url ) ); ?>" class="tools-cat-item tools-cat-item--hl">
							<?php if ( '' !== $_hl_icon ) : ?><span class="tools-cat-icon" aria-hidden="true"><?php echo esc_html( $_hl_icon ); ?></span><?php endif; ?>
							<?php echo esc_html( $_hl_label ); ?>
						</a>
					<?php else : ?>
						<span class="tools-cat-item tools-cat-item--hl">
							<?php if ( '' !== $_hl_icon ) : ?><span class="tools-cat-icon" aria-hidden="true"><?php echo esc_html( $_hl_icon ); ?></span><?php endif; ?>
							<?php echo esc_html( $_hl_label ); ?>
						</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $help ) ) : ?>
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

