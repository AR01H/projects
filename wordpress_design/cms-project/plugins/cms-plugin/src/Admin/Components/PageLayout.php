<?php
namespace Ah\Cms\Admin\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Page layout wrapper for admin pages.
 * Renders the standard admin page shell: header, notices, content, optional sidebar.
 *
 * Usage:
 *   $page = new PageLayout( 'FAQs', 'dashicons-editor-help' );
 *   $page->notice( 'Saved.', 'success' );
 *   $page->content( $html );
 *   $page->render();
 */
class PageLayout {

	private string $title;
	private string $icon;
	private array  $notices = [];
	private string $content = '';
	private array  $actions = []; // ['label' => ..., 'url' => ...]

	public function __construct( string $title, string $icon = 'dashicons-admin-generic' ) {
		$this->title = $title;
		$this->icon  = $icon;
	}

	public function notice( string $message, string $type = 'success' ): self {
		$this->notices[] = [ 'message' => $message, 'type' => $type ];
		return $this;
	}

	public function content( string $html ): self {
		$this->content .= $html;
		return $this;
	}

	public function action( string $label, string $url ): self {
		$this->actions[] = [ 'label' => $label, 'url' => $url ];
		return $this;
	}

	public function render(): void {
		echo '<div class="wrap ah-wrap">';

		// Header
		echo '<h1 style="display:flex;align-items:center;gap:8px;">';
		echo '<span class="dashicons ' . esc_attr( $this->icon ) . '"></span> ';
		echo esc_html( $this->title );
		foreach ( $this->actions as $act ) {
			echo ' <a href="' . esc_url( $act['url'] ) . '" class="page-title-action">' . esc_html( $act['label'] ) . '</a>';
		}
		echo '</h1>';

		// Notices
		foreach ( $this->notices as $n ) {
			AdminComponents::notice( $n['message'], $n['type'] );
		}

		// Content
		echo $this->content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';
	}
}
