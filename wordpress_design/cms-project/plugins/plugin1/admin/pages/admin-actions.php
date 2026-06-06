<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );
?>
<div class="wrap ah-wrap">
	<h1><span class="dashicons dashicons-admin-tools"></span> Admin Actions</h1>
	<p style="color:var(--ah-muted);margin-top:4px;">Quick utilities for maintenance, testing, and diagnostics.</p>

	<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;margin-top:24px;">

		<!-- Flush Rewrite Rules -->
		<div class="ah-card ah-action-card">
			<div class="ah-action-icon" style="background:#eff6ff;">
				<span class="dashicons dashicons-update" style="color:#2563eb;"></span>
			</div>
			<h3>Flush Rewrite Rules</h3>
			<p>Regenerate WordPress permalink rules. Run this after adding new page slugs or custom post types.</p>
			<button class="ah-btn ah-btn-primary ah-action-btn" data-action="ah_flush_rewrites">Run</button>
			<div class="ah-action-result"></div>
		</div>

		<!-- Clear Transients -->
		<div class="ah-card ah-action-card">
			<div class="ah-action-icon" style="background:#f0fdf4;">
				<span class="dashicons dashicons-trash" style="color:#16a34a;"></span>
			</div>
			<h3>Clear Cache</h3>
			<p>Delete all WordPress transient cache entries from the database. Useful when stale cached data causes issues.</p>
			<button class="ah-btn ah-btn-primary ah-action-btn" data-action="ah_clear_transients">Run</button>
			<div class="ah-action-result"></div>
		</div>

		<!-- DB Health Check -->
		<div class="ah-card ah-action-card">
			<div class="ah-action-icon" style="background:#f0fdf4;">
				<span class="dashicons dashicons-database" style="color:#16a34a;"></span>
			</div>
			<h3>DB Health Check</h3>
			<p>Verify that all required <code>wp_ah_*</code> tables exist in the database and report any that are missing.</p>
			<button class="ah-btn ah-btn-primary ah-action-btn" data-action="ah_db_health_check">Run</button>
			<div class="ah-action-result"></div>
		</div>

		<!-- Clear Audit Log -->
		<div class="ah-card ah-action-card">
			<div class="ah-action-icon" style="background:#fff7ed;">
				<span class="dashicons dashicons-list-view" style="color:#ea580c;"></span>
			</div>
			<h3>Clear Audit Log</h3>
			<p>Truncate the audit log table. All recorded create / update / delete events will be permanently removed.</p>
			<button class="ah-btn ah-btn-danger ah-action-btn" data-action="ah_clear_audit_log" data-confirm="This will permanently delete all audit log entries. Continue?">Run</button>
			<div class="ah-action-result"></div>
		</div>

		<!-- Clear Form Submissions -->
		<div class="ah-card ah-action-card">
			<div class="ah-action-icon" style="background:#fdf2f8;">
				<span class="dashicons dashicons-email" style="color:#9333ea;"></span>
			</div>
			<h3>Clear Form Submissions</h3>
			<p>Delete all visitor submissions captured by the Form Builder. Useful when cleaning up test submissions.</p>
			<button class="ah-btn ah-btn-danger ah-action-btn" data-action="ah_clear_form_submissions" data-confirm="This will delete all form submissions. Continue?">Run</button>
			<div class="ah-action-result"></div>
		</div>

		<div class="ah-card ah-action-card" style="border-top:3px solid #dc2626;">
			<div class="ah-action-icon" style="background:#fef2f2;">
				<span class="dashicons dashicons-database" style="color:#dc2626;"></span>
			</div>
			<h3 style="color:#dc2626;">Schema Set up</h3>
			<p>Install Whole Pending Schemas</p>
			<button class="ah-btn ah-btn-danger ah-action-btn"
				data-action="ah_schema_setup"
				data-confirm=" Create Pending Schema"
				data-double-confirm="YES">Schema Create</button>
			<div class="ah-action-result"></div>
		</div>

		<!-- Delete & Create Schema -->
		<div class="ah-card ah-action-card" style="border-top:3px solid #dc2626;">
			<div class="ah-action-icon" style="background:#fef2f2;">
				<span class="dashicons dashicons-database" style="color:#dc2626;"></span>
			</div>
			<h3 style="color:#dc2626;">Delete &amp; Create Schema</h3>
			<p>Drop <strong>all</strong> <code>wp_ah_*</code> tables and rebuild them from scratch. Default settings and seed data will be restored. <strong>All content will be permanently lost.</strong></p>
			<button class="ah-btn ah-btn-danger ah-action-btn"
				data-action="ah_rebuild_schema"
				data-confirm="⚠️ DANGER: This will permanently delete ALL data in every wp_ah_* table and recreate the schema from scratch. This cannot be undone. Type YES in the next prompt to confirm."
				data-double-confirm="YES">Dangerous Run</button>
			<div class="ah-action-result"></div>
		</div>

	</div>
</div>

<style>
.ah-action-card {
	display: flex;
	flex-direction: column;
	gap: 10px;
}
.ah-action-card h3 {
	margin: 0;
	font-size: 15px;
	color: var(--ah-text);
}
.ah-action-card p {
	margin: 0;
	font-size: 13px;
	color: var(--ah-muted);
	flex: 1;
}
.ah-action-icon {
	width: 44px;
	height: 44px;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
}
.ah-action-icon .dashicons {
	font-size: 22px;
	width: 22px;
	height: 22px;
}
.ah-action-result {
	font-size: 12px;
	min-height: 18px;
	padding: 6px 10px;
	border-radius: 4px;
	display: none;
}
.ah-action-result.ok  { display:block; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
.ah-action-result.err { display:block; background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
.ah-action-btn[disabled] { opacity:.6; cursor:not-allowed; }
</style>

<script>
jQuery(function($){
	$('.ah-action-btn').on('click', function(){
		var $btn    = $(this);
		var $result = $btn.siblings('.ah-action-result');
		var action  = $btn.data('action');
		var confirm_msg = $btn.data('confirm');

		if (confirm_msg && !window.confirm(confirm_msg)) return;

		var double_confirm = $btn.data('double-confirm');
		if (double_confirm) {
			var typed = window.prompt('Type "' + double_confirm + '" to confirm:');
			if (typed !== double_confirm) {
				window.alert('Cancelled - text did not match.');
				return;
			}
		}

		$btn.prop('disabled', true).text('Running…');
		$result.removeClass('ok err').hide();

		$.post(ajaxurl, {
			action : action,
			nonce  : ahAdmin.nonce,
		}, function(res){
			$btn.prop('disabled', false).text('Run');
			if (res.success) {
				$result.addClass('ok').text('✓ ' + res.data.message);
			} else {
				$result.addClass('err').text('✗ ' + (res.data ? res.data.message : 'Unknown error.'));
			}
		}).fail(function(){
			$btn.prop('disabled', false).text('Run');
			$result.addClass('err').text('✗ Request failed.');
		});
	});
});
</script>
