<?php
/**
 * components/parts/browse_more_cta.php - Browse-more CTA strip.
 *
 * Used below search/listing results to fill empty space and surface quick nav.
 *
 * Props: $browse_cta {
 *   icon        string  — emoji (default 📚)
 *   heading     string
 *   description string
 *   links[]     { label, url, primary bool }
 * }
 */

defined( 'ABSPATH' ) || exit;

$browse_cta  = isset( $browse_cta ) && is_array( $browse_cta ) ? $browse_cta : array();
$_icon       = isset( $browse_cta['icon'] )        ? (string) $browse_cta['icon']        : '📚';
$_heading    = isset( $browse_cta['heading'] )     ? (string) $browse_cta['heading']     : '';
$_desc       = isset( $browse_cta['description'] ) ? (string) $browse_cta['description'] : '';
$_links      = isset( $browse_cta['links'] )       ? (array)  $browse_cta['links']       : array();

if ( '' === $_heading && empty( $_links ) ) { return; }
?>
<div class="search-browse-more">
	<div class="sbm-inner">
		<span class="sbm-icon" aria-hidden="true"><?php echo adn_icon( $_icon ); ?></span>

		<div class="sbm-text">
			<?php if ( '' !== $_heading ) : ?>
				<strong><?php echo esc_html( $_heading ); ?></strong>
			<?php endif; ?>
			<?php if ( '' !== $_desc ) : ?>
				<span><?php echo esc_html( $_desc ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $_links ) ) : ?>
		<div class="sbm-links">
			<?php foreach ( $_links as $_lnk ) :
				$_lnk_url   = isset( $_lnk['url'] )   ? esc_url( (string) $_lnk['url'] )   : '#';
				$_lnk_label = isset( $_lnk['label'] )  ? esc_html( (string) $_lnk['label'] ) : '';
				$_lnk_cls   = ! empty( $_lnk['primary'] ) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
			?>
				<a href="<?php echo $_lnk_url; ?>" class="<?php echo $_lnk_cls; ?>"><?php echo $_lnk_label; ?></a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</div>
