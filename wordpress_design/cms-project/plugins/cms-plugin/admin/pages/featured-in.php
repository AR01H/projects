<?php
/**
 * admin/pages/featured-in.php
 * Manage "Featured In" logo strip sections.
 * Multiple named sections; each can be used on any page via adn_component().
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

if ( ! defined( 'AH_FI_OPTION' ) ) { define( 'AH_FI_OPTION', 'ah_featured_in_sections' ); }

/* ── helpers ── */
function ah_fi_get_all(): array {
	$raw = get_option( AH_FI_OPTION, '' );
	$dec = $raw ? json_decode( $raw, true ) : array();
	return is_array( $dec ) ? $dec : array();
}

function ah_fi_save_all( array $sections ): void {
	update_option( AH_FI_OPTION, wp_json_encode( array_values( $sections ) ) );
}

function ah_fi_find( string $id ): ?array {
	foreach ( ah_fi_get_all() as $s ) {
		if ( isset( $s['id'] ) && $s['id'] === $id ) { return $s; }
	}
	return null;
}

function ah_fi_url( array $args = array() ): string {
	return esc_url( add_query_arg( array_merge( array( 'page' => 'ah-featured-in' ), $args ), admin_url( 'admin.php' ) ) );
}

/* ── state ── */
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = sanitize_key( $_GET['id'] ?? '' );
$notice  = '';
$n_type  = 'success';

/* ── POST: delete section ── */
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ah_fi_del_nonce'] ) ) {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ah_fi_del_nonce'] ) ), 'ah_fi_delete' ) ) {
		$notice = 'Security check failed.'; $n_type = 'error';
	} else {
		$del_id   = sanitize_key( $_POST['del_section_id'] ?? '' );
		$sections = array_values( array_filter( ah_fi_get_all(), fn( $s ) => ( $s['id'] ?? '' ) !== $del_id ) );
		ah_fi_save_all( $sections );
		$notice  = 'Section deleted.';
		$action  = 'list';
	}
}

/* ── POST: save section (new or edit) ── */
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['ah_fi_save_nonce'] ) ) {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ah_fi_save_nonce'] ) ), 'ah_fi_save' ) ) {
		$notice = 'Security check failed.'; $n_type = 'error';
	} else {
		$sid     = sanitize_key( $_POST['fi_id'] ?? '' );
		$heading = sanitize_text_field( wp_unslash( $_POST['fi_heading'] ?? '' ) );
		$logos   = array();
		foreach ( (array) ( $_POST['fi_logos'] ?? array() ) as $logo ) {
			$img   = esc_url_raw( wp_unslash( $logo['image_url'] ?? '' ) );
			$link  = esc_url_raw( wp_unslash( $logo['link'] ?? '' ) );
			$label = sanitize_text_field( wp_unslash( $logo['label'] ?? '' ) );
			if ( '' === $img ) { continue; }
			$logos[] = array( 'image_url' => $img, 'link' => $link, 'label' => $label );
		}

		if ( '' === $sid ) {
			$notice = 'Section ID is required.'; $n_type = 'error';
		} else {
			$new_section = array( 'id' => $sid, 'heading' => $heading, 'logos' => $logos );
			$all         = ah_fi_get_all();
			$replaced    = false;
			foreach ( $all as &$s ) {
				if ( ( $s['id'] ?? '' ) === $sid ) { $s = $new_section; $replaced = true; break; }
			}
			unset( $s );
			if ( ! $replaced ) { $all[] = $new_section; }
			ah_fi_save_all( $all );
			$notice  = 'Section saved.';
			$action  = 'list';
			$edit_id = '';
		}
	}
}

$all_sections = ah_fi_get_all();
?>
<div class="wrap ah-admin-wrap">
	<div class="ah-wrap" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
		<div>
			<h1>Featured In - Logo Strips</h1>
			<p style="color:#64748b;margin:4px 0 0;font-size:13px">Create named logo strips. Use the section ID in any page template to display whichever strip you need.</p>
		</div>
		<?php if ( 'list' === $action ) : ?>
			<a href="<?php echo ah_fi_url( array( 'action' => 'new' ) ); ?>" class="button button-primary">+ New Section</a>
		<?php endif; ?>
	</div>

	<?php if ( $notice ) : ?>
		<div class="ah-admin-notice ah-admin-notice--<?php echo esc_attr( $n_type ); ?>" style="margin-top:16px"><?php echo esc_html( $notice ); ?></div>
	<?php endif; ?>

	<?php if ( 'list' === $action ) : ?>
		<?php if ( empty( $all_sections ) ) : ?>
			<div class="ah-admin-box" style="max-width:860px;text-align:center;padding:40px;margin-top:20px">
				<p style="color:#64748b;margin:0 0 16px">No sections yet. Create your first logo strip.</p>
				<a href="<?php echo ah_fi_url( array( 'action' => 'new' ) ); ?>" class="button button-primary">+ New Section</a>
			</div>
		<?php else : ?>
			<div style="max-width:860px;display:flex;flex-direction:column;gap:10px;margin-top:20px">
				<?php foreach ( $all_sections as $sec ) :
					$sec_id      = esc_html( $sec['id'] ?? '' );
					$sec_heading = esc_html( $sec['heading'] ?? '' );
					$logo_count  = count( $sec['logos'] ?? array() );
				?>
					<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
						<div style="flex:1;min-width:0">
							<strong style="font-size:0.95rem;color:#111827"><?php echo $sec_heading ?: '<em style="color:#9ca3af">No heading</em>'; ?></strong>
							<span style="display:inline-flex;align-items:center;margin-left:10px;font-size:12px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:20px;padding:2px 10px;color:#475569;font-family:monospace"><?php echo $sec_id; ?></span>
							<p style="margin:4px 0 0;font-size:12px;color:#64748b"><?php echo esc_html( $logo_count ); ?> logo<?php echo 1 !== $logo_count ? 's' : ''; ?></p>
						</div>
						<div style="display:flex;gap:8px;flex-shrink:0">
							<a href="<?php echo ah_fi_url( array( 'action' => 'edit', 'id' => esc_attr( $sec['id'] ?? '' ) ) ); ?>" class="button button-secondary" style="font-size:12px">Edit</a>
							<form method="post" style="display:inline" onsubmit="return confirm('Delete this section?')">
								<?php wp_nonce_field( 'ah_fi_delete', 'ah_fi_del_nonce' ); ?>
								<input type="hidden" name="del_section_id" value="<?php echo esc_attr( $sec['id'] ?? '' ); ?>">
								<button type="submit" class="button" style="color:#dc2626;border-color:#fca5a5;font-size:12px">Delete</button>
							</form>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div style="max-width:860px;margin-top:20px;padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px">
				<p style="margin:0;font-size:12px;color:#64748b">
					<strong>How to use:</strong>
					<code>adn_component( 'parts/featured_in', array( 'section' => '<em>section-id</em>' ) );</code>
					- omit <code>section</code> to show the first one.
				</p>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<?php
		$is_edit    = 'edit' === $action && '' !== $edit_id;
		$existing   = $is_edit ? ah_fi_find( $edit_id ) : null;
		$fi_id      = $existing['id']      ?? ( sanitize_key( $_POST['fi_id'] ?? '' ) );
		$fi_heading = $existing['heading'] ?? sanitize_text_field( wp_unslash( $_POST['fi_heading'] ?? 'As featured in:' ) );
		$fi_logos   = $existing['logos']   ?? array();
		?>
		<div style="max-width:860px;margin-top:20px">
			<a href="<?php echo ah_fi_url(); ?>" style="font-size:13px;color:#64748b;text-decoration:none">← Back to sections</a>
		</div>

		<form method="post" style="max-width:860px">
			<?php wp_nonce_field( 'ah_fi_save', 'ah_fi_save_nonce' ); ?>

			<div class="ah-admin-box" style="margin-top:16px">
				<h2><?php echo $is_edit ? 'Edit Section' : 'New Section'; ?></h2>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
					<label style="display:flex;flex-direction:column;gap:6px;font-weight:600;font-size:13px">
						Section ID <small style="font-weight:400;color:#64748b">(slug - no spaces)</small>
						<input type="text" name="fi_id"
							value="<?php echo esc_attr( $fi_id ); ?>"
							<?php echo $is_edit ? 'readonly style="background:#f1f5f9;color:#64748b"' : ''; ?>
							class="regular-text" placeholder="e.g. press, awards, partners"
							pattern="[a-z0-9\-_]+" title="Lowercase letters, numbers, hyphens only" required>
					</label>
					<label style="display:flex;flex-direction:column;gap:6px;font-weight:600;font-size:13px">
						Heading text
						<input type="text" name="fi_heading" value="<?php echo esc_attr( $fi_heading ); ?>" class="regular-text" placeholder="As featured in:">
					</label>
				</div>
			</div>

			<div class="ah-admin-box">
				<h2>Logos</h2>
				<p style="color:#64748b;font-size:13px;margin:0 0 16px">Each logo needs an image URL. Link and label are optional.</p>
				<div id="ah-fi-logos" style="display:flex;flex-direction:column;gap:10px">
					<?php foreach ( $fi_logos as $i => $logo ) : ?>
						<div class="ah-fi-logo-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 28px;gap:10px;align-items:end;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px">
							<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:#475569">
								Image URL
								<input type="url" name="fi_logos[<?php echo esc_attr( $i ); ?>][image_url]" value="<?php echo esc_attr( $logo['image_url'] ?? '' ); ?>" class="regular-text" placeholder="https://…/logo.png">
							</label>
							<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:#475569">
								Link <small style="font-weight:400">(optional)</small>
								<input type="url" name="fi_logos[<?php echo esc_attr( $i ); ?>][link]" value="<?php echo esc_attr( $logo['link'] ?? '' ); ?>" class="regular-text" placeholder="https://…">
							</label>
							<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:#475569">
								Label / Alt
								<input type="text" name="fi_logos[<?php echo esc_attr( $i ); ?>][label]" value="<?php echo esc_attr( $logo['label'] ?? '' ); ?>" class="regular-text" placeholder="e.g. BBC">
							</label>
							<button type="button" class="button-link-delete ah-fi-remove" style="height:30px" title="Remove">✕</button>
						</div>
					<?php endforeach; ?>
				</div>
				<p style="margin-top:12px">
					<button type="button" class="button button-secondary" id="ah-fi-add">+ Add Logo</button>
				</p>
			</div>

			<div style="display:flex;align-items:center;gap:12px;margin-top:4px">
				<?php submit_button( $is_edit ? 'Save Changes' : 'Create Section', 'primary', 'submit', false ); ?>
				<a href="<?php echo ah_fi_url(); ?>" class="button button-secondary">Cancel</a>
			</div>
		</form>

		<script>
		(function () {
			var list   = document.getElementById('ah-fi-logos');
			var addBtn = document.getElementById('ah-fi-add');
			if (!list || !addBtn) return;

			function rowHtml(i) {
				return '<div class="ah-fi-logo-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 28px;gap:10px;align-items:end;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px">' +
					'<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:#475569">Image URL<input type="url" name="fi_logos[' + i + '][image_url]" class="regular-text" placeholder="https://…/logo.png"></label>' +
					'<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:#475569">Link <small style="font-weight:400">(optional)</small><input type="url" name="fi_logos[' + i + '][link]" class="regular-text" placeholder="https://…"></label>' +
					'<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:#475569">Label / Alt<input type="text" name="fi_logos[' + i + '][label]" class="regular-text" placeholder="e.g. BBC"></label>' +
					'<button type="button" class="button-link-delete ah-fi-remove" style="height:30px" title="Remove">✕</button>' +
					'</div>';
			}

			addBtn.addEventListener('click', function () {
				var i = list.children.length;
				var div = document.createElement('div');
				div.innerHTML = rowHtml(i);
				list.appendChild(div.firstElementChild);
			});

			list.addEventListener('click', function (e) {
				if (e.target.matches('.ah-fi-remove')) {
					e.target.closest('.ah-fi-logo-row').remove();
					list.querySelectorAll('.ah-fi-logo-row').forEach(function (row, idx) {
						row.querySelectorAll('[name]').forEach(function (el) {
							el.name = el.name.replace(/fi_logos\[\d+\]/, 'fi_logos[' + idx + ']');
						});
					});
				}
			});
		})();
		</script>
	<?php endif; ?>
</div>
