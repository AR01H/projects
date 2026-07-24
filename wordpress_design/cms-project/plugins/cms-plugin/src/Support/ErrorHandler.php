<?php

namespace Ah\Cms\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Global error handler for the CMS plugin.
 * Catches uncaught exceptions and PHP errors.
 */
class ErrorHandler {

	/**
	 * Register the error handler.
	 */
	public static function register(): void {
		set_error_handler( [ self::class, 'handleError' ] );
		set_exception_handler( [ self::class, 'handleException' ] );
	}

	/**
	 * Handle PHP errors.
	 */
	public static function handleError( int $code, string $message, string $file, int $line ): bool {
		Logger::error( $message, [
			'code' => $code,
			'file' => $file,
			'line' => $line,
		] );
		return false;
	}

	/**
	 * Handle uncaught exceptions.
	 */
	public static function handleException( \Throwable $e ): void {
		Logger::error( $e->getMessage(), [
			'exception' => get_class( $e ),
			'file'      => $e->getFile(),
			'line'      => $e->getLine(),
			'trace'     => $e->getTraceAsString(),
		] );

		// Show error notice in admin
		if ( is_admin() && current_user_can( 'manage_options' ) ) {
			add_action( 'admin_notices', function () use ( $e ) {
				echo '<div class="notice notice-error"><p>'
					. '<strong>CMS Error:</strong> '
					. esc_html( $e->getMessage() )
					. ' in ' . esc_html( basename( $e->getFile() ) ) . ':' . (int) $e->getLine()
					. '</p></div>';
			} );
		}
	}
}
