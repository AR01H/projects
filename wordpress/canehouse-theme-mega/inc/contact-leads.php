<?php
/**
 * The Cane House — Contact Form System
 * Creates DB tables, handles AJAX submissions,
 * auto-collects IP/location/meta, and renders admin UI.
 */

// ─── 1. CREATE TABLES ON THEME ACTIVATION ────────────────────────────────────
function canehouse_create_tables() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    // Main leads table
    $leads = $wpdb->prefix . 'ch_leads';
    // Meta table (extra info per lead)
    $meta  = $wpdb->prefix . 'ch_leads_meta';

    $sql_leads = "CREATE TABLE IF NOT EXISTS $leads (
        id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name          VARCHAR(200)        NOT NULL DEFAULT '',
        email         VARCHAR(200)        NOT NULL DEFAULT '',
        mobile        VARCHAR(50)         NOT NULL DEFAULT '',
        query_type    VARCHAR(100)        NOT NULL DEFAULT '',
        query         TEXT                NOT NULL,
        status        VARCHAR(50)         NOT NULL DEFAULT 'new',
        admin_comment TEXT                         DEFAULT NULL,
        contacted_at  DATETIME                     DEFAULT NULL,
        created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY created_at (created_at)
    ) $charset;";

    $sql_meta = "CREATE TABLE IF NOT EXISTS $meta (
        id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        lead_id     BIGINT(20) UNSIGNED NOT NULL,
        meta_key    VARCHAR(100)        NOT NULL,
        meta_value  TEXT                         DEFAULT NULL,
        PRIMARY KEY (id),
        KEY lead_id (lead_id),
        KEY meta_key (meta_key)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_leads);
    dbDelta($sql_meta);

    update_option('canehouse_db_version', '1.0');
}
add_action('after_switch_theme', 'canehouse_create_tables');
// Also run on init in case tables are missing
add_action('init', function() {
    if (get_option('canehouse_db_version') !== '1.0') {
        canehouse_create_tables();
    }
});

// ─── 2. AJAX FORM SUBMISSION ──────────────────────────────────────────────────
add_action('wp_ajax_nopriv_ch_submit_lead', 'canehouse_submit_lead');
add_action('wp_ajax_ch_submit_lead',        'canehouse_submit_lead');

function canehouse_submit_lead() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ch_lead_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }

    // Sanitize inputs
    $name       = sanitize_text_field($_POST['name']       ?? '');
    $email      = sanitize_email($_POST['email']           ?? '');
    $mobile     = sanitize_text_field($_POST['mobile']     ?? '');
    $query_type = sanitize_text_field($_POST['query_type'] ?? '');
    $query      = sanitize_textarea_field($_POST['query']  ?? '');

    // Validate required
    if (empty($name) || empty($email) || empty($query)) {
        wp_send_json_error(array('message' => 'Please fill in name, email, and message.'));
    }
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
    }

    global $wpdb;
    $leads = $wpdb->prefix . 'ch_leads';
    $meta  = $wpdb->prefix . 'ch_leads_meta';

    // Insert lead
    $inserted = $wpdb->insert($leads, array(
        'name'       => $name,
        'email'      => $email,
        'mobile'     => $mobile,
        'query_type' => $query_type,
        'query'      => $query,
        'status'     => 'new',
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
    ));

    if (!$inserted) {
        wp_send_json_error(array('message' => 'Something went wrong. Please call us directly.'));
    }

    $lead_id = $wpdb->insert_id;

    // ── Collect meta information automatically ──────────────────────────
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_X_REAL_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'unknown';
    // Get only first IP if comma-separated
    $ip = trim(explode(',', $ip)[0]);

    // Geo-location via free API (ip-api.com)
    $geo      = array();
    $geo_resp = wp_remote_get("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,zip,lat,lon,isp,org,timezone", array('timeout' => 5));
    if (!is_wp_error($geo_resp)) {
        $geo = json_decode(wp_remote_retrieve_body($geo_resp), true) ?? array();
    }

    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $referer    = $_SERVER['HTTP_REFERER']    ?? 'direct';
    $language   = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown';

    // Detect device type from user agent
    $device = 'Desktop';
    if (preg_match('/Mobile|Android|iPhone|iPad/i', $user_agent)) {
        $device = preg_match('/iPad/i', $user_agent) ? 'Tablet' : 'Mobile';
    }

    $meta_rows = array(
        // Network
        array('lead_id' => $lead_id, 'meta_key' => 'ip_address',    'meta_value' => $ip),
        array('lead_id' => $lead_id, 'meta_key' => 'isp',           'meta_value' => $geo['isp']        ?? ''),
        array('lead_id' => $lead_id, 'meta_key' => 'organisation',  'meta_value' => $geo['org']        ?? ''),
        // Location
        array('lead_id' => $lead_id, 'meta_key' => 'country',       'meta_value' => $geo['country']    ?? ''),
        array('lead_id' => $lead_id, 'meta_key' => 'region',        'meta_value' => $geo['regionName'] ?? ''),
        array('lead_id' => $lead_id, 'meta_key' => 'city',          'meta_value' => $geo['city']       ?? ''),
        array('lead_id' => $lead_id, 'meta_key' => 'postcode',      'meta_value' => $geo['zip']        ?? ''),
        array('lead_id' => $lead_id, 'meta_key' => 'latitude',      'meta_value' => $geo['lat']        ?? ''),
        array('lead_id' => $lead_id, 'meta_key' => 'longitude',     'meta_value' => $geo['lon']        ?? ''),
        array('lead_id' => $lead_id, 'meta_key' => 'timezone',      'meta_value' => $geo['timezone']   ?? ''),
        // Browser / Device
        array('lead_id' => $lead_id, 'meta_key' => 'user_agent',    'meta_value' => $user_agent),
        array('lead_id' => $lead_id, 'meta_key' => 'device_type',   'meta_value' => $device),
        array('lead_id' => $lead_id, 'meta_key' => 'browser_lang',  'meta_value' => $language),
        // Session
        array('lead_id' => $lead_id, 'meta_key' => 'referrer',      'meta_value' => $referer),
        array('lead_id' => $lead_id, 'meta_key' => 'page_url',      'meta_value' => $_POST['page_url'] ?? ''),
        array('lead_id' => $lead_id, 'meta_key' => 'submitted_at',  'meta_value' => current_time('mysql')),
        array('lead_id' => $lead_id, 'meta_key' => 'server_time',   'meta_value' => gmdate('Y-m-d H:i:s') . ' UTC'),
    );

    foreach ($meta_rows as $row) {
        $wpdb->insert($meta, $row);
    }

    wp_send_json_success(array('message' => "Thanks {$name}! We'll be in touch soon. 🌿"));
}

// ─── 3. AJAX STATUS/COMMENT UPDATE ───────────────────────────────────────────
add_action('wp_ajax_ch_update_lead', 'canehouse_update_lead');

function canehouse_update_lead() {
    if (!current_user_can('manage_options')) wp_send_json_error();
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ch_admin_nonce')) wp_send_json_error();

    global $wpdb;
    $leads = $wpdb->prefix . 'ch_leads';
    $id    = intval($_POST['id']);

    $data = array('updated_at' => current_time('mysql'));
    if (isset($_POST['status']))        $data['status']        = sanitize_text_field($_POST['status']);
    if (isset($_POST['admin_comment'])) $data['admin_comment'] = sanitize_textarea_field($_POST['admin_comment']);
    if (isset($_POST['contacted_at']) && $_POST['contacted_at'])
        $data['contacted_at'] = sanitize_text_field($_POST['contacted_at']);

    $wpdb->update($leads, $data, array('id' => $id));
    wp_send_json_success();
}

// ─── 4. ENQUEUE ADMIN ASSETS ──────────────────────────────────────────────────
add_action('admin_enqueue_scripts', function($hook) {
    if (strpos($hook, 'ch-leads') === false) return;
    wp_enqueue_style('ch-admin-css', get_template_directory_uri() . '/assets/css/admin-leads.css', array(), '1.0');
    wp_enqueue_script('ch-admin-js',  get_template_directory_uri() . '/assets/js/admin-leads.js', array('jquery'), '1.0', true);
    wp_localize_script('ch-admin-js', 'CH_ADMIN', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ch_admin_nonce'),
    ));
});

// ─── 5. ENQUEUE FRONTEND NONCE ───────────────────────────────────────────────
add_action('wp_enqueue_scripts', function() {
    wp_localize_script('canehouse-script', 'CH_FORM', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ch_lead_nonce'),
    ));
});

// ─── 6. ADMIN MENU PAGE ──────────────────────────────────────────────────────
add_action('admin_menu', function() {
    add_submenu_page(
        'canehouse-settings',
        'Contact Leads',
        '📩 Contact Leads',
        'manage_options',
        'ch-leads',
        'canehouse_leads_page'
    );
});

// ─── 7. ADMIN LEADS PAGE ─────────────────────────────────────────────────────
function canehouse_leads_page() {
    global $wpdb;
    $leads_table = $wpdb->prefix . 'ch_leads';
    $meta_table  = $wpdb->prefix . 'ch_leads_meta';

    // ── Filter params
    $status_filter = sanitize_text_field($_GET['status'] ?? '');
    $search        = sanitize_text_field($_GET['search'] ?? '');
    $per_page      = 20;
    $page          = max(1, intval($_GET['paged'] ?? 1));
    $offset        = ($page - 1) * $per_page;

    $where = 'WHERE 1=1';
    if ($status_filter) $where .= $wpdb->prepare(' AND status = %s', $status_filter);
    if ($search)        $where .= $wpdb->prepare(' AND (name LIKE %s OR email LIKE %s OR mobile LIKE %s)', "%$search%", "%$search%", "%$search%");

    $total  = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table $where");
    $leads  = $wpdb->get_results("SELECT * FROM $leads_table $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");

    // ── Stats
    $stats = $wpdb->get_results("SELECT status, COUNT(*) as cnt FROM $leads_table GROUP BY status", OBJECT_K);
    $total_all  = array_sum(array_column((array)$stats, 'cnt'));
    $stat_new   = $stats['new']->cnt       ?? 0;
    $stat_cont  = $stats['contacted']->cnt ?? 0;
    $stat_rej   = $stats['rejected']->cnt  ?? 0;
    $stat_conv  = $stats['converted']->cnt ?? 0;

    $status_colors = array(
        'new'       => '#3b82f6',
        'contacted' => '#f59e0b',
        'converted' => '#10b981',
        'rejected'  => '#ef4444',
        'pending'   => '#8b5cf6',
        'spam'      => '#6b7280',
    );
    ?>
    <div class="wrap ch-leads-wrap">
      <h1 class="ch-page-title">📩 Contact Leads</h1>

      <!-- STATS CARDS -->
      <div class="ch-stats-row">
        <div class="ch-stat-card"><span class="ch-stat-num"><?php echo $total_all; ?></span><span class="ch-stat-label">Total Leads</span></div>
        <div class="ch-stat-card new"><span class="ch-stat-num"><?php echo $stat_new; ?></span><span class="ch-stat-label">🆕 New</span></div>
        <div class="ch-stat-card contacted"><span class="ch-stat-num"><?php echo $stat_cont; ?></span><span class="ch-stat-label">📞 Contacted</span></div>
        <div class="ch-stat-card converted"><span class="ch-stat-num"><?php echo $stat_conv; ?></span><span class="ch-stat-label">✅ Converted</span></div>
        <div class="ch-stat-card rejected"><span class="ch-stat-num"><?php echo $stat_rej; ?></span><span class="ch-stat-label">❌ Rejected</span></div>
      </div>

      <!-- FILTERS -->
      <div class="ch-filters">
        <form method="get">
          <input type="hidden" name="page" value="ch-leads">
          <input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Search name, email, mobile..." class="ch-search-input">
          <select name="status" class="ch-filter-select">
            <option value="">All Statuses</option>
            <?php foreach(array('new','contacted','converted','rejected','pending','spam') as $s): ?>
            <option value="<?php echo $s; ?>" <?php selected($status_filter, $s); ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="button button-primary">Filter</button>
          <a href="?page=ch-leads" class="button">Reset</a>
          <a href="?page=ch-leads&export=csv<?php echo $status_filter ? '&status='.$status_filter : ''; ?>" class="button ch-export-btn">⬇ Export CSV</a>
        </form>
      </div>

      <!-- TABLE -->
      <div class="ch-table-wrap">
        <table class="ch-leads-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Mobile</th>
              <th>Email</th>
              <th>Query Type</th>
              <th>Message</th>
              <th>Status</th>
              <th>Admin Comment</th>
              <th>Contacted At</th>
              <th>Submitted</th>
              <th>Meta Info</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($leads)): ?>
            <tr><td colspan="12" style="text-align:center;padding:2rem;color:#888;">No leads found.</td></tr>
            <?php else: ?>
            <?php foreach($leads as $lead):
                $meta_rows = $wpdb->get_results($wpdb->prepare(
                    "SELECT meta_key, meta_value FROM $meta_table WHERE lead_id = %d ORDER BY id ASC",
                    $lead->id
                ));
                $meta_map = array();
                foreach($meta_rows as $m) $meta_map[$m->meta_key] = $m->meta_value;
                $color = $status_colors[$lead->status] ?? '#888';
            ?>
            <tr id="lead-row-<?php echo $lead->id; ?>">
              <td><strong><?php echo $lead->id; ?></strong></td>
              <td><strong><?php echo esc_html($lead->name); ?></strong></td>
              <td><?php echo esc_html($lead->mobile) ?: '—'; ?></td>
              <td><a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></td>
              <td><span class="ch-query-badge"><?php echo esc_html($lead->query_type) ?: 'General'; ?></span></td>
              <td>
                <div class="ch-query-preview" title="<?php echo esc_attr($lead->query); ?>">
                  <?php echo esc_html(wp_trim_words($lead->query, 12)); ?>
                </div>
              </td>

              <!-- STATUS -->
              <td>
                <select class="ch-status-select" data-id="<?php echo $lead->id; ?>" style="border-left:3px solid <?php echo $color; ?>">
                  <?php foreach(array('new','contacted','converted','rejected','pending','spam') as $s): ?>
                  <option value="<?php echo $s; ?>" <?php selected($lead->status, $s); ?>><?php echo ucfirst($s); ?></option>
                  <?php endforeach; ?>
                </select>
              </td>

              <!-- ADMIN COMMENT -->
              <td>
                <textarea class="ch-comment-input" data-id="<?php echo $lead->id; ?>" rows="2" placeholder="Add note..."><?php echo esc_textarea($lead->admin_comment ?? ''); ?></textarea>
              </td>

              <!-- CONTACTED AT -->
              <td>
                <input type="datetime-local" class="ch-contacted-input" data-id="<?php echo $lead->id; ?>"
                  value="<?php echo $lead->contacted_at ? date('Y-m-d\TH:i', strtotime($lead->contacted_at)) : ''; ?>"
                  style="width:160px;font-size:11px;">
              </td>

              <!-- SUBMITTED -->
              <td style="white-space:nowrap;font-size:12px;">
                <?php echo esc_html(date('d M Y', strtotime($lead->created_at))); ?><br>
                <span style="color:#888;"><?php echo esc_html(date('H:i:s', strtotime($lead->created_at))); ?></span>
              </td>

              <!-- META INFO -->
              <td>
                <button class="button ch-meta-toggle" data-id="<?php echo $lead->id; ?>">📋 View Meta</button>
                <div class="ch-meta-panel" id="meta-<?php echo $lead->id; ?>" style="display:none;">
                  <table class="ch-meta-table">
                    <thead><tr><th>Key</th><th>Value</th></tr></thead>
                    <tbody>
                      <?php
                      $meta_groups = array(
                          '🌐 Network'  => array('ip_address','isp','organisation'),
                          '📍 Location' => array('country','region','city','postcode','latitude','longitude','timezone'),
                          '💻 Device'   => array('device_type','browser_lang','user_agent'),
                          '🔗 Session'  => array('referrer','page_url','submitted_at','server_time'),
                      );
                      foreach($meta_groups as $group => $keys):
                          $has = false;
                          foreach($keys as $k) if(!empty($meta_map[$k])) { $has=true; break; }
                          if(!$has) continue;
                      ?>
                      <tr><td colspan="2" class="ch-meta-group"><?php echo $group; ?></td></tr>
                      <?php foreach($keys as $k): if(empty($meta_map[$k])) continue; ?>
                      <tr>
                        <td class="ch-meta-key"><?php echo esc_html(str_replace('_',' ', ucfirst($k))); ?></td>
                        <td class="ch-meta-val"><?php echo esc_html($meta_map[$k]); ?></td>
                      </tr>
                      <?php endforeach; endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </td>

              <!-- ACTIONS -->
              <td>
                <button class="button button-primary ch-save-btn" data-id="<?php echo $lead->id; ?>">💾 Save</button>
                <div class="ch-save-msg" id="save-msg-<?php echo $lead->id; ?>" style="display:none;color:green;font-size:11px;margin-top:4px;">✅ Saved!</div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <?php $total_pages = ceil($total / $per_page); if($total_pages > 1): ?>
      <div class="ch-pagination">
        <?php for($i=1;$i<=$total_pages;$i++): ?>
        <a href="?page=ch-leads&paged=<?php echo $i; ?><?php echo $status_filter?"&status=$status_filter":''; ?><?php echo $search?"&search=".urlencode($search):''; ?>"
           class="<?php echo $i===$page?'current':''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php
}

// ─── 8. CSV EXPORT ────────────────────────────────────────────────────────────
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ch-leads') return;
    if (!isset($_GET['export']) || $_GET['export'] !== 'csv') return;
    if (!current_user_can('manage_options')) return;

    global $wpdb;
    $leads_table = $wpdb->prefix . 'ch_leads';
    $meta_table  = $wpdb->prefix . 'ch_leads_meta';

    $status = sanitize_text_field($_GET['status'] ?? '');
    $where  = $status ? $wpdb->prepare('WHERE status = %s', $status) : '';
    $leads  = $wpdb->get_results("SELECT * FROM $leads_table $where ORDER BY created_at DESC");

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="canehouse-leads-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');

    fputcsv($out, array('ID','Name','Email','Mobile','Query Type','Message','Status','Admin Comment','Contacted At','Submitted At','IP','Country','City','Device','Referrer'));

    foreach($leads as $lead) {
        $meta_rows = $wpdb->get_results($wpdb->prepare("SELECT meta_key,meta_value FROM $meta_table WHERE lead_id=%d", $lead->id));
        $m = array();
        foreach($meta_rows as $r) $m[$r->meta_key] = $r->meta_value;

        fputcsv($out, array(
            $lead->id,
            $lead->name,
            $lead->email,
            $lead->mobile,
            $lead->query_type,
            $lead->query,
            $lead->status,
            $lead->admin_comment,
            $lead->contacted_at,
            $lead->created_at,
            $m['ip_address']  ?? '',
            $m['country']     ?? '',
            $m['city']        ?? '',
            $m['device_type'] ?? '',
            $m['referrer']    ?? '',
        ));
    }
    fclose($out);
    exit;
});
