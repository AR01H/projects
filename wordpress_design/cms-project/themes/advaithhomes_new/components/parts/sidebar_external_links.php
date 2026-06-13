<?php
/**
 * components/parts/sidebar_external_links.php - Sidebar: External Links list.
 *
 * Props: $external_links { heading, items[] { icon, title, url, desc } }
 * Usage: adn_component( 'parts/sidebar_external_links', array( 'external_links' => $ctx['sidebar']['external_links'] ) );
 */

defined( 'ABSPATH' ) || exit;

$external_links = isset( $external_links ) && is_array( $external_links ) ? $external_links : array();
$items          = isset( $external_links['items'] ) ? (array) $external_links['items'] : array();
?>
<div class="sidebar-card">
	<?php if ( ! empty( $external_links['heading'] ) ) : ?>
		<div class="sidebar-card-title"><?php echo esc_html( $external_links['heading'] ); ?></div>
	<?php endif; ?>

	<?php foreach ( $items as $item ) :
		$title = isset( $item['title'] ) ? (string) $item['title'] : '';
		$url   = isset( $item['url'] )   ? (string) $item['url']   : '#';
		$icon  = isset( $item['icon'] )  ? (string) $item['icon']  : '';
		$desc  = isset( $item['desc'] )  ? (string) $item['desc']  : '';
		if ( empty( $title ) && empty( $url ) ) { continue; }
	?>
		<div class="ext-link-sidebar-item">
			<?php if ( $icon ) : ?>
				<span class="hot-topic-sidebar-icon"><?php echo adn_icon( $icon ); ?></span>
			<?php endif; ?>
			<div class="ext-link-sidebar-body">
				<a href="<?php echo esc_url( adn_link( $url ) ); ?>" class="hot-topic-sidebar-text" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $title ); ?>
				</a>
				<?php if ( $desc ) : ?>
					<p class="ext-link-sidebar-desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
