<?php
/**
 * scratch/database/seeder.php
 * Updated Seeder with Proper AH Database Names and Structure.
 */
defined('ABSPATH') || exit;

function ah_theme_seed_proper_data() {
    // Flag to check if we've already seeded to avoid duplicates
    if (get_option('ah_db_seeded_v7')) return;

    // 1. Seed Reviews (ah_review)
    $reviews = [
        [
            'title' => 'Saved £25k on my first home!',
            'content' => 'Advaith Homes was incredible. They found a house I loved and negotiated a huge discount.',
            'meta' => ['_ah_rating' => 5, '_ah_user_mini_desc' => 'First-time Buyer', '_ah_status' => 'Active']
        ],
        [
            'title' => 'Expert advice and smooth process',
            'content' => 'The team handled everything from survey to legal. Highly recommended.',
            'meta' => ['_ah_rating' => 5, '_ah_user_mini_desc' => 'Property Investor', '_ah_status' => 'Active']
        ]
    ];

    foreach ($reviews as $r) {
        $pid = wp_insert_post([
            'post_title'   => $r['title'],
            'post_content' => $r['content'],
            'post_status'  => 'publish',
            'post_type'    => 'ah_review'
        ]);
        if ($pid) {
            foreach ($r['meta'] as $k => $v) update_post_meta($pid, $k, $v);
        }
    }

    // 2. Seed Articles (ah_post)
    $articles = [
        [
            'title' => 'UK Property Market Trends 2026',
            'content' => 'Detailed analysis of the current market trends...',
            'meta' => ['_ah_tag' => 'MARKET', '_ah_tag_class' => 'tag-gold', '_ah_status' => 'Active', '_ah_is_suggested' => '1']
        ],
        [
            'title' => 'How to Negotiate Like a Pro',
            'content' => 'Negotiation is an art form. Here is how we do it...',
            'meta' => ['_ah_tag' => 'ADVICE', '_ah_tag_class' => 'tag-blue', '_ah_status' => 'Active']
        ]
    ];

    foreach ($articles as $a) {
        $pid = wp_insert_post([
            'post_title'   => $a['title'],
            'post_content' => $a['content'],
            'post_status'  => 'publish',
            'post_type'    => 'ah_post'
        ]);
        if ($pid) {
            foreach ($a['meta'] as $k => $v) update_post_meta($pid, $k, $v);
        }
    }

    // 3. Seed Client Projects (ah_project)
    $projects = [
        [
            'title' => 'Modern Surrey Family Home',
            'content' => 'Secured this beautiful property for a family of four.',
            'meta' => [
                '_ah_location' => 'Surrey, UK',
                '_ah_saving'   => '£35,000 Saved',
                '_ah_badge'    => 'ELITE BUY',
                '_ah_status'   => 'Active',
                '_ah_features' => "5 Bedrooms\nLarge Garden\nTop Schools Nearby"
            ]
        ]
    ];

    foreach ($projects as $p) {
        $pid = wp_insert_post([
            'post_title'   => $p['title'],
            'post_content' => $p['content'],
            'post_status'  => 'publish',
            'post_type'    => 'ah_project'
        ]);
        if ($pid) {
            foreach ($p['meta'] as $k => $v) update_post_meta($pid, $k, $v);
        }
    }

    // 4. Seed My Reports (ah_report)
    $reports = [
        [
            'title' => 'All Active Leads',
            'meta' => ['_ah_sql' => "SELECT post_title, post_date FROM wp_posts WHERE post_type = 'ah_lead' AND post_status = 'publish'"]
        ],
        [
            'title' => 'High Rated Reviews',
            'meta' => ['_ah_sql' => "SELECT p.post_title, m.meta_value as rating FROM wp_posts p JOIN wp_postmeta m ON p.ID = m.post_id WHERE p.post_type = 'ah_review' AND m.meta_key = '_ah_rating' AND m.meta_value = '5'"]
        ]
    ];

    foreach ($reports as $rep) {
        $pid = wp_insert_post([
            'post_title'  => $rep['title'],
            'post_status' => 'publish',
            'post_type'   => 'ah_report'
        ]);
        if ($pid) {
            foreach ($rep['meta'] as $k => $v) update_post_meta($pid, $k, $v);
        }
    }

    // 5. Seed Dynamic Showcase
    $showcase_json = '{
  "page": {
    "title": "Sample Feature Showcase | Advaith Homes",
    "metaDescription": "Explore all the dynamic components available for Advaith Homes detail pages.",
    "hero": {
      "eyebrow": "Feature Showcase",
      "headline": "The Complete <em>Buyer\'s Toolkit</em>",
      "subtext": "This page demonstrates every component available in our dynamic detail page template, including banners, comparison tables, and more.",
      "stats": [
        { "num": "10+", "label": "Dynamic Components" },
        { "num": "100%", "label": "Responsive Design" },
        { "num": "0", "label": "Coding Required" }
      ]
    },
    "banner": {
      "image": "https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&q=80&w=1200",
      "title": "Premium Property Research",
      "text": "Our research reports provide deep insights into property history, neighborhood safety, and investment potential."
    },
    "commitmentTitle": "Our Promise",
    "commitmentHeadline": "Integrity in Every Transaction",
    "whatWeDo": {
      "do": [
        "Provide 100% transparent advice",
        "Save you time and unnecessary stress",
        "Protect your financial interests"
      ],
      "dont": [
        "Take commissions from sellers",
        "Ignore potential property red flags",
        "Pressure you into a quick sale"
      ]
    },
    "table": {
      "tag": "Service Tiers",
      "headline": "Compare Our Report Packages",
      "columns": ["Features", "Comprehensive", "Standard", "Basic"],
      "rows": [
        {
          "category": "Comparable Listings",
          "items": [
            { "name": "Recent Sales", "desc": "Data on similar properties sold nearby", "values": [true, true, false] },
            { "name": "Available for Sale", "desc": "Current market competition", "values": [true, true, false] },
            { "name": "For Rent", "desc": "Investment yield potential", "values": [true, false, false] }
          ]
        }
      ]
    },
    "process": {
      "tag": "The Method",
      "headline": "From Search to Keys",
      "steps": [
        { "title": "Step 1", "desc": "This is a sample process description." },
        { "title": "Step 2", "desc": "This demonstrates how the timeline flows." },
        { "title": "Step 3", "desc": "Each step has a dot and a line connecting them." }
      ]
    }
  }
}';

    $pid = wp_insert_post([
        'post_title' => 'Dynamic Feature Showcase',
        'post_status' => 'publish',
        'post_type' => 'ah_post'
    ]);
    if ($pid) {
        update_post_meta($pid, '_ah_page_builder_json', $showcase_json);
        update_post_meta($pid, '_ah_status', 'Active');
    }

    update_option('ah_db_seeded_v7', true);
}
add_action('init', 'ah_theme_seed_proper_data');
