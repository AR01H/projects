<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

use Ah\Cms\Admin\Components\AdminComponents;

$model     = new AH_Shortcuts_Model();
$notice    = '';
$notice_type = 'success';
$action    = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id   = (int) ( $_GET['id'] ?? 0 );
$form_item = null; // Repopulated on a rejected save so the admin doesn't lose what they typed.

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_shortcuts_nonce'] ?? '', 'ah_save_shortcut' ) ) wp_die( 'Security.' );

	$tag = sanitize_key( wp_unslash( $_POST['tag'] ?? '' ) );
	$data = array(
		'label'  => sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) ),
		'tag'    => $tag,
		// Trusted admin-authored template (manage_options-gated, same trust model as Custom Code) -
		// not run through wp_kses_post(), which would strip iframes/inline attributes templates may need.
		'html'   => wp_unslash( $_POST['html'] ?? '' ),
		'css'    => wp_unslash( $_POST['css'] ?? '' ),
		'status' => in_array( $_POST['status'] ?? 'draft', array( 'active', 'draft' ), true ) ? $_POST['status'] : 'draft',
	);

	if ( '' === $tag ) {
		$notice      = 'Tag is required.';
		$notice_type = 'error';
		$action      = $edit_id ? 'edit' : 'add';
	} elseif ( $model->tag_exists( $tag, $edit_id ) ) {
		$notice      = 'That tag is already used by another shortcut - pick a different one.';
		$notice_type = 'error';
		$action      = $edit_id ? 'edit' : 'add';
	} else {
		if ( $edit_id ) {
			$model->update( $edit_id, $data );
		} else {
			$edit_id = (int) $model->create( $data );
		}
		$notice = 'Shortcut saved.';
		$action = 'list';
	}

	if ( 'error' === $notice_type ) {
		// Re-render the form with what was typed, not the stale DB row.
		$form_item = (object) array_merge( array( 'id' => $edit_id ), $data );
	}
}

if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_shortcut' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Shortcut deleted.';
}
?>
<div class="wrap ah-wrap">
  <?php AdminComponents::pageHeader( 'shortcode', 'Shortcuts', 'Define reusable HTML snippets with {{variable}} placeholders, usable as [ah_sc_<tag>] shortcodes anywhere in post/page content.' ); ?>
  <?php if ( $notice ) : ?><?php AdminComponents::notice( $notice, $notice_type ); ?><?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $status = sanitize_key( $_GET['status'] ?? '' );
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged, $search, $status );
    $items  = $result['items']; $meta = $result['meta'];
  ?>
    <?php AdminComponents::card( '📘 Example: how a shortcut works', '
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div>
          <p class="description" style="margin-top:0;"><strong>1. You save this</strong> (Label: <code>Promo Box</code>, Tag: <code>promo_box</code>)</p>
          <p class="description">HTML Template:</p>
          <pre style="background:var(--ah-bg-light);padding:10px 12px;border-radius:6px;overflow:auto;">&lt;div class="promo-box"&gt;
  {{title}}: {{price}}
&lt;/div&gt;</pre>
          <p class="description">Custom CSS:</p>
          <pre style="background:var(--ah-bg-light);padding:10px 12px;border-radius:6px;overflow:auto;">.promo-box {
  padding: 12px;
  background: #fef3c7;
  border-radius: 8px;
  font-weight: 600;
}</pre>
        </div>
        <div>
          <p class="description" style="margin-top:0;"><strong>2. Anywhere in a post/page, you write</strong></p>
          <pre style="background:var(--ah-bg-light);padding:10px 12px;border-radius:6px;overflow:auto;">[ah_sc_promo_box title="Summer Sale" price="$49"]</pre>
          <p class="description"><strong>3. It renders as</strong></p>
          <pre style="background:var(--ah-bg-light);padding:10px 12px;border-radius:6px;overflow:auto;">&lt;div class="promo-box"&gt;
  Summer Sale: $49
&lt;/div&gt;</pre>
          <p class="description">Every <code>{{name}}</code> in the template is replaced by the matching <code>name="..."</code> attribute from the shortcode - unmatched ones just come out blank. The tag is always registered as <code>ah_sc_&lt;your tag&gt;</code>, and the Custom CSS only loads on pages that actually use it.</p>
        </div>
      </div>
    ' ); ?>

    <?php AdminComponents::filterBar( array(
      'page_slug'          => 'ah-shortcuts',
      'search_placeholder' => 'Search by tag or label…',
      'search_value'       => $search,
      'filters'            => array(
        array(
          'name'     => 'status',
          'options'  => array( '' => 'All Status', 'active' => 'Active', 'draft' => 'Draft' ),
          'selected' => $status,
        ),
      ),
      'add_url'   => add_query_arg( array( 'page' => 'ah-shortcuts', 'action' => 'add' ), admin_url( 'admin.php' ) ),
      'add_label' => '+ Add Shortcut',
    ) ); ?>

    <?php
    $rows = array();
    foreach ( $items as $item ) {
      $row = new \stdClass();
      $row->id         = $item->id;
      $row->label      = $item->label;
      $row->usage      = '[ah_sc_' . $item->tag . ']';
      $row->status     = $item->status;
      $row->edit_url   = add_query_arg( array( 'page' => 'ah-shortcuts', 'action' => 'edit', 'id' => $item->id ), admin_url( 'admin.php' ) );
      $row->delete_url = wp_nonce_url( add_query_arg( array( 'page' => 'ah-shortcuts', 'delete_id' => $item->id ), admin_url( 'admin.php' ) ), 'ah_del_shortcut' );
      $rows[] = $row;
    }
    AdminComponents::dataTable( array(
      'columns' => array(
        array( 'label' => 'Label', 'render' => function ( $r ) { return esc_html( $r->label ); } ),
        array( 'label' => 'Usage', 'render' => function ( $r ) { return '<code>' . esc_html( $r->usage ) . '</code>'; } ),
        array( 'label' => 'Status', 'render' => function ( $r ) {
          return '<span class="ah-badge ah-badge-' . esc_attr( $r->status ) . '">' . esc_html( $r->status ) . '</span>';
        } ),
      ),
      'items'         => $rows,
      'empty_message' => 'No shortcuts yet.',
      'actions'       => function ( $r ) {
        $html  = '<a href="' . esc_url( $r->edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>';
        $html .= '<a href="' . esc_url( $r->delete_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete &quot;' . esc_attr( $r->label ) . '&quot;" data-confirm="This shortcut will be permanently removed. Any [ah_sc_...] uses of it in content will stop working.">Delete</a>';
        return $html;
      },
    ) ); ?>
    <?php echo AH_Pagination::render( $meta ); ?>

  <?php else :
    $item = $form_item ?: ( $edit_id ? $model->find( $edit_id ) : null );
  ?>
    <?php AdminComponents::backLink( admin_url( 'admin.php?page=ah-shortcuts' ) ); ?>

    <?php if ( ! empty( $item->tag ) ) :
      $usage = '[ah_sc_' . $item->tag . ' your_var="value"]';
    ?>
      <div style="display:flex;align-items:center;gap:10px;background:#fffbe6;border:1px solid #f0d080;border-radius:6px;padding:10px 14px;margin-bottom:16px;">
        <strong style="flex-shrink:0;">Paste this into a post/page:</strong>
        <input type="text" readonly value="<?php echo esc_attr( $usage ); ?>" id="ah-sc-usage-string"
               style="flex:1;font-family:monospace;background:#fff;border:1px solid var(--ah-border);border-radius:4px;padding:5px 8px;"
               onclick="this.select();">
        <button type="button" class="ah-btn ah-btn-secondary ah-btn-sm" onclick="
          var f=document.getElementById('ah-sc-usage-string'); f.select(); f.setSelectionRange(0,99999);
          navigator.clipboard.writeText(f.value).then(function(){
            var b=event.target; var old=b.textContent; b.textContent='Copied!';
            setTimeout(function(){ b.textContent = old; }, 1500);
          });
        ">📋 Copy</button>
      </div>
      <p class="description">The tag is always registered as <code>ah_sc_&lt;your tag&gt;</code> - not just <code>&lt;your tag&gt;</code> on its own. Use <code>{{your_var}}</code> in the HTML below to receive <code>your_var="..."</code> from the shortcode.</p>
    <?php endif; ?>

    <?php ob_start(); ?>
      <form method="post">
        <?php wp_nonce_field( 'ah_save_shortcut', 'ah_shortcuts_nonce' ); ?>

        <?php AdminComponents::formGrid( array(
          array( 'Label *', '<input type="text" name="label" value="' . esc_attr( $item->label ?? '' ) . '" required placeholder="e.g. Promo Box">' ),
          array( 'Tag *', '<input type="text" name="tag" pattern="[a-z0-9_]*" value="' . esc_attr( $item->tag ?? '' ) . '" required placeholder="e.g. promo_box">' ),
        ) ); ?>

        <?php AdminComponents::formRow( 'HTML Template *',
          '<textarea name="html" rows="10" class="large-text code" placeholder="&lt;div class=&quot;promo&quot;&gt;{{title}}: {{price}}&lt;/div&gt;">' . esc_textarea( $item->html ?? '' ) . '</textarea>',
          '<p class="description">Write <code>{{variable_name}}</code> anywhere you want a shortcode attribute substituted. Values are always HTML-escaped. If the shortcode is used in enclosing form, <code>{{content}}</code> holds the enclosed content.</p>'
        ); ?>

        <?php AdminComponents::formRow( 'Custom CSS',
          '<textarea name="css" rows="6" class="large-text code" placeholder=".promo { ... }">' . esc_textarea( $item->css ?? '' ) . '</textarea>',
          '<p class="description">Only printed on pages where this shortcut is actually used.</p>'
        ); ?>

        <?php
        $status_select = '<select name="status"><option value="draft"' . selected( $item->status ?? 'draft', 'draft', false ) . '>Draft (not live)</option><option value="active"' . selected( $item->status ?? '', 'active', false ) . '>Active</option></select>';
        AdminComponents::formRow( 'Status', $status_select );
        ?>

        <button type="submit" class="ah-btn ah-btn-primary">Save Shortcut</button>
      </form>
    <?php AdminComponents::card( $item ? 'Edit Shortcut' : 'Add Shortcut', ob_get_clean() ); ?>
  <?php endif; ?>
</div>
