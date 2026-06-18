<?php
/**
 * components/parts/post_sidebar_related_content.php
 * 
 * Renders the Related Content links in the post sidebar.
 */
defined( 'ABSPATH' ) || exit;

if ( empty( $related_content ) || ! is_array( $related_content ) ) {
	return;
}

$headings = array(
	'articles'   => adn_term( 'sidebar.related_articles', 'Related Articles' ),
	'components' => adn_term( 'sidebar.related_tools', 'Useful Tools' ),
	'support'    => adn_term( 'sidebar.related_support', 'Help & Support' ),
	'external'   => adn_term( 'sidebar.related_external', 'External Links' ),
	'related'    => adn_term( 'sidebar.related_content', 'Related Content' ),
);
?>

<?php foreach ( $related_content as $group => $links ) : ?>
	<?php if ( empty( $links ) ) { continue; } ?>
	<div class="sidebar-box widget-related-content mini_card_container_design">
		<?php 
		$norm_group = strtolower( trim( $group ) );
		if ( 'new' !== $norm_group && 'highlights' !== $norm_group && 'highlight' !== $norm_group ) : 
		?>
			<h3>
				<?php echo esc_html( isset( $headings[ $group ] ) ? $headings[ $group ] : ucwords( str_replace( '_', ' ', $group ) ) ); ?>
			</h3>
		<?php endif; ?>
		<ul class="sidebar-link-list ">
			<?php foreach ( $links as $link ) : ?>
				<li>
					<a href="<?php echo esc_url( adn_link( $link['url'] ) ); ?>">
						<?php if ( ! empty( $link['icon'] ) ) : ?>
							<span class="link-icon"><?php echo adn_icon( $link['icon'] ); ?></span>
						<?php endif; ?>
						<span><?php echo esc_html( $link['title'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php endforeach; ?>

<style>
.widget-related-content.sidebar-box {
	padding: 14px 16px;
}
.widget-related-content h3 {
	margin-bottom: 10px;
	padding-bottom: 8px;
}
.widget-related-content .sidebar-link-list {
	list-style: none;
	padding: 0;
	margin: 0;
	display: flex;
	flex-direction: column;
	gap: 2px;
}
.widget-related-content .sidebar-link-list li a {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 8px 10px;
	border-radius: 6px;
	text-decoration: none;
	color: var(--color-text, #374151);
	transition: background 0.15s ease, color 0.15s ease;
}
.widget-related-content .sidebar-link-list li a:hover {
	background: var(--color-surface, #f3f4f6);
	color: var(--color-primary, #1d5c8e);
}
.widget-related-content .link-icon {
	font-size: 1.1rem;
	width: 24px;
	text-align: center;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	opacity: 0.85;
}
.widget-related-content .sidebar-link-list li a span {
	font-size: 0.83rem;
	font-weight: 500;
	line-height: 1.4;
}
</style>
