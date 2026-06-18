<?php
/**
 * components/parts/post_sidebar_highlights.php
 * 
 * Renders the Highlight Links buttons in the post sidebar.
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $highlight_links ) ) {
	return;
}
?>
<div class="sidebar-box widget-highlights mini_card_container_design">
	<h3><?php esc_html_e( 'Highlights', ADN_TEXT_DOMAIN ); ?></h3>
	<div class="highlight-buttons">
		<?php foreach ( $highlight_links as $hl ) : ?>
			<a href="<?php echo esc_url( adn_link( $hl['url'] ) ); ?>" class="highlight-btn">
				<span class="highlight-label"><?php echo esc_html( $hl['name'] ); ?></span>
				<span class="highlight-arrow" aria-hidden="true">→</span>
			</a>
		<?php endforeach; ?>
	</div>
</div>

<style>
.widget-highlights h3 {
	margin-bottom: 10px;
	padding-bottom: 8px;
}
.widget-highlights .highlight-buttons {
	display: flex;
	flex-direction: column;
	gap: 10px;
}
.widget-highlights .highlight-btn {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 12px 16px;
	background: linear-gradient(135deg, rgba(29, 92, 142, 0.04) 0%, rgba(29, 92, 142, 0.08) 100%);
	border: 1px solid rgba(29, 92, 142, 0.15);
	border-radius: 8px;
	color: var(--color-primary, #1d5c8e);
	font-weight: 500;
	font-size: 0.85rem;
	text-decoration: none;
	transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.widget-highlights .highlight-btn:hover {
	background: var(--color-primary, #1d5c8e);
	color: #fff;
	border-color: var(--color-primary, #1d5c8e);
	transform: translateY(-1px);
	box-shadow: 0 4px 12px rgba(29, 92, 142, 0.15);
}
.widget-highlights .highlight-arrow {
	transition: transform 0.2s ease;
	font-size: 0.9rem;
}
.widget-highlights .highlight-btn:hover .highlight-arrow {
	transform: translateX(4px);
}
</style>
