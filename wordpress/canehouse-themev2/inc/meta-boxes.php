<?php
/**
 * CANEHOUSE — Homepage Meta Boxes
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS FILE EXISTS:
 *   The homepage has many editable sections. Each section needs its own
 *   admin edit box (called a "meta box") that appears when you edit the
 *   Home page in WP Admin → Pages → Edit.
 *
 * WHAT YOU CAN EDIT:
 *   ✅ Hero: title, subtitle, description, buttons, IMAGES (upload)
 *   ✅ How To Order: 5 steps — emoji, title, description
 *   ✅ Reviews: name, role, text, avatar image (upload)
 *   ✅ Events & Hire: title, description, 3 event cards
 *   ✅ FAQ: questions & answers (add/remove fields)
 *   ✅ Build Your Juice: prices and labels
 *   ✅ Benefits section: 5 benefit items
 *   ✅ Story section: quote, year badge text
 *   ✅ Showcase (franchise slider): 4 slides with image + title
 *
 * HOW IT WORKS:
 *   WordPress meta boxes appear below the editor on the Edit Page screen.
 *   Each box saves data using update_post_meta() into the database.
 *   The index.php reads this data with get_post_meta() to display it.
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── ENQUEUE MEDIA UPLOADER on edit pages ──────────────────────────────────────
add_action('admin_enqueue_scripts', function($hook) {
    if (!in_array($hook, array('post.php','post-new.php'))) return;
    wp_enqueue_media();
    wp_enqueue_script('ch-meta-boxes', get_template_directory_uri() . '/assets/js/admin-meta-boxes.js', array('jquery'), '1.0', true);
    wp_enqueue_style('ch-meta-boxes-css', get_template_directory_uri() . '/assets/css/admin-meta-boxes.css', array(), '1.0');
});

// ── REGISTER META BOXES ───────────────────────────────────────────────────────
add_action('add_meta_boxes', function() {
    $screens = array('page');
    add_meta_box('ch_hero',     '🏠 Hero Section',           'ch_mb_hero',     $screens, 'normal', 'high');
    add_meta_box('ch_order',    '📋 How To Order Steps',     'ch_mb_order',    $screens, 'normal', 'default');
    add_meta_box('ch_reviews',  '⭐ Customer Reviews',       'ch_mb_reviews',  $screens, 'normal', 'default');
    add_meta_box('ch_build',    '🥤 Build Your Juice Menu',  'ch_mb_build',    $screens, 'normal', 'default');
    add_meta_box('ch_benefits', '💚 Benefits Section',       'ch_mb_benefits', $screens, 'normal', 'default');
    add_meta_box('ch_story',    '📖 Story Section',          'ch_mb_story',    $screens, 'normal', 'default');
    add_meta_box('ch_hire',     '🎪 Events & Hire',          'ch_mb_hire',     $screens, 'normal', 'default');
    add_meta_box('ch_showcase', '🖼️ Juice Showcase Slider',  'ch_mb_showcase', $screens, 'normal', 'default');
    add_meta_box('ch_faq',      '❓ FAQ Section',            'ch_mb_faq',      $screens, 'normal', 'default');
    add_meta_box('ch_contact',  '📞 Contact Section',        'ch_mb_contact',  $screens, 'normal', 'default');
});

// ── HELPER: image upload field ────────────────────────────────────────────────
function ch_image_field($name, $value, $label = 'Image') {
    $img_html = $value ? '<img src="'.esc_url($value).'" style="max-width:200px;max-height:120px;display:block;margin-bottom:8px;border-radius:6px;border:1px solid #ddd;">' : '';
    echo '<div class="ch-image-field" style="margin-bottom:12px;">';
    echo '<label style="font-weight:600;display:block;margin-bottom:4px;">'.$label.'</label>';
    echo $img_html;
    echo '<input type="hidden" name="'.$name.'" value="'.esc_attr($value).'" class="ch-image-url">';
    echo '<button type="button" class="button ch-upload-btn" data-target="'.$name.'">📷 Upload / Change Image</button>';
    if ($value) echo ' <button type="button" class="button ch-remove-img" data-target="'.$name.'">✕ Remove</button>';
    echo '</div>';
}

// ── HELPER: text input row ────────────────────────────────────────────────────
function ch_text($label, $name, $value, $type='text', $placeholder='') {
    echo '<tr><th style="width:160px;padding:8px 10px;font-size:13px;">'.esc_html($label).'</th>';
    echo '<td style="padding:6px 10px;"><input type="'.esc_attr($type).'" name="'.esc_attr($name).'" value="'.esc_attr($value).'" placeholder="'.esc_attr($placeholder).'" style="width:100%;max-width:500px;"></td></tr>';
}

// ── HELPER: textarea row ──────────────────────────────────────────────────────
function ch_textarea($label, $name, $value, $rows=3) {
    echo '<tr><th style="width:160px;padding:8px 10px;font-size:13px;">'.esc_html($label).'</th>';
    echo '<td style="padding:6px 10px;"><textarea name="'.esc_attr($name).'" rows="'.$rows.'" style="width:100%;max-width:500px;">'.esc_textarea($value).'</textarea></td></tr>';
}

// ── NONCE field helper ────────────────────────────────────────────────────────
function ch_nonce() {
    wp_nonce_field('ch_save_meta', 'ch_meta_nonce');
}

// ════════════════════════════════════════════════════════════════════════════
// 1. HERO META BOX
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_hero($post) {
    ch_nonce();
    $p = $post->ID;
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;">
        <strong>WHY:</strong> This is the first big section visitors see. Edit your headline, tagline, description, buttons, and hero image here.
    </p>
    <table class="form-table" style="margin:0;">
    <?php
    ch_text('Top Tag Text',        '_hero_tag',        get_post_meta($p,'_hero_tag',true)       ?: '100% Natural · No Additives · Pressed Live');
    ch_text('Title Line 1 (white)','_hero_title1',     get_post_meta($p,'_hero_title1',true)    ?: 'Pressed Fresh.');
    ch_text('Title Line 2 (lime)', '_hero_title2',     get_post_meta($p,'_hero_title2',true)    ?: 'Served Cool.');
    ch_text('Subtitle',            '_hero_subtitle',   get_post_meta($p,'_hero_subtitle',true)  ?: 'The Cane House');
    ch_textarea('Description',     '_hero_desc',       get_post_meta($p,'_hero_desc',true)      ?: 'Fresh sugarcane juice pressed live and blended with authentic cold-pressed fruit extracts & natural botanicals.');
    ch_text('Button 1 Text',       '_hero_btn1_text',  get_post_meta($p,'_hero_btn1_text',true) ?: '🥤 Build Your Juice');
    ch_text('Button 1 Link',       '_hero_btn1_url',   get_post_meta($p,'_hero_btn1_url',true)  ?: '#build');
    ch_text('Button 2 Text',       '_hero_btn2_text',  get_post_meta($p,'_hero_btn2_text',true) ?: 'Hire for Events →');
    ch_text('Button 2 Link',       '_hero_btn2_url',   get_post_meta($p,'_hero_btn2_url',true)  ?: '#hire');
    ?>
    </table>
    <div style="margin-top:12px;padding:12px;background:#f9fafb;border-radius:6px;">
    <?php ch_image_field('_hero_image', get_post_meta($p,'_hero_image',true) ?: '', 'Hero Right-Side Image (mascot/character)'); ?>
    </div>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 2. HOW TO ORDER
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_order($post) {
    $p = $post->ID;
    $steps = get_post_meta($p, '_order_steps', true);
    if (empty($steps)) $steps = array(
        array('emoji'=>'📏','title'=>'Select Size',   'desc'=>'Choose from Mini 250ml right up to Group Sharing 1.5L'),
        array('emoji'=>'🌾','title'=>'Select Cane',   'desc'=>'Yellow Cane (light golden) or Red Cane (rich amber, +£0.50)'),
        array('emoji'=>'🥤','title'=>'Select Texture','desc'=>'Classic No Peel or Smooth With Peel (+£0.50)'),
        array('emoji'=>'🍋','title'=>'Select Flavour','desc'=>'Pure Cane (free), Citrus (+£0.50) or Tropical (+£1.00)'),
        array('emoji'=>'🎉','title'=>'Enjoy!',         'desc'=>'Served chilled — pure fresh natural goodness'),
    );
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;"><strong>WHY:</strong> These 5 steps explain your ordering process to customers. Edit emoji, title and description for each step.</p>
    <div id="ch-steps-wrap">
    <?php foreach($steps as $i => $s): ?>
    <div class="ch-repeater-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:10px;background:#fafafa;">
        <strong style="color:#2d5a1b;">Step <?php echo $i+1; ?></strong>
        <table class="form-table" style="margin:8px 0 0 0;">
        <tr>
            <th style="width:100px;font-size:12px;">Emoji</th>
            <td><input type="text" name="_order_steps[<?php echo $i; ?>][emoji]" value="<?php echo esc_attr($s['emoji']); ?>" style="width:80px;font-size:18px;"></td>
            <th style="width:80px;font-size:12px;">Title</th>
            <td><input type="text" name="_order_steps[<?php echo $i; ?>][title]" value="<?php echo esc_attr($s['title']); ?>" style="width:220px;"></td>
        </tr>
        <tr>
            <th style="font-size:12px;">Description</th>
            <td colspan="3"><textarea name="_order_steps[<?php echo $i; ?>][desc]" rows="2" style="width:100%;"><?php echo esc_textarea($s['desc']); ?></textarea></td>
        </tr>
        </table>
    </div>
    <?php endforeach; ?>
    </div>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 3. REVIEWS
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_reviews($post) {
    $p = $post->ID;
    $reviews = get_post_meta($p, '_reviews', true);
    if (empty($reviews)) $reviews = array(
        array('name'=>'Sarah Johnson','role'=>'Verified Customer','text'=>'The freshest cane juice I\'ve ever had in the UK. The ginger blend is absolutely life-changing!','avatar'=>'https://i.pravatar.cc/300?u=1'),
        array('name'=>'Mohammed Ali', 'role'=>'Verified Customer','text'=>'Reminds me of home! Pressed live right in front of you. Naturally sweet and refreshing.','avatar'=>'https://i.pravatar.cc/300?u=2'),
        array('name'=>'Emma Wright',  'role'=>'Event Client',     'text'=>'We hired The Cane House for our wedding — guests loved the live pressing experience!','avatar'=>'https://i.pravatar.cc/300?u=3'),
    );
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;"><strong>WHY:</strong> Social proof! Real customer reviews build trust. You can change names, text, and upload real customer photos.</p>
    <?php foreach($reviews as $i => $r): ?>
    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:12px;background:#fafafa;">
        <strong style="color:#2d5a1b;">Review <?php echo $i+1; ?></strong>
        <table class="form-table" style="margin:8px 0 0;">
        <tr>
            <th style="width:100px;font-size:12px;">Name</th>
            <td><input type="text" name="_reviews[<?php echo $i; ?>][name]" value="<?php echo esc_attr($r['name']); ?>" style="width:200px;"></td>
            <th style="width:80px;font-size:12px;">Role</th>
            <td><input type="text" name="_reviews[<?php echo $i; ?>][role]" value="<?php echo esc_attr($r['role']); ?>" style="width:200px;"></td>
        </tr>
        <tr>
            <th style="font-size:12px;">Review Text</th>
            <td colspan="3"><textarea name="_reviews[<?php echo $i; ?>][text]" rows="2" style="width:100%;"><?php echo esc_textarea($r['text']); ?></textarea></td>
        </tr>
        </table>
        <?php ch_image_field('_reviews['.$i.'][avatar]', $r['avatar'], 'Customer Photo (avatar)'); ?>
    </div>
    <?php endforeach; ?>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 4. BUILD YOUR JUICE — Flavours (prices editable)
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_build($post) {
    $p = $post->ID;
    $flavours = get_post_meta($p, '_build_flavours', true);
    if (empty($flavours)) $flavours = array(
        array('emoji'=>'🌿','name'=>'Pure Cane',      'price'=>'Included','type'=>'Base'),
        array('emoji'=>'🍋','name'=>'Lemon',           'price'=>'+£0.50',  'type'=>'Citrus'),
        array('emoji'=>'🫚','name'=>'Ginger',          'price'=>'+£0.50',  'type'=>'Citrus'),
        array('emoji'=>'🌀','name'=>'Lemon & Ginger',  'price'=>'+£0.50',  'type'=>'Citrus'),
        array('emoji'=>'🌱','name'=>'Mint',             'price'=>'+£0.50',  'type'=>'Citrus'),
        array('emoji'=>'🍍','name'=>'Pineapple',       'price'=>'+£1.00',  'type'=>'Tropical'),
        array('emoji'=>'🍉','name'=>'Watermelon',      'price'=>'+£1.00',  'type'=>'Tropical'),
        array('emoji'=>'🍓','name'=>'Strawberry',      'price'=>'+£1.00',  'type'=>'Tropical'),
    );
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;"><strong>WHY:</strong> Your juice menu. Edit flavour names, prices, and emojis. You can also add new flavours.</p>
    <div id="ch-flavours-wrap">
    <?php foreach($flavours as $i => $f): ?>
    <div class="ch-repeater-row" style="display:grid;grid-template-columns:60px 1fr 1fr 1fr 40px;gap:8px;align-items:center;border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin-bottom:8px;background:#fafafa;">
        <div>
            <label style="font-size:11px;color:#888;">Emoji</label>
            <input type="text" name="_build_flavours[<?php echo $i; ?>][emoji]" value="<?php echo esc_attr($f['emoji']); ?>" style="width:50px;font-size:18px;text-align:center;">
        </div>
        <div>
            <label style="font-size:11px;color:#888;">Flavour Name</label>
            <input type="text" name="_build_flavours[<?php echo $i; ?>][name]" value="<?php echo esc_attr($f['name']); ?>" style="width:100%;">
        </div>
        <div>
            <label style="font-size:11px;color:#888;">Price</label>
            <input type="text" name="_build_flavours[<?php echo $i; ?>][price]" value="<?php echo esc_attr($f['price']); ?>" style="width:100%;" placeholder="+£0.50">
        </div>
        <div>
            <label style="font-size:11px;color:#888;">Type</label>
            <select name="_build_flavours[<?php echo $i; ?>][type]" style="width:100%;">
                <?php foreach(array('Base','Citrus','Tropical','Other') as $t): ?>
                <option value="<?php echo $t; ?>" <?php selected($f['type'],$t); ?>><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><button type="button" class="ch-remove-row button" style="color:red;font-size:16px;padding:2px 8px;">✕</button></div>
    </div>
    <?php endforeach; ?>
    </div>
    <button type="button" id="ch-add-flavour" class="button" style="margin-top:8px;background:#2d5a1b;color:#fff;border-color:#2d5a1b;">+ Add New Flavour</button>
    <script>
    jQuery(function($){
        var i = <?php echo count($flavours); ?>;
        $('#ch-add-flavour').on('click', function(){
            $('#ch-flavours-wrap').append('<div class="ch-repeater-row" style="display:grid;grid-template-columns:60px 1fr 1fr 1fr 40px;gap:8px;align-items:center;border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin-bottom:8px;background:#fafafa;"><div><label style="font-size:11px;color:#888;">Emoji</label><input type="text" name="_build_flavours['+i+'][emoji]" value="🥤" style="width:50px;font-size:18px;text-align:center;"></div><div><label style="font-size:11px;color:#888;">Flavour Name</label><input type="text" name="_build_flavours['+i+'][name]" value="" style="width:100%;"></div><div><label style="font-size:11px;color:#888;">Price</label><input type="text" name="_build_flavours['+i+'][price]" value="+£0.50" style="width:100%;"></div><div><label style="font-size:11px;color:#888;">Type</label><select name="_build_flavours['+i+'][type]" style="width:100%;"><option>Base</option><option>Citrus</option><option selected>Tropical</option><option>Other</option></select></div><div><button type="button" class="ch-remove-row button" style="color:red;font-size:16px;padding:2px 8px;">✕</button></div></div>');
            i++;
        });
        $(document).on('click','.ch-remove-row',function(){ $(this).closest('.ch-repeater-row').remove(); });
    });
    </script>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 5. BENEFITS
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_benefits($post) {
    $p = $post->ID;
    $benefits = get_post_meta($p, '_benefits', true);
    if (empty($benefits)) $benefits = array(
        array('icon'=>'⚡','title'=>'Natural Energy Booster',    'desc'=>'Provides instant energy with natural sugars — no additives or artificial ingredients.'),
        array('icon'=>'💧','title'=>'Hydrating & Cooling',       'desc'=>'Perfect for warm days, helping to refresh and rehydrate the body naturally.'),
        array('icon'=>'🌿','title'=>'Rich in Natural Nutrients', 'desc'=>'Contains antioxidants, minerals, and electrolytes your body loves.'),
        array('icon'=>'🫁','title'=>'Supports Digestion',        'desc'=>'Traditionally enjoyed with lemon and ginger to aid digestion.'),
        array('icon'=>'🛡️','title'=>'Boosts Immunity',           'desc'=>'Natural compounds support overall wellness. Clean, fresh and light.'),
    );
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;"><strong>WHY:</strong> The benefits section educates customers about health advantages of sugarcane juice.</p>
    <div id="ch-benefits-wrap">
    <?php foreach($benefits as $i => $b): ?>
    <div class="ch-repeater-row" style="border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin-bottom:8px;display:grid;grid-template-columns:60px 1fr 2fr 40px;gap:10px;align-items:start;background:#fafafa;">
        <div><label style="font-size:11px;color:#888;">Icon</label><input type="text" name="_benefits[<?php echo $i; ?>][icon]" value="<?php echo esc_attr($b['icon']); ?>" style="width:50px;font-size:20px;text-align:center;"></div>
        <div><label style="font-size:11px;color:#888;">Title</label><input type="text" name="_benefits[<?php echo $i; ?>][title]" value="<?php echo esc_attr($b['title']); ?>" style="width:100%;"></div>
        <div><label style="font-size:11px;color:#888;">Description</label><textarea name="_benefits[<?php echo $i; ?>][desc]" rows="2" style="width:100%;"><?php echo esc_textarea($b['desc']); ?></textarea></div>
        <div style="padding-top:18px;"><button type="button" class="ch-remove-row-b button" style="color:red;font-size:16px;padding:2px 8px;">✕</button></div>
    </div>
    <?php endforeach; ?>
    </div>
    <button type="button" id="ch-add-benefit" class="button" style="margin-top:8px;background:#2d5a1b;color:#fff;border-color:#2d5a1b;">+ Add Benefit</button>
    <script>
    jQuery(function($){
        var bi=<?php echo count($benefits); ?>;
        $('#ch-add-benefit').on('click',function(){
            $('#ch-benefits-wrap').append('<div class="ch-repeater-row" style="border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin-bottom:8px;display:grid;grid-template-columns:60px 1fr 2fr 40px;gap:10px;align-items:start;background:#fafafa;"><div><label style="font-size:11px;color:#888;">Icon</label><input type="text" name="_benefits['+bi+'][icon]" value="✨" style="width:50px;font-size:20px;text-align:center;"></div><div><label style="font-size:11px;color:#888;">Title</label><input type="text" name="_benefits['+bi+'][title]" value="" style="width:100%;"></div><div><label style="font-size:11px;color:#888;">Description</label><textarea name="_benefits['+bi+'][desc]" rows="2" style="width:100%;"></textarea></div><div style="padding-top:18px;"><button type="button" class="ch-remove-row-b button" style="color:red;font-size:16px;padding:2px 8px;">✕</button></div></div>');
            bi++;
        });
        $(document).on('click','.ch-remove-row-b',function(){ $(this).closest('.ch-repeater-row').remove(); });
    });
    </script>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 6. STORY SECTION
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_story($post) {
    $p = $post->ID;
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;"><strong>WHY:</strong> The story section adds depth and heritage to your brand. Edit the quote and year badge.</p>
    <table class="form-table" style="margin:0;">
    <?php
    ch_text('Section Tag',  '_story_tag',   get_post_meta($p,'_story_tag',true)   ?: 'The Story of Sugarcane');
    ch_text('Section Title','_story_title', get_post_meta($p,'_story_title',true) ?: 'Beyond the Juice');
    ch_textarea('Paragraph 1','_story_p1', get_post_meta($p,'_story_p1',true)    ?: 'Sugarcane has been cherished for over 2,000 years, originating in South and Southeast Asia.');
    ch_textarea('Paragraph 2','_story_p2', get_post_meta($p,'_story_p2',true)    ?: 'Beyond juice, sugarcane offers a range of valuable products. Even the leftover fibre is biodegradable.');
    ch_text('Pull Quote',   '_story_quote', get_post_meta($p,'_story_quote',true) ?: 'Sugarcane — one of nature\'s most generous gifts. Pure energy, pressed fresh.');
    ch_text('Year Badge',   '_story_year',  get_post_meta($p,'_story_year',true)  ?: '2,000+ Years of Cane');
    ?>
    </table>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 7. HIRE / EVENTS
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_hire($post) {
    $p = $post->ID;
    $cards = get_post_meta($p, '_hire_cards', true);
    if (empty($cards)) $cards = array(
        array('icon'=>'💒','title'=>'Weddings',        'desc'=>'Add a traditional and healthy touch to your big day.','list'=>"Reception Drinks\nMehndi & Sangeet\nPost-Ceremony Refreshment"),
        array('icon'=>'🏢','title'=>'Corporate Events','desc'=>'Perfect for office parties, wellness days, and conferences.','list'=>"Office Wellness Days\nProduct Launches\nExhibitions & Fairs"),
        array('icon'=>'🎉','title'=>'Private Parties', 'desc'=>'From birthdays to garden parties, we bring the vibe.','list'=>"Birthday Parties\nCommunity Festivals\nGarden & BBQ Events"),
    );
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;"><strong>WHY:</strong> Event hire is a key revenue stream. Edit the 3 event cards — icon, title, description, and list items.</p>
    <table class="form-table" style="margin:0 0 16px;">
    <?php
    ch_text('Section Title',      '_hire_title', get_post_meta($p,'_hire_title',true) ?: 'Bring Us to Your Event');
    ch_textarea('Section Description','_hire_desc',  get_post_meta($p,'_hire_desc',true)  ?: 'Elevate your celebration with our premium live-pressed sugarcane juice experience.');
    ?>
    </table>
    <?php foreach($cards as $i => $c): ?>
    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:12px;background:#fafafa;">
        <strong style="color:#2d5a1b;">Event Card <?php echo $i+1; ?></strong>
        <table class="form-table" style="margin:8px 0 0;">
        <tr>
            <th style="width:100px;font-size:12px;">Icon/Emoji</th>
            <td><input type="text" name="_hire_cards[<?php echo $i; ?>][icon]" value="<?php echo esc_attr($c['icon']); ?>" style="width:60px;font-size:20px;"></td>
            <th style="width:80px;font-size:12px;">Title</th>
            <td><input type="text" name="_hire_cards[<?php echo $i; ?>][title]" value="<?php echo esc_attr($c['title']); ?>" style="width:220px;"></td>
        </tr>
        <tr>
            <th style="font-size:12px;">Description</th>
            <td colspan="3"><textarea name="_hire_cards[<?php echo $i; ?>][desc]" rows="2" style="width:100%;"><?php echo esc_textarea($c['desc']); ?></textarea></td>
        </tr>
        <tr>
            <th style="font-size:12px;">List Items<br><small>(one per line)</small></th>
            <td colspan="3"><textarea name="_hire_cards[<?php echo $i; ?>][list]" rows="3" style="width:100%;"><?php echo esc_textarea($c['list']); ?></textarea></td>
        </tr>
        </table>
    </div>
    <?php endforeach; ?>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 8. SHOWCASE SLIDER
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_showcase($post) {
    $p = $post->ID;
    $slides = get_post_meta($p, '_showcase_slides', true);
    if (empty($slides)) $slides = array(
        array('title'=>'Pure Yellow Cane','subtitle'=>'Fresh & Naturally Sweet',  'image'=>'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=600'),
        array('title'=>'Zesty Lemon',     'subtitle'=>'Citrus Refreshment',       'image'=>'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=600'),
        array('title'=>'Spicy Ginger',    'subtitle'=>'Warming & Healthy',        'image'=>'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&q=80&w=600'),
        array('title'=>'Cooling Mint',    'subtitle'=>'Ultimate Freshness',       'image'=>'https://images.unsplash.com/photo-1595981267035-7b04ca84a82d?auto=format&fit=crop&q=80&w=600'),
    );
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;"><strong>WHY:</strong> The franchise slider shows your juice varieties. Upload real product photos here for maximum impact.</p>
    <div id="ch-slides-wrap">
    <?php foreach($slides as $i => $s): ?>
    <div class="ch-repeater-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:10px;background:#fafafa;">
        <strong style="color:#2d5a1b;">Slide <?php echo $i+1; ?></strong>
        <table class="form-table" style="margin:8px 0 0;">
        <tr>
            <th style="width:100px;font-size:12px;">Title</th>
            <td><input type="text" name="_showcase_slides[<?php echo $i; ?>][title]" value="<?php echo esc_attr($s['title']); ?>" style="width:220px;"></td>
            <th style="width:80px;font-size:12px;">Subtitle</th>
            <td><input type="text" name="_showcase_slides[<?php echo $i; ?>][subtitle]" value="<?php echo esc_attr($s['subtitle']); ?>" style="width:220px;"></td>
        </tr>
        </table>
        <?php ch_image_field('_showcase_slides['.$i.'][image]', $s['image'], 'Slide Image'); ?>
    </div>
    <?php endforeach; ?>
    </div>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 9. FAQ — fully repeatable, add/remove fields
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_faq($post) {
    $p = $post->ID;
    $faqs = get_post_meta($p, '_faqs', true);
    if (empty($faqs)) $faqs = array(
        array('q'=>'Do you add any sugar or preservatives?',  'a'=>'No — 100% natural, pressed live from the stalk.'),
        array('q'=>'How long does the juice stay fresh?',     'a'=>'Best enjoyed immediately. Up to 24 hours if kept chilled.'),
        array('q'=>'What events can I hire you for?',         'a'=>'Weddings, birthdays, corporate events, festivals across the UK.'),
        array('q'=>'Is your sugarcane juice sustainable?',    'a'=>'Yes! Leftover fibre (bagasse) is fully biodegradable.'),
    );
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;">
        <strong>WHY:</strong> FAQs reduce customer queries and build confidence.
        <strong>You can add as many as you need</strong> using the button below.
    </p>
    <div id="ch-faq-wrap">
    <?php foreach($faqs as $i => $f): ?>
    <div class="ch-faq-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:10px;background:#fafafa;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <strong style="color:#2d5a1b;">FAQ <?php echo $i+1; ?></strong>
            <button type="button" class="ch-remove-faq button" style="color:red;">✕ Remove</button>
        </div>
        <label style="font-size:12px;color:#555;">Question</label>
        <input type="text" name="_faqs[<?php echo $i; ?>][q]" value="<?php echo esc_attr($f['q']); ?>" style="width:100%;margin-bottom:8px;">
        <label style="font-size:12px;color:#555;">Answer</label>
        <textarea name="_faqs[<?php echo $i; ?>][a]" rows="3" style="width:100%;"><?php echo esc_textarea($f['a']); ?></textarea>
    </div>
    <?php endforeach; ?>
    </div>
    <button type="button" id="ch-add-faq" class="button" style="background:#2d5a1b;color:#fff;border-color:#2d5a1b;margin-top:8px;">+ Add New FAQ</button>
    <script>
    jQuery(function($){
        var fi = <?php echo count($faqs); ?>;
        $('#ch-add-faq').on('click', function(){
            $('#ch-faq-wrap').append('<div class="ch-faq-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:14px;margin-bottom:10px;background:#fafafa;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;"><strong style="color:#2d5a1b;">FAQ '+(fi+1)+'</strong><button type="button" class="ch-remove-faq button" style="color:red;">✕ Remove</button></div><label style="font-size:12px;color:#555;">Question</label><input type="text" name="_faqs['+fi+'][q]" value="" style="width:100%;margin-bottom:8px;"><label style="font-size:12px;color:#555;">Answer</label><textarea name="_faqs['+fi+'][a]" rows="3" style="width:100%;"></textarea></div>');
            fi++;
        });
        $(document).on('click','.ch-remove-faq',function(){ $(this).closest('.ch-faq-row').remove(); });
    });
    </script>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// 10. CONTACT SECTION — custom fields support
// ════════════════════════════════════════════════════════════════════════════
function ch_mb_contact($post) {
    $p = $post->ID;
    $extra = get_post_meta($p, '_contact_extra_fields', true);
    if (empty($extra)) $extra = array();
    ?>
    <p style="color:#666;font-size:13px;margin-top:0;">
        <strong>WHY:</strong> Contact details come from <a href="<?php echo admin_url('admin.php?page=ch-site-settings'); ?>">⚙️ Site Settings</a> (phone, WhatsApp, email, address). This section lets you add EXTRA custom contact fields to the homepage contact section.
    </p>
    <p style="background:#fff3cd;padding:10px 14px;border-radius:6px;font-size:13px;">
        📍 To change <strong>Phone, WhatsApp, Email, Address</strong> → go to <a href="<?php echo admin_url('admin.php?page=ch-site-settings'); ?>"><strong>🌿 Cane House → ⚙️ Site Settings</strong></a>
    </p>
    <p><strong>Extra Contact Fields</strong> — shown in the contact section below standard details:</p>
    <div id="ch-contact-extra-wrap">
    <?php foreach($extra as $i => $ef): ?>
    <div class="ch-extra-row" style="display:grid;grid-template-columns:50px 1fr 2fr 40px;gap:8px;align-items:center;border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin-bottom:8px;background:#fafafa;">
        <div><label style="font-size:11px;color:#888;">Icon</label><input type="text" name="_contact_extra_fields[<?php echo $i; ?>][icon]" value="<?php echo esc_attr($ef['icon']); ?>" style="width:44px;font-size:18px;text-align:center;"></div>
        <div><label style="font-size:11px;color:#888;">Label</label><input type="text" name="_contact_extra_fields[<?php echo $i; ?>][label]" value="<?php echo esc_attr($ef['label']); ?>" style="width:100%;"></div>
        <div><label style="font-size:11px;color:#888;">Value / Description</label><input type="text" name="_contact_extra_fields[<?php echo $i; ?>][value]" value="<?php echo esc_attr($ef['value']); ?>" style="width:100%;"></div>
        <div><button type="button" class="ch-remove-extra button" style="color:red;font-size:16px;padding:2px 8px;">✕</button></div>
    </div>
    <?php endforeach; ?>
    </div>
    <button type="button" id="ch-add-extra" class="button" style="background:#2d5a1b;color:#fff;border-color:#2d5a1b;margin-top:8px;">+ Add Contact Field</button>
    <script>
    jQuery(function($){
        var ei=<?php echo count($extra); ?>;
        $('#ch-add-extra').on('click',function(){
            $('#ch-contact-extra-wrap').append('<div class="ch-extra-row" style="display:grid;grid-template-columns:50px 1fr 2fr 40px;gap:8px;align-items:center;border:1px solid #e5e7eb;border-radius:6px;padding:10px;margin-bottom:8px;background:#fafafa;"><div><label style="font-size:11px;color:#888;">Icon</label><input type="text" name="_contact_extra_fields['+ei+'][icon]" value="📌" style="width:44px;font-size:18px;text-align:center;"></div><div><label style="font-size:11px;color:#888;">Label</label><input type="text" name="_contact_extra_fields['+ei+'][label]" value="" style="width:100%;"></div><div><label style="font-size:11px;color:#888;">Value / Description</label><input type="text" name="_contact_extra_fields['+ei+'][value]" value="" style="width:100%;"></div><div><button type="button" class="ch-remove-extra button" style="color:red;font-size:16px;padding:2px 8px;">✕</button></div></div>');
            ei++;
        });
        $(document).on('click','.ch-remove-extra',function(){ $(this).closest('.ch-extra-row').remove(); });
    });
    </script>
    <?php
}

// ════════════════════════════════════════════════════════════════════════════
// SAVE ALL META
// ════════════════════════════════════════════════════════════════════════════
add_action('save_post', function($post_id) {
    if (!isset($_POST['ch_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['ch_meta_nonce'], 'ch_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Simple text fields
    $simple = array(
        '_hero_tag','_hero_title1','_hero_title2','_hero_subtitle','_hero_desc',
        '_hero_btn1_text','_hero_btn1_url','_hero_btn2_text','_hero_btn2_url','_hero_image',
        '_hire_title','_hire_desc',
        '_story_tag','_story_title','_story_p1','_story_p2','_story_quote','_story_year',
    );
    foreach ($simple as $key) {
        if (isset($_POST[$key])) update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
    }

    // Array fields (steps, reviews, faqs, etc.)
    $arrays = array('_order_steps','_hire_cards','_faqs','_benefits','_showcase_slides','_build_flavours','_contact_extra_fields');
    foreach ($arrays as $key) {
        if (isset($_POST[$key]) && is_array($_POST[$key])) {
            update_post_meta($post_id, $key, $_POST[$key]);
        }
    }

    // Reviews (handle image URLs properly)
    if (isset($_POST['_reviews']) && is_array($_POST['_reviews'])) {
        update_post_meta($post_id, '_reviews', $_POST['_reviews']);
    }

    // Showcase slides
    if (isset($_POST['_showcase_slides']) && is_array($_POST['_showcase_slides'])) {
        update_post_meta($post_id, '_showcase_slides', $_POST['_showcase_slides']);
    }
});
