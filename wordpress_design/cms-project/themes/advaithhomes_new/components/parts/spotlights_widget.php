<?php
/**
 * components/parts/spotlights_widget.php
 * Spotlight panel — sidebar list widget style.
 *
 * Props:
 *   term_slug    string  required
 *   max_items    int     optional — override term's max_display
 *   widget_title string  optional — override heading
 */

defined( 'ABSPATH' ) || exit;

$_sp_slug  = isset( $term_slug )    ? sanitize_key( (string) $term_slug )  : '';
$_sp_max   = isset( $max_items )    ? (int) $max_items                      : 0;
$_sp_title = isset( $widget_title ) ? (string) $widget_title               : '';

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

$items = $wpdb->get_results( $wpdb->prepare(
	"SELECT * FROM `{$tbl_items}` WHERE term_id = %d AND is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT %d",
	(int) $term->id,
	$limit
) );

if ( empty( $items ) ) { return; }

$_heading = '' !== $_sp_title ? $_sp_title : (string) $term->name;
?>
<div class="sp-panel news-widget" data-term="<?php echo esc_attr( $_sp_slug ); ?>">

	<div class="news-widget-header">
		<h4 class="news-widget-title"><?php echo esc_html( $_heading ); ?></h4>
	</div>

	<ul class="sp-list">
	<?php foreach ( $items as $_sp ) :
		$_icon     = trim( (string) ( $_sp->icon ?? '' ) );
		$_is_emoji = '' !== $_icon && preg_match( '/\p{So}|\p{Sm}|\p{Sk}|\p{Sc}/u', $_icon );
		$_is_fa    = '' !== $_icon && ! $_is_emoji;
		$_fallback = mb_strtoupper( mb_substr( (string) $_sp->title, 0, 1 ) );
		$_stat_val = trim( (string) ( $_sp->point_value ?? '' ) );
		$_stat_lbl = trim( (string) ( $_sp->point_label ?? '' ) );
	?>
	<li class="sp-item<?php echo ( ! $_is_emoji && ! $_is_fa ) ? ' sp-item--no-icon' : ''; ?>">

		<?php if ( $_is_emoji || $_is_fa ) : ?>
		<div class="sp-item__icon" aria-hidden="true">
			<?php if ( $_is_emoji ) : ?>
				<span><?php echo esc_html( $_icon ); ?></span>
			<?php else : ?>
				<i class="<?php echo esc_attr( $_icon ); ?>"></i>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="sp-item__body">
			<span class="sp-item__title"><?php echo esc_html( $_sp->title ); ?></span>
			<?php if ( '' !== $_stat_val ) : ?>
			<span class="sp-item__stat">
				<?php echo esc_html( $_stat_val ); ?>
				<?php if ( '' !== $_stat_lbl ) : ?>
					<span class="sp-item__stat-lbl"><?php echo esc_html( $_stat_lbl ); ?></span>
				<?php endif; ?>
			</span>
			<?php endif; ?>
		</div>

	</li>
	<?php endforeach; ?>
	</ul>

</div>
