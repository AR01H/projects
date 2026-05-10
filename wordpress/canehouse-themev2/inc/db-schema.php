<?php
/**
 * CANEHOUSE DATABASE SCHEMA
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY SEPARATE TABLES?
 *   Instead of storing everything as post_meta (which is hard to query,
 *   sort, filter, and count), each content type gets its OWN table.
 *   This means:
 *   - You can sort reviews by date, filter active ones, count them
 *   - You can reorder FAQs by drag-drop sort_order column
 *   - You can activate/deactivate any item without deleting it
 *   - Every table has: id, status (active/inactive), sort_order,
 *     image_url, created_at, updated_at
 *
 * TABLES CREATED:
 *   wp_ch_reviews          — Customer reviews / testimonials
 *   wp_ch_order_steps      — How To Order steps (5 steps)
 *   wp_ch_flavours         — Build Your Juice flavours + prices
 *   wp_ch_events           — Events & Hire cards
 *   wp_ch_faqs             — Frequently asked questions
 *   wp_ch_franchise_locs   — Franchise locations (marquee)
 *   wp_ch_benefits         — Health benefits items
 *   wp_ch_showcase_slides  — Juice showcase slider slides
 *   wp_ch_leads            — Contact form submissions
 *   wp_ch_leads_meta       — Auto-collected meta per lead
 * ─────────────────────────────────────────────────────────────────────────────
 */

define('CH_DB_VERSION', '2.0');

function ch_create_all_tables() {
    global $wpdb;
    $c = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // ── REVIEWS ───────────────────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_reviews (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name        VARCHAR(200)    NOT NULL DEFAULT '',
        role        VARCHAR(200)    NOT NULL DEFAULT 'Verified Customer',
        review_text TEXT            NOT NULL,
        image_url   TEXT                     DEFAULT NULL,
        rating      TINYINT         NOT NULL DEFAULT 5,
        status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        sort_order  INT             NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY sort_order (sort_order)
    ) $c;");

    // ── HOW TO ORDER STEPS ────────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_order_steps (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        step_number TINYINT         NOT NULL DEFAULT 1,
        emoji       VARCHAR(20)     NOT NULL DEFAULT '🥤',
        title       VARCHAR(200)    NOT NULL DEFAULT '',
        description TEXT            NOT NULL,
        image_url   TEXT                     DEFAULT NULL,
        status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        sort_order  INT             NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY sort_order (sort_order)
    ) $c;");

    // ── FLAVOURS / BUILD YOUR JUICE ───────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_flavours (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        emoji       VARCHAR(20)     NOT NULL DEFAULT '🥤',
        name        VARCHAR(200)    NOT NULL DEFAULT '',
        description TEXT                     DEFAULT NULL,
        price       VARCHAR(50)     NOT NULL DEFAULT 'Included',
        flavour_type VARCHAR(100)   NOT NULL DEFAULT 'Base',
        image_url   TEXT                     DEFAULT NULL,
        status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        sort_order  INT             NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY flavour_type (flavour_type)
    ) $c;");

    // ── EVENTS & HIRE ─────────────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_events (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        icon        VARCHAR(20)     NOT NULL DEFAULT '🎉',
        title       VARCHAR(200)    NOT NULL DEFAULT '',
        description TEXT            NOT NULL,
        list_items  TEXT                     DEFAULT NULL,
        image_url   TEXT                     DEFAULT NULL,
        status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        sort_order  INT             NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status)
    ) $c;");

    // ── FAQs ──────────────────────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_faqs (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        question    TEXT            NOT NULL,
        answer      TEXT            NOT NULL,
        category    VARCHAR(100)    NOT NULL DEFAULT 'General',
        image_url   TEXT                     DEFAULT NULL,
        status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        sort_order  INT             NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY category (category)
    ) $c;");

    // ── FRANCHISE LOCATIONS ───────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_franchise_locs (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name        VARCHAR(200)    NOT NULL DEFAULT '',
        city        VARCHAR(200)    NOT NULL DEFAULT '',
        description TEXT                     DEFAULT NULL,
        image_url   TEXT                     DEFAULT NULL,
        status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        sort_order  INT             NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status)
    ) $c;");

    // ── BENEFITS ──────────────────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_benefits (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        icon        VARCHAR(20)     NOT NULL DEFAULT '✨',
        title       VARCHAR(200)    NOT NULL DEFAULT '',
        description TEXT            NOT NULL,
        image_url   TEXT                     DEFAULT NULL,
        status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        sort_order  INT             NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status)
    ) $c;");

    // ── SHOWCASE SLIDES ───────────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_showcase_slides (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        title       VARCHAR(200)    NOT NULL DEFAULT '',
        subtitle    VARCHAR(200)    NOT NULL DEFAULT '',
        image_url   TEXT                     DEFAULT NULL,
        status      ENUM('active','inactive') NOT NULL DEFAULT 'active',
        sort_order  INT             NOT NULL DEFAULT 0,
        created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status)
    ) $c;");

    // ── CONTACT LEADS ─────────────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_leads (
        id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name            VARCHAR(200)    NOT NULL DEFAULT '',
        email           VARCHAR(200)    NOT NULL DEFAULT '',
        mobile          VARCHAR(50)     NOT NULL DEFAULT '',
        query_type      VARCHAR(100)    NOT NULL DEFAULT '',
        query           TEXT            NOT NULL,
        status          ENUM('new','contacted','converted','rejected','pending','spam') NOT NULL DEFAULT 'new',
        admin_comment   TEXT                     DEFAULT NULL,
        contacted_at    DATETIME                 DEFAULT NULL,
        created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY created_at (created_at)
    ) $c;");

    // ── LEADS META ────────────────────────────────────────────────────────────
    dbDelta("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ch_leads_meta (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        lead_id     BIGINT UNSIGNED NOT NULL,
        meta_key    VARCHAR(100)    NOT NULL,
        meta_value  TEXT                     DEFAULT NULL,
        PRIMARY KEY (id),
        KEY lead_id (lead_id),
        KEY meta_key (meta_key)
    ) $c;");

    update_option('ch_db_version', CH_DB_VERSION);
}

// Run on theme activation and on init if version mismatch
add_action('after_switch_theme', 'ch_create_all_tables');
add_action('init', function() {
    if (get_option('ch_db_version') !== CH_DB_VERSION) {
        ch_create_all_tables();
    }
});

// ── SEED DEFAULT DATA (only if tables are empty) ──────────────────────────────
function ch_seed_defaults() {
    global $wpdb;

    // Reviews
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ch_reviews")) {
        $rows = array(
            array('name'=>'Sarah Johnson','role'=>'Verified Customer','review_text'=>'The freshest cane juice I\'ve ever had in the UK. The ginger blend is absolutely life-changing!','rating'=>5,'sort_order'=>1),
            array('name'=>'Mohammed Ali', 'role'=>'Verified Customer','review_text'=>'Reminds me of home! Pressed live right in front of you. Naturally sweet and refreshing. 10/10.','rating'=>5,'sort_order'=>2),
            array('name'=>'Emma Wright',  'role'=>'Event Client',     'review_text'=>'We hired The Cane House for our wedding — guests loved the live pressing experience!','rating'=>5,'sort_order'=>3),
        );
        foreach ($rows as $r) $wpdb->insert("{$wpdb->prefix}ch_reviews", $r);
    }

    // Order Steps
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ch_order_steps")) {
        $steps = array(
            array('step_number'=>1,'emoji'=>'📏','title'=>'Select Size',   'description'=>'Choose from Mini 250ml right up to Group Sharing 1.5L','sort_order'=>1),
            array('step_number'=>2,'emoji'=>'🌾','title'=>'Select Cane',   'description'=>'Yellow Cane (light golden) or Red Cane (rich amber, +£0.50)','sort_order'=>2),
            array('step_number'=>3,'emoji'=>'🥤','title'=>'Select Texture','description'=>'Classic No Peel or Smooth With Peel (+£0.50)','sort_order'=>3),
            array('step_number'=>4,'emoji'=>'🍋','title'=>'Select Flavour','description'=>'Pure Cane (free), Citrus (+£0.50) or Tropical (+£1.00)','sort_order'=>4),
            array('step_number'=>5,'emoji'=>'🎉','title'=>'Enjoy!',         'description'=>'Served chilled — pure fresh natural goodness','sort_order'=>5),
        );
        foreach ($steps as $s) $wpdb->insert("{$wpdb->prefix}ch_order_steps", $s);
    }

    // Flavours
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ch_flavours")) {
        $flavours = array(
            array('emoji'=>'🌿','name'=>'Pure Cane',     'price'=>'Included','flavour_type'=>'Base',    'sort_order'=>1),
            array('emoji'=>'🍋','name'=>'Lemon',          'price'=>'+£0.50', 'flavour_type'=>'Citrus',  'sort_order'=>2),
            array('emoji'=>'🫚','name'=>'Ginger',         'price'=>'+£0.50', 'flavour_type'=>'Citrus',  'sort_order'=>3),
            array('emoji'=>'🌀','name'=>'Lemon & Ginger', 'price'=>'+£0.50', 'flavour_type'=>'Citrus',  'sort_order'=>4),
            array('emoji'=>'🌱','name'=>'Mint',            'price'=>'+£0.50', 'flavour_type'=>'Citrus',  'sort_order'=>5),
            array('emoji'=>'🍍','name'=>'Pineapple',      'price'=>'+£1.00', 'flavour_type'=>'Tropical','sort_order'=>6),
            array('emoji'=>'🍉','name'=>'Watermelon',     'price'=>'+£1.00', 'flavour_type'=>'Tropical','sort_order'=>7),
            array('emoji'=>'🍓','name'=>'Strawberry',     'price'=>'+£1.00', 'flavour_type'=>'Tropical','sort_order'=>8),
        );
        foreach ($flavours as $f) $wpdb->insert("{$wpdb->prefix}ch_flavours", $f);
    }

    // Events
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ch_events")) {
        $events = array(
            array('icon'=>'💒','title'=>'Weddings',        'description'=>'Add a traditional and healthy touch to your big day.','list_items'=>"Reception Drinks\nMehndi & Sangeet\nPost-Ceremony Refreshment",'sort_order'=>1),
            array('icon'=>'🏢','title'=>'Corporate Events','description'=>'Perfect for office parties, wellness days, and conferences.','list_items'=>"Office Wellness Days\nProduct Launches\nExhibitions & Fairs",'sort_order'=>2),
            array('icon'=>'🎉','title'=>'Private Parties', 'description'=>'From birthdays to garden parties, we bring the vibe.','list_items'=>"Birthday Parties\nCommunity Festivals\nGarden & BBQ Events",'sort_order'=>3),
        );
        foreach ($events as $e) $wpdb->insert("{$wpdb->prefix}ch_events", $e);
    }

    // FAQs
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ch_faqs")) {
        $faqs = array(
            array('question'=>'Do you add any sugar or preservatives?',  'answer'=>'No — 100% natural, pressed live from the stalk. The sweetness comes from the cane itself.','category'=>'Product','sort_order'=>1),
            array('question'=>'How long does the juice stay fresh?',     'answer'=>'Best enjoyed immediately. Up to 24 hours if kept chilled.','category'=>'Product','sort_order'=>2),
            array('question'=>'What events can I hire you for?',         'answer'=>'Weddings, birthdays, corporate events, festivals across the UK.','category'=>'Hire','sort_order'=>3),
            array('question'=>'Is your sugarcane juice sustainable?',    'answer'=>'Yes! Leftover fibre (bagasse) is fully biodegradable.','category'=>'General','sort_order'=>4),
        );
        foreach ($faqs as $f) $wpdb->insert("{$wpdb->prefix}ch_faqs", $f);
    }

    // Franchise Locations
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ch_franchise_locs")) {
        $locs = array('London Central','Manchester Hub','Birmingham West','Leeds North','Glasgow Fresh','Cardiff Bay');
        foreach ($locs as $i => $l) $wpdb->insert("{$wpdb->prefix}ch_franchise_locs", array('name'=>$l,'city'=>$l,'sort_order'=>$i+1));
    }

    // Benefits
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ch_benefits")) {
        $benefits = array(
            array('icon'=>'⚡','title'=>'Natural Energy Booster',   'description'=>'Instant energy with natural sugars — no additives.','sort_order'=>1),
            array('icon'=>'💧','title'=>'Hydrating & Cooling',      'description'=>'Perfect for warm days, refresh and rehydrate naturally.','sort_order'=>2),
            array('icon'=>'🌿','title'=>'Rich in Natural Nutrients','description'=>'Contains antioxidants, minerals, and electrolytes.','sort_order'=>3),
            array('icon'=>'🫁','title'=>'Supports Digestion',       'description'=>'Traditionally enjoyed with lemon and ginger.','sort_order'=>4),
            array('icon'=>'🛡️','title'=>'Boosts Immunity',          'description'=>'Natural compounds support overall wellness.','sort_order'=>5),
        );
        foreach ($benefits as $b) $wpdb->insert("{$wpdb->prefix}ch_benefits", $b);
    }

    // Showcase Slides
    if (!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ch_showcase_slides")) {
        $slides = array(
            array('title'=>'Pure Yellow Cane','subtitle'=>'Fresh & Naturally Sweet',  'image_url'=>'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=600','sort_order'=>1),
            array('title'=>'Zesty Lemon',     'subtitle'=>'Citrus Refreshment',       'image_url'=>'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=600','sort_order'=>2),
            array('title'=>'Spicy Ginger',    'subtitle'=>'Warming & Healthy',        'image_url'=>'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&q=80&w=600','sort_order'=>3),
            array('title'=>'Cooling Mint',    'subtitle'=>'Ultimate Freshness',       'image_url'=>'https://images.unsplash.com/photo-1595981267035-7b04ca84a82d?auto=format&fit=crop&q=80&w=600','sort_order'=>4),
        );
        foreach ($slides as $s) $wpdb->insert("{$wpdb->prefix}ch_showcase_slides", $s);
    }
}
add_action('after_switch_theme', 'ch_seed_defaults');
add_action('init', function() {
    if (get_option('ch_db_seeded') !== '1') {
        ch_seed_defaults();
        update_option('ch_db_seeded', '1');
    }
});

// ── HELPER: fetch active rows from any CH table ────────────────────────────────
function ch_get_active($table_suffix, $order = 'sort_order ASC') {
    global $wpdb;
    return $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}ch_{$table_suffix} WHERE status='active' ORDER BY {$order}"
    );
}
