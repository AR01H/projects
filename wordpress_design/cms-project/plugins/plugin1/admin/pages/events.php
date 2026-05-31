<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Access denied.' );

AH_DB_Installer::ensure_events_table();

$model   = new AH_Events_Model();
$notice  = '';
$n_type  = 'success';
$action  = sanitize_key( $_GET['action'] ?? 'list' );
$edit_id = (int) ( $_GET['id'] ?? 0 );

$colors = array(
	'green'  => 'Green  (default)',
	'amber'  => 'Amber / Gold',
	'teal'   => 'Teal / Blue',
	'purple' => 'Purple / Violet',
	'coral'  => 'Coral / Red',
	'indigo' => 'Indigo / Navy',
);

// ---- Save ----
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( ! wp_verify_nonce( $_POST['ah_events_nonce'] ?? '', 'ah_save_event' ) ) {
		wp_die( 'Security check failed.' );
	}

	$raw_items = sanitize_textarea_field( $_POST['items_raw'] ?? '' );
	$data = array(
		'icon'        => sanitize_text_field( $_POST['icon']        ?? '🎉' ),
		'title'       => sanitize_text_field( $_POST['title']       ?? '' ),
		'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
		'items'       => AH_Events_Model::normalise_items( $raw_items ),
		'color'       => sanitize_key( $_POST['color'] ?? 'green' ),
		'is_featured' => (int) ( $_POST['is_featured'] ?? 0 ),
		'sort_order'  => (int) ( $_POST['sort_order']  ?? 0 ),
		'status'      => sanitize_key( $_POST['status'] ?? 'active' ),
	);

	if ( ! $data['title'] ) {
		$notice = 'Event title is required.';
		$n_type = 'warning';
	} else {
		if ( $edit_id ) {
			$model->update( $edit_id, $data );
			$notice = 'Event updated.';
		} else {
			$model->create( $data );
			$notice = 'Event added.';
		}
		$action  = 'list';
		$edit_id = 0;
	}
}

// ---- Delete ----
if ( isset( $_GET['delete_id'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'ah_del_event' ) ) {
	$model->delete( (int) $_GET['delete_id'] );
	$notice = 'Event deleted.';
}
?>
<div class="wrap ah-wrap">
  <h1><span class="dashicons dashicons-calendar-alt"></span> Events &amp; Hire Packages</h1>
  <?php if ( $notice ) : ?>
    <div class="ah-notice ah-notice-<?php echo esc_attr( $n_type ); ?>"><?php echo esc_html( $notice ); ?></div>
  <?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $status = sanitize_key( $_GET['status'] ?? '' );
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged, $search, $status );
    $items  = $result['items'];
    $meta   = $result['meta'];
  ?>
    <div class="ah-table-top">
      <form class="ah-search-form" method="get">
        <input type="hidden" name="page" value="ah-events">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search events…">
        <select name="status">
          <option value="">All Status</option>
          <option value="active"   <?php selected( $status, 'active' ); ?>>Active</option>
          <option value="inactive" <?php selected( $status, 'inactive' ); ?>>Inactive</option>
        </select>
        <button class="ah-btn ah-btn-secondary">Filter</button>
      </form>
      <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-events', 'action' => 'add' ), admin_url( 'admin.php' ) ) ); ?>"
         class="ah-btn ah-btn-primary">+ Add Event</a>
    </div>

    <div class="ah-table-wrap">
      <table class="ah-table ah-sortable-list" data-model="events">
        <thead>
          <tr>
            <th></th>
            <th>Icon</th>
            <th>Title</th>
            <th>Color</th>
            <th>Items</th>
            <th>Featured</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ( empty( $items ) ) : ?>
            <tr>
              <td colspan="8" style="text-align:center;color:var(--ah-muted);padding:32px;">
                No events yet. Click "+ Add Event" to create one.
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach ( $items as $ev ) :
            $ev_items = $ev->items ? json_decode( $ev->items, true ) : array();
          ?>
            <tr data-id="<?php echo esc_attr( $ev->id ); ?>">
              <td class="ah-sort-handle">&#9776;</td>
              <td style="font-size:2rem;line-height:1;"><?php echo esc_html( $ev->icon ); ?></td>
              <td>
                <strong><?php echo esc_html( $ev->title ); ?></strong>
                <?php if ( $ev->description ) : ?>
                  <br><small style="color:var(--ah-muted);"><?php echo esc_html( wp_trim_words( $ev->description, 10 ) ); ?></small>
                <?php endif; ?>
              </td>
              <td>
                <span class="ah-badge" style="background:<?php echo esc_attr( self_color_bg( $ev->color ?? 'green' ) ); ?>;color:#fff;">
                  <?php echo esc_html( $colors[ $ev->color ] ?? $ev->color ); ?>
                </span>
              </td>
              <td>
                <?php if ( ! empty( $ev_items ) ) : ?>
                  <small style="color:var(--ah-muted);"><?php echo count( $ev_items ); ?> bullet<?php echo count( $ev_items ) !== 1 ? 's' : ''; ?></small>
                <?php else : ?>
                  <small style="color:var(--ah-muted);">—</small>
                <?php endif; ?>
              </td>
              <td>
                <?php echo $ev->is_featured ? '<span class="ah-badge ah-badge-active">Yes</span>' : '—'; ?>
              </td>
              <td>
                <span class="ah-badge ah-badge-<?php echo esc_attr( $ev->status ); ?>">
                  <?php echo esc_html( $ev->status ); ?>
                </span>
              </td>
              <td class="row-actions">
                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ah-events', 'action' => 'edit', 'id' => $ev->id ), admin_url( 'admin.php' ) ) ); ?>"
                   class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>
                <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'ah-events', 'delete_id' => $ev->id ), admin_url( 'admin.php' ) ), 'ah_del_event' ) ); ?>"
                   class="ah-btn ah-btn-danger ah-btn-sm"
                   onclick="return confirm('Delete this event package?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php echo AH_Pagination::render( $meta ); ?>

    <div class="ah-card" style="margin-top:24px;max-width:700px;">
      <div class="ah-card-header"><h2>How It Works</h2></div>
      <p style="color:var(--ah-muted);font-size:13px;margin:0 0 8px;">
        Events created here appear on the <strong>homepage preview</strong> and the <strong>Events &amp; Hire page</strong> of the Cane House website.
        Each package can have a different colour theme to make the page visually varied.
        Use <strong>Sort Order</strong> to control the display sequence (lower number = shown first).
        The homepage limits the number shown via the Home Sections settings.
      </p>
      <p style="color:var(--ah-muted);font-size:13px;margin:0;">
        You can also bulk-import events via <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-import&tab=events' ) ); ?>">Data Import → Events</a>.
      </p>
    </div>

  <?php else :
    $item     = $edit_id ? $model->find( $edit_id ) : null;
    $ev_items = $item && $item->items ? json_decode( $item->items, true ) : array();
    $items_text = implode( "\n", (array) $ev_items );
  ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-events' ) ); ?>"
       class="ah-btn ah-btn-secondary ah-btn-sm" style="margin-bottom:16px;display:inline-flex;">&larr; Back to Events</a>

    <form method="post">
      <?php wp_nonce_field( 'ah_save_event', 'ah_events_nonce' ); ?>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

        <!-- Left: main content -->
        <div>
          <div class="ah-card">
            <div class="ah-card-header"><h2>Event Details</h2></div>
            <div style="display:grid;grid-template-columns:80px 1fr;gap:12px;align-items:end;">
              <div class="ah-form-row">
                <label>Icon / Emoji</label>
                <input type="text" name="icon" value="<?php echo esc_attr( $item->icon ?? '🎉' ); ?>"
                       style="font-size:1.6rem;padding:0.5rem;text-align:center;" maxlength="10">
              </div>
              <div class="ah-form-row">
                <label>Event Title <span style="color:var(--ah-danger);">*</span></label>
                <input type="text" name="title" value="<?php echo esc_attr( $item->title ?? '' ); ?>"
                       placeholder="e.g. Wedding Package" required>
              </div>
            </div>
            <div class="ah-form-row">
              <label>Description <small style="font-weight:400;color:var(--ah-muted);">(shown as subtitle on the card)</small></label>
              <textarea name="description" rows="3" placeholder="Brief description of this event type…"><?php echo esc_textarea( $item->description ?? '' ); ?></textarea>
            </div>
          </div>

          <div class="ah-card">
            <div class="ah-card-header"><h2>Bullet Points / Inclusions</h2></div>
            <p style="color:var(--ah-muted);font-size:12px;margin:0 0 10px;">
              Enter one bullet point per line. These appear as the feature list on the event card.
            </p>
            <div class="ah-form-row">
              <label>Items <small style="font-weight:400;color:var(--ah-muted);">(one per line)</small></label>
              <textarea name="items_raw" rows="8"
                        placeholder="Live sugarcane pressing&#10;Up to 200 servings&#10;2 flavour options&#10;Setup & takedown included"
                        style="font-family:monospace;"><?php echo esc_textarea( $items_text ); ?></textarea>
            </div>
          </div>
        </div>

        <!-- Right: settings -->
        <div>
          <div class="ah-card">
            <div class="ah-card-header"><h2>Display Settings</h2></div>
            <div class="ah-form-row">
              <label>Colour Theme</label>
              <select name="color">
                <?php foreach ( $colors as $val => $lbl ) : ?>
                  <option value="<?php echo esc_attr( $val ); ?>"
                          <?php selected( $item->color ?? 'green', $val ); ?>
                          style="background:<?php echo esc_attr( self_color_bg( $val ) ); ?>;color:#fff;">
                    <?php echo esc_html( $lbl ); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <p class="description" style="font-size:11px;margin-top:4px;color:var(--ah-muted);">
                Sets the accent colour on the event card.
              </p>
            </div>
            <div class="ah-form-row">
              <label>Show on Homepage</label>
              <select name="is_featured">
                <option value="0" <?php selected( (int) ( $item->is_featured ?? 0 ), 0 ); ?>>No</option>
                <option value="1" <?php selected( (int) ( $item->is_featured ?? 0 ), 1 ); ?>>Yes — show in homepage preview</option>
              </select>
            </div>
            <div class="ah-form-row">
              <label>Sort Order</label>
              <input type="number" name="sort_order" value="<?php echo esc_attr( $item->sort_order ?? 0 ); ?>" min="0">
            </div>
            <div class="ah-form-row">
              <label>Status</label>
              <select name="status">
                <option value="active"   <?php selected( $item->status ?? 'active', 'active' ); ?>>Active</option>
                <option value="inactive" <?php selected( $item->status ?? '',        'inactive' ); ?>>Inactive</option>
              </select>
            </div>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
              <span class="dashicons dashicons-saved"></span>
              <?php echo $item ? 'Update Event' : 'Save Event'; ?>
            </button>
          </div>

          <div class="ah-card" style="background:var(--ah-bg-light);">
            <div class="ah-card-header"><h2>Colour Preview</h2></div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <?php foreach ( $colors as $val => $lbl ) : ?>
                <div style="
                  width:60px;height:60px;border-radius:12px;
                  background:<?php echo esc_attr( self_color_bg( $val ) ); ?>;
                  display:flex;align-items:center;justify-content:center;
                  font-size:10px;color:#fff;font-weight:700;text-align:center;
                  line-height:1.2;"><?php echo esc_html( explode( ' ', $lbl )[0] ); ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

      </div>
    </form>

  <?php endif; ?>
</div>

<?php
/**
 * Map color slug → a hex background for the admin swatch.
 */
function self_color_bg( string $color ): string {
	$map = array(
		'green'  => '#4a8c2a',
		'amber'  => '#d97706',
		'teal'   => '#0891b2',
		'purple' => '#7c3aed',
		'coral'  => '#e11d48',
		'indigo' => '#3730a3',
	);
	return $map[ $color ] ?? '#4a8c2a';
}
