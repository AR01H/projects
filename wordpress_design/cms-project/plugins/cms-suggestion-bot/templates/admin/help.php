<?php
/**
 * templates/admin/help.php - static reference material.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Help', 'cms-suggestion-bot' ); ?></h1>

	<div class="card csb-card">
		<h2 class="title"><?php esc_html_e( 'How it works', 'cms-suggestion-bot' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Reader scans your Pages, Posts, and any .txt/.md files dropped into the plugin\'s /resources folder.', 'cms-suggestion-bot' ); ?></li>
			<li><?php esc_html_e( 'Admin Tools -> Generate Cache turns that content into the searchable cache (cms_sug_bot_cache / cms_sug_bot_chunks).', 'cms-suggestion-bot' ); ?></li>
			<li><?php esc_html_e( 'The chat widget answers visitor questions from that cache and the Knowledge Base - no AI required by default.', 'cms-suggestion-bot' ); ?></li>
			<li><?php esc_html_e( 'Questions the bot couldn\'t answer are logged to Knowledge Base -> Unanswered for you to review and fill in.', 'cms-suggestion-bot' ); ?></li>
			<li><?php esc_html_e( 'Optionally enable Configuration -> AI Approach and configure a provider to hand unmatched questions to an AI model instead of the built-in fallback.', 'cms-suggestion-bot' ); ?></li>
		</ol>
	</div>

	<div class="card csb-card">
		<h2 class="title"><?php esc_html_e( 'Keeping content fresh', 'cms-suggestion-bot' ); ?></h2>
		<p><?php esc_html_e( 'Configuration -> Cache controls whether the cache regenerates automatically on publish/update/delete, and on a daily/weekly/monthly schedule. Admin Tools also lets you Destroy or Rebuild the cache manually at any time.', 'cms-suggestion-bot' ); ?></p>
	</div>

	<div class="card csb-card">
		<h2 class="title"><?php esc_html_e( 'Keeping visitors in check', 'cms-suggestion-bot' ); ?></h2>
		<p><?php esc_html_e( 'Configuration -> Restricted Words lets you mask or block specific terms in both indexed content and bot answers. Configuration -> Usage Limits caps how many questions one visitor can ask per session/day.', 'cms-suggestion-bot' ); ?></p>
	</div>

	<div class="card csb-card">
		<h2 class="title"><?php esc_html_e( 'Data & Uninstalling', 'cms-suggestion-bot' ); ?></h2>
		<p><?php esc_html_e( 'By default, deactivating or uninstalling this plugin leaves all of its data (Knowledge Base, cache, logs, settings) in place. Turn on Settings -> "Delete all plugin data on uninstall" first if you want a full removal.', 'cms-suggestion-bot' ); ?></p>
	</div>
</div>
