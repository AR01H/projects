<?php
/**
 * components/parts/quick_links_widget.php - Sidebar quick-links panel.
 *
 * Reusable wherever a list of icon+label+url links appears in a sidebar.
 * Shares CSS with spotlights_widget (.sp-panel, .sp-item, etc.).
 *
 * Props:
 *   $quick_links array {
 *     heading string
 *     items[] { icon, label, url }
 *   }
 */

defined( 'ABSPATH' ) || exit;

$quick_links = isset( $quick_links ) && is_array( $quick_links ) ? $quick_links : array();
$_ql_heading = isset( $quick_links['heading'] ) && '' !== $quick_links['heading']
	? (string) $quick_links['heading']
	: __( 'Quick Links', ADN_TEXT_DOMAIN );
$_ql_items   = isset( $quick_links['items'] ) && is_array( $quick_links['items'] )
	? array_filter( $quick_links['items'], function( $i ) { return ! empty( $i['label'] ); } )
	: array();

if ( empty( $_ql_items ) ) { return; }
?>
<div class="sp-panel news-widget ql-widget">
	<div class="news-widget-header">
		<span class="news-widget-title"><?php echo esc_html( $_ql_heading ); ?></span>
	</div>
	<ul class="sp-list">
	<?php foreach ( $_ql_items as $_ql ) :
		$_ql_icon  = trim( (string) ( $_ql['icon']  ?? '' ) );
		$_ql_label = (string) ( $_ql['label'] ?? '' );
		$_ql_url   = (string) ( $_ql['url']   ?? '' );
		$_is_emoji = '' !== $_ql_icon && preg_match( '/\p{So}|\p{Sm}|\p{Sk}|\p{Sc}/u', $_ql_icon );
		$_is_fa    = '' !== $_ql_icon && ! $_is_emoji;
	?>
	<li class="sp-item<?php echo ( ! $_is_emoji && ! $_is_fa ) ? ' sp-item--no-icon' : ''; ?>">

		<?php if ( '' !== $_ql_url ) : ?>
		<a href="<?php echo esc_url( adn_link( $_ql_url ) ); ?>" class="sp-item__link-wrap">
		<?php endif; ?>

		<?php if ( $_is_emoji || $_is_fa ) : ?>
		<div class="sp-item__icon" aria-hidden="true">
			<?php if ( $_is_emoji ) : ?>
				<span><?php echo esc_html( $_ql_icon ); ?></span>
			<?php else : ?>
				<i class="<?php echo esc_attr( $_ql_icon ); ?>"></i>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="sp-item__body">
			<span class="sp-item__title"><?php echo esc_html( $_ql_label ); ?></span>
		</div>

		<?php if ( '' !== $_ql_url ) : ?>
			<span class="sp-item__arrow" aria-hidden="true">›</span>
		</a>
		<?php endif; ?>

	</li>
	<?php endforeach; ?>
	</ul>
</div>
