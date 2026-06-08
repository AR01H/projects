<?php
defined( 'ABSPATH' ) || exit;

/**
 * Seeder-only data functions.
 *
 * PURPOSE: These functions are called ONLY by mock_data/seeder.php to
 * populate the database / WP options during "Install Mock Data".
 * They are NEVER called at runtime for display.
 *
 * RULES:
 *   - Plugin-managed tables (services, team, reviews, FAQs, properties):
 *     The seeder reads from mock_data/csv/ via AH_Data::load_csv().
 *     The functions below return [] — kept as stubs so any old call fails gracefully.
 *
 *   - WP-option data seeded inline (guide_nav, nav_topics, process_steps,
 *     site_stats, trust_signals): seeder still calls these functions;
 *     content lives here until migrated to real_data/ CSV/JSON files.
 *
 * For static display data with NO admin edit, use AH_Page_Data / AH_Real_Loader
 * and put the files in real_data/csv/ or real_data/json/.
 */

// ── Plugin-managed (seeded from CSV, admin-editable via CMS portal) ───────────
// Seeder uses AH_Data::load_csv() for these — functions kept as stubs only.

function ah_mock_default_settings(): array    { return []; }
function ah_mock_home_settings_array(): array  { return []; }
function ah_mock_services(): array             { return []; }
function ah_mock_team(): array                 { return []; }
function ah_mock_reviews(): array              { return []; }
function ah_mock_properties(): array           { return []; }
function ah_mock_news_bar_items(): array       { return []; }

function ah_mock_faqs( string $topic = '' ): array { return []; }

// ── WP-option data (seeder calls these directly) ──────────────────────────────
// To migrate: create real_data/csv/<name>.csv and update the helpers to use
// AH_Real_Loader instead of get_option(). Then the seeder can also be updated.

function ah_mock_guide_nav(): array {
	return [
		[ 'icon' => '🏠', 'title' => 'First-Time Buyers',    'slug' => 'first-time-buyers',  'desc' => 'Complete step-by-step guide' ],
		[ 'icon' => '🔑', 'title' => 'Moving Home',           'slug' => 'moving-home',         'desc' => 'What changes when you upsize' ],
		[ 'icon' => '🏘️', 'title' => 'Buy-to-Let',            'slug' => 'buy-to-let',          'desc' => 'Investor buying strategy' ],
		[ 'icon' => '🔍', 'title' => 'Off-Market Properties', 'slug' => 'off-market',          'desc' => 'Homes not on Rightmove' ],
		[ 'icon' => '🏗️', 'title' => 'New Builds',            'slug' => 'new-builds',          'desc' => 'Developer deals & pitfalls' ],
		[ 'icon' => '🤝', 'title' => "Using a Buyer's Agent", 'slug' => 'buyers-agent',        'desc' => 'What we do & why it works', 'highlight' => true ],
		[ 'icon' => '🏦', 'title' => 'Mortgage Guide',        'slug' => 'mortgage-guide',      'desc' => 'Rates, types & best deals' ],
		[ 'icon' => '💰', 'title' => 'Deposit Guide',         'slug' => 'deposit-guide',       'desc' => 'How much do you really need?' ],
		[ 'icon' => '📋', 'title' => 'Stamp Duty Guide',      'slug' => 'stamp-duty',          'desc' => '2025 rates & exemptions' ],
	];
}

function ah_mock_guide_categories_array(): array {
	return [
		[ 'icon' => '🏠', 'title' => 'Buying Guides',       'desc' => 'Step-by-step guides to buying your first, next, or investment property.',  'count' => 12, 'slug' => 'buying' ],
		[ 'icon' => '🏦', 'title' => 'Finance & Mortgages', 'desc' => 'Understand mortgage options, deposit requirements, stamp duty, and costs.', 'count' => 8,  'slug' => 'finance' ],
		[ 'icon' => '⚖️', 'title' => 'Legal & Surveys',    'desc' => 'Conveyancing, property surveys, legal searches, and what happens after.',   'count' => 7,  'slug' => 'legal' ],
		[ 'icon' => '🔑', 'title' => 'Moving & Settling',   'desc' => 'Area research, school catchments, removal companies, and utilities.',       'count' => 6,  'slug' => 'moving' ],
	];
}

function ah_mock_nav_buying_topics(): array {
	return [
		[ 'icon' => '🏠', 'title' => 'First-Time Buyers',    'desc' => 'Complete step-by-step guide',    'slug' => 'first-time-buyers' ],
		[ 'icon' => '🔑', 'title' => 'Moving Home',           'desc' => 'What changes when you upsize',   'slug' => 'moving-home' ],
		[ 'icon' => '🏘️', 'title' => 'Buy-to-Let',            'desc' => 'Investor buying strategy',       'slug' => 'buy-to-let' ],
		[ 'icon' => '🔍', 'title' => 'Off-Market Properties', 'desc' => 'Homes not on Rightmove',         'slug' => 'off-market' ],
		[ 'icon' => '🏗️', 'title' => 'New Builds',            'desc' => "Developer deals & pitfalls",     'slug' => 'new-builds' ],
		[ 'icon' => '🤝', 'title' => "Using a Buyer's Agent", 'desc' => 'What we do & why it works',      'slug' => 'buyers-agent', 'highlight' => true ],
	];
}

function ah_mock_nav_finance_topics(): array {
	return [
		[ 'icon' => '🏦', 'title' => 'Mortgage Guide',  'desc' => 'Rates, types & best deals',    'slug' => 'mortgage-guide' ],
		[ 'icon' => '💰', 'title' => 'Deposit Guide',   'desc' => 'How much do you really need?', 'slug' => 'deposit-guide' ],
		[ 'icon' => '📋', 'title' => 'Stamp Duty Guide','desc' => '2025 rates & exemptions',      'slug' => 'stamp-duty' ],
		[ 'icon' => '🧮', 'title' => 'Cost Calculator', 'desc' => 'Hidden costs of buying',       'slug' => 'price-calculator', 'highlight' => true ],
	];
}

function ah_mock_nav_legal_topics(): array {
	return [
		[ 'icon' => '⚖️', 'title' => 'Legal Search Packs', 'desc' => "What's hidden in the paperwork", 'slug' => 'legal-search' ],
		[ 'icon' => '📄', 'title' => 'Conveyancing Guide', 'desc' => 'The legal process explained',     'slug' => 'conveyancing' ],
		[ 'icon' => '🔬', 'title' => 'Survey Types',       'desc' => 'Which survey do you need?',       'slug' => 'surveys' ],
		[ 'icon' => '📊', 'title' => 'Property Research',  'desc' => 'Deep analysis before you buy',    'slug' => 'property-research' ],
	];
}

function ah_mock_process_steps(): array {
	return [
		[ 'num' => '01', 'title' => 'Free Consultation', 'desc' => 'We learn your brief - budget, location, must-haves, timeline. No obligation, no pressure.' ],
		[ 'num' => '02', 'title' => 'Property Search',   'desc' => 'We activate our network - estate agents, developers, and off-market connections - to source matched properties.' ],
		[ 'num' => '03', 'title' => 'Shortlisting',      'desc' => 'We visit, assess, and report on every property before you see it. You only view the best 3–5 options.' ],
		[ 'num' => '04', 'title' => 'Offer & Negotiation','desc' => 'We advise on value and negotiate hard. Our data-backed approach regularly achieves below-asking results.' ],
		[ 'num' => '05', 'title' => 'Due Diligence',     'desc' => 'Planning checks, flood risk, structural surveys, local searches - we dig deep before you commit.' ],
		[ 'num' => '06', 'title' => 'Completion Day',    'desc' => 'We manage solicitors, lenders, and agents to the finish line. You just need to pick up the keys.' ],
	];
}

function ah_mock_site_stats(): array {
	return [
		[ 'num' => '£28M+', 'label' => 'Saved for clients in negotiations' ],
		[ 'num' => '500+',  'label' => 'Homes successfully secured' ],
		[ 'num' => '94%',   'label' => 'Clients access off-market properties' ],
		[ 'num' => '4.9★',  'label' => 'Average client satisfaction rating' ],
	];
}

function ah_mock_trust_signals(): array {
	return [
		[ 'icon' => '⭐', 'text' => '4.9/5 average rating from 500+ clients' ],
		[ 'icon' => '🔍', 'text' => '94% of clients secure off-market properties' ],
		[ 'icon' => '💰', 'text' => 'Average saving of £14,200 per purchase' ],
		[ 'icon' => '🇬🇧', 'text' => 'Covering all of England & Wales' ],
		[ 'icon' => '🤝', 'text' => 'We only work for buyers - never sellers' ],
	];
}
