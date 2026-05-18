<?php
defined( 'ABSPATH' ) || exit;

/**
 * Mock data fallback functions for The Cane House theme.
 * Used ONLY when the DB has no seeded data.
 * To populate the DB, use Theme Admin → Install Mock Data.
 */

function ch_mock_default_settings(): array {
	return [
		'phone'          => '+44 7887 699 208',
		'email'          => 'hello@thecanehouse.co.uk',
		'address'        => 'Available across the UK',
		'website'        => 'www.thecanehouse.co.uk',
		'whatsapp'       => '+447887699208',
		'facebook_url'   => '',
		'instagram_url'  => '',
		'tiktok_url'     => '',
		'youtube_url'    => '',
		'tagline'        => 'Pressed Fresh. Served Cool.',
	];
}

function ch_mock_home_settings_array(): array {
	return [
		'hero_tag'           => '100% Natural · No Additives · Pressed Live',
		'hero_headline'      => "Pressed Fresh.<span class=\"accent\">Served Cool.</span>",
		'hero_brand'         => 'The Cane House',
		'hero_desc'          => "Fresh sugarcane juice pressed live and blended with authentic cold-pressed fruit extracts &amp; natural botanicals. Build your perfect juice — your way.",
		'hero_cta_label'     => '🥤 Build Your Juice',
		'hero_cta_url'       => '#build',
		'hero_cta2_label'    => 'Hire for Events →',
		'hero_cta2_url'      => '#hire',
		'hero_badge_1'       => 'No Added Sugar',
		'hero_badge_2'       => 'No Preservatives',
		'hero_badge_3'       => 'Pressed Live',
		'hero_badge_4'       => 'Served Chilled',
	];
}

function ch_mock_menu_sizes(): array {
	return [
		[ 'icon' => '🥤', 'name' => 'Mini (250ml)',          'desc' => 'Quick refresh, great for kids or first-timers',          'price' => '£4.00',  'badge' => '',           'featured' => false ],
		[ 'icon' => '🥤', 'name' => 'Regular (350ml)',        'desc' => 'Ideal single serving — balanced & refreshing',           'price' => '£5.50',  'badge' => 'Popular',    'featured' => true  ],
		[ 'icon' => '🧃', 'name' => 'Large (550ml)',          'desc' => 'For a longer, more refreshing drink',                    'price' => '£7.00',  'badge' => '',           'featured' => false ],
		[ 'icon' => '🫙', 'name' => 'Sharing Jug (750ml)',    'desc' => 'Great for two — perfect for sharing',                    'price' => '£9.00',  'badge' => '',           'featured' => false ],
		[ 'icon' => '🍶', 'name' => 'Family Sharing (1L)',    'desc' => 'Perfect for families at gatherings',                     'price' => '£14.50', 'badge' => '',           'featured' => false ],
		[ 'icon' => '🍾', 'name' => 'Group Sharing (1.5L)',   'desc' => 'Ideal for group gatherings & events',                   'price' => '£19.50', 'badge' => 'Best Value', 'featured' => true  ],
	];
}

function ch_mock_cane_types(): array {
	return [
		[ 'icon' => '🌾', 'name' => 'Yellow Cane', 'desc' => 'Light golden, fresh and refreshing',             'price' => '',       'badge' => 'Included', 'featured' => true  ],
		[ 'icon' => '🎋', 'name' => 'Red Cane',    'desc' => 'Naturally sweeter, rich golden-amber tone',      'price' => '+£0.50', 'badge' => '',         'featured' => false ],
	];
}

function ch_mock_textures(): array {
	return [
		[ 'icon' => '🥢', 'name' => 'Classic',  'desc' => 'No Peel — light grassy, traditional taste', 'price' => '',       'badge' => 'Included', 'featured' => true  ],
		[ 'icon' => '✨', 'name' => 'Smooth',   'desc' => 'With Peel — cleaner, smoother finish',       'price' => '+£0.50', 'badge' => '',         'featured' => false ],
	];
}

function ch_mock_flavours(): array {
	return [
		[ 'emoji' => '🌿', 'name' => 'Pure Cane',       'desc' => 'Included — Clean & natural',   'category' => 'pure'    ],
		[ 'emoji' => '🍋', 'name' => 'Lemon',            'desc' => '+£0.50 · Citrus Blend',        'category' => 'citrus'  ],
		[ 'emoji' => '🫚', 'name' => 'Ginger',           'desc' => '+£0.50 · Citrus Blend',        'category' => 'citrus'  ],
		[ 'emoji' => '🌀', 'name' => 'Lemon & Ginger',   'desc' => '+£0.50 · Citrus Blend',        'category' => 'citrus'  ],
		[ 'emoji' => '🌱', 'name' => 'Mint',             'desc' => '+£0.50 · Citrus Blend',        'category' => 'citrus'  ],
		[ 'emoji' => '🍍', 'name' => 'Pineapple',        'desc' => '+£1.00 · Tropical Blend',      'category' => 'tropical'],
		[ 'emoji' => '🍉', 'name' => 'Watermelon',       'desc' => '+£1.00 · Tropical Blend',      'category' => 'tropical'],
		[ 'emoji' => '🍓', 'name' => 'Strawberry',       'desc' => '+£1.00 · Tropical Blend',      'category' => 'tropical'],
		[ 'emoji' => '🫐', 'name' => 'Blueberry Burst',  'desc' => '+£1.00 · Tropical Blend',      'category' => 'tropical'],
	];
}

function ch_mock_order_steps(): array {
	return [
		[ 'num' => '1', 'emoji' => '📏', 'title' => 'Select Size',    'desc' => 'Choose from Mini 250ml right up to Group Sharing 1.5L — perfect for every occasion' ],
		[ 'num' => '2', 'emoji' => '🌾', 'title' => 'Select Cane',    'desc' => 'Yellow Cane (light golden) or Red Cane (rich amber, +£0.50)' ],
		[ 'num' => '3', 'emoji' => '🥤', 'title' => 'Select Texture', 'desc' => 'Classic No Peel for a grassy taste, or Smooth With Peel for a cleaner finish (+£0.50)' ],
		[ 'num' => '4', 'emoji' => '🍋', 'title' => 'Select Flavour', 'desc' => 'Pure Cane (free), Citrus Blends (Lemon, Ginger, Mint +£0.50) or Tropical Blends (+£1.00)' ],
		[ 'num' => '5', 'emoji' => '🎉', 'title' => 'Enjoy!',         'desc' => 'Served chilled, no ice unless requested — pure fresh natural goodness in every sip', 'highlight' => true ],
	];
}

function ch_mock_marquee_items(): array {
	return [
		'Pressed Fresh',
		'Served Cool',
		'No Added Sugar',
		'No Preservatives',
		'Pressed Live',
		'Natural Goodness',
		'Build Your Juice',
		'Events & Hire',
		'Ayurvedic Tradition',
		'2000+ Years of Cane',
	];
}

function ch_mock_reviews(): array {
	return [
		(object)[
			'author_name' => 'Priya Sharma',
			'location'    => 'Verified Customer — Leicester',
			'review_text' => '"Reminds me of freshly pressed ganna ras from back home in India! The ginger blend is absolutely incredible. My whole family was so happy to have this at our Diwali celebration."',
			'rating'      => 5.0,
			'result'      => 'Perfect for cultural events',
			'status'      => 'active',
		],
		(object)[
			'author_name' => 'Mohammed Al-Rashid',
			'location'    => 'Event Client — Birmingham',
			'review_text' => '"We hired The Cane House for our Eid family gathering. Over 80 guests and everyone was asking about the juice stall! Live pressing in front of guests was such a crowd puller. 10/10 service."',
			'rating'      => 5.0,
			'result'      => 'Hit of the Eid party',
			'status'      => 'active',
		],
		(object)[
			'author_name' => 'Ananya & Rahul Patel',
			'location'    => 'Wedding Clients — Wolverhampton',
			'review_text' => '"The highlight of our Indian wedding reception! Our guests could not believe how fresh and natural it tasted. The Mehndi night setup was stunning. Absolutely recommended for desi weddings!"',
			'rating'      => 5.0,
			'result'      => 'Wedding reception highlight',
			'status'      => 'active',
		],
		(object)[
			'author_name' => 'Sunita Reddy',
			'location'    => 'Verified Customer — Southall, London',
			'review_text' => '"Finally, authentic fresh sugarcane juice in the UK! No artificial flavours, no added sugar — just pure ganna ras the way we used to have it. The lemon-ginger blend is my absolute favourite."',
			'rating'      => 5.0,
			'result'      => 'Authentic taste of home',
			'status'      => 'active',
		],
		(object)[
			'author_name' => 'Vikram Singh',
			'location'    => 'Festival Organiser — Manchester',
			'review_text' => '"Booked The Cane House for our Vaisakhi mela — 500+ attendees and the sugarcane stall was the longest queue all day! Professional, hygienic, and the taste was absolutely brilliant."',
			'rating'      => 5.0,
			'result'      => 'Longest queue at the mela',
			'status'      => 'active',
		],
		(object)[
			'author_name' => 'Sarah & James Thompson',
			'location'    => 'Verified Customer — London',
			'review_text' => '"Tried The Cane House at a summer festival — absolutely loved the pineapple tropical blend! So refreshing, so natural. Never tried sugarcane juice before but now I\'m completely hooked."',
			'rating'      => 5.0,
			'result'      => 'First-time sugarcane convert',
			'status'      => 'active',
		],
	];
}

function ch_mock_faqs( string $topic = '' ): array {
	$all = [
		(object)[ 'topic' => 'General', 'question' => 'Do you add any sugar or preservatives?',         'answer' => 'No, absolutely not. Our sugarcane juice is 100% natural, pressed live from the stalk. The sweetness comes entirely from the natural sugars in the cane itself — just as it has been enjoyed across India, South Asia, and tropical cultures for over 2,000 years.',              'status' => 'active', 'sort_order' => 1 ],
		(object)[ 'topic' => 'General', 'question' => 'How long does the juice stay fresh?',             'answer' => 'Fresh sugarcane juice is best enjoyed immediately after pressing. If kept chilled, it can stay fresh for up to 24 hours. We always recommend drinking it cool and fresh for the best taste — just like you would from a roadside ganna ras stall in India!',               'status' => 'active', 'sort_order' => 2 ],
		(object)[ 'topic' => 'General', 'question' => 'Is the juice suitable for everyone?',             'answer' => 'Yes! Fresh sugarcane juice is enjoyed by people of all ages and dietary backgrounds. It is naturally vegan, gluten-free, and dairy-free. Please consume responsibly if you are managing blood sugar, as it does contain natural sugars.',                                  'status' => 'active', 'sort_order' => 3 ],
		(object)[ 'topic' => 'Events',  'question' => 'What events can I hire you for?',                 'answer' => 'We cater for all types of events including weddings, Mehndi nights, Sangeet, Eid parties, Diwali celebrations, birthdays, corporate gatherings, festivals, and community events across the UK. Our live pressing stall is always a huge hit!',                         'status' => 'active', 'sort_order' => 4 ],
		(object)[ 'topic' => 'Events',  'question' => 'How much does it cost to hire for an event?',     'answer' => 'Pricing is customised based on your event size, location, and duration. We offer competitive packages for small private gatherings right up to large-scale festivals. Contact us for a personalised quote — we are always happy to accommodate your budget.',              'status' => 'active', 'sort_order' => 5 ],
		(object)[ 'topic' => 'Events',  'question' => 'How much notice do you need for event bookings?', 'answer' => 'We recommend booking at least 2–4 weeks in advance to secure your preferred date, especially during peak wedding and festival season (April–October). However, do reach out even at short notice and we will do our best to accommodate you.',                           'status' => 'active', 'sort_order' => 6 ],
		(object)[ 'topic' => 'Juice',   'question' => 'What is the difference between Yellow and Red Cane?', 'answer' => 'Yellow Cane produces a lighter, more refreshing golden juice with a clean, mild sweetness. Red Cane (available at +£0.50) is naturally richer with a deeper amber colour and a more intense sweetness. Both are 100% natural with no additives.',                  'status' => 'active', 'sort_order' => 7 ],
		(object)[ 'topic' => 'Juice',   'question' => 'What flavour blends do you offer?',               'answer' => 'We offer Pure Cane (natural, included), Citrus Blends including Lemon, Ginger, Lemon & Ginger, and Mint (+£0.50 each), and Tropical Blends including Pineapple, Watermelon, Strawberry, and Blueberry Burst (+£1.00 each).',                                         'status' => 'active', 'sort_order' => 8 ],
		(object)[ 'topic' => 'General', 'question' => 'Is your sugarcane juice sustainable?',            'answer' => 'Yes! Sugarcane is a highly sustainable crop. Even our leftover fibre (bagasse) is biodegradable and can be used for composting or as biofuel. We are committed to responsible, eco-conscious serving practices.',                                                        'status' => 'active', 'sort_order' => 9 ],
		(object)[ 'topic' => 'Franchise','question' => 'How can I become a franchise partner?',          'answer' => 'We warmly welcome franchise enquiries from across the UK. Whether you want to run a permanent stall, a mobile unit, or an events-focused operation — we have a model for you. Call us on +44 7887 699 208 or use the contact form to start the conversation.',          'status' => 'active', 'sort_order' => 10 ],
	];
	if ( $topic ) {
		return array_values( array_filter( $all, fn( $f ) => $f->topic === $topic ) );
	}
	return $all;
}

function ch_mock_benefits(): array {
	return [
		[ 'icon' => '⚡', 'title' => 'Natural Energy Booster',     'desc' => 'Provides instant energy with natural sugars — a staple Ayurvedic revitaliser enjoyed across India for centuries. No additives, no crash.' ],
		[ 'icon' => '💧', 'title' => 'Hydrating & Cooling',        'desc' => 'Perfect for warm days, helping to refresh and rehydrate the body naturally. In Ayurveda, sugarcane is classified as a cooling (sheetal) food.' ],
		[ 'icon' => '🌿', 'title' => 'Rich in Natural Nutrients',   'desc' => 'Contains antioxidants, calcium, potassium, magnesium, iron, and essential electrolytes your body loves. No synthetic supplements needed.' ],
		[ 'icon' => '🫁', 'title' => 'Supports Digestion',         'desc' => 'Traditionally combined with lemon and ginger (adrak) to aid digestion — a remedy rooted in thousands of years of South Asian wellness wisdom.' ],
		[ 'icon' => '🛡️', 'title' => 'Boosts Immunity',            'desc' => 'Natural compounds including antioxidant flavonoids support overall wellness and immunity. Unlike fizzy drinks — clean, fresh, and nourishing.' ],
		[ 'icon' => '🌱', 'title' => 'Completely Natural & Vegan', 'desc' => 'No added sugar, no preservatives, no artificial colours. Just pure plant-based refreshment as nature intended.' ],
	];
}

function ch_mock_hire_packages(): array {
	return [
		[
			'icon'  => '💒',
			'title' => 'Weddings & Asian Celebrations',
			'desc'  => 'Add a traditional and healthy touch to your special day. We serve fresh juice live during your reception, Mehndi night, Sangeet, or Baraat — a truly memorable experience your guests will talk about.',
			'items' => [ 'Reception Drinks', 'Mehndi & Sangeet Night', 'Post-Ceremony Refreshment', 'Baraat Welcome Drinks' ],
		],
		[
			'icon'  => '🏢',
			'title' => 'Corporate Events',
			'desc'  => 'Perfect for office parties, wellness days, and conferences. A healthy, natural alternative to sugary sodas — show your team you care.',
			'items' => [ 'Office Wellness Days', 'Product Launches', 'Exhibitions & Trade Fairs', 'Team Away Days' ],
		],
		[
			'icon'  => '🎉',
			'title' => 'Private Parties & Festivals',
			'desc'  => 'From Diwali parties to garden gatherings, Eid celebrations to birthday bashes — we bring the vibe and freshness. Guests of all ages love it.',
			'items' => [ 'Birthday Parties', 'Diwali & Eid Celebrations', 'Community Festivals & Melas', 'Garden & BBQ Events' ],
		],
	];
}

function ch_mock_hire_features(): array {
	return [
		[ 'icon' => '🌿', 'text' => 'Pressed Live On-Site' ],
		[ 'icon' => '❄️', 'text' => 'Naturally Chilled' ],
		[ 'icon' => '🥤', 'text' => 'Unlimited Serving Options' ],
		[ 'icon' => '🛡️', 'text' => 'Fully Insured & Certified' ],
		[ 'icon' => '🚐', 'text' => 'Mobile Unit Available' ],
		[ 'icon' => '🌍', 'text' => 'UK-Wide Coverage' ],
	];
}

function ch_mock_franchise_locations(): array {
	return [
		[ 'icon' => '📍', 'name' => 'London — Southall' ],
		[ 'icon' => '📍', 'name' => 'Birmingham — Handsworth' ],
		[ 'icon' => '📍', 'name' => 'Leicester — Belgrave' ],
		[ 'icon' => '📍', 'name' => 'Manchester — Rusholme' ],
		[ 'icon' => '📍', 'name' => 'Bradford — Manningham' ],
		[ 'icon' => '📍', 'name' => 'Wolverhampton Central' ],
		[ 'icon' => '📍', 'name' => 'Leeds — Beeston' ],
		[ 'icon' => '📍', 'name' => 'Luton — Bury Park' ],
		[ 'icon' => '📍', 'name' => 'Coventry — Foleshill' ],
		[ 'icon' => '📍', 'name' => 'Glasgow — South Side' ],
	];
}

function ch_mock_juice_showcase(): array {
	return [
		[ 'image' => 'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=600', 'title' => 'Pure Yellow Cane',   'desc' => 'Fresh & Naturally Sweet' ],
		[ 'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=600', 'title' => 'Zesty Lemon Blend', 'desc' => 'Citrus Refreshment' ],
		[ 'image' => 'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&q=80&w=600', 'title' => 'Spicy Ginger',       'desc' => 'Warming & Healthy' ],
		[ 'image' => 'https://images.unsplash.com/photo-1595981267035-7b04ca84a82d?auto=format&fit=crop&q=80&w=600', 'title' => 'Cooling Mint',      'desc' => 'Ultimate Freshness' ],
	];
}

function ch_mock_story_settings(): array {
	return [
		'tag'          => 'The Story of Sugarcane',
		'headline'     => 'Beyond the <span class="accent">Juice</span>',
		'body_1'       => 'Sugarcane has been cherished for over 2,000 years, originating in South and Southeast Asia — particularly the Indian subcontinent — where it has been a cornerstone of Ayurvedic medicine, spiritual offerings, and everyday refreshment. Fresh ganna ras (sugarcane juice) remains one of the most beloved street drinks across India.',
		'body_2'       => 'At The Cane House, we bring this centuries-old tradition to the heart of the UK. Every glass honours that heritage — pressed live, served cool, with the same love and craft that has always made sugarcane juice special.',
		'quote'        => '"Sugarcane — one of nature\'s most generous gifts from the Indian subcontinent. Pure energy, pressed fresh."',
		'badge_text'   => "2,000+\nYears\nof Cane",
		'facts'        => [
			[ 'icon' => '🍬', 'title' => 'Sugar & Jaggery',   'desc' => 'Khandsari & gur — traditional Indian sweeteners' ],
			[ 'icon' => '🫙', 'title' => 'Molasses',          'desc' => 'Rich syrup with deep mineral content' ],
			[ 'icon' => '⛽', 'title' => 'Ethanol',           'desc' => 'Clean-burning biofuel from fermentation' ],
			[ 'icon' => '🌱', 'title' => 'Eco Bagasse Fibre', 'desc' => 'Biodegradable by-product — fully sustainable' ],
		],
	];
}
