<?php
/**
 * components/parts/sidebar_guide_parents.php - Sidebar: Browse by Guide Topic.
 *
 * Shows all Guide parent terms (Buying / Selling / Moving …) as anchor links
 * so the user can jump to that group on the Guides Hub page, or navigate to
 * that parent term's own category listing page.
 *
 * Props: $guide_parents { heading, items[] { icon, label, url, count } }
 * Usage: adn_component( 'parts/sidebar_guide_parents', array( 'guide_parents' => $ctx['sidebar']['guide_parents'] ) );
 */

defined( 'ABSPATH' ) || exit;

$guide_parents = isset( $guide_parents ) && is_array( $guide_parents ) ? $guide_parents : array();
$items         = isset( $guide_parents['items'] ) ? (array) $guide_parents['items'] : array();

if ( empty( $items ) ) { return; }
?>
<div class="sidebar-card sidebar-guide-parents">
	<?php if ( ! empty( $guide_parents['heading'] ) ) : ?>
		<div class="sidebar-card-title"><?php echo esc_html( $guide_parents['heading'] ); ?></div>
	<?php endif; ?>

	<ul class="sgp-list">
		<?php foreach ( $items as $item ) :
			$icon  = isset( $item['icon'] )  ? (string) $item['icon']  : '📚';
			$label = isset( $item['label'] ) ? (string) $item['label'] : '';
			$url   = isset( $item['url'] )   ? (string) $item['url']   : '#';
			$count = isset( $item['count'] ) ? (int)    $item['count'] : 0;
		?>
			<li class="sgp-item">
				<a href="<?php echo esc_url( adn_link( $url ) ); ?>" class="sgp-link">
					<span class="sgp-icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
					<span class="sgp-label"><?php echo esc_html( $label ); ?></span>
					<?php if ( $count > 0 ) : ?>
						<span class="sgp-count"><?php echo esc_html( (string) $count ); ?></span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
