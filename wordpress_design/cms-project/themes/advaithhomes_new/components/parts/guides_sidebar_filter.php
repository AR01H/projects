<?php
/**
 * components/parts/guides_sidebar_filter.php - Left sidebar for guides listing.
 *
 * Renders:
 *   1. Hierarchical category groups (parent + subtopics) — drives JS filter via data-cat
 *   2. Quick tools widget
 *   3. News mini widget
 *   4. Expert help CTA
 *
 * Props: $sidebar {
 *   cat_groups[]  { label, slug, icon, url, topics[]{ label, url } }
 *   browse_cats[] { label, slug, active }   — kept for JS filter compatibility
 *   quick_tools   { heading, items[]{ icon, label, url }, cta{ label, url } }
 *   news_mini     { heading, items[]{ title, date, tag, gradient, url }, view_all{ label, url } }
 *   expert_help   { heading, subtitle, cta{ label, url } }
 * }
 */

defined( 'ABSPATH' ) || exit;

$sidebar      = isset( $sidebar ) && is_array( $sidebar ) ? $sidebar : array();
$cat_groups   = isset( $sidebar['cat_groups'] )  && is_array( $sidebar['cat_groups'] )  ? $sidebar['cat_groups']  : array();
$browse_cats  = isset( $sidebar['browse_cats'] ) && is_array( $sidebar['browse_cats'] ) ? $sidebar['browse_cats'] : array();
$expert_help  = isset( $sidebar['expert_help'] ) && is_array( $sidebar['expert_help'] ) ? $sidebar['expert_help'] : array();
?>
<aside class="guides-sidebar">

	<?php /* ── Hierarchical category groups ── */ ?>
	<?php if ( ! empty( $cat_groups ) ) : ?>
	<div class="guides-sidebar-box guides-cat-tree">
		<div class="gct-heading"><?php echo esc_html( defined( 'SITE_CONTENT_PLURAL' ) ? SITE_CONTENT_PLURAL : 'Browse' ); ?></div>
		<?php foreach ( $cat_groups as $grp ) :
			$_g_label  = isset( $grp['label'] )  ? (string) $grp['label']  : '';
			$_g_slug   = isset( $grp['slug'] )   ? (string) $grp['slug']   : '';
			$_g_icon   = isset( $grp['icon'] )   ? (string) $grp['icon']   : '📁';
			$_g_url    = isset( $grp['url'] )    ? (string) $grp['url']    : '#';
			$_g_topics = isset( $grp['topics'] ) && is_array( $grp['topics'] ) ? $grp['topics'] : array();
			if ( '' === $_g_label ) { continue; }
		?>
		<div class="gct-group">
			<button
				class="sidebar-cat-item gct-parent"
				data-cat="<?php echo esc_attr( $_g_label ); ?>"
				type="button"
				aria-expanded="false"
			>
				<span class="gct-icon" aria-hidden="true"><?php echo esc_html( $_g_icon ); ?></span>
				<span class="gct-label"><?php echo esc_html( $_g_label ); ?></span>
				<?php if ( ! empty( $_g_topics ) ) : ?>
				<span class="gct-chevron" aria-hidden="true">›</span>
				<?php endif; ?>
			</button>

			<?php if ( ! empty( $_g_topics ) ) : ?>
			<ul class="gct-children">
				<li>
					<a href="<?php echo esc_url( adn_link( $_g_url ) ); ?>" class="gct-child gct-child--all">
						<?php echo esc_html( sprintf( defined( 'SITE_LABEL_VIEW_ALL' ) ? 'All %s' : 'All %s', $_g_label ) ); ?>
					</a>
				</li>
				<?php foreach ( $_g_topics as $topic ) :
					$_t_label = isset( $topic['label'] ) ? (string) $topic['label'] : '';
					$_t_url   = isset( $topic['url'] )   ? (string) $topic['url']   : '#';
					if ( '' === $_t_label ) { continue; }
				?>
				<li>
					<a href="<?php echo esc_url( adn_link( $_t_url ) ); ?>" class="gct-child"
						data-cat="<?php echo esc_attr( $_t_label ); ?>">
						<?php echo esc_html( $_t_label ); ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php elseif ( ! empty( $browse_cats ) ) : ?>
	<?php /* Fallback: flat filter buttons */ ?>
	<div class="guides-sidebar-box">
		<?php foreach ( $browse_cats as $cat ) :
			$active = ! empty( $cat['active'] );
		?>
			<button
				class="sidebar-cat-item<?php echo $active ? ' active' : ''; ?>"
				data-cat="<?php echo esc_attr( isset( $cat['label'] ) ? $cat['label'] : '' ); ?>"
				type="button"
			>
				<span><?php echo esc_html( isset( $cat['label'] ) ? $cat['label'] : '' ); ?></span>
				<?php if ( ! empty( $cat['count'] ) ) : ?>
					<span class="cat-count"><?php echo esc_html( (string) (int) $cat['count'] ); ?></span>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php /* ── Quick tools ── */ ?>
	<?php if ( ! empty( $quick_tools['items'] ) ) : ?>
		<?php adn_component( 'parts/sidebar_quick_tools', array( 'quick_tools' => $quick_tools ) ); ?>
	<?php endif; ?>

	<?php /* ── News mini ── */ ?>
	<?php if ( ! empty( $news_mini['items'] ) ) : ?>
		<?php adn_component( 'parts/sidebar_news_mini', array( 'news_mini' => $news_mini ) ); ?>
	<?php endif; ?>

	<?php /* ── Expert help CTA ── */ ?>
	<?php if ( ! empty( $expert_help['cta']['label'] ) ) : ?>
		<?php adn_component( 'parts/sidebar_expert_help', array( 'expert_help' => $expert_help ) ); ?>
	<?php endif; ?>

</aside>
