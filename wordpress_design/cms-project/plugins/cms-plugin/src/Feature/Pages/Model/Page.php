<?php

namespace Ah\Cms\Feature\Pages\Model;

defined( 'ABSPATH' ) || exit;

/**
 * Page entity model.
 * Facade over AH_Pages_Model for backward compatibility.
 */
class Page extends \AH_Pages_Model {
	// All methods inherited from AH_Pages_Model.
	// Future: add domain methods here (isPublished, hasBlocks, etc.)
}
