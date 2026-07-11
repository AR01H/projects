<?php
/**
 * components/parts/hot_topics_widget.php
 * Hot Topics: dark icon box (left) · title (centre) · edge-bleed photo (right).
 * No arrow. Matches the reference design exactly.
 *
 * Props via $widget:
 *   heading  string
 *   items[]  { title|text, url, icon?, thumbnail? }
 *   cta      { label, url }
 *   tag      'h2'|'h3'|'h4'
 */

defined( 'ABSPATH' ) || exit;

$widget  = isset( $widget ) && is_array( $widget ) ? $widget : array();
$heading = isset( $widget['heading'] ) ? (string) $widget['heading'] : '';
$items   = isset( $widget['items'] )   && is_array( $widget['items'] ) ? $widget['items'] : array();
$cta     = isset( $widget['cta'] )     && is_array( $widget['cta'] )   ? $widget['cta']   : array();

$allowed_tags = array( 'h2', 'h3', 'h4' );
$htag         = isset( $widget['tag'] ) && in_array( $widget['tag'], $allowed_tags, true ) ? $widget['tag'] : 'h4';

$cta_label = isset( $cta['label'] ) ? (string) $cta['label'] : '';
$cta_url   = isset( $cta['url'] )   ? (string) $cta['url']   : '';

if ( empty( $items ) ) { return; }
?>
<div class="ht-widget">

	<?php if ( '' !== $heading || ( '' !== $cta_label && '' !== $cta_url ) ) : ?>
	<div class="ht-widget__header">
		<div class="list-widget-header__title-wrap">
			<?php if ( '' !== $heading ) : ?>
				<<?php echo $htag; ?>><?php echo esc_html( $heading ); ?></<?php echo $htag; ?>>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $cta_label && '' !== $cta_url ) : ?>
			<a href="<?php echo esc_url( adn_link( $cta_url ) ); ?>" class="list-widget-view-all">
				<?php echo esc_html( $cta_label ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<ul class="ht-widget__list">
		<?php foreach ( $items as $_item ) :
			$_title = isset( $_item['title'] ) ? (string) $_item['title']
			        : ( isset( $_item['text'] ) ? (string) $_item['text'] : '' );
			$_url   = isset( $_item['url'] )   ? (string) $_item['url']   : '';
			$_icon  = isset( $_item['icon'] )  ? (string) $_item['icon']  : '🔥';
			$_photo = isset( $_item['img_url'] )   ? (string) $_item['img_url']
			        : ( isset( $_item['thumbnail'] ) ? (string) $_item['thumbnail'] : '' );
			if ( '' === $_title ) { continue; }
		?>
		<li class="ht-widget__item">
			<a href="<?php echo esc_url( adn_link( $_url ) ); ?>" class="ht-widget__link">

				<div class="ht-widget__icon-box">
					<span class="ht-widget__icon" aria-hidden="true"><?php
						// FA class string → render as <i>; otherwise treat as emoji/text.
						if ( str_starts_with( $_icon, 'fa-' ) || str_starts_with( $_icon, 'fas ' ) || str_starts_with( $_icon, 'far ' ) ) {
							echo '<i class="' . esc_attr( $_icon ) . '" aria-hidden="true"></i>';
						} else {
							echo esc_html( $_icon );
						}
					?></span>
				</div>

				<span class="ht-widget__label"><?php echo esc_html( $_title ); ?></span>

				<?php if ( '' !== $_photo ) : ?>
				<div class="ht-widget__photo">
					<img src="<?php echo esc_url( $_photo ); ?>"
						 alt="<?php echo esc_attr( $_title ); ?>"
						 loading="lazy" decoding="async">
				</div>
				<?php endif; ?>

			</a>
		</li>
		<?php endforeach; ?>
	</ul>

</div>
