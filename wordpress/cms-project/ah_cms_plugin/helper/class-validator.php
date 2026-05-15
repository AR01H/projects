<?php
defined( 'ABSPATH' ) || exit;

class AH_Validator {

	private array $errors = array();
	private array $data   = array();

	public function __construct( array $data ) {
		$this->data = $data;
	}

	public function required( string $field, string $label = '' ): self {
		$label = $label ?: ucfirst( str_replace( '_', ' ', $field ) );
		if ( empty( trim( (string) ( $this->data[ $field ] ?? '' ) ) ) ) {
			$this->errors[ $field ] = "{$label} is required.";
		}
		return $this;
	}

	public function email( string $field, string $label = '' ): self {
		$label = $label ?: ucfirst( str_replace( '_', ' ', $field ) );
		$val   = $this->data[ $field ] ?? '';
		if ( $val && ! is_email( $val ) ) {
			$this->errors[ $field ] = "{$label} must be a valid email address.";
		}
		return $this;
	}

	public function url( string $field, string $label = '' ): self {
		$label = $label ?: ucfirst( str_replace( '_', ' ', $field ) );
		$val   = $this->data[ $field ] ?? '';
		if ( $val && ! filter_var( $val, FILTER_VALIDATE_URL ) ) {
			$this->errors[ $field ] = "{$label} must be a valid URL.";
		}
		return $this;
	}

	public function max_length( string $field, int $max, string $label = '' ): self {
		$label = $label ?: ucfirst( str_replace( '_', ' ', $field ) );
		$val   = $this->data[ $field ] ?? '';
		if ( mb_strlen( (string) $val ) > $max ) {
			$this->errors[ $field ] = "{$label} must not exceed {$max} characters.";
		}
		return $this;
	}

	public function in_list( string $field, array $allowed, string $label = '' ): self {
		$label = $label ?: ucfirst( str_replace( '_', ' ', $field ) );
		$val   = $this->data[ $field ] ?? '';
		if ( $val && ! in_array( $val, $allowed, true ) ) {
			$this->errors[ $field ] = "{$label} has an invalid value.";
		}
		return $this;
	}

	public function passes(): bool {
		return empty( $this->errors );
	}

	public function fails(): bool {
		return ! $this->passes();
	}

	public function errors(): array {
		return $this->errors;
	}

	public function first_error(): string {
		return reset( $this->errors ) ?: '';
	}

	// ----------------------------------------------------------------
	// Static sanitization helpers
	// ----------------------------------------------------------------

	public static function sanitize_text( string $val ): string {
		return sanitize_text_field( $val );
	}

	public static function sanitize_textarea( string $val ): string {
		return sanitize_textarea_field( $val );
	}

	public static function sanitize_html( string $val ): string {
		return wp_kses_post( $val );
	}

	public static function sanitize_url( string $val ): string {
		return esc_url_raw( trim( $val ) );
	}

	public static function sanitize_int( $val ): int {
		return (int) $val;
	}

	public static function sanitize_slug( string $val ): string {
		return sanitize_title( $val );
	}

	public static function sanitize_color( string $val ): string {
		return preg_match( '/^#[0-9a-fA-F]{3,6}$/', $val ) ? $val : '';
	}
}
