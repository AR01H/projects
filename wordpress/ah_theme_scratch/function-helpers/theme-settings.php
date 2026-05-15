<?php
/**
 * Advaith Homes Elite Content Command Center
 * Features: AJAX Saving, Media Upload, Security Nonces, and Full Field Control.
 */

function ah_theme_add_admin_menu() {
    $parent_slug = 'advaith-homes';
    add_menu_page('Advaith Homes', 'Advaith Homes', 'manage_options', $parent_slug, 'ah_theme_dashboard_page', 'dashicons-admin-home', 3);
    add_submenu_page($parent_slug, 'Dashboard', 'Dashboard', 'manage_options', $parent_slug, 'ah_theme_dashboard_page');
    add_submenu_page($parent_slug, 'Command Center', 'Manage Articles', 'manage_options', 'ah-manager', 'ah_theme_content_manager_page');
    add_submenu_page($parent_slug, 'Site Settings', 'Site Settings', 'manage_options', 'ah-settings', 'ah_theme_settings_page');
    
    add_submenu_page($parent_slug, 'Properties', 'All Properties', 'edit_posts', 'edit.php?post_type=property');
    add_submenu_page($parent_slug, 'Services', 'All Services', 'edit_posts', 'edit.php?post_type=service');
    add_submenu_page($parent_slug, 'Contact Leads', 'All Messages', 'edit_posts', 'edit.php?post_type=inquiry');
}
add_action('admin_menu', 'ah_theme_add_admin_menu');

/**
 * AJAX Save Handler (Security + Speed)
 */
function ah_theme_ajax_save_content() {
    check_ajax_referer('ah_admin_nonce', 'security');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    $pid = intval($_POST['post_id']);
    wp_update_post([
        'ID' => $pid,
        'post_title' => sanitize_text_field($_POST['title']),
    ]);

    // Update Meta Fields
    update_post_meta($pid, 'ah_mini_info', sanitize_textarea_field($_POST['summary']));
    update_post_meta($pid, 'ah_tag_text', sanitize_text_field($_POST['tag']));
    update_post_meta($pid, 'ah_tag_color', sanitize_hex_color($_POST['color']));
    update_post_meta($pid, 'ah_sort_order', intval($_POST['sort']));
    update_post_meta($pid, 'ah_card_style', sanitize_text_field($_POST['style']));
    update_post_meta($pid, 'ah_post_type', sanitize_text_field($_POST['type']));
    update_post_meta($pid, 'display_status', sanitize_text_field($_POST['status']));

    // Update Featured Image if provided
    if (isset($_POST['image_id']) && !empty($_POST['image_id'])) {
        set_post_thumbnail($pid, intval($_POST['image_id']));
    }

    wp_send_json_success(['message' => 'Saved Successfully!']);
}
add_action('wp_ajax_ah_save_content', 'ah_theme_ajax_save_content');

/**
 * 1. Content Command Center (The Upgraded Manager)
 */
function ah_theme_content_manager_page() {
    $query = new WP_Query(['post_type' => 'post', 'posts_per_page' => -1, 'meta_key' => 'ah_sort_order', 'orderby' => 'meta_value_num', 'order' => 'ASC']);
    wp_enqueue_media(); // For image upload
    ?>
    <div class="ah-admin-wrap" style="padding: 30px;">
        <div class="ah-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:40px;">
            <div>
                <h1 class="ah-title" style="margin:0; font-size:28px;">Content Command Center</h1>
                <p style="color:#64748b; margin-top:5px;">Securely manage your articles, news, and podcasts with live AJAX updates.</p>
            </div>
            <a href="post-new.php" class="button button-primary" style="height:45px; line-height:43px; padding:0 30px; background:#0f172a; border:none; font-weight:700;">+ Add New Article</a>
        </div>

        <div class="ah-card" style="padding:0; overflow:hidden; border-radius:16px; border:1px solid #e2e8f0; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
            <table class="ah-table" style="width:100%; border-collapse:collapse;">
                <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <tr>
                        <th style="padding:15px 20px; text-align:center; width:60px;">Sort</th>
                        <th style="padding:15px 20px; text-align:left; width:80px;">Image</th>
                        <th style="padding:15px 20px; text-align:left;">Content Details</th>
                        <th style="padding:15px 20px; text-align:left;">Style/Type</th>
                        <th style="padding:15px 20px; text-align:left;">Tag</th>
                        <th style="padding:15px 20px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); 
                        $pid = get_the_ID();
                        $meta = get_post_custom($pid);
                        $sort = isset($meta['ah_sort_order'][0]) ? $meta['ah_sort_order'][0] : '0';
                        $tag = isset($meta['ah_tag_text'][0]) ? $meta['ah_tag_text'][0] : 'Blog';
                        $color = isset($meta['ah_tag_color'][0]) ? $meta['ah_tag_color'][0] : '#8b5cf6';
                        $style = isset($meta['ah_card_style'][0]) ? $meta['ah_card_style'][0] : 'standard';
                        $type = isset($meta['ah_post_type'][0]) ? $meta['ah_post_type'][0] : 'blog';
                        $summary = isset($meta['ah_mini_info'][0]) ? $meta['ah_mini_info'][0] : '';
                    ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:15px 20px; text-align:center;"><strong>#<?php echo $sort; ?></strong></td>
                            <td style="padding:15px 20px;">
                                <?php if (has_post_thumbnail()) : the_post_thumbnail([50,50], ['style'=>'border-radius:8px;']); else: ?>
                                    <div style="width:50px; height:50px; background:#f1f5f9; border-radius:8px; display:flex; align-items:center; justify-content:center;">🖼️</div>
                                <?php endif; ?>
                            </td>
                            <td style="padding:15px 20px;">
                                <div style="font-weight:700; color:#1e293b;"><?php the_title(); ?></div>
                                <div style="font-size:11px; color:#64748b; margin-top:4px;"><?php echo wp_trim_words($summary, 8); ?></div>
                            </td>
                            <td style="padding:15px 20px;">
                                <span style="font-size:10px; font-weight:700; text-transform:uppercase; color:#94a3b8;"><?php echo $style; ?> / <?php echo $type; ?></span>
                            </td>
                            <td style="padding:15px 20px;">
                                <span style="background:<?php echo $color; ?>; color:white; padding:3px 10px; border-radius:4px; font-size:10px; font-weight:800;"><?php echo $tag; ?></span>
                            </td>
                            <td style="padding:15px 20px; text-align:right;">
                                <button class="button ah-open-modal" 
                                    data-id="<?php echo $pid; ?>" 
                                    data-title="<?php echo esc_attr(get_the_title()); ?>" 
                                    data-summary="<?php echo esc_attr($summary); ?>" 
                                    data-tag="<?php echo esc_attr($tag); ?>" 
                                    data-color="<?php echo esc_attr($color); ?>" 
                                    data-sort="<?php echo esc_attr($sort); ?>"
                                    data-style="<?php echo esc_attr($style); ?>"
                                    data-type="<?php echo esc_attr($type); ?>"
                                    style="border-radius:6px;">Quick Edit</button>
                                <a href="post.php?post=<?php echo $pid; ?>&action=edit" class="button" style="border-radius:6px;">Full Editor</a>
                            </td>
                        </tr>
                    <?php endwhile; wp_reset_postdata(); endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ELITE QUICK EDIT MODAL -->
    <div id="ah-quick-modal" class="ah-modal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.6); align-items:center; justify-content:center;">
        <div class="ah-modal-content" style="background:white; width:650px; border-radius:20px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
            <div class="ah-modal-header" style="padding:20px 30px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                <h2 style="margin:0; font-size:18px;">Edit Content Profile</h2>
                <span class="ah-close" style="cursor:pointer; font-size:24px; color:#94a3b8;">&times;</span>
            </div>
            
            <form id="ah-quick-form" class="ah-modal-body" style="padding:30px;">
                <?php wp_nonce_field('ah_admin_nonce', 'security'); ?>
                <input type="hidden" name="post_id" id="m-id">
                <input type="hidden" name="image_id" id="m-image-id">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div class="ah-field-group">
                        <label class="ah-label" style="display:block; font-weight:700; font-size:12px; color:#64748b; margin-bottom:8px;">Article Title</label>
                        <input type="text" name="title" id="m-title" class="ah-input" style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" required>
                    </div>
                    <div class="ah-field-group">
                        <label class="ah-label" style="display:block; font-weight:700; font-size:12px; color:#64748b; margin-bottom:8px;">Featured Image</label>
                        <button type="button" id="m-upload-btn" class="button" style="width:100%; height:45px;">Change Image</button>
                    </div>
                </div>

                <div class="ah-field-group" style="margin-bottom:20px;">
                    <label class="ah-label" style="display:block; font-weight:700; font-size:12px; color:#64748b; margin-bottom:8px;">Mini Description (For Cards)</label>
                    <textarea name="summary" id="m-summary" class="ah-input" rows="2" style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;"></textarea>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div class="ah-field-group">
                        <label class="ah-label">Display Style</label>
                        <select name="style" id="m-style" class="ah-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
                            <option value="standard">Standard Card</option>
                            <option value="podcast">Podcast Card</option>
                            <option value="mini">Mini Hint</option>
                        </select>
                    </div>
                    <div class="ah-field-group">
                        <label class="ah-label">Post Type</label>
                        <select name="type" id="m-type" class="ah-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
                            <option value="blog">Standard Blog</option>
                            <option value="news">Breaking News</option>
                        </select>
                    </div>
                    <div class="ah-field-group">
                        <label class="ah-label">Sort Order</label>
                        <input type="number" name="sort" id="m-sort" class="ah-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                    <div class="ah-field-group">
                        <label class="ah-label">Tag Text</label>
                        <input type="text" name="tag" id="m-tag" class="ah-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
                    </div>
                    <div class="ah-field-group">
                        <label class="ah-label">Tag Color</label>
                        <input type="color" name="color" id="m-color" style="width:100%; height:45px; border:none; padding:5px; border-radius:8px; background:#f8fafc;">
                    </div>
                </div>

                <div id="m-feedback" style="margin-top:20px; padding:10px; border-radius:8px; display:none;"></div>

                <div class="ah-modal-footer" style="margin-top:30px; text-align:right; border-top:1px solid #f1f5f9; padding-top:20px; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" class="button ah-close-btn">Cancel</button>
                    <button type="submit" id="ah-save-btn" class="button button-primary" style="background:#0f172a; color:white; border:none; padding:10px 30px; font-weight:700;">Save Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($){
        // Modal Open
        $('.ah-open-modal').click(function(){
            var d = $(this).data();
            $('#m-id').val(d.id); $('#m-title').val(d.title); $('#m-summary').val(d.summary); $('#m-tag').val(d.tag); $('#m-color').val(d.color); $('#m-sort').val(d.sort); $('#m-style').val(d.style); $('#m-type').val(d.type);
            $('#m-feedback').hide();
            $('#ah-quick-modal').css('display','flex').hide().fadeIn(200);
        });

        // Close Modal
        $('.ah-close, .ah-close-btn').click(function(){ $('#ah-quick-modal').fadeOut(200); });

        // Media Uploader
        $('#m-upload-btn').click(function(e){
            e.preventDefault();
            var image = wp.media({ title: 'Select Article Image', multiple: false }).open().on('select', function(){
                var uploaded = image.state().get('selection').first().toJSON();
                $('#m-image-id').val(uploaded.id);
                $('#m-upload-btn').text('Image Selected: ' + uploaded.filename).css('color', '#10b981');
            });
        });

        // AJAX Save
        $('#ah-quick-form').submit(function(e){
            e.preventDefault();
            var btn = $('#ah-save-btn');
            btn.text('Saving...').prop('disabled', true);
            
            var data = $(this).serialize() + '&action=ah_save_content';
            
            $.post(ajaxurl, data, function(res){
                if(res.success){
                    $('#m-feedback').text(res.data.message).css({'background':'#f0fdf4','color':'#166534','display':'block'});
                    setTimeout(function(){ location.reload(); }, 800);
                } else {
                    alert('Error saving content.');
                }
            });
        });
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
    <div class="ah-admin-wrap" style="padding:40px;">
        <h1 class="ah-title" style="font-size:32px;">Site Overview Dashboard</h1>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:30px; margin-top:30px;">
            <div class="ah-stat-card" style="background:white; padding:40px; border-radius:24px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); border-top:6px solid var(--accent);"><div class="ah-stat-num" style="font-size:48px; font-weight:800; color:#0f172a;"><?php echo $total_leads; ?></div><div class="ah-stat-label" style="font-size:14px; font-weight:700; color:#64748b; margin-top:10px; text-transform:uppercase;">Total Client Leads</div></div>
            <div class="ah-stat-card" style="background:white; padding:40px; border-radius:24px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); border-top:6px solid #ef4444;"><div class="ah-stat-num" style="font-size:48px; font-weight:800; color:#ef4444;"><?php echo $new_leads; ?></div><div class="ah-stat-label" style="font-size:14px; font-weight:700; color:#64748b; margin-top:10px; text-transform:uppercase;">Unread Messages</div></div>
        </div>
    </div>
    <?php
}

/**
 * 3. Settings Page (Expanded)
 */
function ah_theme_settings_page() {
    $options = ['ah_site_logo', 'ah_company_name', 'ah_contact_phone', 'ah_contact_email', 'ah_consultation_url', 'ah_mega_menu_json', 'ah_buying_guides_json'];
    if (isset($_POST['ah_save_settings'])) {
        foreach ($options as $opt) { if (isset($_POST[$opt])) update_option($opt, $_POST[$opt]); }
        echo '<div class="updated"><p>All Site Settings Saved Successfully!</p></div>';
    }
    $vals = [];
    foreach ($options as $opt) { $vals[$opt] = get_option($opt, ''); }
    ?>
    <div class="ah-admin-wrap" style="padding:40px;">
        <h1 class="ah-title" style="font-size:32px;">Global Site Settings</h1>
        <form method="post" style="margin-top:30px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
                <div class="ah-card" style="background:white; padding:30px; border-radius:16px; border:1px solid #e2e8f0;">
                    <h3>Branding</h3>
                    <div class="ah-field-group"><label class="ah-label">Company Name</label><input type="text" name="ah_company_name" value="<?php echo esc_attr($vals['ah_company_name'] ?: 'Advaith Homes'); ?>" class="ah-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;"></div>
                    <div class="ah-field-group" style="margin-top:15px;"><label class="ah-label">Logo URL</label><input type="text" name="ah_site_logo" value="<?php echo esc_attr($vals['ah_site_logo']); ?>" class="ah-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;"></div>
                </div>
                <div class="ah-card" style="background:white; padding:30px; border-radius:16px; border:1px solid #e2e8f0;">
                    <h3>Contact Information</h3>
                    <div class="ah-field-group"><label class="ah-label">Email Address</label><input type="email" name="ah_contact_email" value="<?php echo esc_attr($vals['ah_contact_email']); ?>" class="ah-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;"></div>
                    <div class="ah-field-group" style="margin-top:15px;"><label class="ah-label">Phone Number</label><input type="text" name="ah_contact_phone" value="<?php echo esc_attr($vals['ah_contact_phone']); ?>" class="ah-input" style="width:100%; padding:10px; border-radius:8px; border:1px solid #e2e8f0;"></div>
                </div>
            </div>
            <div style="margin-top:30px; text-align:right;"><?php submit_button('Save Site Profile'); ?></div>
        </form>
    </div>
    <?php
}
