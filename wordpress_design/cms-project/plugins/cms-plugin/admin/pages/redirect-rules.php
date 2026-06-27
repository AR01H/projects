<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

global $wpdb;
$table    = $wpdb->prefix . 'ah_redirect_rules';
$site_url = trailingslashit( home_url() );
$notice   = '';
$created  = null; // last-created rule, for copy panel

/* ── POST handlers ──────────────────────────────────────────────── */
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ah_redirect_nonce'] ) ) {
	if ( ! wp_verify_nonce( $_POST['ah_redirect_nonce'], 'ah_redirect_action' ) ) wp_die( 'Security check failed.' );

	/* Save / update */
	if ( isset( $_POST['save_rule'] ) ) {
		$id     = (int) ( $_POST['rule_id'] ?? 0 );
		$raw_target = trim( wp_unslash( $_POST['target_url'] ?? '' ) );
		// If the user entered a slug or relative path (no protocol), resolve it to a full internal URL.
		if ( $raw_target !== '' && ! preg_match( '#^https?://#i', $raw_target ) ) {
			$raw_target = home_url( '/' . ltrim( $raw_target, '/' ) );
		}
		$target = esc_url_raw( $raw_target );
		$source = trim( sanitize_text_field( wp_unslash( $_POST['source_slug'] ?? '' ) ), '/' );
		$type   = in_array( $_POST['type'] ?? '', array( '301', '302', 'exit', '410' ), true ) ? $_POST['type'] : '301';
		$notes  = sanitize_text_field( wp_unslash( $_POST['notes'] ?? '' ) );
		$active = isset( $_POST['is_active'] ) ? 1 : 0;

		// Auto-generate slug if blank
		if ( '' === $source ) {
			$source = substr( base_convert( crc32( $target . microtime() ), 10, 36 ), 0, 6 );
		}

		if ( '410' !== $type && '' === $target ) {
			$notice = 'error:Destination URL is required.';
		} elseif ( '' === $source ) {
			$notice = 'error:Short code could not be generated.';
		} else {
			$data   = array( 'source_slug' => $source, 'target_url' => $target, 'type' => $type, 'notes' => $notes, 'is_active' => $active );
			$format = array( '%s', '%s', '%s', '%s', '%d' );
			if ( $id ) {
				$wpdb->update( $table, $data, array( 'id' => $id ), $format, array( '%d' ) );
				$notice = $wpdb->last_error ? 'error:' . $wpdb->last_error : 'success:Rule updated.';
			} else {
				$wpdb->insert( $table, $data, $format );
				if ( ! $wpdb->last_error ) {
					$created = (object) array_merge( $data, array( 'id' => (int) $wpdb->insert_id ) );
					$notice  = 'success:Short link created!';
				} else {
					$notice = 'error:' . $wpdb->last_error;
				}
			}
		}
	}

	if ( isset( $_POST['toggle_id'] ) ) {
		$wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET is_active = 1 - is_active WHERE id = %d", (int) $_POST['toggle_id'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$notice = 'success:Toggled.';
	}
	if ( isset( $_POST['delete_id'] ) ) {
		$wpdb->delete( $table, array( 'id' => (int) $_POST['delete_id'] ), array( '%d' ) );
		$notice = 'success:Deleted.';
	}
	if ( isset( $_POST['reset_hits_id'] ) ) {
		$wpdb->update( $table, array( 'hit_count' => 0 ), array( 'id' => (int) $_POST['reset_hits_id'] ), array( '%d' ), array( '%d' ) );
		$notice = 'success:Hits reset.';
	}
}

/* ── Data ───────────────────────────────────────────────────────── */
$edit_id   = (int) ( $_GET['edit_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$edit_rule = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE id = %d", $edit_id ) ) : null; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$rules     = $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY is_active DESC, id DESC" ) ?: array(); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

$total_hits = array_sum( array_column( (array) $rules, 'hit_count' ) );
$active_cnt = count( array_filter( (array) $rules, fn( $r ) => $r->is_active ) );

[ $nt, $nm ] = $notice ? explode( ':', $notice, 2 ) : [ '', '' ];

$type_meta = array(
	'301'  => array( 'label' => '301 Permanent', 'color' => '#1d4ed8', 'bg' => '#eff6ff', 'border' => '#93c5fd', 'icon' => '↪' ),
	'302'  => array( 'label' => '302 Temporary', 'color' => '#d97706', 'bg' => '#fffbeb', 'border' => '#fbbf24', 'icon' => '⇄' ),
	'exit' => array( 'label' => 'Exit Link',      'color' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#c4b5fd', 'icon' => '↗' ),
	'410'  => array( 'label' => '410 Gone',       'color' => '#b91c1c', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'icon' => '✕' ),
);
?>
<div class="wrap ah-wrap">

<h1 style="display:flex;align-items:center;gap:10px;">
	<span class="dashicons dashicons-randomize" style="font-size:24px;width:24px;height:24px;color:#7c3aed;"></span>
	Redirect Rules
	<span style="font-size:13px;font-weight:400;color:var(--ah-muted);margin-left:4px;">- short links &amp; redirects</span>
</h1>

<?php if ( $nm ) : ?>
<div class="ah-notice ah-notice-<?php echo $nt === 'error' ? 'warning' : 'success'; ?>" style="margin:10px 0;">
	<?php echo esc_html( $nm ); ?>
</div>
<?php endif; ?>

<?php /* ── Created banner ── */ if ( $created ) : ?>
<div style="background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 100%);border-radius:12px;padding:20px 24px;margin:12px 0 20px;color:#fff;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
	<span style="font-size:28px;">🎉</span>
	<div style="flex:1;min-width:200px;">
		<div style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;opacity:.8;margin-bottom:4px;">Short link ready</div>
		<div style="font-size:18px;font-weight:700;letter-spacing:.01em;" id="ah-created-url">
			<?php echo esc_html( trailingslashit( home_url() ) . $created->source_slug ); ?>
		</div>
	</div>
	<button type="button" id="ah-copy-created"
	        style="background:rgba(255,255,255,.2);border:1.5px solid rgba(255,255,255,.4);color:#fff;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;flex-shrink:0;">
		📋 Copy Link
	</button>
	<a href="<?php echo esc_url( trailingslashit( home_url() ) . $created->source_slug ); ?>" target="_blank"
	   style="background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.3);color:#fff;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;text-decoration:none;flex-shrink:0;">
		↗ Test It
	</a>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 420px;gap:24px;align-items:start;margin-top:16px;">

<!-- ── Left: rules list ─────────────────────────────────────────── -->
<div>

	<!-- Stats row -->
	<div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
		<?php foreach ( array(
			array( 'v' => count( $rules ),            'l' => 'Total Links',  'c' => 'var(--ah-primary)' ),
			array( 'v' => $active_cnt,                'l' => 'Active',       'c' => 'var(--ah-success)' ),
			array( 'v' => number_format($total_hits), 'l' => 'Total Clicks', 'c' => '#7c3aed' ),
		) as $s ) : ?>
		<div style="background:#fff;border:1px solid var(--ah-border);border-radius:10px;padding:12px 18px;min-width:110px;">
			<div style="font-size:20px;font-weight:700;color:<?php echo esc_attr( $s['c'] ); ?>;"><?php echo esc_html( $s['v'] ); ?></div>
			<div style="font-size:11px;color:var(--ah-muted);margin-top:2px;"><?php echo esc_html( $s['l'] ); ?></div>
		</div>
		<?php endforeach; ?>
	</div>

	<?php if ( empty( $rules ) ) : ?>
	<div class="ah-card" style="text-align:center;padding:56px 24px;color:var(--ah-muted);">
		<div style="font-size:48px;margin-bottom:12px;">🔗</div>
		<strong style="color:var(--ah-text);font-size:15px;display:block;margin-bottom:6px;">No links yet</strong>
		Paste a long URL in the form and create your first short link →
	</div>
	<?php else : ?>
	<div class="ah-card" style="overflow:hidden;padding:0;">
		<table style="width:100%;border-collapse:collapse;">
			<thead>
				<tr style="background:var(--ah-bg-light);border-bottom:1px solid var(--ah-border);">
					<th style="padding:10px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ah-muted);text-align:left;">Short Link → Destination</th>
					<th style="padding:10px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ah-muted);width:90px;text-align:left;">Type</th>
					<th style="padding:10px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ah-muted);width:56px;text-align:center;">Clicks</th>
					<th style="padding:10px 14px;width:140px;"></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $rules as $r ) :
				$tm    = $type_meta[ $r->type ] ?? $type_meta['301'];
				$short = trailingslashit( home_url() ) . $r->source_slug;
			?>
			<tr style="border-top:1px solid var(--ah-border);<?php echo ! $r->is_active ? 'opacity:.4;' : ''; ?>">
				<td style="padding:12px 14px;">
					<!-- Short URL -->
					<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
						<code id="url-<?php echo (int) $r->id; ?>"
						      style="font-size:12px;background:var(--ah-bg-light);border:1px solid var(--ah-border);border-radius:5px;padding:2px 8px;color:#1d4ed8;font-weight:600;">
							<?php echo esc_html( $short ); ?>
						</code>
						<button type="button" class="ah-copy-btn ah-btn ah-btn-sm"
						        data-target="url-<?php echo (int) $r->id; ?>"
						        style="padding:2px 7px;font-size:11px;" title="Copy short link">📋</button>
						<a href="<?php echo esc_url( $short ); ?>" target="_blank"
						   style="font-size:11px;color:var(--ah-muted);text-decoration:none;" title="Test">↗</a>
					</div>
					<!-- Arrow + destination -->
					<div style="display:flex;align-items:center;gap:5px;">
						<span style="color:var(--ah-muted);font-size:11px;"><?php echo esc_html( $tm['icon'] ); ?></span>
						<?php if ( '410' === $r->type ) : ?>
							<em style="font-size:11px;color:#b91c1c;">- page permanently removed</em>
						<?php elseif ( $r->target_url ) : ?>
							<span style="font-size:11px;color:var(--ah-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px;"
							      title="<?php echo esc_attr( $r->target_url ); ?>">
								<?php echo esc_html( $r->target_url ); ?>
							</span>
						<?php endif; ?>
					</div>
					<?php if ( $r->notes ) : ?>
						<div style="font-size:11px;color:var(--ah-muted);margin-top:3px;">📝 <?php echo esc_html( $r->notes ); ?></div>
					<?php endif; ?>
				</td>
				<td style="padding:12px 14px;">
					<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:<?php echo esc_attr( $tm['bg'] ); ?>;border:1px solid <?php echo esc_attr( $tm['border'] ); ?>;color:<?php echo esc_attr( $tm['color'] ); ?>;">
						<?php echo esc_html( $tm['icon'] . ' ' . $tm['label'] ); ?>
					</span>
				</td>
				<td style="padding:12px 14px;text-align:center;">
					<span style="font-size:16px;font-weight:700;color:<?php echo $r->hit_count > 0 ? '#7c3aed' : 'var(--ah-muted)'; ?>;">
						<?php echo number_format( (int) $r->hit_count ); ?>
					</span>
				</td>
				<td style="padding:12px 14px;">
					<div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-redirects&edit_id=' . (int) $r->id ) ); ?>"
						   class="ah-btn ah-btn-sm">Edit</a>
						<form method="post" style="display:inline;">
							<?php wp_nonce_field( 'ah_redirect_action', 'ah_redirect_nonce' ); ?>
							<input type="hidden" name="toggle_id" value="<?php echo (int) $r->id; ?>">
							<button type="submit" class="ah-btn ah-btn-sm"
							        style="<?php echo $r->is_active ? 'color:#92400e;' : 'color:var(--ah-success);'; ?>">
								<?php echo $r->is_active ? 'Pause' : 'Enable'; ?>
							</button>
						</form>
						<?php if ( $r->hit_count > 0 ) : ?>
						<form method="post" style="display:inline;">
							<?php wp_nonce_field( 'ah_redirect_action', 'ah_redirect_nonce' ); ?>
							<input type="hidden" name="reset_hits_id" value="<?php echo (int) $r->id; ?>">
							<button type="submit" class="ah-btn ah-btn-sm" title="Reset clicks">↺</button>
						</form>
						<?php endif; ?>
						<form method="post" style="display:inline;"
						      onsubmit="return confirm('Delete this short link?');">
							<?php wp_nonce_field( 'ah_redirect_action', 'ah_redirect_nonce' ); ?>
							<input type="hidden" name="delete_id" value="<?php echo (int) $r->id; ?>">
							<button type="submit" class="ah-btn ah-btn-sm" style="color:var(--ah-danger);">✕</button>
						</form>
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>

<!-- ── Right: create / edit form ────────────────────────────────── -->
<div style="position:sticky;top:32px;">
<div class="ah-card">
	<div class="ah-card-header" style="border-bottom:1px solid var(--ah-border);padding-bottom:14px;margin-bottom:18px;">
		<h2 style="margin:0;display:flex;align-items:center;gap:8px;">
			<?php if ( $edit_rule ) : ?>
				<span style="font-size:18px;">✏️</span> Edit Link
			<?php else : ?>
				<span style="font-size:18px;color:#7c3aed;">🔗</span> Create Short Link
			<?php endif; ?>
		</h2>
	</div>

	<form method="post" id="ah-rdr-form">
		<?php wp_nonce_field( 'ah_redirect_action', 'ah_redirect_nonce' ); ?>
		<input type="hidden" name="save_rule" value="1">
		<?php if ( $edit_rule ) : ?>
			<input type="hidden" name="rule_id" value="<?php echo (int) $edit_rule->id; ?>">
		<?php endif; ?>

		<!-- ① Destination URL - primary, most important -->
		<div class="ah-form-row" style="margin-bottom:16px;">
			<label style="font-weight:700;font-size:13px;display:block;margin-bottom:6px;">
				Destination URL
				<span style="color:var(--ah-danger);">*</span>
				<span id="ah-ext-badge" style="display:none;margin-left:6px;font-size:10px;font-weight:600;padding:1px 7px;border-radius:20px;background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;">↗ External</span>
				<span id="ah-int-badge" style="display:none;margin-left:6px;font-size:10px;font-weight:600;padding:1px 7px;border-radius:20px;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">⌂ Internal</span>
			</label>
			<input type="text" name="target_url" id="ah-rdr-target"
			       value="<?php echo esc_attr( $edit_rule ? $edit_rule->target_url : '' ); ?>"
			       placeholder="Paste the long URL here…"
			       class="regular-text"
			       style="width:100%;font-size:13px;"
			       <?php echo $edit_rule ? '' : 'autofocus'; ?>>
		</div>

		<!-- ② Short code - secondary -->
		<div class="ah-form-row" style="margin-bottom:16px;">
			<label style="font-weight:700;font-size:13px;display:block;margin-bottom:6px;">
				Short Code
				<small style="font-weight:400;color:var(--ah-muted);">(leave blank to auto-generate)</small>
			</label>
			<div style="display:flex;align-items:stretch;gap:0;">
				<span style="background:var(--ah-bg-light);border:1px solid var(--ah-border);border-right:none;border-radius:6px 0 0 6px;padding:8px 10px;font-size:11px;color:var(--ah-muted);white-space:nowrap;display:flex;align-items:center;">
					<?php echo esc_html( parse_url( $site_url, PHP_URL_HOST ) ); ?>/
				</span>
				<input type="text" name="source_slug" id="ah-rdr-slug"
				       value="<?php echo esc_attr( $edit_rule ? $edit_rule->source_slug : '' ); ?>"
				       placeholder="my-link"
				       style="border-radius:0;border-left:none;border-right:none;flex:1;font-size:13px;">
				<button type="button" id="ah-gen-slug"
				        style="background:var(--ah-bg-light);border:1px solid var(--ah-border);border-left:none;border-radius:0 6px 6px 0;padding:0 12px;cursor:pointer;font-size:13px;color:#7c3aed;font-weight:600;white-space:nowrap;"
				        title="Generate random code">🎲</button>
			</div>
			<div id="ah-slug-preview" style="margin-top:5px;font-size:11px;color:var(--ah-muted);display:none;">
				→ <strong id="ah-slug-preview-url" style="color:var(--ah-primary);"></strong>
			</div>
		</div>

		<!-- ③ Type -->
		<div class="ah-form-row" style="margin-bottom:16px;">
			<label style="font-weight:700;font-size:13px;display:block;margin-bottom:8px;">Redirect Type</label>
			<div style="display:grid;grid-template-columns:1fr 1fr;gap:7px;">
				<?php foreach ( $type_meta as $val => $tm ) :
					$checked = ( $edit_rule ? $edit_rule->type : '301' ) === $val;
				?>
				<label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:2px solid <?php echo $checked ? esc_attr( $tm['color'] ) : 'var(--ah-border)'; ?>;background:<?php echo $checked ? esc_attr( $tm['bg'] ) : '#fff'; ?>;border-radius:8px;cursor:pointer;transition:all .15s;" class="ah-type-card">
					<input type="radio" name="type" value="<?php echo esc_attr( $val ); ?>"
					       <?php checked( $checked ); ?> style="display:none;">
					<span style="font-size:16px;color:<?php echo esc_attr( $tm['color'] ); ?>;"><?php echo esc_html( $tm['icon'] ); ?></span>
					<div>
						<strong style="font-size:11px;color:<?php echo esc_attr( $tm['color'] ); ?>;display:block;"><?php echo esc_html( $tm['label'] ); ?></strong>
					</div>
				</label>
				<?php endforeach; ?>
			</div>
			<div id="ah-gone-tip" style="display:none;margin-top:8px;padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;font-size:12px;color:#b91c1c;">
				410 Gone - no destination needed. Tells Google this page is permanently removed.
			</div>
			<div id="ah-exit-tip" style="display:none;margin-top:8px;padding:8px 12px;background:#f5f3ff;border:1px solid #c4b5fd;border-radius:6px;font-size:12px;color:#7c3aed;">
				Exit Link - shows a "leaving site" page before sending the visitor to the destination.
			</div>
		</div>

		<!-- ④ Notes -->
		<div class="ah-form-row" style="margin-bottom:16px;">
			<label style="font-weight:700;font-size:13px;display:block;margin-bottom:6px;">Label <small style="font-weight:400;color:var(--ah-muted);">(optional - shown in this list)</small></label>
			<input type="text" name="notes"
			       value="<?php echo esc_attr( $edit_rule ? $edit_rule->notes : '' ); ?>"
			       placeholder="e.g. Brochure download, July email campaign…"
			       class="regular-text" style="width:100%;">
		</div>

		<!-- Active -->
		<div class="ah-form-row" style="margin-bottom:18px;">
			<label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
				<input type="checkbox" name="is_active" value="1"
				       <?php checked( $edit_rule ? (int) $edit_rule->is_active : 1, 1 ); ?>>
				<span><strong>Active</strong> - link works immediately after saving</span>
			</label>
		</div>

		<div style="display:flex;gap:10px;">
			<button type="submit" class="ah-btn ah-btn-primary" style="flex:1;justify-content:center;font-size:14px;padding:11px;">
				<?php if ( $edit_rule ) : ?>
					<span class="dashicons dashicons-yes" style="font-size:14px;line-height:1.8;margin-right:4px;"></span> Update
				<?php else : ?>
					🔗 Create Short Link
				<?php endif; ?>
			</button>
			<?php if ( $edit_rule ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-redirects' ) ); ?>" class="ah-btn">Cancel</a>
			<?php endif; ?>
		</div>
	</form>
</div>

<!-- Type guide -->
<div style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
	<?php foreach ( $type_meta as $tm ) : ?>
	<div style="background:#fff;border:1px solid var(--ah-border);border-radius:8px;padding:10px 12px;">
		<div style="font-size:14px;margin-bottom:3px;"><?php echo esc_html( $tm['icon'] ); ?></div>
		<strong style="font-size:11px;color:<?php echo esc_attr( $tm['color'] ); ?>;"><?php echo esc_html( $tm['label'] ); ?></strong>
	</div>
	<?php endforeach; ?>
</div>

</div><!-- /sticky -->
</div><!-- /grid -->
</div><!-- /wrap -->

<script>
jQuery(function ($) {
	var siteBase = <?php echo wp_json_encode( trailingslashit( home_url() ) ); ?>;
	var siteHost = <?php echo wp_json_encode( parse_url( home_url(), PHP_URL_HOST ) ); ?>;

	/* ── Target URL field: resolve slug → full URL preview ── */
	var $targetField   = $('#ah-rdr-target');
	var $resolvedBox   = $('<div id="ah-resolved-preview" style="display:none;margin-top:6px;padding:7px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;font-size:12px;color:#166534;"></div>').insertAfter( $targetField.closest('.ah-form-row').find('p.description').length ? $targetField.closest('.ah-form-row').find('p') : $targetField );

	$targetField.on('input', function () {
		var v      = $.trim( this.value );
		var isHttp = /^https?:\/\//i.test( v );
		var isExt  = isHttp && v.toLowerCase().indexOf( siteHost.toLowerCase() ) === -1;

		$('#ah-ext-badge').toggle( isExt );
		$('#ah-int-badge').toggle( !! v && ! isExt );

		// If it looks like a slug/path (no protocol), show what it'll resolve to.
		if ( v && ! isHttp ) {
			var resolved = siteBase.replace(/\/+$/, '') + '/' + v.replace(/^\/+/, '');
			$resolvedBox.html( '→ Will redirect to: <strong>' + resolved + '</strong>' ).show();
		} else {
			$resolvedBox.hide();
		}
	}).trigger('input');

	/* ── Slug preview ── */
	function updatePreview() {
		var s = $.trim( $('#ah-rdr-slug').val() ).replace( /^\/+|\/+$/g, '' );
		if ( s ) {
			$('#ah-slug-preview').show();
			$('#ah-slug-preview-url').text( siteBase + s );
		} else {
			$('#ah-slug-preview').hide();
		}
	}
	$('#ah-rdr-slug').on('input', updatePreview);
	updatePreview();

	/* ── Generate random slug ── */
	$('#ah-gen-slug').on('click', function () {
		var chars  = 'abcdefghjkmnpqrstuvwxyz23456789';
		var result = '';
		for ( var i = 0; i < 6; i++ ) result += chars[ Math.floor( Math.random() * chars.length ) ];
		$('#ah-rdr-slug').val( result ).trigger('input');
	});

	/* ── Type card selector ── */
	function syncTypeCards() {
		var val = $('input[name=type]:checked').val();
		$('.ah-type-card').each(function () {
			var $c  = $(this);
			var v   = $c.find('input').val();
			var col = $c.find('strong').css('color');
			if ( v === val ) {
				$c.css({ borderColor: col, background: '' });
			} else {
				$c.css({ borderColor: 'var(--ah-border)', background: '#fff' });
			}
		});
		// Target row visibility
		$('#ah-rdr-target').closest('.ah-form-row').toggle( val !== '410' );
		$('#ah-gone-tip').toggle( val === '410' );
		$('#ah-exit-tip').toggle( val === 'exit' );
	}
	$('.ah-type-card').on('click', function () {
		$(this).find('input').prop('checked', true);
		syncTypeCards();
	});
	syncTypeCards();

	/* ── Copy buttons in the list ── */
	$(document).on('click', '.ah-copy-btn', function () {
		var $btn = $(this);
		var text = document.getElementById( $btn.data('target') ).textContent.trim();
		navigator.clipboard.writeText( text ).then(function () {
			$btn.text('✓').css('color','var(--ah-success)');
			setTimeout(function(){ $btn.text('📋').css('color',''); }, 1600);
		});
	});

	/* ── Copy created link banner ── */
	$('#ah-copy-created').on('click', function () {
		var text = $('#ah-created-url').text().trim();
		navigator.clipboard.writeText( text ).then(function () {
			$('#ah-copy-created').text('✓ Copied!');
			setTimeout(function(){ $('#ah-copy-created').text('📋 Copy Link'); }, 2000);
		});
	});
});
</script>
