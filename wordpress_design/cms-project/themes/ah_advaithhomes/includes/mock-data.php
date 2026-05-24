<?php
defined( 'ABSPATH' ) || exit;

/**
 * Mock data fallback functions.
 * These are ONLY used when the DB has no data.
 * To populate the DB, use the Theme Admin → Install Mock Data tool.
 */

function ah_mock_default_settings(): array {
	return [
		'phone'            => CLIENT_PHONE,
		'email'            => CLIENT_EMAIL,
		'address'          => CLIENT_ADDRESS,
		'facebook_url'     => '',
		'instagram_url'    => '',
		'twitter_url'      => '',
		'linkedin_url'     => '',
		'youtube_url'      => '',
		'consultation_url' => '/contact/',
		'tagline'          => "The UK's buyer's agent - working exclusively for you.",
		'map_embed_url'    => 'https://maps.google.com/maps?q=London,UK&output=embed&z=12',
	];
}

function ah_mock_home_settings_array(): array {
	return [
		'hero_headline'      => "Make Smarter<br><em>Property Decisions</em>",
		'hero_subline'       => "Navigating the UK housing market can be complex, but having access to the right information makes all the difference. With unbiased market data, expert guidance, and practical tools, you can make confident property decisions based on facts rather than speculation. Whether you're buying your first home, investing, or simply exploring the market, our insights help you better understand trends, pricing, and opportunities across the UK.",
		'hero_cta_label'     => 'Book a Free Consultation',
		'hero_cta_url'       => '/contact/',
		'hero_stat_1'        => '£28M+',
		'hero_stat_1_label'  => 'Saved for clients',
		'hero_stat_2'        => '94%',
		'hero_stat_2_label'  => 'Off-market success rate',
		'hero_stat_3'        => '500+',
		'hero_stat_3_label'  => 'Homes secured',
		'hero_stat_4'        => '4.9★',
		'hero_stat_4_label'  => 'Average client rating',
	];
}

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

function ah_mock_news_bar_items(): array {
	return [
		'✦ Mortgage rates update: average 5-year fix now at 4.2% - our guide explains what this means for buyers',
		'✦ Off-market deals available now in London, Bristol, and Manchester - speak to our team today',
		'✦ Stamp duty relief for first-time buyers extended - check our calculator for your savings',
		'✦ New: Free 30-minute consultation with a buyer\'s agent - limited slots available this week',
		'✦ Q1 2025: Average negotiation saving for ' . CLIENT_PRIMARY_TITLE . ' clients was £14,200',
	];
}

function ah_mock_services(): array {
	return [
		(object)[ 'id' => 1, 'title' => 'Property Search & Sourcing',    'summary' => 'We access the full market - including off-market and pre-market properties - to find homes that match your exact brief.',                                                 'icon' => '🔍', 'status' => 'active', 'sort_order' => 1 ],
		(object)[ 'id' => 2, 'title' => 'Negotiation & Offer Strategy',  'summary' => 'Our agents have negotiated hundreds of purchases and know how to position your offer to win at the right price - often saving clients 3–8%.',                            'icon' => '🤝', 'status' => 'active', 'sort_order' => 2 ],
		(object)[ 'id' => 3, 'title' => 'Due Diligence & Research',      'summary' => 'We dig deep into every property - planning history, flood risk, structural issues, and comparable sales - so you buy with full confidence.',                             'icon' => '📋', 'status' => 'active', 'sort_order' => 3 ],
		(object)[ 'id' => 4, 'title' => 'Legal & Survey Coordination',   'summary' => 'We manage solicitors, surveyors, and mortgage brokers so you never have to chase anyone. One point of contact from offer to completion.',                                'icon' => '⚖️', 'status' => 'active', 'sort_order' => 4 ],
		(object)[ 'id' => 5, 'title' => 'Relocation Service',            'summary' => 'Moving from overseas or another city? We cover everything from area research and school catchments to removals coordination and council registration.',                   'icon' => '✈️', 'status' => 'active', 'sort_order' => 5 ],
		(object)[ 'id' => 6, 'title' => 'Investment Portfolio Building', 'summary' => 'Building a buy-to-let portfolio? We source high-yield properties, analyse rental returns, and structure your acquisitions for long-term growth.',                        'icon' => '📈', 'status' => 'active', 'sort_order' => 6 ],
	];
}

function ah_mock_team(): array {
	return [
		(object)[ 'id' => 1, 'name' => 'James Whitfield', 'role' => "Founder & Lead Buyer's Agent", 'bio' => '15 years sourcing off-market properties across London and the South East. Former senior negotiator at a top-5 estate agency.', 'photo_url' => '', 'status' => 'active', 'sort_order' => 1 ],
		(object)[ 'id' => 2, 'name' => 'Priya Sharma',    'role' => 'Senior Property Analyst',      'bio' => 'MRICS-qualified specialist in development site analysis and planning research. Particular expertise in new-build negotiations.',   'photo_url' => '', 'status' => 'active', 'sort_order' => 2 ],
		(object)[ 'id' => 3, 'name' => 'Tom Harding',     'role' => 'Relocation Specialist',         'bio' => 'Helps international buyers and city movers find homes outside London. Covers the Midlands, North West, and South West.',          'photo_url' => '', 'status' => 'active', 'sort_order' => 3 ],
		(object)[ 'id' => 4, 'name' => 'Anika Patel',     'role' => 'Client Operations Manager',    'bio' => 'Keeps every transaction on track from offer to keys. Manages solicitor chains, surveyor bookings, and lender timelines.',         'photo_url' => '', 'status' => 'active', 'sort_order' => 4 ],
	];
}

function ah_mock_reviews(): array {
	return [
		(object)[ 'id' => 1, 'author_name' => 'Sarah & Marcus T.', 'location' => 'First-time buyers - East London',      'review_text' => 'James found us a flat off-market, negotiated £18,000 off the asking price, and coordinated everything. Completion was 7 weeks from offer. Cannot recommend enough.',                    'rating' => 5.0, 'result' => 'Saved £18,000 vs asking price',     'status' => 'active' ],
		(object)[ 'id' => 2, 'author_name' => 'Dr. Ravi Menon',    'location' => 'Relocating from Dubai - Surrey',        'review_text' => 'Bought a 5-bedroom home in Surrey entirely remotely. Priya handled every site visit, due diligence report, and legal question on my behalf. Flawless service.',                          'rating' => 5.0, 'result' => 'Full remote purchase completed',     'status' => 'active' ],
		(object)[ 'id' => 3, 'author_name' => 'Claire Ashworth',   'location' => 'Buy-to-let investor - Manchester',      'review_text' => "Third property I've bought through " . CLIENT_PRIMARY_TITLE . ". Average yield across my portfolio is 7.4%. They genuinely understand investment criteria, not just 'nice homes'.",                         'rating' => 5.0, 'result' => '7.4% avg portfolio yield',          'status' => 'active' ],
		(object)[ 'id' => 4, 'author_name' => 'Henry & Jo Blackwell','location' => 'Upsizing - Bristol',                  'review_text' => 'After two failed offers on homes we found ourselves, we brought in ' . CLIENT_PRIMARY_TITLE . '. They found a better property and we got it first time. Three months of stress gone in three weeks.',   'rating' => 5.0, 'result' => 'Secured on first offer',             'status' => 'active' ],
		(object)[ 'id' => 5, 'author_name' => 'Anoushka Reid',     'location' => 'New build purchase - Canary Wharf',     'review_text' => 'Developers are very savvy. Tom explained what was negotiable and got a parking space, £5k in extras, and a better completion date included. The fee paid for itself three times over.', 'rating' => 5.0, 'result' => '£5k+ in developer extras negotiated', 'status' => 'active' ],
		(object)[ 'id' => 6, 'author_name' => 'David & Lisa Okonkwo','location' => 'Family home - Hertfordshire',          'review_text' => 'School catchments, planning applications, flood risk, Japanese knotweed - they checked everything. We bought knowing exactly what we were getting. No surprises after completion.',       'rating' => 5.0, 'result' => 'Full due diligence on family home',  'status' => 'active' ],
	];
}

function ah_mock_properties(): array {
	return [
		[ 'price' => '£850k',  'location' => 'Richmond',     'area' => 'South West London', 'saved' => 'Saved £20k',   'type' => 'Detached',       'beds' => 5, 'emoji' => '🏡', 'result' => 'Off-market purchase - 3 competing buyers outbid' ],
		[ 'price' => '£1.2M',  'location' => 'Wimbledon',    'area' => 'South London',       'saved' => 'Saved £35k',   'type' => 'Semi-detached',  'beds' => 4, 'emoji' => '🏘️', 'result' => 'Negotiated below asking during survey stage' ],
		[ 'price' => '£425k',  'location' => 'Bristol',      'area' => 'South West',         'saved' => 'Off-market',   'type' => 'Flat',           'beds' => 2, 'emoji' => '🏙️', 'result' => 'Pre-market access - never listed on Rightmove' ],
		[ 'price' => '£675k',  'location' => 'Manchester',   'area' => 'North West',         'saved' => 'Saved £18k',   'type' => 'Townhouse',      'beds' => 3, 'emoji' => '🏗️', 'result' => 'Investor portfolio: 7.1% gross yield secured' ],
		[ 'price' => '£550k',  'location' => 'Brighton',     'area' => 'East Sussex',        'saved' => 'Saved £15k',   'type' => 'Terraced',       'beds' => 4, 'emoji' => '🌊', 'result' => 'Chain-free purchase completed in 9 weeks' ],
		[ 'price' => '£2.1M',  'location' => 'Notting Hill', 'area' => 'West London',        'saved' => 'Saved £90k',   'type' => 'Georgian house', 'beds' => 6, 'emoji' => '🏛️', 'result' => 'Exclusive off-market access through agent network' ],
	];
}

function ah_mock_faqs( string $topic = '' ): array {
	$all = [
		(object)[ 'id' => 1,  'topic' => 'General',  'question' => "Mock - What is a buyer's agent?",                             'answer' => "A buyer's agent works exclusively on behalf of the buyer - not the seller. Unlike an estate agent paid by the seller to achieve the highest price, we are paid by you to find the right property at the best possible price.",            'status' => 'active', 'sort_order' => 1 ],
		(object)[ 'id' => 2,  'topic' => 'General',  'question' => "How much does a buyer's agent cost?",                  'answer' => "Our fee structure is transparent: a retainer to begin the search, and a success fee (typically 1–2.5% of purchase price) on completion. We offer a free 30-minute consultation to explain costs with no obligation.",            'status' => 'active', 'sort_order' => 2 ],
		(object)[ 'id' => 3,  'topic' => 'General',  'question' => "Can I use a buyer's agent as a first-time buyer?",     'answer' => "Absolutely. First-time buyers benefit enormously - you get expert guidance on every step of a process that's unfamiliar, and we often save clients far more than our fee through negotiation alone.",                              'status' => 'active', 'sort_order' => 3 ],
		(object)[ 'id' => 4,  'topic' => 'Process',  'question' => 'How do you find off-market properties?',               'answer' => 'We maintain active relationships with estate agents, developers, property solicitors, and institutional landlords. Many properties are offered to us before they go to Rightmove - sometimes weeks before.',                            'status' => 'active', 'sort_order' => 4 ],
		(object)[ 'id' => 5,  'topic' => 'Process',  'question' => 'How long does the process take?',                      'answer' => "From instruction to completion, most clients are done in 3–6 months. We've completed some relocations in under 8 weeks. Timeline depends on your requirements and market conditions in your target area.",                         'status' => 'active', 'sort_order' => 5 ],
		(object)[ 'id' => 6,  'topic' => 'Process',  'question' => 'Do you cover the whole of the UK?',                   'answer' => 'Yes - we work nationally. We have specialist knowledge in London, the South East, the Midlands, and the North West, with agents on the ground in each region.',                                                                       'status' => 'active', 'sort_order' => 6 ],
		(object)[ 'id' => 7,  'topic' => 'Finance',  'question' => 'Should I get a mortgage in principle before searching?','answer' => 'Yes, always. A mortgage in principle (MIP) shows sellers and estate agents you are a serious buyer. We can refer you to independent mortgage brokers who access the whole market.',                                               'status' => 'active', 'sort_order' => 7 ],
		(object)[ 'id' => 8,  'topic' => 'Finance',  'question' => 'What is stamp duty and how much will I pay?',          'answer' => 'Stamp Duty Land Tax (SDLT) is a tax on property purchases in England. Rates vary depending on purchase price, whether it\'s your main home, a second property, or a first-time purchase. Use our stamp duty calculator for an estimate.', 'status' => 'active', 'sort_order' => 8 ],
		(object)[ 'id' => 9,  'topic' => 'Legal',    'question' => 'What does conveyancing involve?',                      'answer' => "Conveyancing is the legal transfer of a property from seller to buyer. Your solicitor conducts searches, reviews contracts, manages exchange of funds, and registers the property in your name at the Land Registry.",               'status' => 'active', 'sort_order' => 9 ],
		(object)[ 'id' => 10, 'topic' => 'Legal',    'question' => 'Do I need a survey?',                                  'answer' => 'For any property built before the 1990s, we strongly recommend a Level 2 or Level 3 structural survey. A £600 survey can identify £20,000 of hidden issues. We coordinate surveys and help you interpret the report.',            'status' => 'active', 'sort_order' => 10 ],
	];
	if ( $topic ) {
		return array_values( array_filter( $all, fn($f) => strtolower($f->topic) === strtolower($topic) ) );
	}
	return $all;
}
