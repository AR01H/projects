<?php
/**
 * components/parts/sidebar_link_list.php - Unified sidebar list panel.
 *
 * Single reusable component for any sidebar list of links.
 * Left-visual priority per item: thumbnail (photo) → icon (emoji/FA) → nothing.
 *
 * Props via $list array:
 *   heading   string                              Panel title
 *   items[]   { label, url, thumbnail?, icon?, meta? }
 *   view_all  { label, url }                      optional header link
 *   cta       { label, url }                      optional footer CTA button
 *
 * Usage:
 *   adn_component( 'parts/sidebar_link_list', array( 'list' => array(
 *       'heading'  => 'Hot Topics',
 *       'items'    => $items,
 *       'view_all' => array( 'label' => 'View all →', 'url' => '/news/' ),
 *   ) ) );
 */

defined( 'ABSPATH' ) || exit;

$list  = isset( $list ) && is_array( $list ) ? $list : array();
$items = isset( $list['items'] ) && is_array( $list['items'] ) ? $list['items'] : array();

if ( empty( $items ) ) { return; }

$_heading   = isset( $list['heading'] )          ? (string) $list['heading']          : '';
$view_all   = isset( $list['view_all'] )         ? (array)  $list['view_all']         : array();
$cta        = isset( $list['cta'] )              ? (array)  $list['cta']              : array();

$_all_url   = ! empty( $view_all['url'] )   ? esc_url( adn_link( (string) $view_all['url'] ) )   : '';
$_all_label = ! empty( $view_all['label'] ) ? esc_html( (string) $view_all['label'] )             : '';
$_cta_url   = ! empty( $cta['url'] )        ? esc_url( adn_link( (string) $cta['url'] ) )        : '';
$_cta_label = ! empty( $cta['label'] )      ? esc_html( (string) $cta['label'] )                  : '';
?>
<div class="sw-panel">

	<div class="sw-header">
		<?php if ( '' !== $_heading ) : ?>
			<h3 class="sw-title"><?php echo esc_html( $_heading ); ?></h3>
		<?php endif; ?>
		<?php if ( '' !== $_all_url && '' !== $_all_label ) : ?>
			<a href="<?php echo $_all_url; ?>" class="sw-view-all"><?php echo $_all_label; ?></a>
		<?php endif; ?>
	</div>

	<ul class="sw-list" role="list">
		<?php foreach ( $items as $item ) :
			$_label = isset( $item['label'] )     ? (string) $item['label']     : '';
			$_url   = isset( $item['url'] )       ? (string) $item['url']       : '';
			$_thumb = isset( $item['thumbnail'] ) ? (string) $item['thumbnail'] : '';
			$_icon  = isset( $item['icon'] )      ? (string) $item['icon']      : '';
			$_meta  = isset( $item['meta'] )      ? (string) $item['meta']      : '';
			if ( '' === $_label ) { continue; }
			$_href = '' !== $_url ? esc_url( adn_link( $_url ) ) : '';
		?>
		<li class="sw-item">
			<?php if ( '' !== $_href ) : ?>
			<a href="<?php echo $_href; ?>" class="sw-item-link">
			<?php else : ?>
			<span class="sw-item-link">
			<?php endif; ?>

				<?php if ( '' !== $_thumb ) : ?>
					<span class="sw-item-thumb" aria-hidden="true">
						<img src="<?php echo esc_url( $_thumb ); ?>" alt="" loading="lazy">
					</span>
				<?php elseif ( '' !== $_icon ) : ?>
					<span class="sw-item-icon" aria-hidden="true"><?php echo adn_icon( $_icon ); ?></span>
				<?php endif; ?>

				<span class="sw-item-label">
					<?php echo esc_html( $_label ); ?>
					<?php if ( '' !== $_meta ) : ?>
						<span class="sw-item-meta"><?php echo esc_html( $_meta ); ?></span>
					<?php endif; ?>
				</span>

				<?php if ( '' !== $_href ) : ?>
					<span class="sw-item-arrow" aria-hidden="true">›</span>
				<?php endif; ?>

			<?php if ( '' !== $_href ) : ?>
			</a>
			<?php else : ?>
			</span>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( '' !== $_cta_url && '' !== $_cta_label ) : ?>
	<div class="sw-footer">
		<a href="<?php echo $_cta_url; ?>" class="sw-cta-btn"><?php echo $_cta_label; ?></a>
	</div>
	<?php endif; ?>

</div>
