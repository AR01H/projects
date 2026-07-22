<?php
/**
 * templates/chat-widget.php - front-end floating chat widget markup.
 * Text/color content comes from the csbChat object localized in
 * Front\ChatWidget::enqueue() - this file only lays out the structure.
 */
defined( 'ABSPATH' ) || exit;
?>
<div id="csb-chat-widget" class="csb-widget" aria-live="polite">

	<button type="button" id="csb-chat-toggle" class="csb-toggle" aria-label="<?php esc_attr_e( 'Open chat', 'cms-suggestion-bot' ); ?>" aria-expanded="false">
		<span class="csb-toggle-icon" id="csb-toggle-icon"></span>
	</button>

	<div id="csb-chat-panel" class="csb-panel">

		<div class="csb-header">
			<span class="csb-header-icon" id="csb-header-icon"></span>
			<div class="csb-header-text">
				<strong id="csb-header-name"></strong>
				<span class="csb-identity-badge" id="csb-identity-badge"></span>
			</div>
			<button type="button" id="csb-chat-close" class="csb-close" aria-label="<?php esc_attr_e( 'Close chat', 'cms-suggestion-bot' ); ?>">&times;</button>
		</div>

		<div class="csb-post-context" id="csb-post-context">
			<span class="csb-post-context-icon">📄</span>
			<span class="csb-post-context-text">Asking about: <strong id="csb-post-context-title"></strong></span>
			<button type="button" id="csb-post-context-close" class="csb-post-context-close" aria-label="<?php esc_attr_e( 'Remove context', 'cms-suggestion-bot' ); ?>">&times;</button>
		</div>

		<p class="csb-description" id="csb-description"></p>

		<div class="csb-messages" id="csb-messages" role="log"></div>

		<div class="csb-thinking" id="csb-thinking" hidden></div>

		<form id="csb-chat-form" class="csb-form">
			<input type="text" id="csb-chat-input" class="csb-input" autocomplete="off"
				placeholder="<?php esc_attr_e( 'Type your question…', 'cms-suggestion-bot' ); ?>">
			<button type="submit" class="csb-send" aria-label="<?php esc_attr_e( 'Send', 'cms-suggestion-bot' ); ?>">➤</button>
		</form>

	</div>
</div>
