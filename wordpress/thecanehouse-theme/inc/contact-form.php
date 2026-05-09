<?php
/**
 * Contact Form Handler — Shared logic for both themes
 * Creates DB table on activation, processes submissions, handles admin status updates.
 *
 * TABLE: wp_{prefix}_contacts
 * Columns: id, name, email, phone, message, enquiry_type, status, admin_notes, created_at, updated_at
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create the contacts table on theme activation.
 * Call this from functions.php via after_switch_theme hook.
 */
function theme_create_contacts_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'theme_contacts';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name         VARCHAR(100) NOT NULL,
        email        VARCHAR(150) NOT NULL,
        phone        VARCHAR(30)  DEFAULT '',
        message      TEXT         NOT NULL,
        enquiry_type VARCHAR(60)  DEFAULT 'General',
        status       VARCHAR(30)  NOT NULL DEFAULT 'New',
        admin_notes  TEXT         DEFAULT '',
        created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

/**
 * Process contact form POST submission.
 * Returns ['success' => bool, 'message' => string]
 */
function theme_process_contact_form() {
    if ( ! isset( $_POST['tch_contact_nonce'] ) ||
         ! wp_verify_nonce( $_POST['tch_contact_nonce'], 'tch_contact_submit' ) ) {
        return ['success' => false, 'message' => 'Security check failed. Please refresh and try again.'];
    }

    $name    = sanitize_text_field( $_POST['contact_name']    ?? '' );
    $email   = sanitize_email(      $_POST['contact_email']   ?? '' );
    $phone   = sanitize_text_field( $_POST['contact_phone']   ?? '' );
    $message = sanitize_textarea_field( $_POST['contact_message'] ?? '' );
    $type    = sanitize_text_field( $_POST['contact_type']    ?? 'General' );

    if ( empty($name) || empty($email) || empty($message) ) {
        return ['success' => false, 'message' => 'Please fill in all required fields.'];
    }
    if ( ! is_email($email) ) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }

    global $wpdb;
    $table = $wpdb->prefix . 'theme_contacts';

    $inserted = $wpdb->insert( $table, [
        'name'         => $name,
        'email'        => $email,
        'phone'        => $phone,
        'message'      => $message,
        'enquiry_type' => $type,
        'status'       => 'New',
    ]);

    if ( $inserted ) {
        // Send admin notification email
        $admin_email = defined('TCH_ADMIN_NOTIFY_EMAIL') ? TCH_ADMIN_NOTIFY_EMAIL
                     : (defined('AH_ADMIN_NOTIFY_EMAIL')  ? AH_ADMIN_NOTIFY_EMAIL
                     : get_option('admin_email'));

        $site = get_bloginfo('name');
        wp_mail(
            $admin_email,
            "[{$site}] New Contact Form Submission from {$name}",
            "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\nType: {$type}\n\nMessage:\n{$message}\n\nView in admin: " . admin_url('admin.php?page=theme-contacts')
        );

        return ['success' => true, 'message' => 'Thank you! We\'ll be in touch very soon.'];
    }

    return ['success' => false, 'message' => 'Something went wrong. Please try again or contact us directly.'];
}

/**
 * Admin: Update contact status and notes.
 */
function theme_update_contact_status() {
    if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
    check_admin_referer('theme_update_contact');

    $id     = intval( $_POST['contact_id'] ?? 0 );
    $status = sanitize_text_field( $_POST['status'] ?? 'New' );
    $notes  = sanitize_textarea_field( $_POST['admin_notes'] ?? '' );

    global $wpdb;
    $wpdb->update(
        $wpdb->prefix . 'theme_contacts',
        ['status' => $status, 'admin_notes' => $notes],
        ['id' => $id]
    );

    wp_redirect( admin_url('admin.php?page=theme-contacts&updated=1') );
    exit;
}
add_action('admin_post_theme_update_contact', 'theme_update_contact_status');

/**
 * Render the Admin Contacts Dashboard page.
 */
function theme_render_contacts_admin() {
    global $wpdb;
    $table = $wpdb->prefix . 'theme_contacts';

    // Filter
    $status_filter = sanitize_text_field( $_GET['status_filter'] ?? '' );
    $where = $status_filter ? $wpdb->prepare("WHERE status = %s", $status_filter) : '';
    $contacts = $wpdb->get_results("SELECT * FROM {$table} {$where} ORDER BY created_at DESC");

    $statuses = ['New', 'Called', 'In Progress', 'Not Interested', 'Converted'];
    $status_colors = [
        'New'           => '#3b82f6',
        'Called'        => '#f59e0b',
        'In Progress'   => '#8b5cf6',
        'Not Interested'=> '#ef4444',
        'Converted'     => '#10b981',
    ];
    ?>
    <div class="wrap" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
        <h1 style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            📋 Contact Submissions
            <span style="font-size:14px;background:#f1f5f9;padding:4px 12px;border-radius:20px;color:#64748b;">
                <?php echo count($contacts); ?> entries
            </span>
        </h1>

        <?php if ( isset($_GET['updated']) ): ?>
        <div class="notice notice-success is-dismissible"><p>✅ Contact updated successfully.</p></div>
        <?php endif; ?>

        <!-- Filter Bar -->
        <div style="display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap;">
            <a href="<?php echo admin_url('admin.php?page=theme-contacts'); ?>"
               style="padding:8px 18px;border-radius:20px;text-decoration:none;font-weight:600;font-size:13px;<?php echo !$status_filter ? 'background:#0f172a;color:white;' : 'background:#f1f5f9;color:#475569;'; ?>">
                All
            </a>
            <?php foreach ($statuses as $s): ?>
            <a href="<?php echo admin_url('admin.php?page=theme-contacts&status_filter=' . urlencode($s)); ?>"
               style="padding:8px 18px;border-radius:20px;text-decoration:none;font-weight:600;font-size:13px;<?php echo $status_filter === $s ? 'background:' . $status_colors[$s] . ';color:white;' : 'background:#f1f5f9;color:#475569;'; ?>">
                <?php echo esc_html($s); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Table -->
        <div style="background:white;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.1);overflow:hidden;">
            <?php if ( empty($contacts) ): ?>
            <div style="padding:60px;text-align:center;color:#94a3b8;">
                <div style="font-size:48px;margin-bottom:12px;">📭</div>
                <p style="font-size:16px;">No submissions yet.</p>
            </div>
            <?php else: ?>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:14px 20px;text-align:left;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Name</th>
                        <th style="padding:14px 20px;text-align:left;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Contact</th>
                        <th style="padding:14px 20px;text-align:left;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Type</th>
                        <th style="padding:14px 20px;text-align:left;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Message</th>
                        <th style="padding:14px 20px;text-align:left;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Date</th>
                        <th style="padding:14px 20px;text-align:left;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Status & Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $contacts as $c ): ?>
                    <tr style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                        <td style="padding:16px 20px;font-weight:600;color:#0f172a;"><?php echo esc_html($c->name); ?></td>
                        <td style="padding:16px 20px;">
                            <a href="mailto:<?php echo esc_attr($c->email); ?>" style="color:#3b82f6;text-decoration:none;display:block;"><?php echo esc_html($c->email); ?></a>
                            <?php if ($c->phone): ?>
                            <a href="tel:<?php echo esc_attr($c->phone); ?>" style="color:#64748b;font-size:13px;text-decoration:none;"><?php echo esc_html($c->phone); ?></a>
                            <?php endif; ?>
                        </td>
                        <td style="padding:16px 20px;">
                            <span style="display:inline-block;padding:4px 12px;border-radius:20px;background:#f1f5f9;color:#475569;font-size:12px;font-weight:600;">
                                <?php echo esc_html($c->enquiry_type); ?>
                            </span>
                        </td>
                        <td style="padding:16px 20px;max-width:240px;color:#475569;font-size:13px;line-height:1.5;">
                            <?php echo esc_html( mb_strimwidth($c->message, 0, 120, '…') ); ?>
                        </td>
                        <td style="padding:16px 20px;color:#94a3b8;font-size:13px;white-space:nowrap;">
                            <?php echo esc_html( date('d M Y, H:i', strtotime($c->created_at)) ); ?>
                        </td>
                        <td style="padding:16px 20px;">
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:flex;flex-direction:column;gap:8px;min-width:200px;">
                                <input type="hidden" name="action" value="theme_update_contact" />
                                <input type="hidden" name="contact_id" value="<?php echo intval($c->id); ?>" />
                                <?php wp_nonce_field('theme_update_contact'); ?>
                                <select name="status" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;color:<?php echo $status_colors[$c->status] ?? '#475569'; ?>;">
                                    <?php foreach ($statuses as $s): ?>
                                    <option value="<?php echo esc_attr($s); ?>" <?php selected($c->status, $s); ?>><?php echo esc_html($s); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <textarea name="admin_notes" rows="2" placeholder="Add notes (client said, next steps…)" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;resize:vertical;"><?php echo esc_textarea($c->admin_notes); ?></textarea>
                                <button type="submit" style="padding:6px 14px;background:#0f172a;color:white;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
