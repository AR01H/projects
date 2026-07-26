<?php
namespace CMS_ECOMMERCE\Helpers;

if ( ! defined( 'ABSPATH' ) ) exit;

class Validator {
    public static function required( array $data, array $fields ): ?\WP_Error {
        $errors = new \WP_Error();
        foreach ( $fields as $field ) {
            if ( empty( $data[ $field ] ) && $data[ $field ] !== '0' && $data[ $field ] !== 0 ) {
                $errors->add( 'missing_field', "Field '$field' is required." );
            }
        }
        return $errors->get_error_codes() ? $errors : null;
    }
    public static function email( string $email ): bool {
        return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL );
    }
    public static function sanitize_string( string $input ): string {
        return sanitize_text_field( wp_unslash( $input ) );
    }
}
