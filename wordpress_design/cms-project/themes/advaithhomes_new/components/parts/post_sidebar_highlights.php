<?php
/**
 * components/parts/post_sidebar_highlights.php
 * Sidebar highlight links — action buttons inside sw-panel.
 *
 * Props: $highlight_links[] { name, url }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $highlight_links ) ) { return; }
?>
<div class="sw-panel">
	<div class="sw-header">
		<h3 class="sw-title"><?php esc_html_e( 'Highlights', ADN_TEXT_DOMAIN ); ?></h3>
	</div>
	<div class="highlight-buttons">
		<?php foreach ( (array) $highlight_links as $hl ) : ?>
			<a href="<?php echo esc_url( adn_link( $hl['url'] ) ); ?>" class="highlight-btn">
				<span><?php echo esc_html( $hl['name'] ); ?></span>
				<span class="highlight-arrow" aria-hidden="true">→</span>
			</a>
		<?php endforeach; ?>
	</div>
</div>
