<?php
/**
 * components/parts/spotlights_widget.php
 * Spotlight panel - two rendering modes.
 *
 * Props:
 *   term_slug    string  required
 *   max_items    int     optional - override term's max_display
 *   widget_title string  optional - override heading
 *   sidebar      bool    optional - true = sw-panel via sidebar_link_list
 *                                   false (default) = sp-panel via list_widget
 *   compact      bool    optional - true = metric cards for category top band
 */

defined( 'ABSPATH' ) || exit;

$_sp_slug    = isset( $term_slug )    ? sanitize_key( (string) $term_slug )  : '';
$_sp_max     = isset( $max_items )    ? (int) $max_items                      : 0;
$_sp_title   = isset( $widget_title ) ? (string) $widget_title               : '';
$_is_sidebar = ! empty( $sidebar );
$_is_compact  = ! empty( $compact );

if ( '' === $_sp_slug ) { return; }

global $wpdb;
$tbl_terms = $wpdb->prefix . 'ah_spotlight_terms';
$tbl_items = $wpdb->prefix . 'ah_spotlights';

$term = $wpdb->get_row( $wpdb->prepare(
	"SELECT * FROM `{$tbl_terms}` WHERE slug = %s AND is_active = 1 LIMIT 1",
	$_sp_slug
) );

if ( ! $term ) { return; }

$limit = $_sp_max > 0 ? $_sp_max : (int) $term->max_display;

$rows = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM `{$tbl_items}` WHERE term_id = %d AND is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT %d",
	(int) $term->id,
	$limit
) );

if ( empty( $rows ) ) { return; }

$_heading = '' !== $_sp_title ? $_sp_title : (string) $term->name;

if ( $_is_compact ) {

	?>
	<div class="sp-metrics-panel" data-term="<?php echo esc_attr( $_sp_slug ); ?>">
		<div class="sp-metrics-grid">
			<?php foreach ( $rows as $_sp ) :
				$_icon     = trim( (string) ( $_sp->icon ?? '' ) );
				$_val      = trim( (string) ( $_sp->point_value ?? '' ) );
				$_lbl      = trim( (string) ( $_sp->point_label ?? '' ) );
				$_has_link = ! empty( $_sp->show_link ) && ! empty( $_sp->link_url );
				$_url      = $_has_link ? adn_link( (string) $_sp->link_url ) : '';
				$_tag      = ! empty( $_sp->description ) ? (string) $_sp->description : '';
			?>
				<?php if ( $_url ) : ?>
				<a href="<?php echo esc_url( $_url ); ?>" class="sp-metric-card">
				<?php else : ?>
				<div class="sp-metric-card">
				<?php endif; ?>
					<div class="sp-metric-card__body">
						<span class="sp-metric-card__label">
							<?php echo esc_html((string) $_sp->title); ?>

						</span>
						<div class="sp-metric-detail_label">
							<?php if ( '' !== $_val ) : ?>
								<strong class="sp-metric-card__value"><?php echo esc_html( $_val ); ?></strong>
								<?php endif; ?>
								<?php if ( '' !== $_lbl ) : ?>
									<span class="sp-metric-card__meta"><?php echo esc_html( $_lbl ); ?></span>
									<?php endif; ?>
									<?php if (!empty($_sp->link_label)) : ?>
										<span class="spotlight-card__count">
											<?php echo ' ' . esc_html((string) $_sp->link_label); ?>
										</span>
									<?php endif; ?>
						</div>
						<?php if ( '' !== $_tag ) : ?>
							<span class="sp-metric-card__desc"><?php echo esc_html( $_tag ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( '' !== $_icon ) : ?>
						<span class="sp-metric-card__icon" aria-hidden="true"><?php echo adn_icon( $_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
				<?php if ( $_url ) : ?>
				</a>
				<?php else : ?>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php

} elseif ( $_is_sidebar ) {

	/* ── Sidebar mode: compact sw-panel list ── */
	$_items = array();
	foreach ( $rows as $_sp ) {
		$_icon = trim( (string) ( $_sp->icon ?? '' ) );
		$_val  = trim( (string) ( $_sp->point_value ?? '' ) );
		$_lbl  = trim( (string) ( $_sp->point_label ?? '' ) );
		$_meta = '' !== $_val && '' !== $_lbl ? $_val . ' ' . $_lbl : ( '' !== $_val ? $_val : $_lbl );

		$_items[] = array(
			'icon'  => '' !== $_icon ? $_icon : mb_strtoupper( mb_substr( (string) $_sp->title, 0, 1 ) ),
			'label' => (string) $_sp->title,
			'meta'  => $_meta,
			'url'   => $_has_link ? adn_link( (string) $_sp->link_url ) : '',
		);
	}

	adn_component( 'parts/sidebar_link_list', array( 'list' => array(
		'heading' => $_heading,
		'items'   => $_items,
	) ) );

} else {

	/* ── Section mode: sp-panel via list_widget ── */
	?>
	<div class="sp-panel mini_card_container_design spotlight-panel" data-term="<?php echo esc_attr( $_sp_slug ); ?>">
		<div class="spotlight-grid">
			<div class="list-widget-header">
				<h3><?= $_heading ?></h3>
			</div>

			<div class="spotlight-items">
			<?php foreach ( $rows as $_sp ) :
				$_icon     = trim( (string) ( $_sp->icon ?? '' ) );
				$_val      = trim( (string) ( $_sp->point_value ?? '' ) );
				$_lbl      = trim( (string) ( $_sp->point_label ?? '' ) );
				$_has_link = ! empty( $_sp->show_link ) && ! empty( $_sp->link_url );
				$card = array(
					'icon' => '' !== $_icon ? $_icon : mb_strtoupper( mb_substr( (string) $_sp->title, 0, 1 ) ),
					'title' => (string) $_sp->title,
					'tag' => $_lbl,
					'meta' => $_val,
					'thumb_label' => ! empty( $_sp->link_label ) ? (string) $_sp->link_label : '',
					'desc' => ! empty( $_sp->description ) ? (string) $_sp->description : '',
					'url' => $_has_link ? adn_link( (string) $_sp->link_url ) : '',
				);
				adn_component( 'cards/spotlight_card', array( 'card' => $card ) );
			endforeach; ?>
			</div>
		</div>
	</div>
	<?php

}
