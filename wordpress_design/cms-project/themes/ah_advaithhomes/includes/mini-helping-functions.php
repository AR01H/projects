<?php

function handle_defined( $constant_name, $value ) {
    if ( ! defined( $constant_name ) ) {
        define( $constant_name, $value );
    }
}