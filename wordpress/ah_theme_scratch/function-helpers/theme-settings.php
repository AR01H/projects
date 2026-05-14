<?php
/**
 * Advaith Homes Mega Content Manager & Settings
 */

function ah_theme_add_admin_menu() {
    $parent_slug = 'advaith-homes';
    add_menu_page('Advaith Homes', 'Advaith Homes', 'manage_options', $parent_slug, 'ah_theme_dashboard_page', 'dashicons-admin-home', 3);
    add_submenu_page($parent_slug, 'Dashboard', 'Dashboard', 'manage_options', $parent_slug, 'ah_theme_dashboard_page');
    add_submenu_page($parent_slug, 'Manage Articles', 'Manage Articles', 'manage_options', 'ah-manager', 'ah_theme_content_manager_page');
    add_submenu_page($parent_slug, 'Site Settings', 'Site Settings', 'manage_options', 'ah-settings', 'ah_theme_settings_page');
    
    // Core Lists
    add_submenu_page($parent_slug, 'Properties', 'All Properties', 'edit_posts', 'edit.php?post_type=property');
    add_submenu_page($parent_slug, 'Services', 'All Services', 'edit_posts', 'edit.php?post_type=service');
    add_submenu_page($parent_slug, 'Contact Leads', 'All Messages', 'edit_posts', 'edit.php?post_type=inquiry');
}
add_action('admin_menu', 'ah_theme_add_admin_menu');

/**
 * 1. Content Manager Page (The 2-Way Editor)
 */
function ah_theme_content_manager_page() {
    if (isset($_POST['ah_quick_save'])) {
        $pid = intval($_POST['post_id']);
        wp_update_post(['ID' => $pid, 'post_title' => $_POST['title']]);
        update_post_meta($pid, 'ah_mini_info', $_POST['summary']);
        update_post_meta($pid, 'ah_tag_text', $_POST['tag']);
        update_post_meta($pid, 'ah_tag_color', $_POST['color']);
        update_post_meta($pid, 'ah_sort_order', intval($_POST['sort']));
        update_post_meta($pid, 'display_status', $_POST['status']);
        echo '<div class="updated"><p>Item Updated!</p></div>';
    }

    $query = new WP_Query(['post_type' => 'post', 'posts_per_page' => -1, 'meta_key' => 'ah_sort_order', 'orderby' => 'meta_value_num', 'order' => 'ASC']);
    ?>
    <div class="ah-admin-wrap">
        <div class="ah-header">
            <h1 class="ah-title">Manage Articles & News</h1>
            <a href="post-new.php" class="button button-primary">+ Add New Article</a>
        </div>
        <div class="ah-card" style="padding:0; overflow:hidden;">
            <table class="ah-table">
                <thead><tr><th>Sort</th><th>Image</th><th>Details</th><th>Tag</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                    <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); 
                        $pid = get_the_ID();
                        $sort = get_post_meta($pid, 'ah_sort_order', true) ?: '0';
                        $tag = get_post_meta($pid, 'ah_tag_text', true) ?: 'Blog';
                        $color = get_post_meta($pid, 'ah_tag_color', true) ?: '#8b5cf6';
                        $status = get_post_meta($pid, 'display_status', true) ?: 'Active';
                        $summary = get_post_meta($pid, 'ah_mini_info', true);
                    ?>
                        <tr>
                            <td><strong>#<?php echo $sort; ?></strong></td>
                            <td><?php if (has_post_thumbnail()) the_post_thumbnail([50,50], ['style'=>'border-radius:6px;']); ?></td>
                            <td><strong><?php the_title(); ?></strong><div style="font-size:11px; color:#64748b;"><?php echo wp_trim_words($summary, 8); ?></div></td>
                            <td><span style="background:<?php echo $color; ?>; color:white; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:800;"><?php echo $tag; ?></span></td>
                            <td><span class="ah-badge <?php echo ($status === 'Active') ? 'ah-badge-done' : 'ah-badge-new'; ?>"><?php echo $status; ?></span></td>
                            <td style="text-align:right;">
                                <button class="button ah-open-modal" data-id="<?php echo $pid; ?>" data-title="<?php echo esc_attr(get_the_title()); ?>" data-summary="<?php echo esc_attr($summary); ?>" data-tag="<?php echo esc_attr($tag); ?>" data-color="<?php echo esc_attr($color); ?>" data-sort="<?php echo esc_attr($sort); ?>" data-status="<?php echo esc_attr($status); ?>">Quick Edit</button>
                                <a href="post.php?post=<?php echo $pid; ?>&action=edit" class="button">Full Editor</a>
                            </td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- QUICK EDIT MODAL -->
    <div id="ah-quick-modal" class="ah-modal" style="display:none;">
        <div class="ah-modal-content">
            <div class="ah-modal-header"><h2>Quick Edit Item</h2><span class="ah-close">&times;</span></div>
            <form method="post" class="ah-modal-body">
                <input type="hidden" name="post_id" id="m-id">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div class="ah-field-group"><label class="ah-label">Title</label><input type="text" name="title" id="m-title" class="ah-input" required></div>
                    <div class="ah-field-group"><label class="ah-label">Sort Order</label><input type="number" name="sort" id="m-sort" class="ah-input"></div>
                </div>
                <div class="ah-field-group"><label class="ah-label">Mini Summary</label><textarea name="summary" id="m-summary" class="ah-input" rows="2"></textarea></div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div class="ah-field-group"><label class="ah-label">Tag Text</label><input type="text" name="tag" id="m-tag" class="ah-input"></div>
                    <div class="ah-field-group"><label class="ah-label">Tag Color</label><input type="color" name="color" id="m-color" style="height:45px; width:100%;"></div>
                </div>
                <div class="ah-field-group">
                    <label class="ah-label">Status</label>
                    <select name="status" id="m-status" class="ah-input">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="ah-modal-footer">
                    <button type="button" class="button ah-close-btn">Cancel</button>
                    <button type="submit" name="ah_quick_save" class="button button-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .ah-modal { position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; }
    .ah-modal-content { background:white; width:550px; border-radius:12px; box-shadow:0 20px 40px rgba(0,0,0,0.2); overflow:hidden; }
    .ah-modal-header { padding:15px 25px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
    .ah-modal-header h2 { margin:0; font-size:16px; }
    .ah-modal-body { padding:25px; }
    .ah-modal-footer { padding:15px 25px; background:#f9f9f9; border-top:1px solid #eee; text-align:right; }
    .ah-close { cursor:pointer; font-size:20px; }
    </style>

    <script>
    jQuery(document).ready(function($){
        $('.ah-open-modal').click(function(){
            var d = $(this).data();
            $('#m-id').val(d.id); $('#m-title').val(d.title); $('#m-summary').val(d.summary); $('#m-tag').val(d.tag); $('#m-color').val(d.color); $('#m-sort').val(d.sort); $('#m-status').val(d.status);
            $('#ah-quick-modal').fadeIn(150);
        });
        $('.ah-close, .ah-close-btn').click(function(){ $('#ah-quick-modal').fadeOut(150); });
    });
    </script>
    <?php
}

/**
 * 2. Dashboard Page
 */
function ah_theme_dashboard_page() {
    $total_leads = wp_count_posts('inquiry')->publish;
    $new_leads = count(get_posts(['post_type'=>'inquiry','meta_key'=>'status','meta_value'=>'New','posts_per_page'=>-1]));
    ?>
    <div class="ah-admin-wrap">
        <h1 class="ah-title">Welcome to Advaith Homes Dashboard</h1>
        <div class="ah-stats-grid">
            <div class="ah-stat-card"><div class="ah-stat-num"><?php echo $total_leads; ?></div><div class="ah-stat-label">Total Leads</div></div>
            <div class="ah-stat-card" style="border-top-color:#ef4444;"><div class="ah-stat-num" style="color:#ef4444;"><?php echo $new_leads; ?></div><div class="ah-stat-label">New Leads</div></div>
        </div>
    </div>
    <?php
}

/**
 * 3. Settings Page (Expanded)
 */
function ah_theme_settings_page() {
    $options = [
        'ah_site_logo', 
        'ah_company_name',
        'ah_contact_phone', 
        'ah_contact_email', 
        'ah_consultation_url', 
        'ah_mega_menu_json',
        'ah_buying_guides_json'
    ];
    
    if (isset($_POST['ah_save_settings'])) {
        foreach ($options as $opt) { if (isset($_POST[$opt])) update_option($opt, $_POST[$opt]); }
        echo '<div class="updated"><p>All Site Settings Saved Successfully!</p></div>';
    }
    
    $vals = [];
    foreach ($options as $opt) { $vals[$opt] = get_option($opt, ''); }
    ?>
    <div class="ah-admin-wrap">
        <div class="ah-header">
            <h1 class="ah-title">Global Site Settings & Branding</h1>
            <p style="color:#64748b; margin-top:-20px;">Control all professional branding and contact information from here.</p>
        </div>

        <form method="post">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
                <!-- Branding Card -->
                <div class="ah-card">
                    <h3 style="margin-top:0;">Branding & Identity</h3>
                    <div class="ah-field-group">
                        <label class="ah-label">Company Name</label>
                        <input type="text" name="ah_company_name" value="<?php echo esc_attr($vals['ah_company_name'] ?: 'Advaith Homes'); ?>" class="ah-input">
                    </div>
                    <div class="ah-field-group">
                        <label class="ah-label">Logo Image URL</label>
                        <input type="text" name="ah_site_logo" value="<?php echo esc_attr($vals['ah_site_logo']); ?>" class="ah-input" placeholder="https://...">
                    </div>
                </div>

                <!-- Contact Card -->
                <div class="ah-card">
                    <h3 style="margin-top:0;">Contact Information</h3>
                    <div class="ah-field-group">
                        <label class="ah-label">Contact Phone Number</label>
                        <input type="text" name="ah_contact_phone" value="<?php echo esc_attr($vals['ah_contact_phone'] ?: '+44 7747 223762'); ?>" class="ah-input">
                    </div>
                    <div class="ah-field-group">
                        <label class="ah-label">Official Email Address</label>
                        <input type="email" name="ah_contact_email" value="<?php echo esc_attr($vals['ah_contact_email'] ?: 'info@advaithhomes.com'); ?>" class="ah-input">
                    </div>
                    <div class="ah-field-group">
                        <label class="ah-label">Consultation Link URL</label>
                        <input type="text" name="ah_consultation_url" value="<?php echo esc_attr($vals['ah_consultation_url'] ?: home_url('/free-consultation')); ?>" class="ah-input">
                    </div>
                </div>
            </div>

            <!-- Mega Menu Card -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-top:30px;">
                <div class="ah-card">
                    <h3 style="margin-top:0;">Mega Menu Manager</h3>
                    <p style="font-size:12px; color:#64748b; margin-bottom:15px;">Format: <code>Icon | Title | Subtitle | URL</code></p>
                    <textarea name="ah_mega_menu_json" rows="8" class="ah-input" style="font-family:monospace;"><?php echo esc_textarea($vals['ah_mega_menu_json']); ?></textarea>
                </div>
                <div class="ah-card">
                    <h3 style="margin-top:0;">Buying Hub Manager (MoveIQ Style)</h3>
                    <p style="font-size:12px; color:#64748b; margin-bottom:15px;">Manage the guides on your <strong>/buying</strong> page.</p>
                    <textarea name="ah_buying_guides_json" rows="8" class="ah-input" style="font-family:monospace;"><?php echo esc_textarea(get_option('ah_buying_guides_json', "")); ?></textarea>
                </div>
            </div>

            <div style="margin-top:30px;">
                <button type="submit" name="ah_save_settings" class="button button-primary" style="height:45px; padding:0 40px; font-weight:700;">Save All Settings</button>
            </div>
        </form>
    </div>
    <?php
}
