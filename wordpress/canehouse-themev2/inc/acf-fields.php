<?php
/**
 * inc/acf-fields.php
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THIS FILE EXISTS:
 *   ACF (Advanced Custom Fields) lets us add custom input boxes to the
 *   WordPress admin edit screens. Instead of using the ACF plugin's UI to
 *   create fields (which stores them in the DB and can be lost), we define
 *   them here in code — they always exist, even after a DB reset.
 *
 * HOW IT WORKS:
 *   acf_add_local_field_group() registers a group of fields.
 *   'location' says which edit screen they appear on (which post type).
 *   'fields' is the array of inputs — text, image, select, repeater etc.
 *
 * FIELD TYPES USED:
 *   text      → single line input
 *   textarea  → multi-line input
 *   image     → WordPress media uploader (returns image ID or URL)
 *   select    → dropdown
 *   true_false→ toggle/checkbox
 *   repeater  → repeatable group of sub-fields (like a table of rows)
 *   number    → numeric input
 *   url       → URL input
 *
 * REQUIRES: ACF Free or ACF Pro plugin to be installed.
 *           Repeater field requires ACF Pro.
 * ─────────────────────────────────────────────────────────────────────────────
 */

if (!defined('ABSPATH')) exit;

// Safety check — if ACF is not installed, do nothing
if (!function_exists('acf_add_local_field_group')) return;


add_action('acf/init', function () {


// ════════════════════════════════════════════════════════════════════════════
// 1. SPECIALTIES — 🥤 Our Specialties page (pages/our-specialties.php)
// ════════════════════════════════════════════════════════════════════════════
// HOW USED IN TEMPLATE:
//   $items = get_posts(['post_type'=>'ch_specialty','posts_per_page'=>-1]);
//   foreach($items as $item) {
//     $icon     = get_field('spec_icon',    $item->ID);
//     $badge    = get_field('spec_badge',   $item->ID);
//     $tagline  = get_field('spec_tagline', $item->ID);
//     $category = get_field('spec_category',$item->ID);
//     $price    = get_field('spec_price',   $item->ID);
//     $tags     = get_field('spec_tags',    $item->ID); // array
//     $color    = get_field('spec_color',   $item->ID);
//   }

acf_add_local_field_group([
    'key'      => 'group_specialty',
    'title'    => '🥤 Specialty Details',
    'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'ch_specialty']]],
    'fields'   => [

        // Title is already the WP post title (juice name)
        // These are the extra fields below the title box:

        [
            'key'          => 'field_spec_icon',
            'label'        => 'Icon (Emoji)',
            'name'         => 'spec_icon',
            'type'         => 'text',
            'instructions' => 'Paste one emoji. e.g. 🌿 or ⚡',
            'placeholder'  => '🥤',
        ],
        [
            'key'          => 'field_spec_badge',
            'label'        => 'Badge Text',
            'name'         => 'spec_badge',
            'type'         => 'text',
            'instructions' => 'e.g. Bestseller, Fan Favourite — leave blank to hide badge',
            'placeholder'  => 'Bestseller',
        ],
        [
            'key'          => 'field_spec_tagline',
            'label'        => 'Tagline',
            'name'         => 'spec_tagline',
            'type'         => 'text',
            'instructions' => 'Short descriptor shown under the name. e.g. Pure · Unadulterated · Timeless',
            'placeholder'  => 'Pure · Natural · Fresh',
        ],
        [
            'key'          => 'field_spec_desc',
            'label'        => 'Description',
            'name'         => 'spec_desc',
            'type'         => 'textarea',
            'rows'         => 3,
            'instructions' => '2-3 sentences about this specialty.',
        ],
        [
            'key'          => 'field_spec_category',
            'label'        => 'Filter Category',
            'name'         => 'spec_category',
            'type'         => 'select',
            'instructions' => 'Used for the filter buttons on the specialties page.',
            'choices'      => [
                'classic'  => 'Classic',
                'citrus'   => 'Citrus',
                'tropical' => 'Tropical',
                'wellness' => 'Wellness',
            ],
            'default_value'=> 'classic',
        ],
        [
            'key'         => 'field_spec_price',
            'label'       => 'Price',
            'name'        => 'spec_price',
            'type'        => 'text',
            'placeholder' => 'From £4.00',
        ],
        [
            'key'         => 'field_spec_price_note',
            'label'       => 'Price Note',
            'name'        => 'spec_price_note',
            'type'        => 'text',
            'placeholder' => '250ml – 1.5L',
        ],
        [
            // REPEATER: adds multiple ingredient tag rows
            // Each row = one pill shown on the card
            'key'          => 'field_spec_tags',
            'label'        => 'Ingredient Tags',
            'name'         => 'spec_tags',
            'type'         => 'repeater',        // requires ACF Pro
            'button_label' => '+ Add Tag',
            'instructions' => 'Add up to 4 ingredient tags shown as pills on the card.',
            'sub_fields'   => [
                [
                    'key'         => 'field_spec_tag_text',
                    'label'       => 'Tag',
                    'name'        => 'tag',
                    'type'        => 'text',
                    'placeholder' => 'e.g. Yellow Cane',
                ],
            ],
        ],
        [
            'key'          => 'field_spec_color',
            'label'        => 'Card Colour',
            'name'         => 'spec_color',
            'type'         => 'select',
            'instructions' => 'Sets the card header gradient. Match to the flavour mood.',
            'choices'      => [
                'card-classic'  => 'Classic Green',
                'card-mint'     => 'Mint Dark',
                'card-ginger'   => 'Ginger Brown',
                'card-tropical' => 'Tropical Blue',
                'card-coconut'  => 'Coconut Teal',
                'card-detox'    => 'Detox Lime',
                'card-energy'   => 'Energy Amber',
            ],
        ],
        [
            'key'           => 'field_spec_order',
            'label'         => 'Sort Order',
            'name'          => 'spec_order',
            'type'          => 'number',
            'instructions'  => 'Lower number = shown first. Use 10, 20, 30 so you can insert between.',
            'default_value' => 10,
            'min'           => 0,
        ],
    ],
]);


// ════════════════════════════════════════════════════════════════════════════
// 2. TESTIMONIALS — ⭐ Reviews page (pages/reviews-gallery.php)
// ════════════════════════════════════════════════════════════════════════════
// HOW USED IN TEMPLATE:
//   $reviews = get_posts(['post_type'=>'ch_testimonial','posts_per_page'=>-1]);
//   foreach($reviews as $r) {
//     $text   = get_field('testi_review', $r->ID);
//     $stars  = get_field('testi_stars',  $r->ID);
//     $type   = get_field('testi_type',   $r->ID);
//     $avatar = get_field('testi_avatar', $r->ID); // returns image array
//     $handle = get_field('testi_handle', $r->ID);
//   }

acf_add_local_field_group([
    'key'      => 'group_testimonial',
    'title'    => '⭐ Review Details',
    'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'ch_testimonial']]],
    'fields'   => [

        // WP post title = reviewer name (shows in admin list)

        [
            'key'          => 'field_testi_review',
            'label'        => 'Review Text',
            'name'         => 'testi_review',
            'type'         => 'textarea',
            'rows'         => 4,
            'instructions' => 'The actual review text shown in the card.',
        ],
        [
            'key'          => 'field_testi_stars',
            'label'        => 'Star Rating',
            'name'         => 'testi_stars',
            'type'         => 'select',
            'choices'      => ['5'=>'★★★★★  5 Stars','4'=>'★★★★☆  4 Stars','3'=>'★★★☆☆  3 Stars'],
            'default_value'=> '5',
        ],
        [
            'key'          => 'field_testi_type',
            'label'        => 'Customer Type',
            'name'         => 'testi_type',
            'type'         => 'text',
            'instructions' => 'e.g. Wedding Client, Regular Customer, Corporate Event',
            'placeholder'  => 'Verified Customer',
        ],
        [
            'key'          => 'field_testi_avatar',
            'label'        => 'Avatar Photo',
            'name'         => 'testi_avatar',
            'type'         => 'image',
            'instructions' => 'Customer photo. Square, at least 100x100px.',
            'return_format'=> 'array',  // returns ['url'=>..., 'alt'=>...]
            'preview_size' => 'thumbnail',
        ],
        [
            'key'         => 'field_testi_handle',
            'label'       => 'Social Handle / Instagram',
            'name'        => 'testi_handle',
            'type'        => 'text',
            'placeholder' => '@username',
        ],
        [
            'key'           => 'field_testi_featured',
            'label'         => 'Feature on Homepage?',
            'name'          => 'testi_featured',
            'type'          => 'true_false',
            'instructions'  => 'Turn on to show this review on the homepage slider.',
            'default_value' => 0,
            'ui'            => 1,   // shows as a toggle switch
        ],
    ],
]);


// ════════════════════════════════════════════════════════════════════════════
// 3. LOCATIONS — 📍 Contact page store locator (pages/contact-us.php)
// ════════════════════════════════════════════════════════════════════════════
// HOW USED IN TEMPLATE:
//   $locations = get_posts(['post_type'=>'ch_location','posts_per_page'=>-1]);
//   foreach($locations as $loc) {
//     $city    = get_field('loc_city',    $loc->ID);
//     $address = get_field('loc_address', $loc->ID);
//     $hours   = get_field('loc_hours',   $loc->ID);
//     $status  = get_field('loc_status',  $loc->ID); // 'open' or 'coming'
//     $maps    = get_field('loc_maps',    $loc->ID); // Google Maps URL
//   }

acf_add_local_field_group([
    'key'      => 'group_location',
    'title'    => '📍 Location Details',
    'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'ch_location']]],
    'fields'   => [

        // WP post title = location name (e.g. "London Central")

        [
            'key'         => 'field_loc_city',
            'label'       => 'City',
            'name'        => 'loc_city',
            'type'        => 'text',
            'placeholder' => 'London',
        ],
        [
            'key'         => 'field_loc_address',
            'label'       => 'Full Address',
            'name'        => 'loc_address',
            'type'        => 'text',
            'placeholder' => 'Central London Market, London EC1V 2NX',
        ],
        [
            'key'         => 'field_loc_hours',
            'label'       => 'Opening Hours',
            'name'        => 'loc_hours',
            'type'        => 'text',
            'placeholder' => 'Mon–Fri: 8am–8pm · Sat–Sun: 9am–9pm',
        ],
        [
            'key'          => 'field_loc_status',
            'label'        => 'Status',
            'name'         => 'loc_status',
            'type'         => 'select',
            'choices'      => ['open' => '✅ Open Now', 'coming' => '🔜 Coming Soon'],
            'default_value'=> 'open',
        ],
        [
            'key'          => 'field_loc_maps',
            'label'        => 'Google Maps URL',
            'name'         => 'loc_maps',
            'type'         => 'url',
            'instructions' => 'Paste the full Google Maps link for this location.',
        ],
        [
            'key'          => 'field_loc_embed',
            'label'        => 'Google Maps Embed URL',
            'name'         => 'loc_embed',
            'type'         => 'url',
            'instructions' => 'Maps embed src URL (from Share → Embed a Map → copy src value only).',
        ],
        [
            'key'           => 'field_loc_order',
            'label'         => 'Sort Order',
            'name'          => 'loc_order',
            'type'          => 'number',
            'default_value' => 10,
        ],
    ],
]);


// ════════════════════════════════════════════════════════════════════════════
// 4. EVENTS — 🎪 Events & Catering page (pages/events.php)
// ════════════════════════════════════════════════════════════════════════════
// HOW USED IN TEMPLATE:
//   $events = get_posts(['post_type'=>'ch_event','posts_per_page'=>-1]);
//   foreach($events as $ev) {
//     $icon     = get_field('ev_icon',     $ev->ID);
//     $subtitle = get_field('ev_subtitle', $ev->ID);
//     $desc     = get_field('ev_desc',     $ev->ID);
//     $features = get_field('ev_features', $ev->ID); // repeater array
//     $color    = get_field('ev_color',    $ev->ID);
//   }

acf_add_local_field_group([
    'key'      => 'group_event',
    'title'    => '🎪 Event Type Details',
    'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'ch_event']]],
    'fields'   => [

        // WP post title = event type name (e.g. "Weddings")

        [
            'key'         => 'field_ev_icon',
            'label'       => 'Icon (Emoji)',
            'name'        => 'ev_icon',
            'type'        => 'text',
            'placeholder' => '💒',
        ],
        [
            'key'         => 'field_ev_subtitle',
            'label'       => 'Subtitle',
            'name'        => 'ev_subtitle',
            'type'        => 'text',
            'placeholder' => 'The Most Special Day',
        ],
        [
            'key'  => 'field_ev_desc',
            'label'=> 'Description',
            'name' => 'ev_desc',
            'type' => 'textarea',
            'rows' => 3,
        ],
        [
            // Repeater: bullet point list of features/uses for this event type
            'key'          => 'field_ev_features',
            'label'        => 'Features / Use Cases',
            'name'         => 'ev_features',
            'type'         => 'repeater',
            'button_label' => '+ Add Feature',
            'instructions' => 'Add bullet points shown in the card (e.g. "Reception welcome drinks")',
            'sub_fields'   => [
                [
                    'key'   => 'field_ev_feature_text',
                    'label' => 'Feature',
                    'name'  => 'feature',
                    'type'  => 'text',
                ],
            ],
        ],
        [
            'key'          => 'field_ev_color',
            'label'        => 'Card Theme Colour',
            'name'         => 'ev_color',
            'type'         => 'select',
            'choices'      => [
                'ev-weddings'  => 'Purple (Weddings)',
                'ev-corporate' => 'Navy (Corporate)',
                'ev-parties'   => 'Green (Parties)',
                'ev-popups'    => 'Olive (Popups)',
                'ev-festivals' => 'Red-Brown (Festivals)',
                'ev-catering'  => 'Teal (Catering)',
            ],
        ],
        [
            'key'           => 'field_ev_order',
            'label'         => 'Sort Order',
            'name'          => 'ev_order',
            'type'          => 'number',
            'default_value' => 10,
        ],
    ],
]);


// ════════════════════════════════════════════════════════════════════════════
// 5. GALLERY — 🖼️ Gallery section (pages/reviews-gallery.php)
// ════════════════════════════════════════════════════════════════════════════
// HOW USED IN TEMPLATE:
//   $gallery = get_posts(['post_type'=>'ch_gallery','posts_per_page'=>-1]);
//   foreach($gallery as $g) {
//     $img     = get_field('gal_image',    $g->ID); // returns image array
//     $caption = get_field('gal_caption',  $g->ID);
//     $type    = get_field('gal_type',     $g->ID); // for filter tabs
//     $size    = get_field('gal_size',     $g->ID); // normal/wide/tall
//   }

acf_add_local_field_group([
    'key'      => 'group_gallery',
    'title'    => '🖼️ Gallery Image Details',
    'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'ch_gallery']]],
    'fields'   => [

        // WP post title = internal label (not shown on frontend)

        [
            'key'          => 'field_gal_image',
            'label'        => 'Image',
            'name'         => 'gal_image',
            'type'         => 'image',
            'instructions' => 'Upload the gallery photo. Min 800px wide.',
            'return_format'=> 'array',
            'preview_size' => 'medium',
        ],
        [
            'key'         => 'field_gal_caption',
            'label'       => 'Caption',
            'name'        => 'gal_caption',
            'type'        => 'text',
            'placeholder' => 'Classic Cane · Live Pressed',
        ],
        [
            'key'          => 'field_gal_type',
            'label'        => 'Category (Filter Tab)',
            'name'         => 'gal_type',
            'type'         => 'select',
            'instructions' => 'Which tab this image appears under.',
            'choices'      => [
                'drinks'    => 'Drinks',
                'interiors' => 'Interiors',
                'customers' => 'Customers',
                'events'    => 'Events & Catering',
            ],
        ],
        [
            'key'          => 'field_gal_size',
            'label'        => 'Card Size',
            'name'         => 'gal_size',
            'type'         => 'select',
            'instructions' => 'Wide = spans 2 columns. Tall = spans 2 rows. Normal = 1x1.',
            'choices'      => [
                'normal' => 'Normal (1×1)',
                'wide'   => 'Wide (2×1)',
                'tall'   => 'Tall (1×2)',
            ],
            'default_value'=> 'normal',
        ],
        [
            'key'           => 'field_gal_order',
            'label'         => 'Sort Order',
            'name'          => 'gal_order',
            'type'          => 'number',
            'default_value' => 10,
        ],
    ],
]);


// ════════════════════════════════════════════════════════════════════════════
// 6. FRANCHISE ENQUIRIES — 🤝 (admin view only, filled by form)
// ════════════════════════════════════════════════════════════════════════════
// HOW CREATED: contact-leads.php saves form data into these fields.
// HOW VIEWED:  Admin opens the enquiry post to see all submitted data.
// NOTE: These fields are read-only from admin — form fills them programmatically.

acf_add_local_field_group([
    'key'      => 'group_enquiry',
    'title'    => '🤝 Enquiry Submission Data',
    'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'ch_enquiry']]],
    'fields'   => [

        // WP post title = enquirer's full name

        [
            'key'      => 'field_enq_email',
            'label'    => 'Email Address',
            'name'     => 'enq_email',
            'type'     => 'text',
            'readonly' => 1,
        ],
        [
            'key'      => 'field_enq_phone',
            'label'    => 'Phone Number',
            'name'     => 'enq_phone',
            'type'     => 'text',
            'readonly' => 1,
        ],
        [
            'key'      => 'field_enq_city',
            'label'    => 'City / Location Interest',
            'name'     => 'enq_city',
            'type'     => 'text',
            'readonly' => 1,
        ],
        [
            'key'      => 'field_enq_type',
            'label'    => 'Enquiry Type',
            'name'     => 'enq_type',
            'type'     => 'text',
            'readonly' => 1,
        ],
        [
            'key'      => 'field_enq_message',
            'label'    => 'Message',
            'name'     => 'enq_message',
            'type'     => 'textarea',
            'readonly' => 1,
        ],
        [
            'key'      => 'field_enq_submitted',
            'label'    => 'Submitted At',
            'name'     => 'enq_submitted',
            'type'     => 'text',
            'readonly' => 1,
        ],
        [
            'key'          => 'field_enq_status',
            'label'        => 'Lead Status',
            'name'         => 'enq_status',
            'type'         => 'select',
            'instructions' => 'Track where this lead is in the pipeline.',
            'choices'      => [
                'new'       => '🔵 New',
                'contacted' => '🟡 Contacted',
                'qualified' => '🟠 Qualified',
                'closed'    => '🟢 Closed / Won',
                'lost'      => '🔴 Lost',
            ],
            'default_value'=> 'new',
        ],
        [
            'key'          => 'field_enq_notes',
            'label'        => 'Admin Notes',
            'name'         => 'enq_notes',
            'type'         => 'textarea',
            'instructions' => 'Internal notes about this lead. Not visible to the enquirer.',
            'rows'         => 4,
        ],
    ],
]);


}); // end acf/init
