<?php
/**
 * admin/FeaturedIn.php
 * Manage "Featured In" logo strip sections.
 * Multiple named sections; each can be used on any page via adn_component().
 */
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

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
<div class="wrap ah-wrap">
	<?php AdminComponents::pageHeader( 'awards', 'Featured In - Logo Strips', 'Create named logo strips. Use the section ID in any page template to display whichever strip you need.' ); ?>

	<?php if ( $notice ) : ?>
		<?php AdminComponents::notice( $notice, $n_type ); ?>
	<?php endif; ?>

	<?php if ( 'list' === $action ) : ?>
		<?php if ( empty( $all_sections ) ) : ?>
			<?php AdminComponents::emptyState( 'No sections yet. Create your first logo strip.', 'awards' ); ?>
			<div style="text-align:center;margin-top:12px;">
				<a href="<?php echo ah_fi_url( array( 'action' => 'new' ) ); ?>" class="ah-btn ah-btn-primary">+ New Section</a>
			</div>
		<?php else : ?>
			<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
				<a href="<?php echo ah_fi_url( array( 'action' => 'new' ) ); ?>" class="ah-btn ah-btn-primary">+ New Section</a>
			</div>
			<?php
			$fi_rows = array();
			foreach ( $all_sections as $sec ) {
				$logo_count = count( $sec['logos'] ?? array() );
				$edit_url   = ah_fi_url( array( 'action' => 'edit', 'id' => esc_attr( $sec['id'] ?? '' ) ) );
				$del_nonce  = wp_create_nonce( 'ah_fi_delete' );
				$actions_html = '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>'
					. '<form method="post" style="display:inline;margin:0;padding:0">'
					. '<input type="hidden" name="ah_fi_del_nonce" value="' . esc_attr( $del_nonce ) . '">'
					. '<input type="hidden" name="del_section_id" value="' . esc_attr( $sec['id'] ?? '' ) . '">'
					. '<button type="submit" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Section" data-confirm="Delete this section?">Delete</button>'
					. '</form>';

				$row = new \stdClass();
				$row->id = $sec['id'] ?? '';
				$row->heading = $sec['heading'] ?? '';
				$row->logo_count = $logo_count;
				$row->actions_html = $actions_html;
				$fi_rows[] = $row;
			}
			AdminComponents::dataTable( array(
				'columns' => array(
					array( 'label' => 'Heading', 'render' => function ( $r ) {
						return '<strong>' . esc_html( $r->heading ?: '(no heading)' ) . '</strong>';
					} ),
					array( 'label' => 'Section ID', 'render' => function ( $r ) {
						return '<code>' . esc_html( $r->id ) . '</code>';
					} ),
					array( 'label' => 'Logos', 'render' => function ( $r ) {
						return (int) $r->logo_count;
					} ),
				),
				'items'         => $fi_rows,
				'empty_message' => 'No sections yet.',
				'actions'       => function ( $r ) {
					return $r->actions_html;
				},
			) ); ?>

			<div class="ah-card" style="margin-top:16px;">
				<div class="ah-card-body" style="padding:12px 16px;">
					<p class="ah-builder-note" style="margin:0;">
						<strong>How to use:</strong>
						<code>adn_component( 'parts/featured_in', array( 'section' => '&lt;section-id&gt;' ) );</code>
						- omit <code>section</code> to show the first one.
					</p>
				</div>
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
		<?php AdminComponents::backLink( ah_fi_url() ); ?>

		<form method="post">
			<?php wp_nonce_field( 'ah_fi_save', 'ah_fi_save_nonce' ); ?>

			<?php
			ob_start();
			AdminComponents::formGrid( array(
				array( 'Section ID <small>(slug - no spaces)</small>', '<input type="text" name="fi_id" value="' . esc_attr( $fi_id ) . '"' . ( $is_edit ? ' readonly style="background:var(--ah-bg-light);color:var(--ah-muted);"' : '' ) . ' class="regular-text" placeholder="e.g. press, awards, partners" pattern="[a-z0-9\\-_]+" required>' ),
				array( 'Heading text', '<input type="text" name="fi_heading" value="' . esc_attr( $fi_heading ) . '" class="regular-text" placeholder="As featured in:">' ),
			) );
			AdminComponents::card( $is_edit ? 'Edit Section' : 'New Section', ob_get_clean() ); ?>

			<?php AdminComponents::card( 'Logos', '<p class="ah-builder-note">Each logo needs an image URL. Link and label are optional.</p>' ); ?>

			<div id="ah-fi-logos" class="ah-builder-stack" style="margin-top:-16px;">
				<?php foreach ( $fi_logos as $i => $logo ) : ?>
					<div class="ah-fi-logo-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 28px;gap:10px;align-items:end;background:#f8fafc;border:1px solid var(--ah-border);border-radius:var(--ah-radius);padding:12px 14px;">
						<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:var(--ah-text-muted);">
							Image URL
							<input type="url" name="fi_logos[<?php echo esc_attr( $i ); ?>][image_url]" value="<?php echo esc_attr( $logo['image_url'] ?? '' ); ?>" class="regular-text" placeholder="https://…/logo.png">
						</label>
						<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:var(--ah-text-muted);">
							Link <small style="font-weight:400">(optional)</small>
							<input type="url" name="fi_logos[<?php echo esc_attr( $i ); ?>][link]" value="<?php echo esc_attr( $logo['link'] ?? '' ); ?>" class="regular-text" placeholder="https://…">
						</label>
						<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:var(--ah-text-muted);">
							Label / Alt
							<input type="text" name="fi_logos[<?php echo esc_attr( $i ); ?>][label]" value="<?php echo esc_attr( $logo['label'] ?? '' ); ?>" class="regular-text" placeholder="e.g. BBC">
						</label>
						<button type="button" class="ah-btn ah-btn-danger ah-btn-sm ah-fi-remove" title="Remove" style="height:30px;">✕</button>
					</div>
				<?php endforeach; ?>
			</div>
			<p style="margin-top:12px;">
				<button type="button" class="ah-btn ah-btn-secondary" id="ah-fi-add">+ Add Logo</button>
			</p>

			<div style="display:flex;align-items:center;gap:12px;margin-top:16px;">
				<button type="submit" class="ah-btn ah-btn-primary"><?php echo $is_edit ? 'Save Changes' : 'Create Section'; ?></button>
				<a href="<?php echo ah_fi_url(); ?>" class="ah-btn ah-btn-secondary">Cancel</a>
			</div>
		</form>

		<script>
		(function () {
			var list   = document.getElementById('ah-fi-logos');
			var addBtn = document.getElementById('ah-fi-add');
			if (!list || !addBtn) return;

			function rowHtml(i) {
				return '<div class="ah-fi-logo-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 28px;gap:10px;align-items:end;background:#f8fafc;border:1px solid var(--ah-border);border-radius:var(--ah-radius);padding:12px 14px;">' +
					'<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:var(--ah-text-muted);">Image URL<input type="url" name="fi_logos[' + i + '][image_url]" class="regular-text" placeholder="https://…/logo.png"></label>' +
					'<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:var(--ah-text-muted);">Link <small style="font-weight:400">(optional)</small><input type="url" name="fi_logos[' + i + '][link]" class="regular-text" placeholder="https://…"></label>' +
					'<label style="display:flex;flex-direction:column;gap:4px;font-size:12px;font-weight:600;color:var(--ah-text-muted);">Label / Alt<input type="text" name="fi_logos[' + i + '][label]" class="regular-text" placeholder="e.g. BBC"></label>' +
					'<button type="button" class="ah-btn ah-btn-danger ah-btn-sm ah-fi-remove" title="Remove" style="height:30px;">✕</button>' +
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
