<?php
/**
 * Admin view: Dashboard
 * Shows schema status, row counts, and AJAX-powered action buttons.
 */

defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/includes/admin/class-pt-stories-db.php';
require_once get_template_directory() . '/includes/admin/class-pt-ajax.php';

$counts = PT_Ajax::get_counts();
$schema = PT_Ajax::get_schema_state();
$theme  = wp_get_theme();
?>
<div class="wrap pt-admin-wrap">

	<div class="pt-admin-header">
		<div class="pt-admin-logo">PT</div>
		<div>
			<h1><?php echo esc_html( $theme->get( 'Name' ) ); ?>
				<span style="font-weight:400;font-size:.9rem;color:#94a3b8">
					v<?php echo esc_html( $theme->get( 'Version' ) ); ?>
				</span>
			</h1>
			<p>Theme admin — schema, mock data, and content management.</p>
		</div>
		<div style="margin-left:auto">
			<button class="button" data-pt-action="refresh-status">&#8635; Refresh</button>
		</div>
	</div>

	<!-- AJAX notice target -->
	<div id="pt-ajax-notice"></div>

	<!-- ── Schema status ──────────────────────────────────────── -->
	<div class="pt-admin-box">
		<h2>Schema Status</h2>
		<table class="pt-admin-table">
			<thead>
				<tr>
					<th>Table</th>
					<th>Status</th>
					<th>Rows</th>
					<th>Version Key</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $schema as $key => $info ) :
					$label   = ucfirst( $key );
					$version = $info['version'] ?: '—';
				?>
				<tr>
					<td><code><?php echo esc_html( $info['table'] ); ?></code></td>
					<td>
						<span class="pt-badge <?php echo $info['exists'] ? 'pt-badge--yes' : 'pt-badge--no'; ?>"
						      data-schema-badge="<?php echo esc_attr( $key ); ?>">
							<?php echo $info['exists'] ? 'EXISTS' : 'MISSING'; ?>
						</span>
					</td>
					<td>
						<strong data-count="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $counts[ $key ] ?? '—' ); ?>
						</strong>
					</td>
					<td><code style="color:#94a3b8;font-size:.78rem"><?php echo esc_html( $version ); ?></code></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<!-- ── Schema actions ─────────────────────────────────────── -->
	<div class="pt-admin-box">
		<h2>Schema Actions</h2>
		<p style="color:#64748b;font-size:.875rem;margin-bottom:18px;">
			All actions run via AJAX — no page reload needed.
		</p>
		<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center">
			<button class="button button-primary" data-pt-action="schema-install">
				&#8679; Install / Update Schema
			</button>
			<button class="button" style="color:#dc2626;border-color:#fca5a5" data-pt-action="schema-drop">
				&#10005; Drop All Tables
			</button>
		</div>
		<p style="margin-top:10px;font-size:.78rem;color:#94a3b8">
			<strong>Install / Update</strong> — runs <code>dbDelta()</code>: creates missing tables, adds missing columns. Safe to re-run anytime.<br>
			<strong>Drop</strong> — runs <code>DROP TABLE</code>: permanently destroys all data.
		</p>
	</div>

	<!-- ── Mock data actions ──────────────────────────────────── -->
	<div class="pt-admin-box">
		<h2>Mock Data</h2>
		<p style="color:#64748b;font-size:.875rem;margin-bottom:18px;">
			Seed realistic sample content for development. Existing rows are skipped.
		</p>
		<div style="display:flex;gap:12px;flex-wrap:wrap">
			<button class="button button-primary" data-pt-action="seed-mock">
				&#8681; Seed All Mock Data
			</button>
			<button class="button" data-pt-action="cleanup">
				&#9003; Cleanup All Rows
			</button>
		</div>
	</div>

	<!-- ── Quick navigation ───────────────────────────────────── -->
	<div class="pt-admin-box">
		<h2>Quick Links</h2>
		<div style="display:flex;gap:10px;flex-wrap:wrap">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-stories' ) ); ?>" class="button button-primary">
				Stories
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-mock-data' ) ); ?>" class="button">
				Mock Data Page
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-cleanup' ) ); ?>" class="button">
				Cleanup Page
			</a>
			<a href="<?php echo esc_url( rest_url( 'pt/v1/stories' ) ); ?>" target="_blank" class="button">
				REST API: /stories &#8599;
			</a>
			<a href="<?php echo esc_url( rest_url( 'pt/v1/status' ) ); ?>" target="_blank" class="button">
				REST API: /status &#8599;
			</a>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button">
				View Site &#8599;
			</a>
		</div>
	</div>

	<!-- ── REST API reference ─────────────────────────────────── -->
	<div class="pt-admin-box">
		<h2>REST API Reference</h2>
		<p style="color:#64748b;font-size:.875rem;margin-bottom:14px">
			Base: <code><?php echo esc_html( rest_url( 'pt/v1' ) ); ?></code>
			&nbsp;·&nbsp; Nonce header: <code>X-WP-Nonce</code> &nbsp;·&nbsp;
			Auth required for write operations.
		</p>
		<table class="pt-admin-table">
			<thead>
				<tr><th>Method</th><th>Endpoint</th><th>Auth</th><th>Description</th></tr>
			</thead>
			<tbody>
				<tr><td><span class="pt-badge pt-badge--yes">GET</span></td>    <td><code>/stories</code></td>          <td>Public</td>  <td>List published stories. Admin sees all. Supports <code>?published=false&featured=true</code></td></tr>
				<tr><td><span class="pt-badge pt-badge--yes">GET</span></td>    <td><code>/stories/{id}</code></td>     <td>Public</td>  <td>Single story by slug ID</td></tr>
				<tr><td><span class="pt-badge" style="background:#dbeafe;color:#1d4ed8">POST</span></td>   <td><code>/stories</code></td>          <td>Admin</td>   <td>Create a new story. Requires <code>id</code> field.</td></tr>
				<tr><td><span class="pt-badge" style="background:#fef3c7;color:#92400e">PUT</span></td>    <td><code>/stories/{id}</code></td>     <td>Admin</td>   <td>Update an existing story</td></tr>
				<tr><td><span class="pt-badge pt-badge--no" style="background:#fee2e2;color:#991b1b">DELETE</span></td> <td><code>/stories/{id}</code></td>     <td>Admin</td>   <td>Delete a story permanently</td></tr>
				<tr><td><span class="pt-badge pt-badge--yes">GET</span></td>    <td><code>/status</code></td>           <td>Admin</td>   <td>Schema state, table counts, available routes</td></tr>
			</tbody>
		</table>
		<p style="margin-top:12px;font-size:.78rem;color:#94a3b8">
			Test via browser console on any admin page: <code>PT.api('/stories').then(console.log)</code>
		</p>
	</div>

</div>
