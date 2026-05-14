<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ah_theme_seed_default_data() {
    // Flag to check if we've already seeded to avoid duplicates
    if (get_option('ah_theme_seeded')) {
        return;
    }

    // 1. Seed Benefits (Why Us)
    $benefits = [
        [
            'title' => 'Hidden Property Problems',
            'content' => "Most buyers miss structural issues, planning problems, and neighbourhood concerns that only an expert eye catches — before you're legally committed.",
            'meta' => ['icon' => '🔍']
        ],
        [
            'title' => 'Overpaying Without Knowing',
            'content' => "Without access to real comparable data and skilled negotiation, most buyers pay 5—15% above fair market value. That's tens of thousands of pounds.",
            'meta' => ['icon' => '💸']
        ],
        [
            'title' => 'Months of Wasted Time',
            'content' => "The average first-time buyer spends 6—18 months searching, viewing unsuitable properties, and losing out to other buyers. We cut that dramatically.",
            'meta' => ['icon' => '⏱️']
        ],
        [
            'title' => 'Legal & Survey Surprises',
            'content' => "Conveyancing, surveys, searches — the legal process is a minefield. We ensure nothing slips through, protecting you from nasty surprises after exchange.",
            'meta' => ['icon' => '⚖️']
        ],
        [
            'title' => 'No One In Your Corner',
            'content' => "The seller has their agent, their solicitor, their surveyor. You have… nobody. Until now. Advaith Homes is 100% on your side, every single step.",
            'meta' => ['icon' => '🛡️']
        ],
        [
            'title' => 'Confusing, Stressful Process',
            'content' => "Most buyers have never done this before. We give you a clear roadmap, explain every step in plain English, and handle the complexity so you don't have to.",
            'meta' => ['icon' => '🧭']
        ]
    ];

    foreach ($benefits as $b) {
        $post_id = wp_insert_post([
            'post_title' => $b['title'],
            'post_content' => $b['content'],
            'post_status' => 'publish',
            'post_type' => 'benefit'
        ]);
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, 'icon', $b['meta']['icon']);
        }
    }

    // 2. Seed Services
    $services = [
        [
            'title' => 'Property Search',
            'content' => 'Use the knowledge of the local market and networks to access a wide range of properties, including those that may not be publicly advertised.',
            'meta' => ['image_url' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80']
        ],
        [
            'title' => 'Negotiations',
            'content' => 'Negotiate the purchase price and terms on behalf of the clients. The expertise and negotiation skills can often result in better terms and lower prices.',
            'meta' => ['image_url' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=800&q=80']
        ],
        [
            'title' => 'Market Analysis and Advise',
            'content' => 'Provide buyers with detailed analysis and insights on the local market, including schools, crime rates, neighborhoods, amenities, and price trends.',
            'meta' => ['image_url' => 'https://images.unsplash.com/photo-1560520653-9e0e4c89eb11?w=800&q=80']
        ],
    ];

    foreach ($services as $s) {
        $post_id = wp_insert_post([
            'post_title' => $s['title'],
            'post_content' => $s['content'],
            'post_status' => 'publish',
            'post_type' => 'service'
        ]);
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, 'image_url', $s['meta']['image_url']);
        }
    }

    // 3. Seed Essential Pages
    $pages = [
        'services' => 'Services',
        'about' => 'About Us',
        'previous-clients' => 'Client Stories',
        'contact' => 'Contact',
        'privacy-policy' => 'Privacy Policy',
        'terms' => 'Terms & Conditions',
        'refund-policy' => 'Refund Policy',
        'property-research' => 'Property Research Report',
        'legal-search' => 'Legal Search Packs',
        'buyers-guide' => 'Buyer\'s Guide',
        'deposit-guide' => 'Deposit Guide',
        'mortgage-guide' => 'Mortgage Guide',
        'moving-guide' => 'Moving Guide',
        'price-calculator' => 'Price Calculator',
        'free-consultation' => 'Free Consultation Guide',
        'blog' => 'Blog',
        'news' => 'Market News',
        'buying' => 'Buying Advice'
    ];

    foreach ($pages as $slug => $title) {
        $page_check = get_page_by_path($slug);
        if (!isset($page_check->ID)) {
            wp_insert_post([
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => '<!-- Page content will be loaded from template -->',
                'post_status' => 'publish',
                'post_type' => 'page'
            ]);
        }
    }

    // 4. Seed Professional Blog Posts & Podcasts
    $blogs = [
        [
            'title' => 'UK Government Announces 1,500 New Affordable Homes',
            'type' => 'news',
            'style' => 'standard',
            'summary' => 'The government confirmed a new programme to fast-track planning for 1,500 affordable homes across England.',
            'tag' => 'POLICY',
            'color' => '#ef4444'
        ],
        [
            'title' => 'Moving Day: Smooth Moves Start With These 5 Tips',
            'type' => 'blog',
            'style' => 'podcast',
            'summary' => 'Packing can be overwhelming, but starting early can make all the difference. Discover top hacks here.',
            'tag' => 'MOVING',
            'color' => '#8b5cf6',
            'eid' => 'S9E6',
            'btn' => 'Listen Now'
        ],
        [
            'title' => 'London Hotspots: Where to Invest in 2026',
            'type' => 'blog',
            'style' => 'standard',
            'summary' => 'Our expert team identifies the top 5 growth areas in London for high rental yields and capital growth.',
            'tag' => 'INVEST',
            'color' => '#f97316'
        ]
    ];

    foreach ($blogs as $b) {
        $check = get_page_by_path(sanitize_title($b['title']), OBJECT, 'post');
        if (!$check) {
            $pid = wp_insert_post([
                'post_title' => $b['title'],
                'post_content' => 'Full article content for ' . $b['title'],
                'post_status' => 'publish',
                'post_type' => 'post'
            ]);
            if ($pid) {
                update_post_meta($pid, 'ah_post_type', $b['type']);
                update_post_meta($pid, 'ah_card_style', $b['style']);
                update_post_meta($pid, 'ah_mini_info', $b['summary']);
                update_post_meta($pid, 'ah_tag_text', $b['tag']);
                update_post_meta($pid, 'ah_tag_color', $b['color']);
                if (isset($b['eid'])) update_post_meta($pid, 'ah_episode_id', $b['eid']);
                if (isset($b['btn'])) update_post_meta($pid, 'ah_btn_label', $b['btn']);
            }
        }
    }
    // 4. Seed Blog Posts
    $blogs = [
        'london-hotspots-2026' => 'London Hotspots 2026: Top 5 Growth Areas',
        'negotiation-secrets' => 'Negotiation Secrets: How to Save £20k+ on Your Home',
        'property-rules-2026' => 'UK Property Law Changes You Need to Know for 2026',
        'mortgage-secrets-2026' => 'Unlock the Best Mortgage Rates with These Hidden Secrets',
        'midlands-boom-2026' => 'Why The Midlands is the Smartest Property Buy in 2026',
        'first-time-buyer-2026' => 'The Ultimate Step-by-Step Key for First-Time Buyers',
        'hidden-costs-buying' => 'The Extra £10k: Hidden Costs of Buying Property',
        'new-build-vs-period' => 'New Build vs Period Homes: Which Wins in 2026?',
        'shared-ownership-reality' => 'Shared Ownership: The Reality Check You Need',
        'digital-legals-2026' => 'Paperwork-Free Buying: The Rise of Digital Legals'
    ];

    foreach ($blogs as $slug => $title) {
        $post_check = get_page_by_path($slug, OBJECT, 'post');
        if (!isset($post_check->ID)) {
            wp_insert_post([
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => '<p>Buying a home in the current UK market requires more than just a search—it requires strategy. In this article, we dive deep into the latest data and insights to help you make an informed decision.</p><p>Our team at Advaith Homes has analyzed thousands of data points to bring you this exclusive guide. Whether you are a first-time buyer or an experienced investor, understanding these trends is the key to securing your dream property at the right price.</p>',
                'post_status' => 'publish',
                'post_type' => 'post'
            ]);
        }
    }

    // 5. Seed Featured Properties
    $properties = [
        [
            'title' => 'Luxury Family Home',
            'slug' => 'luxury-family-home',
            'content' => 'A stunning 5-bedroom property in the heart of Surrey.',
            'meta' => [
                'price' => '£1.2M',
                'location' => 'Surrey',
                'saved' => 'Saved £45k',
                'image_url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80'
            ]
        ],
        [
            'title' => 'Modern Apartment',
            'slug' => 'modern-apartment',
            'content' => 'Exclusive riverside living in Richmond.',
            'meta' => [
                'price' => '£850k',
                'location' => 'Richmond',
                'saved' => 'Saved £20k',
                'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80'
            ]
        ],
        [
            'title' => 'Premium City Residence',
            'slug' => 'premium-city-residence',
            'content' => 'High-end penthouse in Central London.',
            'meta' => [
                'price' => '£1.5M',
                'location' => 'London',
                'saved' => 'Saved £55k',
                'image_url' => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?w=800&q=80'
            ]
        ],
        [
            'title' => 'Country Estate',
            'slug' => 'country-estate',
            'content' => 'Beautiful coastal property with vast land.',
            'meta' => [
                'price' => '£950k',
                'location' => 'Cornwall',
                'saved' => 'Saved £30k',
                'image_url' => 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=800&q=80'
            ]
        ]
    ];

    foreach ($properties as $p) {
        $post_check = get_page_by_path($p['slug'], OBJECT, 'property');
        if (!isset($post_check->ID)) {
            $post_id = wp_insert_post([
                'post_title' => $p['title'],
                'post_name' => $p['slug'],
                'post_content' => $p['content'],
                'post_status' => 'publish',
                'post_type' => 'property'
            ]);
            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, 'price', $p['meta']['price']);
                update_post_meta($post_id, 'location', $p['meta']['location']);
                update_post_meta($post_id, 'saved', $p['meta']['saved']);
                update_post_meta($post_id, 'image_url', $p['meta']['image_url']);
            }
        }
    }

    // Mark as seeded
    update_option('ah_theme_seeded_v6', true);
}
// Hook into after_switch_theme so it only runs when the theme is activated
add_action('after_switch_theme', 'ah_theme_seed_default_data');

// Run seeder directly if not seeded yet to fix user's missing pages immediately
if (!get_option('ah_theme_seeded_v6')) {
    add_action('init', 'ah_theme_seed_default_data');
}
