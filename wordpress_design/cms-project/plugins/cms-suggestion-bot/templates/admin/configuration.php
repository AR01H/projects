<?php
/**
 * templates/admin/configuration.php
 *
 * Vars in scope (from Admin\Pages\ConfigurationPage::render()):
 * @var array<string, mixed> $settings
 * @var string               $active_tab
 * @var string               $notice
 */

use CmsSuggestionBot\Admin\View;

defined( 'ABSPATH' ) || exit;

$tabs = array(
	'general'          => __( 'General', 'cms-suggestion-bot' ),
	'behaviour'        => __( 'Behaviour', 'cms-suggestion-bot' ),
	'cache'            => __( 'Cache', 'cms-suggestion-bot' ),
	'api'              => __( 'API', 'cms-suggestion-bot' ),
	'reader'           => __( 'Reader', 'cms-suggestion-bot' ),
	'common_questions' => __( 'Common Questions Cache', 'cms-suggestion-bot' ),
	'greetings'        => __( 'Greetings', 'cms-suggestion-bot' ),
	'restricted_words' => __( 'Restricted Words', 'cms-suggestion-bot' ),
	'ai_approach'      => __( 'AI Approach', 'cms-suggestion-bot' ),
	'usage_limits'     => __( 'Usage Limits', 'cms-suggestion-bot' ),
);
if ( ! isset( $tabs[ $active_tab ] ) ) {
	$active_tab = 'general';
}
$base_url = admin_url( 'admin.php?page=' . CSB_MENU_SLUG . '-configuration' );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Configuration', 'cms-suggestion-bot' ); ?></h1>

	<?php View::notice( $notice ); ?>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $tabs as $slug => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $slug, $base_url ) ); ?>"
				class="nav-tab<?php echo $slug === $active_tab ? ' nav-tab-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</h2>

	<form method="post">
		<?php wp_nonce_field( 'csb_save_configuration' ); ?>
		<input type="hidden" name="csb_config_group" value="<?php echo esc_attr( $active_tab ); ?>">

		<table class="form-table" role="presentation">
		<?php if ( 'general' === $active_tab ) : $g = $settings['general']; ?>
			<tr><th><label>Enable Bot</label></th><td><label><input type="checkbox" name="csb[enabled]" value="1" <?php checked( $g['enabled'] ); ?>> Bot is active</label></td></tr>
			<tr><th><label>Bot Name</label></th><td><input type="text" class="regular-text" name="csb[bot_name]" value="<?php echo esc_attr( $g['bot_name'] ); ?>"></td></tr>
			<tr><th><label>Identity Label</label></th>
				<td>
					<input type="text" class="regular-text" name="csb[identity_label]" value="<?php echo esc_attr( $g['identity_label'] ); ?>" placeholder="Suggestion Bot">
					<p class="description"><?php esc_html_e( 'Badge text shown under the bot name in the widget header. Leave empty to auto-detect.', 'cms-suggestion-bot' ); ?></p>
				</td>
			</tr>
			<tr><th><label>Bot Description</label></th><td><input type="text" class="regular-text" name="csb[bot_description]" value="<?php echo esc_attr( $g['bot_description'] ); ?>"></td></tr>
			<tr><th><label>Show Description</label></th><td><label><input type="checkbox" name="csb[show_description]" value="1" <?php checked( $g['show_description'] ); ?>> Show description in chat widget</label></td></tr>
			<tr><th><label>Bot Icon</label></th><td><input type="text" name="csb[bot_icon]" value="<?php echo esc_attr( $g['bot_icon'] ); ?>" class="csb-icon-input"></td></tr>
			<tr><th><label>Theme Color</label></th><td><input type="color" name="csb[theme_color]" value="<?php echo esc_attr( $g['theme_color'] ); ?>"></td></tr>
			<tr><th><label>Background Color</label></th><td><input type="color" name="csb[background_color]" value="<?php echo esc_attr( $g['background_color'] ); ?>"></td></tr>
			<tr><th><label>Text Color</label></th><td><input type="color" name="csb[text_color]" value="<?php echo esc_attr( $g['text_color'] ); ?>"></td></tr>
			<tr><th><label>Welcome Message</label></th><td><input type="text" class="regular-text" name="csb[welcome_message]" value="<?php echo esc_attr( $g['welcome_message'] ); ?>"></td></tr>
			<tr><th><label>Goodbye Message</label></th><td><input type="text" class="regular-text" name="csb[goodbye_message]" value="<?php echo esc_attr( $g['goodbye_message'] ); ?>"></td></tr>
			<tr><th><label>Thinking Message</label></th><td><input type="text" class="regular-text" name="csb[thinking_message]" value="<?php echo esc_attr( $g['thinking_message'] ); ?>"></td></tr>
			<tr><th><label>Typing Speed (ms/char)</label></th><td><input type="number" name="csb[typing_speed_ms]" value="<?php echo esc_attr( $g['typing_speed_ms'] ); ?>"></td></tr>

		<?php elseif ( 'behaviour' === $active_tab ) : $b = $settings['behaviour']; ?>
			<tr><th><label>Tone</label></th><td>
				<select name="csb[tone]">
					<?php foreach ( array( 'friendly', 'professional', 'formal', 'casual' ) as $t ) : ?>
						<option value="<?php echo esc_attr( $t ); ?>" <?php selected( $b['tone'], $t ); ?>><?php echo esc_html( ucfirst( $t ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</td></tr>
			<tr><th><label>Links Only Mode</label></th><td>
				<label><input type="checkbox" name="csb[links_only_mode]" value="1" <?php checked( $b['links_only_mode'] ); ?>> Enabled</label>
				<p class="description"><?php esc_html_e( 'When enabled, the bot returns only matching post/page links without verbose answers. Useful for quick navigation.', 'cms-suggestion-bot' ); ?></p>
			</td></tr>
			<tr><th><label>Show Related Articles</label></th><td>
				<label><input type="checkbox" name="csb[show_related]" value="1" <?php checked( $b['show_related'] ); ?>> Enabled</label>
				<p class="description"><?php esc_html_e( 'Show related article suggestions below answers. Disable for a cleaner, answer-only experience.', 'cms-suggestion-bot' ); ?></p>
			</td></tr>
			<tr><th><label>Developer Mode</label></th><td><label><input type="checkbox" name="csb[developer_mode]" value="1" <?php checked( $b['developer_mode'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Verbose Mode</label></th><td><label><input type="checkbox" name="csb[verbose_mode]" value="1" <?php checked( $b['verbose_mode'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Debug Mode</label></th><td><label><input type="checkbox" name="csb[debug_mode]" value="1" <?php checked( $b['debug_mode'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Safe Mode</label></th><td><label><input type="checkbox" name="csb[safe_mode]" value="1" <?php checked( $b['safe_mode'] ); ?>> Enabled</label></td></tr>

		<?php elseif ( 'cache' === $active_tab ) : $c = $settings['cache']; ?>
			<tr><th><label>Auto Generate</label></th><td><label><input type="checkbox" name="csb[auto_generate]" value="1" <?php checked( $c['auto_generate'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Generate on Publish</label></th><td><label><input type="checkbox" name="csb[generate_on_publish]" value="1" <?php checked( $c['generate_on_publish'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Generate on Update</label></th><td><label><input type="checkbox" name="csb[generate_on_update]" value="1" <?php checked( $c['generate_on_update'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Generate on Delete</label></th><td><label><input type="checkbox" name="csb[generate_on_delete]" value="1" <?php checked( $c['generate_on_delete'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Generate Daily</label></th><td><label><input type="checkbox" name="csb[generate_daily]" value="1" <?php checked( $c['generate_daily'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Generate Weekly</label></th><td><label><input type="checkbox" name="csb[generate_weekly]" value="1" <?php checked( $c['generate_weekly'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Generate Monthly</label></th><td><label><input type="checkbox" name="csb[generate_monthly]" value="1" <?php checked( $c['generate_monthly'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Maximum Cache Size (MB)</label></th><td><input type="number" name="csb[max_cache_size_mb]" value="<?php echo esc_attr( $c['max_cache_size_mb'] ); ?>"></td></tr>
			<tr><th><label>Chunk Size (words)</label></th><td><input type="number" name="csb[chunk_words]" value="<?php echo esc_attr( $c['chunk_words'] ); ?>"></td></tr>

		<?php elseif ( 'api' === $active_tab ) : $a = $settings['api']; ?>
			<tr><th><label>Enable API</label></th><td><label><input type="checkbox" name="csb[enabled]" value="1" <?php checked( $a['enabled'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Rate Limit (req/min)</label></th><td><input type="number" name="csb[rate_limit]" value="<?php echo esc_attr( $a['rate_limit'] ); ?>"></td></tr>
			<tr><th><label>API Logging</label></th><td><label><input type="checkbox" name="csb[logging]" value="1" <?php checked( $a['logging'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Allowed Origins</label></th><td><textarea name="csb[allowed_origins_text]" class="large-text" rows="3" placeholder="https://example.com"><?php echo esc_textarea( implode( "\n", (array) $a['allowed_origins'] ) ); ?></textarea></td></tr>

		<?php elseif ( 'reader' === $active_tab ) : $r = $settings['reader']; ?>
			<tr><th><label>Reader Speed</label></th><td>
				<select name="csb[speed]">
					<?php foreach ( array( 'slow', 'normal', 'fast' ) as $s ) : ?>
						<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $r['speed'], $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</td></tr>
			<tr><th><label>Chunk Size (words)</label></th><td><input type="number" name="csb[chunk_size]" value="<?php echo esc_attr( $r['chunk_size'] ); ?>"></td></tr>
			<tr><th><label>Memory Limit (MB)</label></th><td><input type="number" name="csb[memory_limit_mb]" value="<?php echo esc_attr( $r['memory_limit_mb'] ); ?>"></td></tr>
			<tr><th><label>Batch Size</label></th><td><input type="number" name="csb[batch_size]" value="<?php echo esc_attr( $r['batch_size'] ); ?>"></td></tr>
			<tr><th><label>Max Execution Time (sec)</label></th><td><input type="number" name="csb[max_execution_sec]" value="<?php echo esc_attr( $r['max_execution_sec'] ); ?>"></td></tr>

		<?php elseif ( 'common_questions' === $active_tab ) : $cq = $settings['common_questions']; ?>
			<tr><th><label>Enable Common Questions Cache</label></th><td><label><input type="checkbox" name="csb[enabled]" value="1" <?php checked( $cq['enabled'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Cache TTL (seconds)</label></th><td><input type="number" name="csb[cache_ttl]" value="<?php echo esc_attr( $cq['cache_ttl'] ); ?>"></td></tr>
			<tr><th><label>Max Entries</label></th><td><input type="number" name="csb[max_entries]" value="<?php echo esc_attr( $cq['max_entries'] ); ?>"></td></tr>
			<tr><td colspan="2"><p class="description"><?php esc_html_e( 'The most-used entries from the Knowledge Base are cached here for instant lookup, refreshed every TTL seconds.', 'cms-suggestion-bot' ); ?></p></td></tr>

		<?php elseif ( 'greetings' === $active_tab ) : $gr = $settings['greetings']; ?>
			<tr><th><label>Enable Greetings</label></th><td><label><input type="checkbox" name="csb[enabled]" value="1" <?php checked( $gr['enabled'] ); ?>> Enabled</label></td></tr>
			<tr><td colspan="2"><p class="description"><?php esc_html_e( 'Small talk like "hi" or "thanks" is answered directly, without touching the Knowledge Base or cached content. Edit each response below.', 'cms-suggestion-bot' ); ?></p></td></tr>
			<?php foreach ( (array) $gr['phrases'] as $phrase => $response ) : ?>
				<tr>
					<th><label><?php echo esc_html( ucfirst( (string) $phrase ) ); ?></label></th>
					<td><input type="text" class="regular-text" name="csb[phrases][<?php echo esc_attr( (string) $phrase ); ?>]" value="<?php echo esc_attr( (string) $response ); ?>"></td>
				</tr>
			<?php endforeach; ?>

		<?php elseif ( 'restricted_words' === $active_tab ) : $rw = $settings['restricted_words']; ?>
			<tr><th><label>Enable Restricted Words</label></th><td><label><input type="checkbox" name="csb[enabled]" value="1" <?php checked( $rw['enabled'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Mode</label></th><td>
				<select name="csb[mode]">
					<option value="mask" <?php selected( $rw['mode'], 'mask' ); ?>><?php esc_html_e( 'Mask (replace with ****)', 'cms-suggestion-bot' ); ?></option>
					<option value="block" <?php selected( $rw['mode'], 'block' ); ?>><?php esc_html_e( 'Block (suppress entirely)', 'cms-suggestion-bot' ); ?></option>
				</select>
			</td></tr>
			<tr><th><label>Word List</label></th><td>
				<textarea name="csb[words_text]" class="large-text" rows="6" placeholder="one word or phrase per line"><?php echo esc_textarea( implode( "\n", (array) $rw['words'] ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One word or phrase per line. Applied to indexed content and to bot answers.', 'cms-suggestion-bot' ); ?></p>
			</td></tr>

		<?php elseif ( 'ai_approach' === $active_tab ) : $ai = $settings['ai_approach']; ?>
			<tr><th><label>Enable AI Approach</label></th><td>
				<label><input type="checkbox" name="csb[enabled]" value="1" <?php checked( $ai['enabled'] ); ?>> Enabled</label>
				<p class="description"><?php esc_html_e( 'Off by default: the bot answers only from cached content and the Knowledge Base. Turning this on hands unmatched questions to the active provider below.', 'cms-suggestion-bot' ); ?></p>
			</td></tr>
			<tr><th><label>Active Provider</label></th><td>
				<select name="csb[active_provider]" id="csb-ai-active-provider">
					<option value=""><?php esc_html_e( '- None -', 'cms-suggestion-bot' ); ?></option>
					<?php foreach ( (array) $ai['providers'] as $pid => $p ) : ?>
						<option value="<?php echo esc_attr( $pid ); ?>" <?php selected( $ai['active_provider'], $pid ); ?>><?php echo esc_html( $p['label'] ?? $pid ); ?></option>
					<?php endforeach; ?>
				</select>
			</td></tr>
			<?php foreach ( (array) $ai['providers'] as $pid => $p ) : ?>
				<tr class="csb-ai-provider-row" data-provider="<?php echo esc_attr( $pid ); ?>">
					<th><label><?php echo esc_html( $p['label'] ?? $pid ); ?></label></th>
					<td>
						<label class="csb-label-block">
							<?php esc_html_e( 'API Key', 'cms-suggestion-bot' ); ?>
							<input type="password" autocomplete="off" class="regular-text" name="csb[providers][<?php echo esc_attr( $pid ); ?>][api_key]" value="<?php echo esc_attr( $p['api_key'] ?? '' ); ?>">
						</label>
						<label class="csb-label-block">
							<?php esc_html_e( 'Model', 'cms-suggestion-bot' ); ?>
							<input type="text" class="regular-text" name="csb[providers][<?php echo esc_attr( $pid ); ?>][model]" value="<?php echo esc_attr( $p['model'] ?? '' ); ?>">
						</label>
						<?php if ( isset( $p['endpoint'] ) ) : ?>
							<label class="csb-label-block">
								<?php esc_html_e( 'Endpoint', 'cms-suggestion-bot' ); ?>
								<input type="text" class="regular-text" name="csb[providers][<?php echo esc_attr( $pid ); ?>][endpoint]" value="<?php echo esc_attr( $p['endpoint'] ); ?>">
							</label>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<tr><td colspan="2"><p class="description"><?php esc_html_e( 'Credentials for every provider are saved even when inactive, so switching "Active Provider" is a one-field change - no need to re-enter keys.', 'cms-suggestion-bot' ); ?></p></td></tr>

		<?php elseif ( 'usage_limits' === $active_tab ) : $ul = $settings['usage_limits']; ?>
			<tr><th><label>Enable Usage Limits</label></th><td><label><input type="checkbox" name="csb[enabled]" value="1" <?php checked( $ul['enabled'] ); ?>> Enabled</label></td></tr>
			<tr><th><label>Max Messages / Session</label></th><td><input type="number" name="csb[max_messages_per_session]" value="<?php echo esc_attr( $ul['max_messages_per_session'] ); ?>"></td></tr>
			<tr><th><label>Max Messages / Day (per visitor)</label></th><td><input type="number" name="csb[max_messages_per_day]" value="<?php echo esc_attr( $ul['max_messages_per_day'] ); ?>"></td></tr>
			<tr><th><label>Limit Reached Message</label></th><td><input type="text" class="regular-text" name="csb[limit_reached_message]" value="<?php echo esc_attr( $ul['limit_reached_message'] ); ?>"></td></tr>
		<?php endif; ?>
		</table>

		<?php submit_button( __( 'Save Settings', 'cms-suggestion-bot' ) ); ?>
	</form>
</div>

<?php if ( 'ai_approach' === $active_tab ) : ?>
<script>
(function () {
	var select = document.getElementById( 'csb-ai-active-provider' );
	var rows   = document.querySelectorAll( '.csb-ai-provider-row' );

	function sync() {
		rows.forEach( function ( row ) {
			row.style.display = row.dataset.provider === select.value ? '' : 'none';
		} );
	}

	select.addEventListener( 'change', sync );
	sync();
})();
</script>
<?php endif; ?>
