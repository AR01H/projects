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
		'notify_on_booking'    => (int) ( $_POST['notify_on_booking'] ?? 0 ),
		'booking_trigger_name' => sanitize_text_field( $_POST['booking_trigger_name'] ?? '' ),
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
  <?php \Ah\Cms\Admin\Components\AdminComponents::pageHeader( 'calendar-alt', 'Events &amp; Hire Packages', 'Manage upcoming events, open days, and hire package listings.' ); ?>
  <?php if ( $notice ) : ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::notice( $notice, $n_type ); ?>
  <?php endif; ?>

  <?php if ( $action === 'list' ) :
    $search = sanitize_text_field( $_GET['s'] ?? '' );
    $status = sanitize_key( $_GET['status'] ?? '' );
    $paged  = AH_Pagination::current_page();
    $result = $model->get_paginated( $paged, $search, $status );
    $items  = $result['items'];
    $meta   = $result['meta'];
  ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::filterBar( array(
      'page_slug'          => 'ah-events',
      'search_placeholder' => 'Search events…',
      'search_value'       => $search,
      'filters'            => array(
        array(
          'name'     => 'status',
          'options'  => array(
            ''        => 'All Status',
            'active'  => 'Active',
            'inactive' => 'Inactive',
          ),
          'selected' => $status,
        ),
      ),
      'add_url'   => add_query_arg( array( 'page' => 'ah-events', 'action' => 'add' ), admin_url( 'admin.php' ) ),
      'add_label' => '+ Add Event',
    ) ); ?>

    <?php \Ah\Cms\Admin\Components\AdminComponents::dataTable( array(
      'columns' => array(
        array( 'label' => '', 'render' => function ( $ev ) {
          return '<span style="font-size:2rem;line-height:1;">' . esc_html( $ev->icon ) . '</span>';
        } ),
        array( 'label' => 'Title', 'render' => function ( $ev ) {
          $html = '<strong>' . esc_html( $ev->title ) . '</strong>';
          if ( $ev->description ) {
            $html .= '<br><small style="color:var(--ah-muted);">' . esc_html( wp_trim_words( $ev->description, 10 ) ) . '</small>';
          }
          return $html;
        } ),
        array( 'label' => 'Color', 'render' => function ( $ev ) use ( $colors ) {
          return '<span class="ah-badge" style="background:' . esc_attr( self_color_bg( $ev->color ?? 'green' ) ) . ';color:#fff;">'
               . esc_html( $colors[ $ev->color ] ?? $ev->color ) . '</span>';
        } ),
        array( 'label' => 'Items', 'render' => function ( $ev ) {
          $ev_items = $ev->items ? json_decode( $ev->items, true ) : array();
          if ( ! empty( $ev_items ) ) {
            return '<small style="color:var(--ah-muted);">' . count( $ev_items ) . ' bullet' . ( count( $ev_items ) !== 1 ? 's' : '' ) . '</small>';
          }
          return '<small style="color:var(--ah-muted);">-</small>';
        } ),
        array( 'label' => 'Featured', 'render' => function ( $ev ) {
          return $ev->is_featured ? '<span class="ah-badge ah-badge-active">Yes</span>' : '-';
        } ),
        array( 'label' => 'Status', 'render' => function ( $ev ) {
          return \Ah\Cms\Admin\Components\AdminComponents::statusBadge( $ev->status );
        } ),
      ),
      'items'    => $items,
      'sortable' => true,
      'model'    => 'events',
      'actions'  => function ( $ev ) {
        $edit_url = add_query_arg( array( 'page' => 'ah-events', 'action' => 'edit', 'id' => $ev->id ), admin_url( 'admin.php' ) );
        $del_url  = wp_nonce_url( add_query_arg( array( 'page' => 'ah-events', 'delete_id' => $ev->id ), admin_url( 'admin.php' ) ), 'ah_del_event' );
        return '<a href="' . esc_url( $edit_url ) . '" class="ah-btn ah-btn-secondary ah-btn-sm">Edit</a>'
             . '<a href="' . esc_url( $del_url ) . '" class="ah-btn ah-btn-danger ah-btn-sm ah-confirm-delete" data-title="Delete Event" data-confirm="This event will be permanently removed.">Delete</a>';
      },
    ) ); ?>

    <?php \Ah\Cms\Admin\Components\AdminComponents::pagination( $meta ); ?>

    <?php ob_start(); ?>
      <p style="color:var(--ah-muted);font-size:13px;margin:0 0 8px;">
        Events created here appear on the <strong>homepage preview</strong> and the <strong>Events &amp; Hire page</strong> of the Cane House website.
        Each package can have a different colour theme to make the page visually varied.
        Use <strong>Sort Order</strong> to control the display sequence (lower number = shown first).
        The homepage limits the number shown via the Home Sections settings.
      </p>
      <p style="color:var(--ah-muted);font-size:13px;margin:0;">
        You can also bulk-import events via <a href="<?php echo esc_url( admin_url( 'admin.php?page=ah-import&tab=events' ) ); ?>">Data Import → Events</a>.
      </p>
    <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'How It Works', ob_get_clean() ); ?>

  <?php else :
    $item     = $edit_id ? $model->find( $edit_id ) : null;
    $ev_items = $item && $item->items ? json_decode( $item->items, true ) : array();
    $items_text = implode( "\n", (array) $ev_items );
  ?>
    <?php \Ah\Cms\Admin\Components\AdminComponents::backLink( admin_url( 'admin.php?page=ah-events' ), '← Back to Events' ); ?>

    <form method="post">
      <?php wp_nonce_field( 'ah_save_event', 'ah_events_nonce' ); ?>
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

        <!-- Left: main content -->
        <div>
          <?php ob_start(); ?>
            <div style="display:grid;grid-template-columns:80px 1fr;gap:12px;align-items:end;">
              <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Icon / Emoji', '<input type="text" name="icon" value="' . esc_attr( $item->icon ?? '🎉' ) . '" style="font-size:1.6rem;padding:0.5rem;text-align:center;" maxlength="10">' ); ?>
              <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Event Title <span style="color:var(--ah-danger);">*</span>', '<input type="text" name="title" value="' . esc_attr( $item->title ?? '' ) . '" placeholder="e.g. Wedding Package" required>' ); ?>
            </div>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Description <small style="font-weight:400;color:var(--ah-muted);">(shown as subtitle on the card)</small>', '<textarea name="description" rows="3" placeholder="Brief description of this event type…">' . esc_textarea( $item->description ?? '' ) . '</textarea>' ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Event Details', ob_get_clean() ); ?>

          <?php ob_start(); ?>
            <p style="color:var(--ah-muted);font-size:12px;margin:0 0 10px;">
              Enter one bullet point per line. These appear as the feature list on the event card.
            </p>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Items <small style="font-weight:400;color:var(--ah-muted);">(one per line)</small>', '<textarea name="items_raw" rows="8" placeholder="Live sugarcane pressing&#10;Up to 200 servings&#10;2 flavour options&#10;Setup & takedown included" style="font-family:monospace;">' . esc_textarea( $items_text ) . '</textarea>' ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Bullet Points / Inclusions', ob_get_clean() ); ?>
        </div>

        <!-- Right: settings -->
        <div>
          <?php ob_start(); ?>
            <?php
            $color_opts = array();
            foreach ( $colors as $val => $lbl ) {
              $color_opts[ $val ] = $lbl;
            }
            ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Colour Theme',
              '<select name="color">'
              . implode( '', array_map( function( $val ) use ( $item, $colors ) {
                  return '<option value="' . esc_attr( $val ) . '"' . selected( $item->color ?? 'green', $val, false ) . ' style="background:' . esc_attr( self_color_bg( $val ) ) . ';color:#fff;">' . esc_html( $colors[ $val ] ) . '</option>';
              }, array_keys( $colors ) ) )
              . '</select>'
              . '<p class="description" style="font-size:11px;margin-top:4px;color:var(--ah-muted);">Sets the accent colour on the event card.</p>'
            ); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Show on Homepage',
              '<select name="is_featured"><option value="0"' . selected( (int) ( $item->is_featured ?? 0 ), 0, false ) . '>No</option><option value="1"' . selected( (int) ( $item->is_featured ?? 0 ), 1, false ) . '>Yes - show in homepage preview</option></select>'
            ); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Sort Order', '<input type="number" name="sort_order" value="' . esc_attr( $item->sort_order ?? 0 ) . '" min="0">' ); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Status',
              '<select name="status"><option value="active"' . selected( $item->status ?? 'active', 'active', false ) . '>Active</option><option value="inactive"' . selected( $item->status ?? '', 'inactive', false ) . '>Inactive</option></select>'
            ); ?>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Display Settings', ob_get_clean() ); ?>

          <?php ob_start(); ?>
            <p style="color:#1e40af;font-size:12px;margin:0 0 12px;">
              When someone submits a booking for this event, automatically fire a trigger to send email notifications via the Rules Engine.
            </p>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( '',
              '<label style="display:flex;align-items:center;gap:8px;font-weight:600;margin-bottom:0;"><input type="checkbox" name="notify_on_booking" value="1" ' . checked( (int) ( $item->notify_on_booking ?? 0 ), 1, false ) . '><span>Enable email notifications for bookings</span></label>'
              . '<p class="description" style="font-size:11px;margin-top:4px;color:#1e40af;">When enabled, booking submissions will trigger the Rules Engine to send configured emails.</p>'
            ); ?>
            <?php \Ah\Cms\Admin\Components\AdminComponents::formRow( 'Trigger Event Name <small style="font-weight:400;color:#666;">(shown in Rules Engine)</small>',
              '<input type="text" name="booking_trigger_name" value="' . esc_attr( $item->booking_trigger_name ?? '' ) . '" placeholder="e.g. booking_wedding, booking_corporate" style="font-family:monospace;font-size:12px;">'
              . '<p class="description" style="font-size:11px;margin-top:4px;color:#666;">In the <a href="' . esc_url( admin_url( 'admin.php?page=ah-workflow-manager' ) ) . '" target="_blank" style="color:#1e40af;">Workflow Manager</a>, create a rule with this trigger name to send emails. If left blank, will use: <code style="background:#fff;border:1px solid #ddd;padding:2px 5px;border-radius:3px;">booking_event_' . esc_html( $edit_id ?: '{id}' ) . '</code></p>'
            ); ?>
            <button type="submit" class="ah-btn ah-btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
              <span class="dashicons dashicons-saved"></span>
              <?php echo $item ? 'Update Event' : 'Save Event'; ?>
            </button>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Email Notifications', ob_get_clean() ); ?>

          <?php ob_start(); ?>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <?php foreach ( $colors as $val => $lbl ) : ?>
                <div style="width:60px;height:60px;border-radius:12px;background:<?php echo esc_attr( self_color_bg( $val ) ); ?>;display:flex;align-items:center;justify-content:center;font-size:10px;color:#fff;font-weight:700;text-align:center;line-height:1.2;"><?php echo esc_html( explode( ' ', $lbl )[0] ); ?></div>
              <?php endforeach; ?>
            </div>
          <?php \Ah\Cms\Admin\Components\AdminComponents::card( 'Colour Preview', ob_get_clean() ); ?>
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
