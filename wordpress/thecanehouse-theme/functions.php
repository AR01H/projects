<?php
/**
 * The Cane House - functions.php
 * All theme setup, custom fields, menus, and settings
 */

// ─── THEME SETUP ────────────────────────────────────────────────────────────
function canehouse_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 80,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array('search-form','comment-form','gallery','caption'));
    add_theme_support('editor-styles');

    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'canehouse'),
        'footer'  => __('Footer Navigation', 'canehouse'),
    ));
}
add_action('after_setup_theme', 'canehouse_setup');

// ─── ENQUEUE SCRIPTS & STYLES ────────────────────────────────────────────────
function canehouse_enqueue() {
    // Google Fonts
    wp_enqueue_style('canehouse-fonts',
        'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;0,800;0,900;1,700&family=Poppins:wght@300;400;500;600;700;800&display=swap',
        array(), null
    );
    // Main CSS
    wp_enqueue_style('canehouse-main', get_template_directory_uri() . '/assets/css/main.css', array(), '1.0.0');
    // Theme style.css (just for WP identification – no extra styles needed)
    wp_enqueue_style('canehouse-style', get_stylesheet_uri(), array(), '1.0.0');
    // JS
    wp_enqueue_script('canehouse-script',
        get_template_directory_uri() . '/assets/js/script.js',
        array(), '1.0.0', true
    );
}
add_action('wp_enqueue_scripts', 'canehouse_enqueue');

// ─── CUSTOM META BOXES (drag-and-drop editable sections) ─────────────────────
function canehouse_add_meta_boxes() {

    // Hero Section
    add_meta_box('canehouse_hero', '🏠 Hero Section', 'canehouse_hero_callback', 'page', 'normal', 'high');
    // How To Order Section
    add_meta_box('canehouse_order', '📋 How To Order Section', 'canehouse_order_callback', 'page', 'normal', 'high');
    // Reviews Section
    add_meta_box('canehouse_reviews', '⭐ Reviews Section', 'canehouse_reviews_callback', 'page', 'normal', 'high');
    // Hire Section
    add_meta_box('canehouse_hire', '🎪 Events & Hire Section', 'canehouse_hire_callback', 'page', 'normal', 'high');
    // FAQ Section
    add_meta_box('canehouse_faq', '❓ FAQ Section', 'canehouse_faq_callback', 'page', 'normal', 'high');
    // Contact Section
    add_meta_box('canehouse_contact', '📞 Contact Details', 'canehouse_contact_callback', 'page', 'normal', 'high');
    // Footer
    add_meta_box('canehouse_footer', '🔻 Footer Settings', 'canehouse_footer_callback', 'page', 'normal', 'high');
}
add_action('add_meta_boxes', 'canehouse_add_meta_boxes');

// ─── HERO META BOX ───────────────────────────────────────────────────────────
function canehouse_hero_callback($post) {
    wp_nonce_field('canehouse_save_meta', 'canehouse_nonce');
    $tag     = get_post_meta($post->ID, '_hero_tag', true)     ?: '100% Natural · No Additives · Pressed Live';
    $title1  = get_post_meta($post->ID, '_hero_title1', true)  ?: 'Pressed Fresh.';
    $title2  = get_post_meta($post->ID, '_hero_title2', true)  ?: 'Served Cool.';
    $sub     = get_post_meta($post->ID, '_hero_subtitle', true)?: 'The Cane House';
    $desc    = get_post_meta($post->ID, '_hero_desc', true)    ?: 'Fresh sugarcane juice pressed live and blended with authentic cold-pressed fruit extracts & natural botanicals. Build your perfect juice — your way.';
    $btn1txt = get_post_meta($post->ID, '_hero_btn1_text', true)?: '🥤 Build Your Juice';
    $btn1url = get_post_meta($post->ID, '_hero_btn1_url', true) ?: '#build';
    $btn2txt = get_post_meta($post->ID, '_hero_btn2_text', true)?: 'Hire for Events →';
    $btn2url = get_post_meta($post->ID, '_hero_btn2_url', true) ?: '#hire';
    ?>
    <table class="form-table">
        <tr><th>Top Tag Text</th><td><input type="text" name="_hero_tag" value="<?php echo esc_attr($tag); ?>" style="width:100%"></td></tr>
        <tr><th>Main Title Line 1 (white)</th><td><input type="text" name="_hero_title1" value="<?php echo esc_attr($title1); ?>" style="width:100%"></td></tr>
        <tr><th>Main Title Line 2 (lime/accent)</th><td><input type="text" name="_hero_title2" value="<?php echo esc_attr($title2); ?>" style="width:100%"></td></tr>
        <tr><th>Subtitle</th><td><input type="text" name="_hero_subtitle" value="<?php echo esc_attr($sub); ?>" style="width:100%"></td></tr>
        <tr><th>Description</th><td><textarea name="_hero_desc" rows="3" style="width:100%"><?php echo esc_textarea($desc); ?></textarea></td></tr>
        <tr><th>Button 1 Text</th><td><input type="text" name="_hero_btn1_text" value="<?php echo esc_attr($btn1txt); ?>" style="width:100%"></td></tr>
        <tr><th>Button 1 URL</th><td><input type="text" name="_hero_btn1_url" value="<?php echo esc_attr($btn1url); ?>" style="width:100%"></td></tr>
        <tr><th>Button 2 Text</th><td><input type="text" name="_hero_btn2_text" value="<?php echo esc_attr($btn2txt); ?>" style="width:100%"></td></tr>
        <tr><th>Button 2 URL</th><td><input type="text" name="_hero_btn2_url" value="<?php echo esc_attr($btn2url); ?>" style="width:100%"></td></tr>
    </table>
    <?php
}

// ─── HOW TO ORDER META BOX ───────────────────────────────────────────────────
function canehouse_order_callback($post) {
    $steps = get_post_meta($post->ID, '_order_steps', true);
    if (empty($steps)) {
        $steps = array(
            array('num'=>'1','emoji'=>'📏','title'=>'Select Size',   'desc'=>'Choose from Mini 250ml right up to Group Sharing 1.5L'),
            array('num'=>'2','emoji'=>'🌾','title'=>'Select Cane',   'desc'=>'Yellow Cane (light golden) or Red Cane (rich amber, +£0.50)'),
            array('num'=>'3','emoji'=>'🥤','title'=>'Select Texture','desc'=>'Classic No Peel for a grassy taste, or Smooth With Peel for a cleaner finish (+£0.50)'),
            array('num'=>'4','emoji'=>'🍋','title'=>'Select Flavour','desc'=>'Pure Cane (free), Citrus Blends (+£0.50) or Tropical Blends (+£1.00)'),
            array('num'=>'5','emoji'=>'🎉','title'=>'Enjoy!',         'desc'=>'Served chilled, no ice unless requested — pure fresh natural goodness'),
        );
    }
    ?>
    <p><strong>Edit the 5 order steps below:</strong></p>
    <?php foreach($steps as $i => $step): ?>
    <div style="border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:4px;">
        <strong>Step <?php echo $i+1; ?></strong><br><br>
        <label>Emoji: <input type="text" name="_order_steps[<?php echo $i; ?>][emoji]" value="<?php echo esc_attr($step['emoji']); ?>" style="width:80px"></label> &nbsp;
        <label>Title: <input type="text" name="_order_steps[<?php echo $i; ?>][title]" value="<?php echo esc_attr($step['title']); ?>" style="width:200px"></label><br><br>
        <label>Description:<br><textarea name="_order_steps[<?php echo $i; ?>][desc]" rows="2" style="width:100%"><?php echo esc_textarea($step['desc']); ?></textarea></label>
    </div>
    <?php endforeach; ?>
    <?php
}

// ─── REVIEWS META BOX ────────────────────────────────────────────────────────
function canehouse_reviews_callback($post) {
    $reviews = get_post_meta($post->ID, '_reviews', true);
    if (empty($reviews)) {
        $reviews = array(
            array('name'=>'Sarah Johnson','role'=>'Verified Customer','text'=>'The freshest cane juice I\'ve ever had in the UK. The ginger blend is absolutely life-changing!','avatar'=>'https://i.pravatar.cc/300?u=1'),
            array('name'=>'Mohammed Ali', 'role'=>'Verified Customer','text'=>'Reminds me of home! Pressed live right in front of you. No added sugar but so naturally sweet.','avatar'=>'https://i.pravatar.cc/300?u=2'),
            array('name'=>'Emma Wright',  'role'=>'Event Client',     'text'=>'We hired The Cane House for our wedding and it was the highlight! Guests loved the live pressing experience.','avatar'=>'https://i.pravatar.cc/300?u=3'),
        );
    }
    ?>
    <p><strong>Edit reviews below (3 reviews):</strong></p>
    <?php foreach($reviews as $i => $r): ?>
    <div style="border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:4px;">
        <strong>Review <?php echo $i+1; ?></strong><br><br>
        <label>Name: <input type="text" name="_reviews[<?php echo $i; ?>][name]" value="<?php echo esc_attr($r['name']); ?>" style="width:200px"></label> &nbsp;
        <label>Role: <input type="text" name="_reviews[<?php echo $i; ?>][role]" value="<?php echo esc_attr($r['role']); ?>" style="width:200px"></label><br><br>
        <label>Avatar URL: <input type="text" name="_reviews[<?php echo $i; ?>][avatar]" value="<?php echo esc_attr($r['avatar']); ?>" style="width:100%"></label><br><br>
        <label>Review Text:<br><textarea name="_reviews[<?php echo $i; ?>][text]" rows="2" style="width:100%"><?php echo esc_textarea($r['text']); ?></textarea></label>
    </div>
    <?php endforeach; ?>
    <?php
}

// ─── HIRE META BOX ───────────────────────────────────────────────────────────
function canehouse_hire_callback($post) {
    $title = get_post_meta($post->ID, '_hire_title', true) ?: 'Bring Us to Your Event';
    $desc  = get_post_meta($post->ID, '_hire_desc', true)  ?: 'Elevate your celebration with our premium live-pressed sugarcane juice experience.';
    $cards = get_post_meta($post->ID, '_hire_cards', true);
    if (empty($cards)) {
        $cards = array(
            array('icon'=>'💒','title'=>'Weddings',        'desc'=>'Add a traditional and healthy touch to your big day.','list'=>"Reception Drinks\nMehndi & Sangeet\nPost-Ceremony Refreshment"),
            array('icon'=>'🏢','title'=>'Corporate Events','desc'=>'Perfect for office parties, wellness days, and conferences.','list'=>"Office Wellness Days\nProduct Launches\nExhibitions & Fairs"),
            array('icon'=>'🎉','title'=>'Private Parties', 'desc'=>'From birthdays to garden parties, we bring the vibe.','list'=>"Birthday Parties\nCommunity Festivals\nGarden & BBQ Events"),
        );
    }
    ?>
    <label>Section Title: <input type="text" name="_hire_title" value="<?php echo esc_attr($title); ?>" style="width:100%"></label><br><br>
    <label>Description:<br><textarea name="_hire_desc" rows="2" style="width:100%"><?php echo esc_textarea($desc); ?></textarea></label><br><br>
    <p><strong>Event Cards:</strong></p>
    <?php foreach($cards as $i => $c): ?>
    <div style="border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:4px;">
        <strong>Card <?php echo $i+1; ?></strong><br><br>
        <label>Icon/Emoji: <input type="text" name="_hire_cards[<?php echo $i; ?>][icon]" value="<?php echo esc_attr($c['icon']); ?>" style="width:80px"></label> &nbsp;
        <label>Title: <input type="text" name="_hire_cards[<?php echo $i; ?>][title]" value="<?php echo esc_attr($c['title']); ?>" style="width:200px"></label><br><br>
        <label>Description:<br><textarea name="_hire_cards[<?php echo $i; ?>][desc]" rows="2" style="width:100%"><?php echo esc_textarea($c['desc']); ?></textarea></label><br><br>
        <label>List Items (one per line):<br><textarea name="_hire_cards[<?php echo $i; ?>][list]" rows="3" style="width:100%"><?php echo esc_textarea($c['list']); ?></textarea></label>
    </div>
    <?php endforeach; ?>
    <?php
}

// ─── FAQ META BOX ────────────────────────────────────────────────────────────
function canehouse_faq_callback($post) {
    $faqs = get_post_meta($post->ID, '_faqs', true);
    if (empty($faqs)) {
        $faqs = array(
            array('q'=>'Do you add any sugar or preservatives?',    'a'=>'No, absolutely not. Our sugarcane juice is 100% natural, pressed live from the stalk.'),
            array('q'=>'How long does the juice stay fresh?',       'a'=>'Fresh sugarcane juice is best enjoyed immediately. If kept chilled, up to 24 hours.'),
            array('q'=>'What events can I hire you for?',           'a'=>'We cater for weddings, birthdays, corporate gatherings, festivals, and community events across the UK.'),
            array('q'=>'Is your sugarcane juice sustainable?',      'a'=>'Yes! Sugarcane is a highly sustainable crop. Our leftover fibre (bagasse) is biodegradable.'),
        );
    }
    ?>
    <p><strong>Edit FAQ items below:</strong></p>
    <?php foreach($faqs as $i => $faq): ?>
    <div style="border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:4px;">
        <strong>FAQ <?php echo $i+1; ?></strong><br><br>
        <label>Question:<br><input type="text" name="_faqs[<?php echo $i; ?>][q]" value="<?php echo esc_attr($faq['q']); ?>" style="width:100%"></label><br><br>
        <label>Answer:<br><textarea name="_faqs[<?php echo $i; ?>][a]" rows="2" style="width:100%"><?php echo esc_textarea($faq['a']); ?></textarea></label>
    </div>
    <?php endforeach; ?>
    <?php
}

// ─── CONTACT META BOX ────────────────────────────────────────────────────────
function canehouse_contact_callback($post) {
    $phone   = get_post_meta($post->ID, '_contact_phone', true)   ?: '+44 7887 699 208';
    $website = get_post_meta($post->ID, '_contact_website', true) ?: 'www.thecanehouse.co.uk';
    $events  = get_post_meta($post->ID, '_contact_events', true)  ?: 'Available across the UK for events, weddings & community gatherings';
    $franchise = get_post_meta($post->ID, '_contact_franchise', true) ?: 'Franchise enquiries warmly welcomed — reach out today';
    $wa      = get_post_meta($post->ID, '_contact_whatsapp', true)?: '447887699208';
    $api     = get_post_meta($post->ID, '_contact_api', true)     ?: '';
    ?>
    <table class="form-table">
        <tr><th>Phone Number</th><td><input type="text" name="_contact_phone" value="<?php echo esc_attr($phone); ?>" style="width:100%"></td></tr>
        <tr><th>Website</th><td><input type="text" name="_contact_website" value="<?php echo esc_attr($website); ?>" style="width:100%"></td></tr>
        <tr><th>Events Text</th><td><input type="text" name="_contact_events" value="<?php echo esc_attr($events); ?>" style="width:100%"></td></tr>
        <tr><th>Franchise Text</th><td><input type="text" name="_contact_franchise" value="<?php echo esc_attr($franchise); ?>" style="width:100%"></td></tr>
        <tr><th>WhatsApp Number (digits only)</th><td><input type="text" name="_contact_whatsapp" value="<?php echo esc_attr($wa); ?>" style="width:100%"></td></tr>
        <tr><th>Google Form API URL</th><td><input type="text" name="_contact_api" value="<?php echo esc_attr($api); ?>" style="width:100%"><br><small>Your Google Apps Script URL for form submissions</small></td></tr>
    </table>
    <?php
}

// ─── FOOTER META BOX ─────────────────────────────────────────────────────────
function canehouse_footer_callback($post) {
    $copy = get_post_meta($post->ID, '_footer_copy', true) ?: '© 2025 The Cane House. Pressed Fresh. Served Cool.';
    $ig   = get_post_meta($post->ID, '_footer_ig', true)   ?: '#';
    $fb   = get_post_meta($post->ID, '_footer_fb', true)   ?: '#';
    $tt   = get_post_meta($post->ID, '_footer_tt', true)   ?: '#';
    $yt   = get_post_meta($post->ID, '_footer_yt', true)   ?: '#';
    ?>
    <table class="form-table">
        <tr><th>Copyright Text</th><td><input type="text" name="_footer_copy" value="<?php echo esc_attr($copy); ?>" style="width:100%"></td></tr>
        <tr><th>Instagram URL</th><td><input type="text" name="_footer_ig" value="<?php echo esc_attr($ig); ?>" style="width:100%"></td></tr>
        <tr><th>Facebook URL</th><td><input type="text" name="_footer_fb" value="<?php echo esc_attr($fb); ?>" style="width:100%"></td></tr>
        <tr><th>TikTok URL</th><td><input type="text" name="_footer_tt" value="<?php echo esc_attr($tt); ?>" style="width:100%"></td></tr>
        <tr><th>YouTube URL</th><td><input type="text" name="_footer_yt" value="<?php echo esc_attr($yt); ?>" style="width:100%"></td></tr>
    </table>
    <?php
}

// ─── SAVE ALL META ────────────────────────────────────────────────────────────
function canehouse_save_meta($post_id) {
    if (!isset($_POST['canehouse_nonce']) || !wp_verify_nonce($_POST['canehouse_nonce'], 'canehouse_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = array(
        '_hero_tag','_hero_title1','_hero_title2','_hero_subtitle','_hero_desc',
        '_hero_btn1_text','_hero_btn1_url','_hero_btn2_text','_hero_btn2_url',
        '_hire_title','_hire_desc',
        '_contact_phone','_contact_website','_contact_events','_contact_franchise','_contact_whatsapp','_contact_api',
        '_footer_copy','_footer_ig','_footer_fb','_footer_tt','_footer_yt',
    );
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Array fields
    $array_fields = array('_order_steps', '_reviews', '_hire_cards', '_faqs');
    foreach ($array_fields as $field) {
        if (isset($_POST[$field]) && is_array($_POST[$field])) {
            update_post_meta($post_id, $field, $_POST[$field]);
        }
    }
}
add_action('save_post', 'canehouse_save_meta');

// ─── THEME OPTIONS PAGE (Site-wide settings) ──────────────────────────────────
function canehouse_options_page() {
    add_menu_page('Cane House Settings', '🌿 Cane House', 'manage_options', 'canehouse-settings', 'canehouse_options_render', 'dashicons-admin-customizer', 80);
}
add_action('admin_menu', 'canehouse_options_page');

function canehouse_options_render() {
    if (isset($_POST['canehouse_options_nonce']) && wp_verify_nonce($_POST['canehouse_options_nonce'], 'canehouse_options_save')) {
        update_option('canehouse_marquee', sanitize_text_field($_POST['canehouse_marquee'] ?? ''));
        update_option('canehouse_franchise_locations', sanitize_textarea_field($_POST['canehouse_franchise_locations'] ?? ''));
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    $marquee   = get_option('canehouse_marquee', 'Pressed Fresh ✦ Served Cool ✦ No Added Sugar ✦ No Preservatives ✦ Pressed Live ✦ Natural Goodness ✦ Build Your Juice ✦ Events & Hire');
    $locations = get_option('canehouse_franchise_locations', "London Central\nManchester Hub\nBirmingham West\nLeeds North\nGlasgow Fresh\nCardiff Bay");
    ?>
    <div class="wrap">
        <h1>🌿 The Cane House — Theme Settings</h1>
        <form method="post">
            <?php wp_nonce_field('canehouse_options_save', 'canehouse_options_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th>Marquee Text</th>
                    <td><input type="text" name="canehouse_marquee" value="<?php echo esc_attr($marquee); ?>" style="width:100%"><br>
                    <small>Scrolling text strip. Separate items with ✦</small></td>
                </tr>
                <tr>
                    <th>Franchise Locations</th>
                    <td><textarea name="canehouse_franchise_locations" rows="8" style="width:100%"><?php echo esc_textarea($locations); ?></textarea><br>
                    <small>One location per line</small></td>
                </tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
        <hr>
        <h2>📖 How to Edit Your Website</h2>
        <ol style="font-size:14px;line-height:2;">
            <li>Go to <strong>Pages → Home</strong> → You will see all sections to edit</li>
            <li>Each section (Hero, Reviews, FAQ etc.) has its own edit box</li>
            <li>Change text, images, buttons — click <strong>Update</strong></li>
            <li>For site-wide settings (marquee, locations) — edit here and save</li>
            <li>For Navigation — go to <strong>Appearance → Menus</strong></li>
            <li>For Logo — go to <strong>Appearance → Customize → Site Identity</strong></li>
        </ol>
    </div>
    <?php
}

// ─── HELPER: get meta with fallback ──────────────────────────────────────────
function ch_meta($post_id, $key, $fallback = '') {
    $val = get_post_meta($post_id, $key, true);
    return ($val !== '' && $val !== false) ? $val : $fallback;
}

// ─── ENQUEUE CONTACT FORM JS ─────────────────────────────────────────────────
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'canehouse-contact-form',
        get_template_directory_uri() . '/assets/js/contact-form.js',
        array('canehouse-script'), '1.0.0', true
    );
}, 20);

// ─── INCLUDE MODULES ─────────────────────────────────────────────────────────
require_once get_template_directory() . '/inc/contact-leads.php';
