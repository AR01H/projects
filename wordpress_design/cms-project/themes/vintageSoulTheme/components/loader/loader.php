<?php
/**
 * VintageSoulTheme - Cinematic Sugarcane Plantation Forest Gates Page Loader
 *
 * Grand wooden plantation double doors with authentic tall standing sugarcane farm plantation
 * engraving and large prominent central Cane House emblem crest with botanical loading progress.
 * All texts, labels, and image assets are loaded dynamically from data/content/loader.json.
 */
use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

// Only execute on the Home Page
if ( ! is_front_page() && ! is_home() ) {
	return;
}

// 1. Fetch Loader Data from JSON
$loader_data = (array) ( JsonFileProvider::read( 'data/content/loader.json' ) ?? array() );

$pick_random_image = static function( $candidates, string $default ): string {
	if ( is_string( $candidates ) && '' !== trim( $candidates ) ) {
		return trim( $candidates );
	}
	if ( is_array( $candidates ) && ! empty( $candidates ) ) {
		$valid = array_values( array_filter( array_map( 'trim', array_map( 'strval', $candidates ) ) ) );
		if ( ! empty( $valid ) ) {
			return $valid[ array_rand( $valid ) ];
		}
	}
	return $default;
};

$raw_left   = $left_images ?? $left_image ?? $loader_data['left_door_images'] ?? $loader_data['left_door_image'] ?? $loader_data['door_farm_image'] ?? null;
$raw_right  = $right_images ?? $right_image ?? $loader_data['right_door_images'] ?? $loader_data['right_door_image'] ?? $loader_data['door_farm_image'] ?? null;
$raw_mobile = $mobile_images ?? $mobile_image ?? $loader_data['mobile_images'] ?? $loader_data['mobile_image'] ?? $loader_data['door_farm_image'] ?? null;

$left_door_img   = UrlHelper::resolve( $pick_random_image( $raw_left, 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg' ) );
$right_door_img  = UrlHelper::resolve( $pick_random_image( $raw_right, 'assets/images/backgrounds/vintage_coldpress_bar_catering.jpg' ) );
$mobile_door_img = UrlHelper::resolve( $pick_random_image( $raw_mobile, 'assets/images/backgrounds/sugarcane_farm_plantation_engraving.jpg' ) );

$logo_img     = UrlHelper::resolve( (string) ( $loader_data['logo_image'] ?? 'assets/images/logo/logo.png' ) );
$wood_texture = UrlHelper::resolve( (string) ( $loader_data['door_wood_texture'] ?? 'assets/images/decorative/wooden-plank-riveted.png' ) );
$seal_tagline = (string) ( $loader_data['tagline'] ?? 'EST. LONDON • 100% PURE RAW CANE' );
$status_text  = (string) ( $loader_data['status_text'] ?? 'OPENING THE CANE GATES...' );
?>
<div id="cane-plantation-loader" 
	class="plantation-loader" 
	style="--loader-left-img: url('<?php echo esc_url( $left_door_img ); ?>'); --loader-right-img: url('<?php echo esc_url( $right_door_img ); ?>'); --loader-mobile-img: url('<?php echo esc_url( $mobile_door_img ); ?>');" 
	aria-live="polite" 
	aria-label="<?php esc_attr_e( 'Loading The Cane House', 'vintagesoul' ); ?>">
	
	<!-- 1. The Grand Plantation Double Doors (Left & Right Panels) -->
	<div class="plantation-loader__doors-stage">
		
		<!-- Left Door Panel: Distinct Left Background Image -->
		<div class="plantation-loader__door plantation-loader__door--left" style="background-image: url('<?php echo esc_url( $wood_texture ); ?>'), linear-gradient(135deg, #361c0b 0%, #1f0f04 60%, #120802 100%);">
			<div class="plantation-loader__door-overlay"></div>
			<div class="plantation-loader__door-frame">
				<div class="plantation-loader__door-farm plantation-loader__door-farm--left"></div>
				<div class="plantation-loader__door-handle handle-left">
					<div class="handle-bracket top-bracket"></div>
					<div class="handle-plate">
						<div class="handle-ring"></div>
					</div>
					<div class="handle-bracket bottom-bracket"></div>
				</div>
			</div>
			<div class="plantation-loader__door-seam"></div>
		</div>

		<!-- Right Door Panel: Distinct Right Background Image -->
		<div class="plantation-loader__door plantation-loader__door--right" style="background-image: url('<?php echo esc_url( $wood_texture ); ?>'), linear-gradient(225deg, #361c0b 0%, #1f0f04 60%, #120802 100%);">
			<div class="plantation-loader__door-overlay"></div>
			<div class="plantation-loader__door-frame">
				<div class="plantation-loader__door-farm plantation-loader__door-farm--right"></div>
				<div class="plantation-loader__door-handle handle-right">
					<div class="handle-bracket top-bracket"></div>
					<div class="handle-plate">
						<div class="handle-ring"></div>
					</div>
					<div class="handle-bracket bottom-bracket"></div>
				</div>
			</div>
			<div class="plantation-loader__door-seam"></div>
		</div>

	</div>

	<!-- 2. Center Emblem Crest & Botanical Loading Progress (Prominent Large Logo) -->
	<div class="plantation-loader__center-emblem">
		<div class="plantation-loader__crest-glow"></div>
		<div class="plantation-loader__crest-frame">
			<div class="plantation-loader__logo-wrap">
				<img class="plantation-loader__logo-icon" src="<?php echo esc_url( $logo_img ); ?>" alt="The Cane House Crest" width="210" height="210">
			</div>
			<div class="plantation-loader__seal-text">
				<span><?php echo esc_html( $seal_tagline ); ?></span>
			</div>
			<div class="plantation-loader__progress-track">
				<div class="plantation-loader__progress-bar"></div>
			</div>
			<p class="plantation-loader__status-text"><?php echo esc_html( $status_text ); ?></p>
		</div>
	</div>

</div>
