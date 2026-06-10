<?php
/**
 * Important Notice - Dialog Popup (Component Template)
 * Shows once per session/day as a small modal dialog
 *
 * CSS: assets/css/components/important-notice.css
 * JS:  assets/js/components/important-notice.js
 */
defined( 'ABSPATH' ) || exit;

$notice = ch_get_important_notice();
if ( empty( $notice['enabled'] ) ) return;

// Enqueue assets on first load
if ( ! wp_script_is( 'ch-notice-js', 'registered' ) ) {
	wp_enqueue_style(
		'ch-notice-css',
		get_template_directory_uri() . '/assets/css/components/important-notice.css',
		[],
		wp_get_theme()->get( 'Version' )
	);
	wp_enqueue_script(
		'ch-notice-js',
		get_template_directory_uri() . '/assets/js/components/important-notice.js',
		[],
		wp_get_theme()->get( 'Version' ),
		true
	);
}

$title   = esc_html( $notice['title']      ?? 'Important Update' );
$message = wp_kses_post( $notice['message'] ?? '' );
$image   = esc_url( $notice['image']       ?? '' );
$button  = $notice['button_label']         ?? '';
$btn_url = esc_url( $notice['button_url']  ?? '' );
?>

<!-- Notice Modal Overlay -->
<div class="ch-notice-overlay" id="ch-notice-overlay" data-notice-id="<?php echo esc_attr( $notice['id'] ?? 'default' ); ?>"></div>

<!-- Notice Dialog -->
<dialog class="ch-notice-dialog" id="ch-notice" role="dialog" aria-labelledby="ch-notice-title">
	<!-- Close Button (Outside content for proper positioning) -->
	<button type="button" class="ch-notice-close" aria-label="Close notice">
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M18 6L6 18M6 6l12 12"/>
		</svg>
	</button>

	<div class="ch-notice-dialog-content">
		<!-- Image (if provided) -->
		<?php if ( $image ) : ?>
		<div class="ch-notice-image">
			<img src="<?php echo $image; ?>" alt="<?php echo $title; ?>" loading="lazy">
		</div>
		<?php endif; ?>

		<!-- Title -->
		<h3 class="ch-notice-title" id="ch-notice-title"><?php echo $title; ?></h3>

		<!-- Message -->
		<?php if ( $message ) : ?>
		<div class="ch-notice-message"><?php echo $message; ?></div>
		<?php endif; ?>

		<!-- Button -->
		<?php if ( $button && $btn_url ) : ?>
		<div class="ch-notice-actions">
			<a href="<?php echo $btn_url; ?>" class="ch-notice-btn"><?php echo esc_html( $button ); ?></a>
		</div>
		<?php endif; ?>
	</div>
</dialog>
