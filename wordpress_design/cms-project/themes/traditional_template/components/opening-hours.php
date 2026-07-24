<?php
/**
 * Opening hours - a week table with a live open / closed badge.
 *
 * GENERIC: any business that keeps hours. Switch data per page with `source`.
 *
 * The badge is worked out on the SERVER from the site's WordPress timezone, so
 * it is correct in the HTML with no JavaScript and cannot disagree with a
 * visitor's device clock. Rows whose `day` matches today are highlighted.
 *
 * A closing time earlier than the opening time is read as running past
 * midnight (e.g. 18:00 - 01:00), so late venues work without extra config.
 *
 * Data: { tag, title (em allowed), sub, note,
 *         days[] { label, day (0 = Sunday … 6 = Saturday),
 *                  open "HH:MM", close "HH:MM", closed (bool), text } }
 */
defined( 'ABSPATH' ) || exit;

$oh_source = ( isset( $source ) && $source ) ? (string) $source : 'opening_hours';
$data      = nt_data( $oh_source );
$days      = ( is_array( $data ) && ! empty( $data['days'] ) ) ? (array) $data['days'] : array();
if ( empty( $days ) ) {
	return;
}

$tag   = $data['tag']   ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub']   ?? '';
$note  = $data['note']  ?? '';

$now       = current_datetime();
$today_num = (int) $now->format( 'w' );
$minute_of = (int) $now->format( 'G' ) * 60 + (int) $now->format( 'i' );

/**
 * "HH:MM" -> minutes since midnight, or null when unparseable.
 */
$to_minutes = static function ( $value ) {
	if ( ! is_string( $value ) || ! preg_match( '/^(\d{1,2}):(\d{2})$/', trim( $value ), $m ) ) {
		return null;
	}
	$hours   = (int) $m[1];
	$minutes = (int) $m[2];
	if ( $hours > 24 || $minutes > 59 ) {
		return null;
	}
	return $hours * 60 + $minutes;
};

// Walk the week and decide whether any row covers "now". A row that runs past
// midnight is also checked against yesterday's slot spilling into today.
$is_open = false;
$rows    = array();

foreach ( $days as $day ) {
	$day   = (array) $day;
	$label = trim( (string) ( $day['label'] ?? '' ) );
	if ( '' === $label ) {
		continue;
	}

	$day_num = isset( $day['day'] ) ? (int) $day['day'] % 7 : -1;
	$closed  = ! empty( $day['closed'] );
	$open_m  = $closed ? null : $to_minutes( $day['open'] ?? '' );
	$close_m = $closed ? null : $to_minutes( $day['close'] ?? '' );
	$overnight = ( null !== $open_m && null !== $close_m && $close_m <= $open_m );

	if ( null !== $open_m && null !== $close_m ) {
		$span = $overnight ? ( $close_m + 1440 ) : $close_m;
		if ( $day_num === $today_num && $minute_of >= $open_m && $minute_of < $span ) {
			$is_open = true;
		}
		// Yesterday's overnight slot still running into today.
		if ( $overnight && $day_num === ( ( $today_num + 6 ) % 7 ) && $minute_of < $close_m ) {
			$is_open = true;
		}
	}

	if ( ! empty( $day['text'] ) ) {
		$value = (string) $day['text'];
	} elseif ( $closed || null === $open_m || null === $close_m ) {
		$value = __( 'Closed', NT_TEXT_DOMAIN );
	} else {
		/* translators: 1: opening time, 2: closing time. */
		$value = sprintf( __( '%1$s - %2$s', NT_TEXT_DOMAIN ), $day['open'], $day['close'] );
	}

	$rows[] = array(
		'label'   => $label,
		'value'   => $value,
		'closed'  => ( $closed || null === $open_m ),
		'is_today' => ( $day_num === $today_num ),
	);
}

if ( empty( $rows ) ) {
	return;
}
?>
<section class="nt-hours" id="opening-hours">
	<div class="container nt-hours__inner">

		<div class="nt-hours__intro">
			<?php if ( $tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="section-title"><?php echo wp_kses( $title, array( 'em' => array() ) ); ?></h2>
			<?php endif; ?>

			<p class="nt-hours__status <?php echo $is_open ? 'is-open' : 'is-closed'; ?>">
				<span class="nt-hours__dot" aria-hidden="true"></span>
				<?php echo $is_open ? esc_html__( 'Open now', NT_TEXT_DOMAIN ) : esc_html__( 'Closed right now', NT_TEXT_DOMAIN ); ?>
			</p>

			<?php if ( $sub ) : ?><p class="section-body"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
			<?php if ( $note ) : ?><p class="nt-hours__note"><?php echo esc_html( $note ); ?></p><?php endif; ?>
		</div>

		<table class="nt-hours__table">
			<caption class="screen-reader-text"><?php esc_html_e( 'Opening hours by day', NT_TEXT_DOMAIN ); ?></caption>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr class="nt-hours__row<?php echo $row['is_today'] ? ' is-today' : ''; ?><?php echo $row['closed'] ? ' is-shut' : ''; ?>">
						<th scope="row" class="nt-hours__day">
							<?php echo esc_html( $row['label'] ); ?>
							<?php if ( $row['is_today'] ) : ?>
								<span class="nt-hours__today"><?php esc_html_e( 'Today', NT_TEXT_DOMAIN ); ?></span>
							<?php endif; ?>
						</th>
						<td class="nt-hours__time"><?php echo esc_html( $row['value'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	</div>
</section>
