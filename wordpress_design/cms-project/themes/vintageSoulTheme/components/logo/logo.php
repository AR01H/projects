<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\SettingsService;

$context       = isset( $context ) && in_array( $context, array( 'header', 'footer' ), true ) ? $context : 'header';
$has_logo      = has_custom_logo();
$fallback_path = SettingsService::logo_fallback( $context );
$site_name     = get_bloginfo( 'name' );
$logo_class    = 'logo logo--' . $context;
?>
<?php if ( $has_logo ) : ?>
	<div class="<?php echo esc_attr( $logo_class ); ?>">
		<?php the_custom_logo();  ?>
	</div>
<?php elseif ( '' !== $fallback_path && is_file( VINTAGESOUL_DIR . '/' . ltrim( $fallback_path, '/' ) ) ) : ?>
	<a class="<?php echo esc_attr( $logo_class ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<img class="logo__mark" src="<?php echo esc_url( VINTAGESOUL_URI . '/' . ltrim( $fallback_path, '/' ) ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
	</a>
<?php else : ?>
	<a class="<?php echo esc_attr( $logo_class ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<span class="logo__fallback">
			<span class="logo__icon" aria-hidden="true"></span>
			<span class="logo__text"><?php echo esc_html( $site_name ); ?></span>
		</span>
	</a>
<?php endif; ?>
