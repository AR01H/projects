<?php
/**
 * components/parts/spotlights_widget.php
 * Spotlight panel — two rendering modes.
 *
 * Props:
 *   term_slug    string  required
 *   max_items    int     optional — override term's max_display
 *   widget_title string  optional — override heading
 *   sidebar      bool    optional — true = sw-panel via sidebar_link_list
 *                                   false (default) = sp-panel via list_widget
 */

defined( 'ABSPATH' ) || exit;

$_sp_slug    = isset( $term_slug )    ? sanitize_key( (string) $term_slug )  : '';
$_sp_max     = isset( $max_items )    ? (int) $max_items                      : 0;
$_sp_title   = isset( $widget_title ) ? (string) $widget_title               : '';
$_is_sidebar = ! empty( $sidebar );

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

if ( $_is_sidebar ) {

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
			'url'   => ! empty( $_sp->show_link ) && ! empty( $_sp->link_url ) ? (string) $_sp->link_url : '',
		);
	}

	adn_component( 'parts/sidebar_link_list', array( 'list' => array(
		'heading' => $_heading,
		'items'   => $_items,
	) ) );

} else {

	/* ── Section mode: sp-panel via list_widget ── */
	$_sp_cards = array();
	foreach ( $rows as $_sp ) {
		$_icon     = trim( (string) ( $_sp->icon ?? '' ) );
		$_val      = trim( (string) ( $_sp->point_value ?? '' ) );
		$_lbl      = trim( (string) ( $_sp->point_label ?? '' ) );
		$_has_link = ! empty( $_sp->show_link ) && ! empty( $_sp->link_url );

		$_sp_cards[] = array(
			'icon'        => '' !== $_icon ? $_icon : mb_strtoupper( mb_substr( (string) $_sp->title, 0, 1 ) ),
			'title'       => (string) $_sp->title,
			'tag'         => $_lbl,
			'meta'        => $_val,
			'thumb_label' => ! empty( $_sp->link_label )  ? (string) $_sp->link_label  : '',
			'desc'        => ! empty( $_sp->description ) ? (string) $_sp->description : '',
			'url'         => $_has_link ? (string) $_sp->link_url : '',
		);
	}

	?>
	<div class="sp-panel mini_card_container_design" data-term="<?php echo esc_attr( $_sp_slug ); ?>">
		<?php adn_component( 'parts/list_widget', array( 'widget' => array(
			'heading' => array( 'title' => $_heading ),
			'items'   => $_sp_cards,
			'tag'     => 'h4',
		) ) ); ?>
	</div>
	<?php

}
