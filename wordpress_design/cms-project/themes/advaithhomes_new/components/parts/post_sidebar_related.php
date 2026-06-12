<?php
/**
 * components/parts/post_sidebar_related.php
 *
 * Sidebar — Related Guides list (populated from WP_Query in post_logical.php).
 *
 * Props (via extract):
 *   $related_guides = [ { icon, title, read_time, url }, … ]
 */

defined( 'ABSPATH' ) || exit;

$_items = ( isset( $related_guides ) && is_array( $related_guides ) ) ? $related_guides : array();

if ( empty( $_items ) ) {
	return;
}
?>
<div class="sidebar-box">
	<h3><?php esc_html_e( 'Related Guides', ADN_TEXT_DOMAIN ); ?></h3>
	<ul class="sidebar-related-list" role="list">
		<?php foreach ( $_items as $_g ) :
			$_g_icon = adn_icon( isset( $_g['icon'] )      ? (string) $_g['icon']      : '🏠' );
			$_g_ttl  = esc_html( isset( $_g['title'] )     ? (string) $_g['title']     : '' );
			$_g_rt   = esc_html( isset( $_g['read_time'] ) ? (string) $_g['read_time'] : '' );
			$_g_url  = isset( $_g['url'] ) ? esc_url( (string) $_g['url'] ) : '#';
		?>
			<li>
				<a href="<?php echo $_g_url; ?>" class="sidebar-related-item">
					<span class="sidebar-related-img" aria-hidden="true"><?php echo $_g_icon; ?></span>
					<span class="sidebar-related-text">
						<span class="sidebar-related-title"><?php echo $_g_ttl; ?></span>
						<?php if ( '' !== $_g_rt ) : ?>
							<span class="sidebar-related-meta">⏱ <?php echo $_g_rt; ?></span>
						<?php endif; ?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
