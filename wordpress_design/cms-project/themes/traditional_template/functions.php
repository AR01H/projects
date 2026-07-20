<?php
/**
 * Theme entry point - intentionally tiny.
 *
 * All behaviour is DATA (config/*.php arrays) executed by ENGINES (core/*.php).
 * To change the site you edit /config, /pages, /components, /handlers and
 * /assets. You should almost never need to touch /core or this file.
 *
 * Full design doc: ARCHITECTURE.md
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/core/bootstrap.php';
