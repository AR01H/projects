<?php
/**
 * components/parts/post_sidebar_tools.php - Sidebar: Popular Tools list.
 *
 * Props: $calculators { view_all_url, items[] { icon, label, url } }
 */

defined( 'ABSPATH' ) || exit;

$_calcs    = isset( $calculators ) ? (array) $calculators : array();
$_items    = isset( $_calcs['items'] ) ? (array) $_calcs['items'] : array();
$_view_url = isset( $_calcs['view_all_url'] ) ? esc_url( adn_link( (string) $_calcs['view_all_url'] ) ) : '';

if ( empty( $_items ) ) { return; }
?>
<div class="sw-panel">
	<div class="sw-header">
		<h3 class="sw-title"><?php echo esc_html( adn_term( 'sidebar.popular_tools_heading', 'Popular ' . SITE_TOOLS_PLURAL ) ); ?></h3>
		<?php if ( '' !== $_view_url ) : ?>
			<a href="<?php echo $_view_url; ?>" class="sw-view-all"><?php echo esc_html( 'View all ' . SITE_TOOLS_PLURAL . ' →' ); ?></a>
		<?php endif; ?>
	</div>

	<ul class="sw-list" role="list">
		<?php foreach ( $_items as $_c ) : ?>
		<li class="sw-item">
			<a href="<?php echo esc_url( adn_link( isset( $_c['url'] ) ? (string) $_c['url'] : '#' ) ); ?>" class="sw-item-link">
				<span class="sw-item-icon" aria-hidden="true"><?php echo adn_icon( isset( $_c['icon'] ) ? (string) $_c['icon'] : '🧮' ); ?></span>
				<span class="sw-item-label"><?php echo esc_html( isset( $_c['label'] ) ? (string) $_c['label'] : '' ); ?></span>
				<span class="sw-item-arrow" aria-hidden="true">›</span>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
