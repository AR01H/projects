<?php
/**
 * components/parts/random_mini_cta.php - Dynamic Random Mini CTA Banner
 *
 * 100% DB / WordPress Options-driven.
 * Randomly selects and renders a high-converting, luxury mini CTA message
 * managed directly from WordPress Admin (Additionals -> Random CTAs).
 *
 * Props:
 *   $cta?           array        Optional specific CTA item to render
 *   $exclude_id?    string|array Optional CTA IDs to exclude from random pool
 *   $wrapper_class? string       Optional CSS classes to append to wrapper
 *
 * Usage:
 *   adn_component( 'parts/random_mini_cta' );
 *   // or helper:
 *   adn_random_mini_cta();
 */

defined( 'ABSPATH' ) || exit;

// Check if master feature is enabled
if ( class_exists( 'ADN_Additionals_Handler' ) && ! ADN_Additionals_Handler::is_enabled() ) {
	return;
}

// 1. Resolve CTA item (passed explicitly or randomly chosen from active pool)
$_item = isset( $cta ) && is_array( $cta ) ? $cta : null;

if ( empty( $_item ) ) {
	static $cached_random_ctas = null;
	if ( null === $cached_random_ctas ) {
		if ( class_exists( 'ADN_Additionals_Handler' ) ) {
			$cached_random_ctas = ADN_Additionals_Handler::get_active_ctas();
		} else {
			$db_ctas = get_option( 'ah_random_cta_messages', array() );
			$cached_random_ctas = is_array( $db_ctas ) ? $db_ctas : array();
		}
	}

	$available_pool = $cached_random_ctas;

	// Detect current page path to avoid showing a CTA linking to the page the user is currently on
	$current_uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	$current_path = trim( (string) ( parse_url( $current_uri, PHP_URL_PATH ) ?: '' ), '/' );

	$_exclude_list = array();
	if ( ! empty( $exclude_id ) ) {
		$_exclude_list = is_array( $exclude_id ) ? $exclude_id : array( $exclude_id );
	}

	$filtered_pool = array();
	foreach ( $available_pool as $_c ) {
		$_cid = isset( $_c['id'] ) ? (string) $_c['id'] : '';
		if ( in_array( $_cid, $_exclude_list, true ) ) {
			continue;
		}

		// Check if CTA button_url matches current path
		if ( ! empty( $_c['button_url'] ) && ! empty( $current_path ) ) {
			$_btn_path = trim( (string) ( parse_url( $_c['button_url'], PHP_URL_PATH ) ?: '' ), '/' );
			if ( ! empty( $_btn_path ) && ( $_btn_path === $current_path || strpos( $current_path, $_btn_path ) === 0 ) ) {
				continue;
			}
		}

		$filtered_pool[] = $_c;
	}

	// Fallback to full pool if all were filtered out
	if ( empty( $filtered_pool ) ) {
		$filtered_pool = $available_pool;
	}

	if ( ! empty( $filtered_pool ) ) {
		$random_idx = array_rand( $filtered_pool );
		$_item = $filtered_pool[ $random_idx ];
	}
}

if ( empty( $_item ) || empty( $_item['message'] ) ) {
	return;
}

// 2. Extract and sanitize values
$_id          = isset( $_item['id'] ) ? sanitize_key( $_item['id'] ) : 'cta';
$_heading     = isset( $_item['heading'] ) ? trim( (string) $_item['heading'] ) : '';
$_message     = isset( $_item['message'] ) ? trim( (string) $_item['message'] ) : '';
$_icon        = isset( $_item['icon'] ) ? trim( (string) $_item['icon'] ) : 'fa-solid fa-sparkles';
$_color       = isset( $_item['color'] ) ? sanitize_hex_color( $_item['color'] ) : '#1e3a2f';
if ( ! $_color ) {
	$_color = '#1e3a2f';
}
$_color_name  = isset( $_item['color_name'] ) ? sanitize_html_class( $_item['color_name'] ) : 'default';
$_btn_name    = isset( $_item['button_name'] ) ? trim( (string) $_item['button_name'] ) : 'Learn More';
$_btn_url     = isset( $_item['button_url'] ) ? (string) $_item['button_url'] : '#';
if ( strpos( $_btn_url, 'http' ) !== 0 && strpos( $_btn_url, '#' ) !== 0 && strpos( $_btn_url, '/' ) === 0 ) {
	$_btn_url = home_url( $_btn_url );
}

$_wrap_classes = array(
	'ah-random-mini-cta',
	'ah-random-mini-cta--' . $_color_name,
	'ah-random-mini-cta--' . $_id,
);
if ( ! empty( $wrapper_class ) ) {
	$_wrap_classes[] = sanitize_html_class( $wrapper_class );
}
?>
<aside class="<?php echo esc_attr( implode( ' ', $_wrap_classes ) ); ?>" style="--ah-cta-color: <?php echo esc_attr( $_color ); ?>;" aria-label="<?php echo esc_attr( $_heading ?: 'Featured Guidance' ); ?>">
	<!-- Ambient Background Glow & Particles -->
	<div class="ah-mini-cta__bg" aria-hidden="true">
		<span class="ah-mini-cta__glow"></span>
		<span class="ah-mini-cta__shimmer"></span>
	</div>

	<div class="ah-mini-cta__container">
		<!-- Icon Emblem -->
		<div class="ah-mini-cta__icon-badge" aria-hidden="true">
			<span class="ah-mini-cta__icon"><?php echo adn_icon( $_icon ); ?></span>
		</div>

		<!-- Content Block -->
		<div class="ah-mini-cta__content">
			<?php if ( ! empty( $_heading ) ) : ?>
				<h4 class="ah-mini-cta__heading"><?php echo esc_html( $_heading ); ?></h4>
			<?php endif; ?>
			<p class="ah-mini-cta__message"><?php echo esc_html( $_message ); ?></p>
		</div>

		<!-- Action Button -->
		<div class="ah-mini-cta__action">
			<a href="<?php echo esc_url( $_btn_url ); ?>" class="ah-mini-cta__btn">
				<span class="ah-mini-cta__btn-text"><?php echo esc_html( $_btn_name ); ?></span>
				<span class="ah-mini-cta__btn-icon" aria-hidden="true">
					<svg width="15" height="15" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M3.33337 8H12.6667M12.6667 8L8.00004 3.33334M12.6667 8L8.00004 12.6667" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			</a>
		</div>
	</div>
</aside>
