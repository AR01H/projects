<?php
/**
 * components/parts/spotlights_widget.php
 * Spotlight panel - pure presentation, receives data via props.
 *
 * Props:
 *   heading   string  section heading
 *   items[]   array   shaped items from SpotlightService::buildProps()
 *   slug      string  spotlight term slug (for data attributes)
 *   mode      string  'compact' | 'sidebar' | 'section'
 *   tag       string  heading tag: h2|h3|h4 (default: h4)
 */

defined( 'ABSPATH' ) || exit;

$heading = isset( $heading ) ? (string) $heading : '';
$items   = isset( $items ) && is_array( $items ) ? $items : array();
$slug    = isset( $slug )  ? (string) $slug : '';
$mode    = isset( $mode )  ? (string) $mode : 'section';

if ( empty( $items ) ) { return; }

$htag = isset( $tag ) && in_array( $tag, array( 'h2', 'h3', 'h4' ), true ) ? $tag : 'h4';

/* ── Compact mode: metric cards for category top band / home ── */
if ( 'compact' === $mode ) {
	?>
	<div class="sp-metrics-panel" data-term="<?php echo esc_attr( $slug ); ?>">
		<div class="sp-metrics-grid">
			<?php foreach ( $items as $_item ) :
				$_val   = $_item['value'] ?? '';
				$_lbl   = $_item['label'] ?? '';
				$_url   = $_item['url'] ?? '';
				$_title = $_item['title'] ?? '';
				$_tag   = $_item['description'] ?? '';
				$_icon  = $_item['icon'] ?? '';
				$_link_label = $_item['link_label'] ?? '';
			?>
				<?php if ( '' !== $_url ) : ?>
				<a href="<?php echo esc_url( $_url ); ?>" class="sp-metric-card" title="<?php echo esc_attr( $_title ); ?>">
				<?php else : ?>
				<div class="sp-metric-card" title="<?php echo esc_attr( $_title ); ?>">
				<?php endif; ?>
				<div class="sp-metric-card__body">
						<span class="sp-metric-card__label"><?php echo esc_html( $_title ); ?></span>
						<?php if ( '' !== $_val || '' !== $_lbl ) : ?>
							<div class="sp-metric-detail_label">
								<?php if ( '' !== $_val ) : ?>
									<strong class="sp-metric-card__value"><?php echo esc_html( $_val ); ?></strong>
								<?php endif; ?>
								<?php if ( '' !== $_lbl ) : ?>
									<span class="sp-metric-card__meta"><?php echo esc_html( $_lbl ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ( '' !== $_tag ) : ?>
							<span class="sp-metric-card__desc" title="<?php echo esc_html( $_tag ); ?>"><?php echo esc_html( $_tag ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $_link_label ) : ?>
							<span class="spotlight-card__link-label"><?php echo esc_html( $_link_label ); ?><?php if ( '' !== $_url ) : ?> <i class="fa-solid fa-arrow-right" aria-hidden="true" style="font-size:0.7em;"></i><?php endif; ?></span>
						<?php endif; ?>
					</div>
					<?php if ( '' !== $_icon ) : ?>
						<span class="sp-metric-card__icon" aria-hidden="true"><?php echo \adn_icon( $_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
				<?php if ( '' !== $_url ) : ?>
				</a>
				<?php else : ?>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return;
}

/* ── Sidebar mode: compact sw-panel list ── */
if ( 'sidebar' === $mode ) {
	$_sidebar_items = array();
	foreach ( $items as $_item ) {
		$_val  = $_item['value'] ?? '';
		$_lbl  = $_item['label'] ?? '';
		$_meta = ( '' !== $_val && '' !== $_lbl ) ? $_val . ' ' . $_lbl : ( '' !== $_val ? $_val : $_lbl );

		$_sidebar_items[] = array(
			'icon'  => ! empty( $_item['icon'] ) ? $_item['icon'] : mb_strtoupper( mb_substr( $_item['title'] ?? '', 0, 1 ) ),
			'label' => $_item['title'] ?? '',
			'meta'  => $_meta,
			'url'   => $_item['url'] ?? '',
		);
	}

	\adn_component( 'parts/sidebar_link_list', array( 'list' => array(
		'heading' => $heading,
		'items'   => $_sidebar_items,
	) ) );
	return;
}

/* ── Section mode: sp-panel via spotlight_card ── */
?>
<div class="sp-panel mini_card_container_design spotlight-panel" data-term="<?php echo esc_attr( $slug ); ?>">
	<div class="spotlight-grid">
		<div class="list-widget-header">
			<h3><?php echo esc_html( $heading ); ?></h3>
		</div>

		<div class="spotlight-items">
		<?php foreach ( $items as $_item ) :
			$_icon  = $_item['icon'] ?? '';
			$_val   = $_item['value'] ?? '';
			$_lbl   = $_item['label'] ?? '';
			$card   = array(
				'icon'        => '' !== $_icon ? $_icon : mb_strtoupper( mb_substr( $_item['title'] ?? '', 0, 1 ) ),
				'title'       => $_item['title'] ?? '',
				'tag'         => $_lbl,
				'meta'        => $_val,
				'thumb_label' => $_item['link_label'] ?? '',
				'desc'        => $_item['description'] ?? '',
				'url'         => $_item['url'] ?? '',
			);
			\adn_component( 'cards/spotlight_card', array( 'card' => $card ) );
		endforeach; ?>
		</div>
	</div>
</div>
<?php
