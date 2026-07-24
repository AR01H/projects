<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract base tab for theme admin.
 * Each tab controller extends this.
 */
abstract class AbstractTab {

	/** Tab slug (used in URL). */
	abstract public function slug(): string;

	/** Tab display title. */
	abstract public function title(): string;

	/** Required capability. */
	public function capability(): string {
		return 'manage_options';
	}

	/** Check if user can access this tab. */
	public function canAccess(): bool {
		return current_user_can( $this->capability() );
	}

	/** Handle POST submissions. */
	public function handlePost(): void {}

	/** Render the tab view. */
	abstract public function render(): void;

	/** Load tab-specific assets. */
	public function enqueueAssets(): void {}
}
