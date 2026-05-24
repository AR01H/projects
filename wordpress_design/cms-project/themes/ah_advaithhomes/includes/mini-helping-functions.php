<?php

function handle_defined( $constant_name, $value ) {
    if ( ! defined( $constant_name ) ) {
        define( $constant_name, $value );
    }
}

function getRequestParameter($variableName='', $default = '') {
    if (strlen($variableName) && isset($_REQUEST[$variableName])) {
        return trim($_REQUEST[$variableName]);
    }
    return $default;
}

function getRequestJSON($variableName = null, $default = null) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return $default;
    }

     if ($variableName === null) {
        return $data;
    }
    return isset($data[$variableName]) ? $data[$variableName] : $default;
}