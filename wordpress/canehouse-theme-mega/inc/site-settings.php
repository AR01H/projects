<?php
/**
 * CANEHOUSE — Global Site Settings
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS FILE EXISTS:
 *   Previously footer settings (phone, social links, copyright) were stored
 *   as post-meta on the Home page. That meant inner pages (Privacy Policy etc.)
 *   couldn't read them. This file moves ALL site-wide settings into wp_options
 *   so every page (header, footer, contact form) reads from ONE place.
 *
 * WHAT IT DOES:
 *   1. Adds "🌿 Cane House → ⚙️ Site Settings" admin page
 *   2. Stores: logo, phone, whatsapp, social links, footer links, copyright,
 *              address, email, google maps embed, marquee, franchise locations
 *   3. Provides ch_opt($key, $fallback) helper used by header/footer/pages
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── DEFAULTS ──────────────────────────────────────────────────────────────────
function ch_opt_defaults() {
    return array(
        // Brand
        'site_name'          => 'The Cane House Mega',
        'tagline'            => 'Pressed Fresh. Served Cool.',
        'phone'              => '+44 7887 699 208',
        'whatsapp'           => '447887699208',
        'email'              => 'hello@thecanehouse.co.uk',
        'website'            => 'www.thecanehouse.co.uk',
        'address'            => 'Available across the UK',
        // Social
        'social_ig'          => '#',
        'social_fb'          => '#',
        'social_tt'          => '#',
        'social_yt'          => '#',
        // Footer
        'footer_copyright'   => '© 2025 The Cane House. Pressed Fresh. Served Cool.',
        'footer_desc'        => 'Fresh sugarcane juice pressed live, served cool. No added sugar, no preservatives — just pure natural refreshment wherever you are.',
        'footer_policy_links'=> "Privacy Policy\nTerms & Conditions\nRefund Policy",
        // Map
        'maps_embed'         => 'https://www.google.com/maps/embed?pb=!1m23!1m12!1m3!1d120560.61893157221!2d73.17017714511401!3d19.21618509484755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!4m8!3e6!4m0!4m5!1s0x3be792574416f8f3%3A0x7663c40ae0d632a6!2sshanti+sagar+resort+map!3m2!1d19.2161984!2d73.2402176!5e0!3m2!1sen!2sin!4v1499686069577',
        // Marquee
        'marquee'            => 'Pressed Fresh ✦ Served Cool ✦ No Added Sugar ✦ No Preservatives ✦ Pressed Live ✦ Natural Goodness ✦ Build Your Juice ✦ Events & Hire',
        // Franchise locations
        'franchise_locations'=> "London Central\nManchester Hub\nBirmingham West\nLeeds North\nGlasgow Fresh\nCardiff Bay",
        // Header notice (optional banner)
        'header_notice'      => '',
        'header_notice_on'   => '0',
    );
}



// ── SAVE OPTIONS ──────────────────────────────────────────────────────────────
function ch_save_options() {
    if (!isset($_POST['ch_settings_nonce'])) return;
    if (!wp_verify_nonce($_POST['ch_settings_nonce'], 'ch_save_settings')) return;
    if (!current_user_can('manage_options')) return;

    $fields = array_keys(ch_opt_defaults());
    foreach ($fields as $key) {
        if (isset($_POST['ch_' . $key])) {
            $val = in_array($key, array('maps_embed','footer_desc','footer_policy_links','franchise_locations','marquee','address'))
                ? sanitize_textarea_field($_POST['ch_' . $key])
                : sanitize_text_field($_POST['ch_' . $key]);
            update_option('ch_site_' . $key, $val);
        }
    }
    // Checkbox
    update_option('ch_site_header_notice_on', isset($_POST['ch_header_notice_on']) ? '1' : '0');
}

// ── ADMIN PAGE ────────────────────────────────────────────────────────────────
add_action('admin_menu', function() {
    add_submenu_page(
        'canehouse-settings',
        '⚙️ Site Settings',
        '⚙️ Site Settings',
        'manage_options',
        'ch-site-settings',
        'ch_site_settings_page'
    );
});

function ch_site_settings_page() {
    ch_save_options();
    if (isset($_POST['ch_settings_nonce'])) {
        echo '<div class="notice notice-success is-dismissible"><p>✅ Settings saved successfully!</p></div>';
    }
    ?>
    <div class="wrap">
    <h1>⚙️ The Cane House — Site Settings</h1>
    <p style="color:#666;">These settings apply to <strong>every page</strong> — header, footer, contact details everywhere.</p>
    <form method="post">
    <?php wp_nonce_field('ch_save_settings','ch_settings_nonce'); ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:20px;">

      <!-- LEFT COLUMN -->
      <div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:20px;">
          <h2 style="margin-top:0;border-bottom:2px solid #c8e830;padding-bottom:8px;">📞 Contact Details</h2>
          <table class="form-table" style="margin:0;">
            <tr><th>Phone Number</th><td><input type="text" name="ch_phone" value="<?php echo esc_attr(ch_opt('phone')); ?>" class="regular-text"><br><small>Shown in footer, contact section, franchise</small></td></tr>
            <tr><th>WhatsApp Number<br><small>(digits only)</small></th><td><input type="text" name="ch_whatsapp" value="<?php echo esc_attr(ch_opt('whatsapp')); ?>" class="regular-text"><br><small>e.g. 447887699208 — used for floating WhatsApp button</small></td></tr>
            <tr><th>Email Address</th><td><input type="text" name="ch_email" value="<?php echo esc_attr(ch_opt('email')); ?>" class="regular-text"></td></tr>
            <tr><th>Website URL</th><td><input type="text" name="ch_website" value="<?php echo esc_attr(ch_opt('website')); ?>" class="regular-text"></td></tr>
            <tr><th>Address / Coverage</th><td><textarea name="ch_address" rows="2" class="regular-text"><?php echo esc_textarea(ch_opt('address')); ?></textarea></td></tr>
          </table>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:20px;">
          <h2 style="margin-top:0;border-bottom:2px solid #c8e830;padding-bottom:8px;">📱 Social Media Links</h2>
          <p style="color:#888;font-size:13px;">Paste full URLs. Leave as # if not used.</p>
          <table class="form-table" style="margin:0;">
            <tr><th>📸 Instagram</th><td><input type="text" name="ch_social_ig" value="<?php echo esc_attr(ch_opt('social_ig')); ?>" class="regular-text"></td></tr>
            <tr><th>📘 Facebook</th><td><input type="text" name="ch_social_fb" value="<?php echo esc_attr(ch_opt('social_fb')); ?>" class="regular-text"></td></tr>
            <tr><th>🎵 TikTok</th><td><input type="text" name="ch_social_tt" value="<?php echo esc_attr(ch_opt('social_tt')); ?>" class="regular-text"></td></tr>
            <tr><th>▶️ YouTube</th><td><input type="text" name="ch_social_yt" value="<?php echo esc_attr(ch_opt('social_yt')); ?>" class="regular-text"></td></tr>
          </table>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:20px;">
          <h2 style="margin-top:0;border-bottom:2px solid #c8e830;padding-bottom:8px;">📢 Header Notice Banner</h2>
          <p style="color:#888;font-size:13px;">Optional announcement bar shown at the very top of every page.</p>
          <table class="form-table" style="margin:0;">
            <tr><th>Enable Banner</th><td><input type="checkbox" name="ch_header_notice_on" value="1" <?php checked(ch_opt('header_notice_on'),'1'); ?>> Show announcement banner</td></tr>
            <tr><th>Banner Text</th><td><input type="text" name="ch_header_notice" value="<?php echo esc_attr(ch_opt('header_notice')); ?>" class="large-text" placeholder="e.g. 🎉 Now available in Manchester! Book now →"></td></tr>
          </table>
        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:20px;">
          <h2 style="margin-top:0;border-bottom:2px solid #c8e830;padding-bottom:8px;">🔻 Footer Settings</h2>
          <table class="form-table" style="margin:0;">
            <tr><th>Copyright Text</th><td><input type="text" name="ch_footer_copyright" value="<?php echo esc_attr(ch_opt('footer_copyright')); ?>" class="large-text"></td></tr>
            <tr><th>Footer Description</th><td><textarea name="ch_footer_desc" rows="3" class="large-text"><?php echo esc_textarea(ch_opt('footer_desc')); ?></textarea></td></tr>
            <tr><th>Policy Page Links<br><small>(one per line)</small></th>
                <td><textarea name="ch_footer_policy_links" rows="4" class="large-text"><?php echo esc_textarea(ch_opt('footer_policy_links')); ?></textarea>
                <br><small>These auto-link to pages with matching titles (Privacy Policy, Terms & Conditions, Refund Policy)</small></td></tr>
          </table>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:20px;">
          <h2 style="margin-top:0;border-bottom:2px solid #c8e830;padding-bottom:8px;">🗺️ Google Maps Embed</h2>
          <textarea name="ch_maps_embed" rows="4" style="width:100%;font-size:11px;"><?php echo esc_textarea(ch_opt('maps_embed')); ?></textarea>
          <small>Paste the full Google Maps embed URL (src="..." value only)</small>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin-bottom:20px;">
          <h2 style="margin-top:0;border-bottom:2px solid #c8e830;padding-bottom:8px;">📜 Marquee Strip Text</h2>
          <input type="text" name="ch_marquee" value="<?php echo esc_attr(ch_opt('marquee')); ?>" style="width:100%">
          <small>Separate items with ✦ symbol</small>
        </div>

        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:20px;">
          <h2 style="margin-top:0;border-bottom:2px solid #c8e830;padding-bottom:8px;">📍 Franchise Locations</h2>
          <textarea name="ch_franchise_locations" rows="8" style="width:100%;"><?php echo esc_textarea(ch_opt('franchise_locations')); ?></textarea>
          <small>One location per line</small>
        </div>
      </div>
    </div>

    <?php submit_button('💾 Save All Settings', 'primary large'); ?>
    </form>

    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:20px;margin-top:20px;">
      <h2 style="margin-top:0;">📖 What Each Setting Does</h2>
      <ul style="font-size:13px;line-height:2.2;columns:2;">
        <li><strong>Phone</strong> → Shows in footer, contact section, franchise section</li>
        <li><strong>WhatsApp</strong> → Powers the floating green button bottom-right</li>
        <li><strong>Email</strong> → Shown in contact section and footer</li>
        <li><strong>Social links</strong> → Footer social buttons (📸📘🎵▶️)</li>
        <li><strong>Header Notice</strong> → Announcement bar at very top of site</li>
        <li><strong>Footer Copyright</strong> → Bottom line of footer</li>
        <li><strong>Policy Links</strong> → Auto-links Privacy, Terms, Refund pages in footer</li>
        <li><strong>Google Maps</strong> → Map shown above contact section</li>
        <li><strong>Marquee</strong> → Scrolling text strip below hero</li>
        <li><strong>Franchise Locations</strong> → Scrolling locations in franchise section</li>
      </ul>
    </div>
    </div>
    <?php
}
