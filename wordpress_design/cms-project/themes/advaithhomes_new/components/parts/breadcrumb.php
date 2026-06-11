<?php
/**
 * components/parts/breadcrumb.php — Breadcrumb navigation trail.
 *
 * Props: $items  array of { label, url }  (last item has url = null)
 * Usage: adn_component( 'parts/breadcrumb', array( 'items' => $ctx['breadcrumb'] ) );
 */

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) && is_array( $items ) ? $items : array();
if ( empty( $items ) ) {
	return;
}
?>
<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', ADN_TEXT_DOMAIN ); ?>">
	<div class="container">
		<div class="breadcrumb-inner">
			<?php foreach ( $items as $i => $crumb ) : ?>
				<?php
				$label    = isset( $crumb['label'] ) ? (string) $crumb['label'] : '';
				$url      = isset( $crumb['url'] )   ? $crumb['url']            : null;
				$is_last  = ( $i === count( $items ) - 1 );
				?>
				<?php if ( ! $is_last && null !== $url ) : ?>
					<a href="<?php echo esc_url( adn_link( $url ) ); ?>" class="breadcrumb-item"><?php echo esc_html( $label ); ?></a>
					<span class="breadcrumb-sep" aria-hidden="true">&rsaquo;</span>
				<?php else : ?>
					<span class="breadcrumb-item<?php echo $is_last ? ' active' : ''; ?>"
						  <?php if ( $is_last ) : ?>aria-current="page"<?php endif; ?>>
						<?php echo esc_html( $label ); ?>
					</span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</nav>
