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
?>
<aside class="tools-sidebar">

	<?php if ( ! empty( $categories ) ) : ?>
		<div class="tools-sidebar-section">
			<h3><?php echo esc_html__( 'Browse by Category', ADN_TEXT_DOMAIN ); ?></h3>
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

