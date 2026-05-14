<?php
/**
 * Advaith Homes Advanced Meta Boxes
 * Synchronized with the Content Manager for "2-Way" editing.
 */

function ah_theme_add_post_meta_boxes() {
    add_meta_box(
        'ah_post_style_box',
        'Article Styling & Premium Fields',
        'ah_theme_render_post_style_box',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'ah_theme_add_post_meta_boxes');

function ah_theme_render_post_style_box($post) {
    // Fetch current values
    $style = get_post_meta($post->ID, 'ah_card_style', true) ?: 'standard';
    $priority = get_post_meta($post->ID, 'ah_post_type', true) ?: 'blog';
    $mini_info = get_post_meta($post->ID, 'ah_mini_info', true);
    $tag_text = get_post_meta($post->ID, 'ah_tag_text', true);
    $tag_color = get_post_meta($post->ID, 'ah_tag_color', true) ?: '#8b5cf6';
    $episode_id = get_post_meta($post->ID, 'ah_episode_id', true);
    $btn_label = get_post_meta($post->ID, 'ah_btn_label', true) ?: 'Read More';
    $sort_order = get_post_meta($post->ID, 'ah_sort_order', true) ?: '0';

    wp_nonce_field('ah_post_meta_save', 'ah_post_meta_nonce');
    ?>
    <style>
        .ah-meta-field { margin-bottom: 15px; }
        .ah-meta-label { display: block; font-weight: 700; font-size: 12px; margin-bottom: 5px; color: #1e293b; }
        .ah-meta-input { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; }
    </style>

    <div class="ah-meta-field">
        <label class="ah-meta-label">Display Style</label>
        <select name="ah_card_style" class="ah-meta-input">
            <option value="standard" <?php selected($style, 'standard'); ?>>Standard Blog Card</option>
            <option value="podcast" <?php selected($style, 'podcast'); ?>>Podcast / Tip Card (Rich UI)</option>
            <option value="mini" <?php selected($style, 'mini'); ?>>Mini Hint Card (Compact)</option>
        </select>
    </div>

    <div class="ah-meta-field">
        <label class="ah-meta-label">Post Type (Priority)</label>
        <label><input type="radio" name="ah_post_type" value="blog" <?php checked($priority, 'blog'); ?>> Standard</label><br>
        <label><input type="radio" name="ah_post_type" value="news" <?php checked($priority, 'news'); ?>> 🚨 Breaking News</label>
    </div>

    <div class="ah-meta-field">
        <label class="ah-meta-label">Mini Information (Summary)</label>
        <textarea name="ah_mini_info" class="ah-meta-input" rows="3"><?php echo esc_textarea($mini_info); ?></textarea>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
        <div class="ah-meta-field">
            <label class="ah-meta-label">Tag Text</label>
            <input type="text" name="ah_tag_text" value="<?php echo esc_attr($tag_text); ?>" class="ah-meta-input" placeholder="e.g. MOVING">
        </div>
        <div class="ah-meta-field">
            <label class="ah-meta-label">Tag Color</label>
            <input type="color" name="ah_tag_color" value="<?php echo esc_attr($tag_color); ?>" style="width:100%; height:35px; border:none; padding:0;">
        </div>
    </div>

    <div class="ah-meta-field">
        <label class="ah-meta-label">Episode ID / Ref (e.g. S9E6)</label>
        <input type="text" name="ah_episode_id" value="<?php echo esc_attr($episode_id); ?>" class="ah-meta-input">
    </div>

    <div class="ah-meta-field">
        <label class="ah-meta-label">Button Label</label>
        <input type="text" name="ah_btn_label" value="<?php echo esc_attr($btn_label); ?>" class="ah-meta-input">
    </div>

    <div class="ah-meta-field">
        <label class="ah-meta-label">Sort Order (Manual)</label>
        <input type="number" name="ah_sort_order" value="<?php echo esc_attr($sort_order); ?>" class="ah-meta-input">
    </div>
    <?php
}

function ah_theme_save_post_meta($post_id) {
    if (!isset($_POST['ah_post_meta_nonce']) || !wp_verify_nonce($_POST['ah_post_meta_nonce'], 'ah_post_meta_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    $fields = ['ah_card_style', 'ah_post_type', 'ah_mini_info', 'ah_tag_text', 'ah_tag_color', 'ah_episode_id', 'ah_btn_label', 'ah_sort_order'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, $_POST[$field]);
        }
    }
}
add_action('save_post', 'ah_theme_save_post_meta');
