<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\SettingsService;

$context       = isset( $context ) && in_array( $context, array( 'header', 'footer', 'mobile-nav' ), true ) ? $context : 'header';
$has_logo      = has_custom_logo();
$fallback_path = SettingsService::logo_fallback( $context );
$site_name     = get_bloginfo( 'name' );
$logo_class    = 'logo logo--' . $context;
?>
<?php if ( $has_logo ) : ?>
	<div class="<?php echo esc_attr( $logo_class ); ?>">
		<?php the_custom_logo(); ?>
	</div>
<?php elseif ( '' !== $fallback_path && is_file( VINTAGESOUL_DIR . '/' . ltrim( $fallback_path, '/' ) ) ) : ?>
	<a class="<?php echo esc_attr( $logo_class ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $site_name ); ?>">
		<img class="logo__mark" src="<?php echo esc_url( VINTAGESOUL_URI . '/' . ltrim( $fallback_path, '/' ) ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
	</a>
<?php else : ?>
	<a class="<?php echo esc_attr( $logo_class ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $site_name ); ?>">
		<span class="logo__brand-lockup">
			<span class="logo__brand-icon" aria-hidden="true">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#184b25" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
				</svg>
			</span>
			<span class="logo__brand-text">
				<span class="logo__title"><?php echo esc_html( ! empty( $site_name ) && 'WordPress' !== $site_name ? $site_name : 'THE CANE HOUSE' ); ?></span>
				<span class="logo__sub">EST. 2014 · LONDON</span>
			</span>
		</span>
	</a>
<?php endif; ?>
