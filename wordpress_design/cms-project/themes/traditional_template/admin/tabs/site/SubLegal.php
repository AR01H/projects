<?php defined('ABSPATH')||exit; require_once dirname(__DIR__, 2) . '/Models/SettingsModel.php'; ?>
<div class="wrap"><h2>Legal Pages</h2><p>Legal page content is managed via the Settings table. Keys prefixed with <code>legal_</code>, <code>legal_privacy_</code>, <code>legal_cookies_</code>, <code>legal_terms_</code>.</p>
<table class="wp-list-table widefat striped"><thead><tr><th>Key</th><th>Preview</th></tr></thead><tbody>
<?php
global $wpdb;
$rows = $wpdb->get_results("SELECT setting_key, setting_value FROM {$wpdb->prefix}tt_settings WHERE setting_key LIKE 'legal%' ORDER BY setting_key ASC", ARRAY_A);
foreach ($rows as $r) { echo '<tr><td><code>' . esc_html($r['setting_key']) . '</code></td><td>' . esc_html(substr($r['setting_value'],0,80)) . '...</td></tr>'; }
?></tbody></table></div>