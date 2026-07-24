<?php
/**
 * Backward-compatibility wrapper — delegates to Ah\Cms\Feature\Taxonomy\TermManager.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/../src/Feature/Taxonomy/TermManager.php';

class_alias( \Ah\Cms\Feature\Taxonomy\TermManager::class, 'AH_Term_Manager' );
