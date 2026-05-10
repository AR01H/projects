<?php
/**
 * CANEHOUSE — Content Manager Admin
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS EXISTS:
 *   One central place in WordPress admin to manage ALL website content.
 *   Each content type (Reviews, FAQs, Events etc.) gets its OWN TAB.
 *   You can: Add, Edit, Delete, Activate/Deactivate, Reorder any item.
 *   Every item has an image field and status toggle.
 *   Only ACTIVE items show on the website. Inactive = hidden but not deleted.
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── REGISTER ADMIN PAGE ───────────────────────────────────────────────────────
add_action('admin_menu', function () {
  add_menu_page(
    '🌿 Cane House Content',
    '🌿 Cane House',
    'manage_options',
    'ch-content',
    'ch_content_manager_page',
    'dashicons-palmtree',
    25
  );
  add_submenu_page('ch-content', '📋 Content Manager', '📋 Content Manager', 'manage_options', 'ch-content', 'ch_content_manager_page');
  add_submenu_page('ch-content', '⚙️ Site Settings', '⚙️ Site Settings', 'manage_options', 'ch-site-settings', 'ch_site_settings_page');
  add_submenu_page('ch-content', '📩 Contact Leads', '📩 Contact Leads', 'manage_options', 'ch-leads', 'canehouse_leads_page');
});

// ── ENQUEUE ADMIN ASSETS ──────────────────────────────────────────────────────
add_action('admin_enqueue_scripts', function ($hook) {
  if (strpos($hook, 'ch-content') === false && strpos($hook, 'ch-leads') === false && strpos($hook, 'ch-site-settings') === false)
    return;
  wp_enqueue_media();
  wp_enqueue_style('ch-admin', get_template_directory_uri() . '/assets/css/ch-admin.css', array(), '2.0');
  wp_enqueue_script('ch-admin', get_template_directory_uri() . '/assets/js/ch-admin.js', array('jquery'), '2.0', true);
  wp_localize_script('ch-admin', 'CH', array(
    'ajax' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('ch_admin_2'),
  ));
});

// ── TAB DEFINITIONS ───────────────────────────────────────────────────────────
function ch_tabs()
{
  return array(
    'reviews' => array('label' => '⭐ Reviews', 'table' => 'ch_reviews', 'singular' => 'Review'),
    'order_steps' => array('label' => '📋 How To Order', 'table' => 'ch_order_steps', 'singular' => 'Step'),
    'flavours' => array('label' => '🥤 Juice Flavours', 'table' => 'ch_flavours', 'singular' => 'Flavour'),
    'events' => array('label' => '🎪 Events & Hire', 'table' => 'ch_events', 'singular' => 'Event'),
    'faqs' => array('label' => '❓ FAQs', 'table' => 'ch_faqs', 'singular' => 'FAQ'),
    'franchise_locs' => array('label' => '📍 Locations', 'table' => 'ch_franchise_locs', 'singular' => 'Location'),
    'benefits' => array('label' => '💚 Benefits', 'table' => 'ch_benefits', 'singular' => 'Benefit'),
    'showcase_slides' => array('label' => '🖼️ Slides', 'table' => 'ch_showcase_slides', 'singular' => 'Slide'),
  );
}

// ── FIELD DEFINITIONS PER TAB ─────────────────────────────────────────────────
function ch_fields($tab)
{
  // Every tab gets: id, image_url, status, sort_order, created_at, updated_at (handled automatically)
  // We define only the CONTENT fields here
  $defs = array(
    'reviews' => array(
      array('name' => 'name', 'label' => 'Customer Name', 'type' => 'text', 'required' => true),
      array('name' => 'role', 'label' => 'Role / Title', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. Verified Customer, Event Client'),
      array('name' => 'review_text', 'label' => 'Review Text', 'type' => 'textarea', 'required' => true),
      array('name' => 'rating', 'label' => 'Star Rating', 'type' => 'select', 'options' => array('5' => '⭐⭐⭐⭐⭐ (5)', '4' => '⭐⭐⭐⭐ (4)', '3' => '⭐⭐⭐ (3)', '2' => '⭐⭐ (2)', '1' => '⭐ (1)')),
      array('name' => 'image_url', 'label' => 'Customer Photo', 'type' => 'image'),
      array('name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number', 'placeholder' => '0 = first'),
      array('name' => 'status', 'label' => 'Status', 'type' => 'status'),
    ),
    'order_steps' => array(
      array('name' => 'step_number', 'label' => 'Step Number', 'type' => 'number', 'required' => true),
      array('name' => 'emoji', 'label' => 'Emoji / Icon', 'type' => 'text', 'placeholder' => 'e.g. 📏'),
      array('name' => 'title', 'label' => 'Step Title', 'type' => 'text', 'required' => true),
      array('name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true),
      array('name' => 'image_url', 'label' => 'Step Image', 'type' => 'image'),
      array('name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number'),
      array('name' => 'status', 'label' => 'Status', 'type' => 'status'),
    ),
    'flavours' => array(
      array('name' => 'emoji', 'label' => 'Emoji', 'type' => 'text', 'placeholder' => '🍋'),
      array('name' => 'name', 'label' => 'Flavour Name', 'type' => 'text', 'required' => true),
      array('name' => 'description', 'label' => 'Description', 'type' => 'textarea'),
      array('name' => 'price', 'label' => 'Price', 'type' => 'text', 'placeholder' => 'e.g. +£0.50 or Included'),
      array('name' => 'flavour_type', 'label' => 'Type', 'type' => 'select', 'options' => array('Base' => 'Base', 'Citrus' => 'Citrus', 'Tropical' => 'Tropical', 'Other' => 'Other')),
      array('name' => 'image_url', 'label' => 'Flavour Image', 'type' => 'image'),
      array('name' => 'status', 'label' => 'Status', 'type' => 'status'),
      array('name' => 'sort_order', 'label' => 'Sort', 'type' => 'number'),
    ),
    'events' => array(
      array('name' => 'icon', 'label' => 'Icon / Emoji', 'type' => 'text', 'placeholder' => '💒'),
      array('name' => 'title', 'label' => 'Event Title', 'type' => 'text', 'required' => true),
      array('name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true),
      array('name' => 'list_items', 'label' => 'List Items', 'type' => 'textarea', 'placeholder' => "One item per line\nItem 2\nItem 3"),
      array('name' => 'image_url', 'label' => 'Event Image', 'type' => 'image'),
      array('name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number'),
      array('name' => 'status', 'label' => 'Status', 'type' => 'status'),
    ),
    'faqs' => array(
      array('name' => 'question', 'label' => 'Question', 'type' => 'textarea', 'required' => true),
      array('name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'required' => true),
      array('name' => 'category', 'label' => 'Category', 'type' => 'select', 'options' => array('General' => 'General', 'Product' => 'Product', 'Hire' => 'Hire', 'Franchise' => 'Franchise', 'Other' => 'Other')),
      array('name' => 'image_url', 'label' => 'Image (optional)', 'type' => 'image'),
      array('name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number'),
      array('name' => 'status', 'label' => 'Status', 'type' => 'status'),
    ),
    'franchise_locs' => array(
      array('name' => 'name', 'label' => 'Location Name', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. London Central'),
      array('name' => 'city', 'label' => 'City', 'type' => 'text', 'placeholder' => 'e.g. London'),
      array('name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'placeholder' => 'Optional details about this location'),
      array('name' => 'image_url', 'label' => 'Location Image', 'type' => 'image'),
      array('name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number'),
      array('name' => 'status', 'label' => 'Status', 'type' => 'status'),
    ),
    'benefits' => array(
      array('name' => 'icon', 'label' => 'Icon / Emoji', 'type' => 'text', 'placeholder' => '⚡'),
      array('name' => 'title', 'label' => 'Benefit Title', 'type' => 'text', 'required' => true),
      array('name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => true),
      array('name' => 'image_url', 'label' => 'Benefit Image', 'type' => 'image'),
      array('name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number'),
      array('name' => 'status', 'label' => 'Status', 'type' => 'status'),
    ),
    'showcase_slides' => array(
      array('name' => 'title', 'label' => 'Slide Title', 'type' => 'text', 'required' => true),
      array('name' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'),
      array('name' => 'image_url', 'label' => 'Slide Image', 'type' => 'image', 'required' => true),
      array('name' => 'sort_order', 'label' => 'Sort Order', 'type' => 'number'),
      array('name' => 'status', 'label' => 'Status', 'type' => 'status'),
    ),
  );
  return $defs[$tab] ?? array();
}

// ── AJAX: SAVE (add/edit) ─────────────────────────────────────────────────────
add_action('wp_ajax_ch_save_item', function () {
  if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'] ?? '', 'ch_admin_2')) {
    wp_send_json_error('Unauthorised');
  }
  global $wpdb;
  $tab = sanitize_key($_POST['tab'] ?? '');
  $id = intval($_POST['id'] ?? 0);
  $tabs = ch_tabs();
  if (!isset($tabs[$tab]))
    wp_send_json_error('Unknown tab');

  $table = $wpdb->prefix . $tabs[$tab]['table'];
  $fields = ch_fields($tab);
  $data = array();

  foreach ($fields as $f) {
    $fname = $f['name'];
    if ($fname === 'status') {
      $data['status'] = in_array($_POST['status'] ?? '', array('active', 'inactive')) ? $_POST['status'] : 'active';
    } elseif ($f['type'] === 'textarea') {
      $data[$fname] = sanitize_textarea_field($_POST[$fname] ?? '');
    } elseif ($f['type'] === 'image') {
      $data[$fname] = esc_url_raw($_POST[$fname] ?? '');
    } elseif ($f['type'] === 'number') {
      $data[$fname] = intval($_POST[$fname] ?? 0);
    } else {
      $data[$fname] = sanitize_text_field($_POST[$fname] ?? '');
    }
  }
  $data['updated_at'] = current_time('mysql');

  if ($id > 0) {
    $wpdb->update($table, $data, array('id' => $id));
    wp_send_json_success(array('action' => 'updated', 'id' => $id));
  } else {
    $data['created_at'] = current_time('mysql');
    $wpdb->insert($table, $data);
    wp_send_json_success(array('action' => 'inserted', 'id' => $wpdb->insert_id));
  }
});

// ── AJAX: DELETE ──────────────────────────────────────────────────────────────
add_action('wp_ajax_ch_delete_item', function () {
  if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'] ?? '', 'ch_admin_2')) {
    wp_send_json_error('Unauthorised');
  }
  global $wpdb;
  $tab = sanitize_key($_POST['tab'] ?? '');
  $id = intval($_POST['id'] ?? 0);
  $tabs = ch_tabs();
  if (!isset($tabs[$tab]) || !$id)
    wp_send_json_error('Invalid');
  $wpdb->delete($wpdb->prefix . $tabs[$tab]['table'], array('id' => $id));
  wp_send_json_success();
});

// ── AJAX: TOGGLE STATUS ───────────────────────────────────────────────────────
add_action('wp_ajax_ch_toggle_status', function () {
  if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'] ?? '', 'ch_admin_2')) {
    wp_send_json_error('Unauthorised');
  }
  global $wpdb;
  $tab = sanitize_key($_POST['tab'] ?? '');
  $id = intval($_POST['id'] ?? 0);
  $tabs = ch_tabs();
  if (!isset($tabs[$tab]) || !$id)
    wp_send_json_error('Invalid');
  $table = $wpdb->prefix . $tabs[$tab]['table'];
  $current = $wpdb->get_var($wpdb->prepare("SELECT status FROM $table WHERE id=%d", $id));
  $new = $current === 'active' ? 'inactive' : 'active';
  $wpdb->update($table, array('status' => $new, 'updated_at' => current_time('mysql')), array('id' => $id));
  wp_send_json_success(array('status' => $new));
});

// ── AJAX: SAVE SORT ORDER ─────────────────────────────────────────────────────
add_action('wp_ajax_ch_save_order', function () {
  if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'] ?? '', 'ch_admin_2')) {
    wp_send_json_error('Unauthorised');
  }
  global $wpdb;
  $tab = sanitize_key($_POST['tab'] ?? '');
  $order = $_POST['order'] ?? array();
  $tabs = ch_tabs();
  if (!isset($tabs[$tab]))
    wp_send_json_error('Invalid');
  $table = $wpdb->prefix . $tabs[$tab]['table'];
  foreach ($order as $pos => $id) {
    $wpdb->update($table, array('sort_order' => intval($pos) + 1), array('id' => intval($id)));
  }
  wp_send_json_success();
});

// ── AJAX: GET SINGLE ITEM (for edit modal) ────────────────────────────────────
add_action('wp_ajax_ch_get_item', function () {
  if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['nonce'] ?? '', 'ch_admin_2')) {
    wp_send_json_error('Unauthorised');
  }
  global $wpdb;
  $tab = sanitize_key($_GET['tab'] ?? '');
  $id = intval($_GET['id'] ?? 0);
  $tabs = ch_tabs();
  if (!isset($tabs[$tab]) || !$id)
    wp_send_json_error('Invalid');
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$tabs[$tab]['table']} WHERE id=%d", $id), ARRAY_A);
  wp_send_json_success($row);
});

// ── MAIN PAGE RENDER ──────────────────────────────────────────────────────────
function ch_content_manager_page()
{
  global $wpdb;
  $tabs = ch_tabs();
  $active_tab = sanitize_key($_GET['tab'] ?? 'reviews');
  if (!isset($tabs[$active_tab]))
    $active_tab = 'reviews';
  $tab_info = $tabs[$active_tab];
  $table = $wpdb->prefix . $tab_info['table'];
  $fields = ch_fields($active_tab);
  $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY sort_order ASC, id ASC");
  $counts = array();
  foreach ($tabs as $key => $t) {
    $counts[$key] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}{$t['table']}");
  }
  ?>
  <div class="wrap ch-wrap">
    <h1 class="ch-title">🌿 Cane House — Content Manager</h1>
    <p class="ch-subtitle">Manage all website content from here. Each tab controls one section of the website. Changes go
      live instantly.</p>

    <!-- TABS -->
    <div class="ch-tabs">
      <?php foreach ($tabs as $key => $t): ?>
        <a href="?page=ch-content&tab=<?php echo $key; ?>"
          class="ch-tab <?php echo $active_tab === $key ? 'active' : ''; ?>">
          <?php echo esc_html($t['label']); ?>
          <span class="ch-tab-count"><?php echo $counts[$key]; ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- TAB CONTENT -->
    <div class="ch-tab-content">

      <!-- HEADER BAR -->
      <div class="ch-content-header">
        <div>
          <h2 class="ch-section-title"><?php echo esc_html($tab_info['label']); ?></h2>
          <p class="ch-section-desc"><?php echo ch_tab_description($active_tab); ?></p>
        </div>
        <button class="ch-btn-primary" id="ch-add-new">
          + Add New <?php echo esc_html($tab_info['singular']); ?>
        </button>
      </div>

      <!-- STATS ROW -->
      <?php
      $active_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status='active'");
      $inactive_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status='inactive'");
      ?>
      <div class="ch-stat-pills">
        <span class="ch-pill total">Total: <?php echo count($rows); ?></span>
        <span class="ch-pill active">✅ Active: <?php echo $active_count; ?></span>
        <span class="ch-pill inactive">⏸ Inactive: <?php echo $inactive_count; ?></span>
        <span class="ch-pill info">ℹ️ Only active items show on website</span>
      </div>

      <!-- ITEMS TABLE -->
      <div class="ch-items-table-wrap">
        <table class="ch-items-table" id="ch-sortable-table">
          <thead>
            <tr>
              <th class="ch-drag-col">⠿</th>
              <th>#</th>
              <?php foreach ($fields as $f):
                if (in_array($f['type'], array('status', 'number')) && $f['name'] === 'sort_order')
                  continue; ?>
                <th><?php echo esc_html($f['label']); ?></th>
              <?php endforeach; ?>
              <th>Status</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="ch-sortable-body">
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="20" class="ch-empty">No items yet. Click "Add New" to get started.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($rows as $row): ?>
                <tr class="ch-item-row <?php echo $row->status === 'inactive' ? 'ch-inactive' : ''; ?>"
                  data-id="<?php echo $row->id; ?>">
                  <td class="ch-drag-handle" title="Drag to reorder">⠿</td>
                  <td><strong><?php echo $row->id; ?></strong></td>
                  <?php foreach ($fields as $f):
                    if ($f['name'] === 'sort_order')
                      continue;
                    if ($f['name'] === 'status')
                      continue;
                    $val = $row->{$f['name']} ?? '';
                    ?>
                    <td class="ch-cell-<?php echo esc_attr($f['name']); ?>">
                      <?php
                      if ($f['type'] === 'image') {
                        if ($val)
                          echo '<img src="' . esc_url($val) . '" class="ch-thumb" alt="">';
                        else
                          echo '<span class="ch-no-img">No image</span>';
                      } elseif ($f['type'] === 'textarea') {
                        echo '<div class="ch-preview">' . esc_html(wp_trim_words($val, 15)) . '</div>';
                      } elseif ($f['name'] === 'emoji' || $f['name'] === 'icon') {
                        echo '<span style="font-size:22px;">' . esc_html($val) . '</span>';
                      } else {
                        echo esc_html($val);
                      }
                      ?>
                    </td>
                  <?php endforeach; ?>
                  <!-- STATUS -->
                  <td>
                    <button class="ch-status-toggle ch-status-<?php echo $row->status; ?>" data-id="<?php echo $row->id; ?>"
                      data-tab="<?php echo $active_tab; ?>" title="Click to toggle">
                      <?php echo $row->status === 'active' ? '✅ Active' : '⏸ Inactive'; ?>
                    </button>
                  </td>
                  <!-- ORDER -->
                  <td class="ch-order-cell"><?php echo $row->sort_order; ?></td>
                  <!-- ACTIONS -->
                  <td class="ch-actions-cell">
                    <button class="ch-edit-btn ch-btn-sm" data-id="<?php echo $row->id; ?>"
                      data-tab="<?php echo $active_tab; ?>">✏️ Edit</button>
                    <button class="ch-delete-btn ch-btn-sm ch-btn-danger" data-id="<?php echo $row->id; ?>"
                      data-tab="<?php echo $active_tab; ?>"
                      data-name="<?php echo esc_attr($row->{array_column($fields, 'name')[0]} ?? 'this item'); ?>">🗑️
                      Delete</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <p class="ch-drag-hint">💡 Drag rows by the ⠿ handle to reorder. Changes save automatically.</p>
    </div>
  </div>

  <!-- ADD / EDIT MODAL -->
  <div id="ch-modal-overlay" class="ch-modal-overlay" style="display:none;">
    <div class="ch-modal">
      <div class="ch-modal-header">
        <h2 id="ch-modal-title">Add New</h2>
        <button class="ch-modal-close" id="ch-modal-close">✕</button>
      </div>
      <div class="ch-modal-body">
        <form id="ch-item-form">
          <input type="hidden" name="id" id="ch-form-id" value="0">
          <input type="hidden" name="tab" id="ch-form-tab" value="<?php echo esc_attr($active_tab); ?>">
          <input type="hidden" name="nonce" id="ch-form-nonce" value="<?php echo wp_create_nonce('ch_admin_2'); ?>">
          <div class="ch-form-fields">
            <?php foreach ($fields as $f): ?>
              <div class="ch-form-group <?php echo $f['type'] === 'image' ? 'ch-full-width' : ''; ?>">
                <label class="ch-form-label">
                  <?php echo esc_html($f['label']); ?>
                  <?php if (!empty($f['required'])): ?><span class="ch-required">*</span><?php endif; ?>
                </label>
                <?php
                $fname = $f['name'];
                $pholder = $f['placeholder'] ?? '';
                if ($f['type'] === 'text' || $f['type'] === 'number') {
                  echo '<input type="' . ($f['type'] === 'number' ? 'number' : 'text') . '" name="' . esc_attr($fname) . '" id="ch-f-' . esc_attr($fname) . '" class="ch-form-input" placeholder="' . esc_attr($pholder) . '"' . (!empty($f['required']) ? ' required' : '') . '>';
                } elseif ($f['type'] === 'textarea') {
                  echo '<textarea name="' . esc_attr($fname) . '" id="ch-f-' . esc_attr($fname) . '" class="ch-form-textarea" placeholder="' . esc_attr($pholder) . '" rows="4"' . (!empty($f['required']) ? ' required' : '') . '></textarea>';
                } elseif ($f['type'] === 'select') {
                  echo '<select name="' . esc_attr($fname) . '" id="ch-f-' . esc_attr($fname) . '" class="ch-form-select">';
                  foreach ($f['options'] as $val => $lbl)
                    echo '<option value="' . esc_attr($val) . '">' . esc_html($lbl) . '</option>';
                  echo '</select>';
                } elseif ($f['type'] === 'image') {
                  echo '<div class="ch-image-wrap">';
                  echo '<div id="ch-img-preview-' . esc_attr($fname) . '" class="ch-img-preview"></div>';
                  echo '<input type="hidden" name="' . esc_attr($fname) . '" id="ch-f-' . esc_attr($fname) . '">';
                  echo '<button type="button" class="ch-btn-upload ch-btn-sm" data-target="ch-f-' . esc_attr($fname) . '" data-preview="ch-img-preview-' . esc_attr($fname) . '">📷 Upload / Choose Image</button>';
                  echo '<button type="button" class="ch-btn-remove-img ch-btn-sm" data-target="ch-f-' . esc_attr($fname) . '" data-preview="ch-img-preview-' . esc_attr($fname) . '">✕ Remove</button>';
                  echo '</div>';
                } elseif ($f['type'] === 'status') {
                  echo '<select name="status" id="ch-f-status" class="ch-form-select">';
                  echo '<option value="active">✅ Active — shows on website</option>';
                  echo '<option value="inactive">⏸ Inactive — hidden from website</option>';
                  echo '</select>';
                }
                ?>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="ch-form-actions">
            <button type="submit" class="ch-btn-primary" id="ch-form-submit">💾 Save</button>
            <button type="button" class="ch-btn-secondary" id="ch-form-cancel">Cancel</button>
            <span class="ch-form-status" id="ch-form-status"></span>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php
}

// ── TAB DESCRIPTIONS ──────────────────────────────────────────────────────────
function ch_tab_description($tab)
{
  $desc = array(
    'reviews' => 'Customer testimonials shown in the Reviews section. Add real customer photos for maximum trust.',
    'order_steps' => 'The 5 steps shown in "How To Order" section. Keep these clear and simple.',
    'flavours' => 'All juice flavours shown in "Build Your Juice" section. Set prices and types here.',
    'events' => 'Event hire cards shown in the Events & Hire section. Each card represents an event type.',
    'faqs' => 'Frequently asked questions. Add as many as needed. Only active ones show on site.',
    'franchise_locs' => 'Franchise locations shown in the scrolling marquee in the Franchise section.',
    'benefits' => 'Health benefits shown in the Benefits section.',
    'showcase_slides' => 'Juice variety slides shown in the franchise showcase slider.',
  );
  return $desc[$tab] ?? '';
}
