<?php
/**
 * FILE: canehouse-theme/inc/legal-pages.php
 *
 * Handles everything for dynamic legal/custom pages:
 *   - DB table: wp_ch_legal_pages
 *   - Admin CRUD with CKEditor rich text
 *   - Slug-based frontend rendering
 *   - SEO meta (title, description)
 *   - Reusable for any future static content page
 */

// ── 1. CREATE TABLE ───────────────────────────────────────────────────────────
function ch_create_legal_table()
{
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $table = $wpdb->prefix . 'ch_legal_pages';

    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        title         VARCHAR(255)    NOT NULL DEFAULT '',
        slug          VARCHAR(255)    NOT NULL DEFAULT '',
        content       LONGTEXT        NOT NULL,
        seo_title     VARCHAR(255)    NOT NULL DEFAULT '',
        seo_desc      VARCHAR(500)    NOT NULL DEFAULT '',
        status        ENUM('published','draft') NOT NULL DEFAULT 'published',
        sort_order    INT             NOT NULL DEFAULT 0,
        created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY slug (slug),
        KEY status (status)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('after_switch_theme', 'ch_create_legal_table');
add_action('init', function () {
    if (get_option('ch_legal_db_version') !== '1.0') {
        ch_create_legal_table();
        ch_seed_legal_pages();
        update_option('ch_legal_db_version', '1.0');
    }
});

// ── 2. SEED DEFAULT PAGES ─────────────────────────────────────────────────────
function ch_seed_legal_pages()
{
    global $wpdb;
    $table = $wpdb->prefix . 'ch_legal_pages';
    if ($wpdb->get_var("SELECT COUNT(*) FROM $table") > 0)
        return;

    $pages = array(
        array(
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'seo_title' => 'Privacy Policy — The Cane House',
            'seo_desc' => 'Read our privacy policy to understand how The Cane House collects and uses your data.',
            'sort_order' => 1,
            'content' => '<h2>Introduction</h2><p>At The Cane House, we are committed to protecting your personal data. This policy explains what data we collect, how we use it, and your rights.</p><h2>What We Collect</h2><p>We may collect your name, email address, phone number, and general location when you contact us or book our services.</p><h2>How We Use It</h2><p>Your data is used solely to respond to enquiries and deliver our services. We never sell your data to third parties.</p><h2>Contact</h2><p>For any data-related queries, contact us at hello@thecanehouse.co.uk</p>',
        ),
        array(
            'title' => 'Refund Policy',
            'slug' => 'refund-policy',
            'seo_title' => 'Refund Policy — The Cane House',
            'seo_desc' => 'Our refund and cancellation policy for event hire and franchise enquiries.',
            'sort_order' => 2,
            'content' => '<h2>Event Hire Refunds</h2><p>Cancellations made more than 14 days before an event are eligible for a full refund. Cancellations within 14 days will receive a 50% refund.</p><h2>No-Show Policy</h2><p>No refunds will be issued for no-shows or same-day cancellations.</p><h2>How to Request</h2><p>Email hello@thecanehouse.co.uk with your booking reference and reason for cancellation.</p>',
        ),
        array(
            'title' => 'Terms & Conditions',
            'slug' => 'terms-and-conditions',
            'seo_title' => 'Terms & Conditions — The Cane House',
            'seo_desc' => 'Terms and conditions governing use of The Cane House services and website.',
            'sort_order' => 3,
            'content' => '<h2>Acceptance of Terms</h2><p>By using our website or booking our services, you agree to these terms and conditions.</p><h2>Services</h2><p>The Cane House provides live-pressed sugarcane juice services for events, private hire, and franchise opportunities.</p><h2>Liability</h2><p>We are not liable for any indirect losses arising from use of our services beyond the value of the booking made.</p><h2>Governing Law</h2><p>These terms are governed by the laws of England and Wales.</p>',
        ),
    );

    foreach ($pages as $page) {
        $wpdb->insert($table, $page);
    }
}

// ── 3. SLUG-BASED FRONTEND ROUTING ───────────────────────────────────────────
// WHY: We intercept WordPress's template loading to check if the URL
//      matches any slug in wp_ch_legal_pages, and render it if found.
add_action('template_redirect', function () {
    global $wpdb;

    // Get current request path
    $request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    // Remove the WordPress subfolder prefix if needed
    $base = trim(parse_url(home_url(), PHP_URL_PATH), '/');
    if ($base && strpos($request, $base) === 0) {
        $request = trim(substr($request, strlen($base)), '/');
    }

    if (empty($request))
        return;

    $table = $wpdb->prefix . 'ch_legal_pages';
    $page = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE slug = %s AND status = 'published'", $request)
    );

    if (!$page)
        return;

    // Found a matching legal page — render it
    ch_render_legal_page($page);
    exit;
});

// ── 4. FRONTEND RENDER ────────────────────────────────────────────────────────
function ch_render_legal_page($page)
{
    $site_name = get_bloginfo('name');
    $seo_title = $page->seo_title ?: $page->title . ' — ' . $site_name;
    $seo_desc = $page->seo_desc ?: '';
    $home_url = home_url('/');
    $theme_uri = get_template_directory_uri();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>

    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            <?php echo esc_html($seo_title); ?>
        </title>
        <?php if ($seo_desc): ?>
            <meta name="description" content="<?php echo esc_attr($seo_desc); ?>">
        <?php endif; ?>
        <!-- Open Graph -->
        <meta property="og:title" content="<?php echo esc_attr($seo_title); ?>">
        <meta property="og:description" content="<?php echo esc_attr($seo_desc); ?>">
        <meta property="og:type" content="website">
        <meta property="og:url" content="<?php echo esc_url(home_url('/' . $page->slug)); ?>">
        <!-- Canonical -->
        <link rel="canonical" href="<?php echo esc_url(home_url('/' . $page->slug)); ?>">
        <?php wp_head(); ?>
    </head>

    <body class="ch-legal-body">

        <?php
        // Use theme header (nav stays consistent)
        get_header(null, array('show_nav' => false));
        ?>

        <div class="ch-legal-wrap">
            <div class="ch-legal-container">

                <nav class="ch-legal-breadcrumb">
                    <a href="<?php echo esc_url($home_url); ?>">Home</a>
                    <span>›</span>
                    <span>
                        <?php echo esc_html($page->title); ?>
                    </span>
                </nav>

                <article class="ch-legal-article">
                    <h1 class="ch-legal-title">
                        <?php echo esc_html($page->title); ?>
                    </h1>
                    <p class="ch-legal-meta">Last updated:
                        <?php echo date('d F Y', strtotime($page->updated_at)); ?>
                    </p>
                    <div class="ch-legal-content">
                        <?php echo wp_kses_post($page->content); ?>
                    </div>
                </article>

                <div class="ch-legal-back">
                    <a href="<?php echo esc_url($home_url); ?>" class="ch-legal-back-btn">← Back to Home</a>
                </div>

            </div>
        </div>

        <?php get_footer(); ?>

    </body>

    </html>
    <?php
}

// ── 5. ADMIN MENU ─────────────────────────────────────────────────────────────
add_action('admin_menu', function () {
    add_submenu_page(
        'ch-content',
        '📄 Legal Pages',
        '📄 Legal Pages',
        'manage_options',
        'ch-legal-pages',
        'ch_legal_pages_admin'
    );
});

// ── 6. ENQUEUE CKEDITOR ───────────────────────────────────────────────────────
add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'ch-legal-pages') === false)
        return;
    // CKEditor 5 CDN — free, no license needed
    wp_enqueue_script(
        'ckeditor5',
        'https://unpkg.com/@ckeditor/ckeditor5-build-classic@41.4.2/build/ckeditor.js',
        array(),
        null,
        true
    );
    wp_enqueue_style('ch-legal-admin', get_template_directory_uri() . '/assets/css/ch-legal-admin.css', array(), '1.0');
});

// ── 7. AJAX HANDLERS ──────────────────────────────────────────────────────────

// Save (insert or update)
add_action('wp_ajax_ch_legal_save', function () {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'] ?? '', 'ch_legal_nonce')) {
        wp_send_json_error('Unauthorised');
    }
    global $wpdb;
    $table = $wpdb->prefix . 'ch_legal_pages';
    $id = intval($_POST['id'] ?? 0);

    // Auto-generate slug from title if not provided
    $slug = sanitize_title($_POST['slug'] ?? $_POST['title'] ?? '');
    if (empty($slug))
        wp_send_json_error('Title is required');

    // Check slug uniqueness (exclude current record on edit)
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE slug = %s AND id != %d",
        $slug,
        $id
    ));
    if ($exists)
        wp_send_json_error('Slug already exists. Please use a different title or slug.');

    $data = array(
        'title' => sanitize_text_field($_POST['title'] ?? ''),
        'slug' => $slug,
        'content' => wp_kses_post($_POST['content'] ?? ''),
        'seo_title' => sanitize_text_field($_POST['seo_title'] ?? ''),
        'seo_desc' => sanitize_textarea_field($_POST['seo_desc'] ?? ''),
        'status' => in_array($_POST['status'] ?? '', array('published', 'draft')) ? $_POST['status'] : 'published',
        'sort_order' => intval($_POST['sort_order'] ?? 0),
        'updated_at' => current_time('mysql'),
    );

    if ($id > 0) {
        $wpdb->update($table, $data, array('id' => $id));
        wp_send_json_success(array('action' => 'updated', 'id' => $id, 'slug' => $slug));
    } else {
        $data['created_at'] = current_time('mysql');
        $wpdb->insert($table, $data);
        wp_send_json_success(array('action' => 'inserted', 'id' => $wpdb->insert_id, 'slug' => $slug));
    }
});

// Get single page
add_action('wp_ajax_ch_legal_get', function () {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['nonce'] ?? '', 'ch_legal_nonce')) {
        wp_send_json_error('Unauthorised');
    }
    global $wpdb;
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ch_legal_pages WHERE id = %d",
        intval($_GET['id'])
    ), ARRAY_A);
    wp_send_json_success($row);
});

// Delete
add_action('wp_ajax_ch_legal_delete', function () {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['nonce'] ?? '', 'ch_legal_nonce')) {
        wp_send_json_error('Unauthorised');
    }
    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'ch_legal_pages', array('id' => intval($_POST['id'])));
    wp_send_json_success();
});

// ── 8. ADMIN PAGE RENDER ──────────────────────────────────────────────────────
function ch_legal_pages_admin()
{
    global $wpdb;
    $table = $wpdb->prefix . 'ch_legal_pages';
    $pages = $wpdb->get_results("SELECT * FROM $table ORDER BY sort_order ASC, id ASC");
    $nonce = wp_create_nonce('ch_legal_nonce');
    $ajax = admin_url('admin-ajax.php');
    ?>
    <div class="wrap ch-legal-admin-wrap">
        <h1 class="ch-legal-admin-title">📄 Legal Pages</h1>
        <p class="ch-legal-admin-sub">
            Create and manage legal pages (Privacy Policy, Terms, Refund Policy, etc.)
            Each page gets its own URL: <code><?php echo home_url('/'); ?><strong>slug</strong></code>
        </p>

        <button class="ch-btn-primary" id="ch-legal-add-new">+ Add New Page</button>

        <!-- PAGES TABLE -->
        <table class="ch-legal-table" id="ch-legal-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>URL Slug</th>
                    <th>SEO Title</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Live URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pages)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:2rem;color:#888;">No pages yet. Click "Add New Page".
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pages as $p): ?>
                        <tr id="legal-row-<?php echo $p->id; ?>">
                            <td>
                                <?php echo $p->id; ?>
                            </td>
                            <td><strong>
                                    <?php echo esc_html($p->title); ?>
                                </strong></td>
                            <td><code><?php echo esc_html($p->slug); ?></code></td>
                            <td style="font-size:12px;color:#666;">
                                <?php echo esc_html(wp_trim_words($p->seo_title, 8)); ?>
                            </td>
                            <td>
                                <span class="ch-status-pill ch-status-<?php echo $p->status; ?>">
                                    <?php echo $p->status === 'published' ? '✅ Published' : '📝 Draft'; ?>
                                </span>
                            </td>
                            <td style="font-size:12px;color:#888;">
                                <?php echo date('d M Y', strtotime($p->updated_at)); ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url(home_url('/' . $p->slug)); ?>" target="_blank" class="ch-link-btn">
                                    🔗 View
                                </a>
                            </td>
                            <td>
                                <button class="ch-btn-sm ch-edit-legal" data-id="<?php echo $p->id; ?>">✏️ Edit</button>
                                <button class="ch-btn-sm ch-btn-danger ch-delete-legal" data-id="<?php echo $p->id; ?>"
                                    data-title="<?php echo esc_attr($p->title); ?>">🗑️ Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ADD / EDIT MODAL -->
    <div id="ch-legal-modal" style="display:none;">
        <div class="ch-legal-modal-box">
            <div class="ch-legal-modal-head">
                <h2 id="ch-legal-modal-title">Add New Page</h2>
                <button id="ch-legal-modal-close" class="ch-modal-close-btn">✕</button>
            </div>
            <div class="ch-legal-modal-body">
                <form id="ch-legal-form">
                    <input type="hidden" id="ch-legal-id" name="id" value="0">
                    <input type="hidden" id="ch-legal-nonce" name="nonce" value="<?php echo $nonce; ?>">

                    <div class="ch-legal-form-row ch-legal-two-col">
                        <div class="ch-legal-field">
                            <label>Page Title <span class="ch-req">*</span></label>
                            <input type="text" id="ch-legal-title" name="title" placeholder="e.g. Privacy Policy" required>
                        </div>
                        <div class="ch-legal-field">
                            <label>
                                URL Slug <span class="ch-req">*</span>
                                <small style="font-weight:400;color:#888;">— auto-generated from title</small>
                            </label>
                            <input type="text" id="ch-legal-slug" name="slug" placeholder="e.g. privacy-policy">
                            <small class="ch-slug-preview">URL: <strong>
                                    <?php echo home_url('/'); ?>
                                </strong><span id="ch-slug-live"></span></small>
                        </div>
                    </div>

                    <div class="ch-legal-field" style="margin-bottom:16px;">
                        <label>Page Content <span class="ch-req">*</span></label>
                        <textarea id="ch-legal-content" name="content" rows="12"></textarea>
                        <!-- CKEditor mounts here -->
                    </div>

                    <div class="ch-legal-seo-box">
                        <div class="ch-legal-seo-title">🔍 SEO Settings</div>
                        <div class="ch-legal-two-col">
                            <div class="ch-legal-field">
                                <label>SEO Title <small>(shown in browser tab & Google)</small></label>
                                <input type="text" id="ch-legal-seo-title" name="seo_title"
                                    placeholder="e.g. Privacy Policy — The Cane House" maxlength="60">
                                <small id="ch-seo-title-count" style="color:#888;">0 / 60</small>
                            </div>
                            <div class="ch-legal-field">
                                <label>Meta Description <small>(shown in Google results)</small></label>
                                <textarea id="ch-legal-seo-desc" name="seo_desc" rows="2"
                                    placeholder="Brief description for search engines..." maxlength="160"></textarea>
                                <small id="ch-seo-desc-count" style="color:#888;">0 / 160</small>
                            </div>
                        </div>
                        <div class="ch-legal-two-col">
                            <div class="ch-legal-field">
                                <label>Status</label>
                                <select id="ch-legal-status" name="status">
                                    <option value="published">✅ Published — live on site</option>
                                    <option value="draft">📝 Draft — hidden from site</option>
                                </select>
                            </div>
                            <div class="ch-legal-field">
                                <label>Sort Order <small>(lower = first in footer)</small></label>
                                <input type="number" id="ch-legal-sort" name="sort_order" value="0" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="ch-legal-form-actions">
                        <button type="submit" class="ch-btn-primary" id="ch-legal-submit">💾 Save Page</button>
                        <button type="button" class="ch-btn-secondary" id="ch-legal-cancel">Cancel</button>
                        <span id="ch-legal-status-msg" style="font-size:13px;margin-left:10px;"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function ($) {
            var ckInstance = null;
            var nonce = '<?php echo $nonce; ?>';
            var ajaxUrl = '<?php echo $ajax; ?>';
            var homeUrl = '<?php echo home_url("/"); ?>';

            // Init CKEditor on textarea
            function initCK() {
                var el = document.querySelector('#ch-legal-content');
                if (!el) return;
                if (typeof ClassicEditor === 'undefined') return;
                if (ckInstance) { ckInstance.destroy(); ckInstance = null; }

                ClassicEditor
                    .create(document.querySelector('#ch-legal-content'), {
                        toolbar: ['heading', '|', 'bold', 'italic', 'underline', 'strikethrough', '|',
                            'bulletedList', 'numberedList', '|', 'link', 'blockQuote', '|',
                            'insertTable', 'horizontalLine', '|', 'undo', 'redo', '|', 'sourceEditing'],
                        heading: {
                            options: [
                                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                            ]
                        }
                    })
                    .then(function (editor) { ckInstance = editor; })
                    .catch(console.error);
            }

            // Open modal
            function openModal(title, data) {
                data = data || {};
                $('#ch-legal-modal-title').text(title);
                $('#ch-legal-id').val(data.id || 0);
                $('#ch-legal-title').val(data.title || '');
                $('#ch-legal-slug').val(data.slug || '');
                $('#ch-legal-seo-title').val(data.seo_title || '');
                $('#ch-legal-seo-desc').val(data.seo_desc || '');
                $('#ch-legal-status').val(data.status || 'published');
                $('#ch-legal-sort').val(data.sort_order || 0);
                $('#ch-slug-live').text(data.slug || '');
                updateCounts();
                $('#ch-legal-status-msg').text('');

                // Set CKEditor content after modal visible
                $('#ch-legal-modal').fadeIn(150, function () {
                    if (ckInstance) {
                        ckInstance.setData(data.content || '');
                    } else {
                        $('#ch-legal-content').val(data.content || '');
                        initCK();
                    }
                });
            }

            function closeModal() {
                $('#ch-legal-modal').fadeOut(150);
            }

            // Auto slug from title
            $('#ch-legal-title').on('input', function () {
                var slug = $(this).val()
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-');
                $('#ch-legal-slug').val(slug);
                $('#ch-slug-live').text(slug);
            });
            $('#ch-legal-slug').on('input', function () {
                $('#ch-slug-live').text($(this).val());
            });

            // SEO char counters
            function updateCounts() {
                var t = $('#ch-legal-seo-title').val().length;
                var d = $('#ch-legal-seo-desc').val().length;
                $('#ch-seo-title-count').text(t + ' / 60').css('color', t > 55 ? '#f59e0b' : '#888');
                $('#ch-seo-desc-count').text(d + ' / 160').css('color', d > 150 ? '#f59e0b' : '#888');
            }
            $('#ch-legal-seo-title, #ch-legal-seo-desc').on('input', updateCounts);

            // Add new
            $('#ch-legal-add-new').on('click', function () { openModal('Add New Page', {}); });

            // Edit
            $(document).on('click', '.ch-edit-legal', function () {
                var id = $(this).data('id');
                $.get(ajaxUrl, { action: 'ch_legal_get', id: id, nonce: nonce }, function (res) {
                    if (res.success) openModal('Edit Page', res.data);
                });
            });

            // Close
            $('#ch-legal-modal-close, #ch-legal-cancel').on('click', closeModal);
            $('#ch-legal-modal').on('click', function (e) { if (e.target === this) closeModal(); });

            // Submit
            $('#ch-legal-form').on('submit', function (e) {
                e.preventDefault();
                var btn = $('#ch-legal-submit');
                var msg = $('#ch-legal-status-msg');
                btn.text('Saving...').prop('disabled', true);
                msg.text('').css('color', '');

                // Sync CKEditor to textarea
                if (ckInstance) $('#ch-legal-content').val(ckInstance.getData());

                var data = $(this).serializeArray();
                data.push({ name: 'action', value: 'ch_legal_save' });

                $.post(ajaxUrl, data, function (res) {
                    btn.text('💾 Save Page').prop('disabled', false);
                    if (res.success) {
                        msg.text('✅ Saved!').css('color', '#166534');
                        setTimeout(function () { closeModal(); location.reload(); }, 800);
                    } else {
                        msg.text('❌ ' + (res.data || 'Error')).css('color', '#ef4444');
                    }
                }).fail(function () {
                    btn.text('💾 Save Page').prop('disabled', false);
                    msg.text('❌ Network error').css('color', '#ef4444');
                });
            });

            // Delete
            $(document).on('click', '.ch-delete-legal', function () {
                var id = $(this).data('id');
                var title = $(this).data('title');
                if (!confirm('Delete "' + title + '"?\n\nThis cannot be undone.')) return;
                var row = $('#legal-row-' + id);
                $.post(ajaxUrl, { action: 'ch_legal_delete', id: id, nonce: nonce }, function (res) {
                    if (res.success) row.fadeOut(300, function () { $(this).remove(); });
                });
            });

        })(jQuery);
    </script>
    <?php
}