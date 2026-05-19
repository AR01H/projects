<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH Theme Seeder
 * Populates all CMS tables and WordPress options with demo data.
 * Triggered from Theme Admin → Install Mock Data.
 */
class CH_Theme_Seeder {

	// ── Table creation ────────────────────────────────────────────────────────

	public static function create_tables(): void {
		global $wpdb;
		$cs = $wpdb->get_charset_collate();

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'reviews' ) . "` (
			id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			author_name VARCHAR(200) NOT NULL,
			location    VARCHAR(200),
			review_text TEXT NOT NULL,
			rating      DECIMAL(3,1) UNSIGNED DEFAULT 5.0,
			result      VARCHAR(200),
			status      ENUM('active','inactive') DEFAULT 'active',
			created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'faqs' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			topic      VARCHAR(150),
			question   TEXT NOT NULL,
			answer     TEXT NOT NULL,
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `" . ch_theme_table( 'news_bar' ) . "` (
			id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			message    VARCHAR(500) NOT NULL,
			status     ENUM('active','inactive') DEFAULT 'active',
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB {$cs}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Contact submissions table (theme-owned, not shared with plugin)
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$contact_table = $wpdb->prefix . 'ch_contact_submissions';
		$sql = "CREATE TABLE IF NOT EXISTS `{$contact_table}` (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name          VARCHAR(200)    NOT NULL DEFAULT '',
			email         VARCHAR(200)    NOT NULL DEFAULT '',
			phone         VARCHAR(50)     NOT NULL DEFAULT '',
			enquiry_type  VARCHAR(100)    NOT NULL DEFAULT 'general',
			message       TEXT            NOT NULL DEFAULT '',
			ip_address    VARCHAR(50)     NOT NULL DEFAULT '',
			created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$cs};";
		dbDelta( $sql );
	}

	/** @return array{inserted:int, updated:int, errors:string[]} */
	public static function seed_all(): array {
		self::create_tables();
		$results = [ 'inserted' => 0, 'updated' => 0, 'errors' => [] ];
		$methods = [
			'seed_settings',
			'seed_home_settings',
			'seed_section_visibility',
			'seed_navigation',
			'seed_footer',
			'seed_contact_settings',
			'seed_menu_sizes',
			'seed_cane_types',
			'seed_textures',
			'seed_flavours',
			'seed_order_steps',
			'seed_marquee_items',
			'seed_benefits',
			'seed_hire_packages',
			'seed_hire_features',
			'seed_franchise_locations',
			'seed_juice_showcase',
			'seed_story_settings',
			'seed_reviews',
			'seed_faqs',
			'seed_news_bar',
			'seed_journal_page',
			'seed_journal_posts',
		];
		foreach ( $methods as $method ) {
			try {
				$r = self::$method();
				$results['inserted'] += $r['inserted'] ?? 0;
				$results['updated']  += $r['updated']  ?? 0;
			} catch ( \Throwable $e ) {
				$results['errors'][] = "{$method}: " . $e->getMessage();
			}
		}
		return $results;
	}

	/** @return array{deleted:int} */
	public static function cleanup_all(): array {
		global $wpdb;
		$deleted = 0;

		// Truncate CMS tables
		foreach ( [ 'reviews', 'faqs', 'news_bar' ] as $t ) {
			$table = ch_theme_table( $t );
			$wpdb->query( "TRUNCATE TABLE `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$deleted++;
		}

		// Remove all ch_ options
		$options = [
			'ch_site_settings', 'ch_home_settings', 'ch_section_visibility',
			'ch_theme_navigation', 'ch_nav_cta', 'ch_theme_footer', 'ch_contact_settings',
			'ch_menu_sizes', 'ch_cane_types', 'ch_textures', 'ch_flavours',
			'ch_order_steps', 'ch_marquee_items', 'ch_benefits',
			'ch_hire_packages', 'ch_hire_features', 'ch_franchise_locations',
			'ch_juice_showcase', 'ch_story_settings', 'ch_faqs_manual',
		];
		foreach ( $options as $opt ) {
			if ( delete_option( $opt ) ) {
				$deleted++;
			}
		}

		// Remove seeded WP pages
		foreach ( [ 'journal' ] as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_delete_post( $page->ID, true );
				$deleted++;
			}
		}

		return [ 'deleted' => $deleted ];
	}

	// ── Individual seed methods ───────────────────────────────────────────────

	private static function seed_settings(): array {
		update_option( 'ch_site_settings', wp_json_encode( [
			'phone'         => '+44 7887 699 208',
			'email'         => 'hello@thecanehouse.co.uk',
			'address'       => 'Available across the UK',
			'website'       => 'www.thecanehouse.co.uk',
			'whatsapp'      => '447887699208',
			'facebook_url'  => '',
			'instagram_url' => '',
			'tiktok_url'    => '',
			'youtube_url'   => '',
			'tagline'       => 'Pressed Fresh. Served Cool.',
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_home_settings(): array {
		update_option( 'ch_home_settings', wp_json_encode( [
			'hero_tag'        => '100% Natural · No Additives · Pressed Live',
			'hero_headline'   => 'Pressed Fresh.<span class="accent">Served Cool.</span>',
			'hero_brand'      => 'The Cane House',
			'hero_desc'       => 'Fresh sugarcane juice pressed live and blended with authentic cold-pressed fruit extracts & natural botanicals. Build your perfect juice - your way.',
			'hero_cta_label'  => '🥤 Build Your Juice',
			'hero_cta_url'    => '#build',
			'hero_cta2_label' => 'Hire for Events →',
			'hero_cta2_url'   => '#hire',
			'hero_badge_1'    => 'No Added Sugar',
			'hero_badge_2'    => 'No Preservatives',
			'hero_badge_3'    => 'Pressed Live',
			'hero_badge_4'    => 'Served Chilled',
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_section_visibility(): array {
		update_option( 'ch_section_visibility', wp_json_encode( [
			'news_ticker' => 1,
			'hero'        => 1,
			'marquee'     => 1,
			'how_to_order'=> 1,
			'reviews'     => 1,
			'menu_builder'=> 1,
			'benefits'    => 1,
			'story'       => 1,
			'hire'        => 1,
			'franchise'   => 1,
			'faqs'        => 1,
			'contact'     => 1,
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_navigation(): array {
		$nav = wp_json_encode( [
			[ 'id' => 'how-to-order', 'label' => 'How To Order', 'type' => 'link', 'url' => '#how-to-order', 'visible' => true, 'submenu' => [] ],
			[ 'id' => 'build',        'label' => 'Build Juice',  'type' => 'link', 'url' => '#build',        'visible' => true, 'submenu' => [] ],
			[ 'id' => 'hire',         'label' => 'Hire Us',      'type' => 'link', 'url' => '#hire',         'visible' => true, 'submenu' => [] ],
			[ 'id' => 'franchise',    'label' => 'Franchise',    'type' => 'link', 'url' => '#franchise',    'visible' => true, 'submenu' => [] ],
			[ 'id' => 'faq',          'label' => 'FAQ',          'type' => 'link', 'url' => '#faq',          'visible' => true, 'submenu' => [] ],
			[ 'id' => 'journal',      'label' => 'Journal',      'type' => 'link', 'url' => '/journal/',     'visible' => true, 'submenu' => [] ],
		] );
		$cta = wp_json_encode( [ 'label' => 'Contact Us', 'url' => '#contact' ] );
		// Write to both the plugin's shared key (used by admin CMS) and the theme fallback key
		update_option( 'ah_cms_navigation', $nav );
		update_option( 'ch_theme_navigation', $nav );
		update_option( 'ah_cms_nav_cta', $cta );
		update_option( 'ch_nav_cta', $cta );
		return [ 'updated' => 4 ];
	}

	private static function seed_footer(): array {
		update_option( 'ch_theme_footer', wp_json_encode( [
			'brand_description' => 'The Cane House - UK\'s premium live-pressed sugarcane juice experience. Serving fresh ganna ras at weddings, festivals, and events across the UK.',
			'copyright'         => '© ' . gmdate( 'Y' ) . ' The Cane House. All rights reserved.',
			'columns'           => [
				[
					'title' => 'Quick Links',
					'items' => [
						[ 'label' => 'How To Order', 'url' => '#how-to-order' ],
						[ 'label' => 'Build Your Juice', 'url' => '#build' ],
						[ 'label' => 'Hire for Events', 'url' => '#hire' ],
						[ 'label' => 'Franchise', 'url' => '#franchise' ],
						[ 'label' => 'FAQ', 'url' => '#faq' ],
					],
				],
				[
					'title' => 'Contact',
					'items' => [
						[ 'label' => '+44 7887 699 208', 'url' => 'tel:+447887699208' ],
						[ 'label' => 'hello@thecanehouse.co.uk', 'url' => 'mailto:hello@thecanehouse.co.uk' ],
						[ 'label' => 'WhatsApp Us', 'url' => 'https://wa.me/447887699208' ],
					],
				],
			],
			'legal_links' => [
				[ 'label' => 'Privacy Policy', 'url' => '/privacy-policy' ],
				[ 'label' => 'Terms & Conditions', 'url' => '/terms' ],
			],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_contact_settings(): array {
		update_option( 'ch_contact_settings', wp_json_encode( [
			'recipient_email' => get_option( 'admin_email' ),
			'subject_prefix'  => '[The Cane House Enquiry]',
			'thank_you_msg'   => "Thanks for your message! We'll be in touch shortly. Pressed Fresh. Served Cool. 🌿",
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_menu_sizes(): array {
		update_option( 'ch_menu_sizes', wp_json_encode( [
			[ 'icon' => '🥤', 'name' => 'Mini (250ml)',        'desc' => 'Quick refresh, great for kids or first-timers',        'price' => '£4.00',  'badge' => '',           'featured' => false ],
			[ 'icon' => '🥤', 'name' => 'Regular (350ml)',      'desc' => 'Ideal single serving - balanced & refreshing',         'price' => '£5.50',  'badge' => 'Popular',    'featured' => true  ],
			[ 'icon' => '🧃', 'name' => 'Large (550ml)',        'desc' => 'For a longer, more refreshing drink',                  'price' => '£7.00',  'badge' => '',           'featured' => false ],
			[ 'icon' => '🫙', 'name' => 'Sharing Jug (750ml)', 'desc' => 'Great for two - perfect for sharing',                  'price' => '£9.00',  'badge' => '',           'featured' => false ],
			[ 'icon' => '🍶', 'name' => 'Family Sharing (1L)', 'desc' => 'Perfect for families at gatherings',                   'price' => '£14.50', 'badge' => '',           'featured' => false ],
			[ 'icon' => '🍾', 'name' => 'Group Sharing (1.5L)','desc' => 'Ideal for group gatherings & Mehndi nights',           'price' => '£19.50', 'badge' => 'Best Value', 'featured' => true  ],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_cane_types(): array {
		update_option( 'ch_cane_types', wp_json_encode( [
			[ 'icon' => '🌾', 'name' => 'Yellow Cane', 'desc' => 'Light golden, fresh and refreshing',        'price' => '',       'badge' => 'Included', 'featured' => true  ],
			[ 'icon' => '🎋', 'name' => 'Red Cane',    'desc' => 'Naturally sweeter, rich golden-amber tone', 'price' => '+£0.50', 'badge' => '',         'featured' => false ],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_textures(): array {
		update_option( 'ch_textures', wp_json_encode( [
			[ 'icon' => '🥢', 'name' => 'Classic', 'desc' => 'No Peel - light grassy, traditional taste',  'price' => '',       'badge' => 'Included', 'featured' => true  ],
			[ 'icon' => '✨', 'name' => 'Smooth',  'desc' => 'With Peel - cleaner, smoother finish',       'price' => '+£0.50', 'badge' => '',         'featured' => false ],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_flavours(): array {
		update_option( 'ch_flavours', wp_json_encode( [
			[ 'emoji' => '🌿', 'name' => 'Pure Cane',      'desc' => 'Included - Clean & natural',  'category' => 'pure'    ],
			[ 'emoji' => '🍋', 'name' => 'Lemon',           'desc' => '+£0.50 · Citrus Blend',       'category' => 'citrus'  ],
			[ 'emoji' => '🫚', 'name' => 'Ginger',          'desc' => '+£0.50 · Citrus Blend',       'category' => 'citrus'  ],
			[ 'emoji' => '🌀', 'name' => 'Lemon & Ginger',  'desc' => '+£0.50 · Citrus Blend',       'category' => 'citrus'  ],
			[ 'emoji' => '🌱', 'name' => 'Mint',            'desc' => '+£0.50 · Citrus Blend',       'category' => 'citrus'  ],
			[ 'emoji' => '🍍', 'name' => 'Pineapple',       'desc' => '+£1.00 · Tropical Blend',     'category' => 'tropical'],
			[ 'emoji' => '🍉', 'name' => 'Watermelon',      'desc' => '+£1.00 · Tropical Blend',     'category' => 'tropical'],
			[ 'emoji' => '🍓', 'name' => 'Strawberry',      'desc' => '+£1.00 · Tropical Blend',     'category' => 'tropical'],
			[ 'emoji' => '🫐', 'name' => 'Blueberry Burst', 'desc' => '+£1.00 · Tropical Blend',     'category' => 'tropical'],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_order_steps(): array {
		update_option( 'ch_order_steps', wp_json_encode( [
			[ 'num' => '1', 'emoji' => '📏', 'title' => 'Select Size',    'desc' => 'Choose from Mini 250ml right up to Group Sharing 1.5L - perfect for every occasion', 'highlight' => false ],
			[ 'num' => '2', 'emoji' => '🌾', 'title' => 'Select Cane',    'desc' => 'Yellow Cane (light golden) or Red Cane (rich amber, +£0.50)',                        'highlight' => false ],
			[ 'num' => '3', 'emoji' => '🥤', 'title' => 'Select Texture', 'desc' => 'Classic No Peel for a grassy taste, or Smooth With Peel for a cleaner finish (+£0.50)', 'highlight' => false ],
			[ 'num' => '4', 'emoji' => '🍋', 'title' => 'Select Flavour', 'desc' => 'Pure Cane (free), Citrus Blends (Lemon, Ginger, Mint +£0.50) or Tropical Blends (+£1.00)', 'highlight' => false ],
			[ 'num' => '5', 'emoji' => '🎉', 'title' => 'Enjoy!',         'desc' => 'Served chilled, no ice unless requested - pure fresh natural goodness in every sip', 'highlight' => true  ],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_marquee_items(): array {
		update_option( 'ch_marquee_items', wp_json_encode( [
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
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_benefits(): array {
		update_option( 'ch_benefits', wp_json_encode( [
			[ 'icon' => '⚡', 'title' => 'Natural Energy Booster',     'desc' => 'Provides instant energy with natural sugars - a staple Ayurvedic revitaliser enjoyed across India for centuries. No additives, no crash.' ],
			[ 'icon' => '💧', 'title' => 'Hydrating & Cooling',        'desc' => 'Perfect for warm days, helping to refresh and rehydrate the body naturally. In Ayurveda, sugarcane is classified as a cooling (sheetal) food.' ],
			[ 'icon' => '🌿', 'title' => 'Rich in Natural Nutrients',  'desc' => 'Contains antioxidants, calcium, potassium, magnesium, iron, and essential electrolytes. No synthetic supplements needed.' ],
			[ 'icon' => '🫁', 'title' => 'Supports Digestion',        'desc' => 'Traditionally combined with lemon and ginger (adrak) to aid digestion - a remedy rooted in thousands of years of South Asian wellness wisdom.' ],
			[ 'icon' => '🛡️', 'title' => 'Boosts Immunity',           'desc' => 'Natural antioxidant flavonoids support overall wellness and immunity. Unlike fizzy drinks - clean, fresh, and nourishing.' ],
			[ 'icon' => '🌱', 'title' => 'Completely Natural & Vegan','desc' => 'No added sugar, no preservatives, no artificial colours. Just pure plant-based refreshment as nature intended.' ],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_hire_packages(): array {
		update_option( 'ch_hire_packages', wp_json_encode( [
			[
				'icon'  => '💒',
				'title' => 'Weddings & Asian Celebrations',
				'desc'  => 'Add a traditional and healthy touch to your special day. We serve fresh juice live during your reception, Mehndi night, Sangeet, or Baraat - a truly memorable experience your guests will talk about.',
				'items' => [ 'Reception Drinks', 'Mehndi & Sangeet Night', 'Post-Ceremony Refreshment', 'Baraat Welcome Drinks' ],
			],
			[
				'icon'  => '🏢',
				'title' => 'Corporate Events',
				'desc'  => 'Perfect for office parties, wellness days, and conferences. A healthy, natural alternative to sugary sodas - show your team you care.',
				'items' => [ 'Office Wellness Days', 'Product Launches', 'Exhibitions & Trade Fairs', 'Team Away Days' ],
			],
			[
				'icon'  => '🎉',
				'title' => 'Private Parties & Festivals',
				'desc'  => 'From Diwali parties to garden gatherings, Eid celebrations to birthday bashes - we bring the vibe and freshness. Guests of all ages love it.',
				'items' => [ 'Birthday Parties', 'Diwali & Eid Celebrations', 'Community Festivals & Melas', 'Garden & BBQ Events' ],
			],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_hire_features(): array {
		update_option( 'ch_hire_features', wp_json_encode( [
			[ 'icon' => '🌿', 'text' => 'Pressed Live On-Site' ],
			[ 'icon' => '❄️', 'text' => 'Naturally Chilled' ],
			[ 'icon' => '🥤', 'text' => 'Unlimited Serving Options' ],
			[ 'icon' => '🛡️', 'text' => 'Fully Insured & Certified' ],
			[ 'icon' => '🚐', 'text' => 'Mobile Unit Available' ],
			[ 'icon' => '🌍', 'text' => 'UK-Wide Coverage' ],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_franchise_locations(): array {
		update_option( 'ch_franchise_locations', wp_json_encode( [
			[ 'icon' => '📍', 'name' => 'London - Southall' ],
			[ 'icon' => '📍', 'name' => 'Birmingham - Handsworth' ],
			[ 'icon' => '📍', 'name' => 'Leicester - Belgrave' ],
			[ 'icon' => '📍', 'name' => 'Manchester - Rusholme' ],
			[ 'icon' => '📍', 'name' => 'Bradford - Manningham' ],
			[ 'icon' => '📍', 'name' => 'Wolverhampton Central' ],
			[ 'icon' => '📍', 'name' => 'Leeds - Beeston' ],
			[ 'icon' => '📍', 'name' => 'Luton - Bury Park' ],
			[ 'icon' => '📍', 'name' => 'Coventry - Foleshill' ],
			[ 'icon' => '📍', 'name' => 'Glasgow - South Side' ],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_juice_showcase(): array {
		update_option( 'ch_juice_showcase', wp_json_encode( [
			[ 'image' => 'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=600', 'title' => 'Pure Yellow Cane',   'desc' => 'Fresh & Naturally Sweet' ],
			[ 'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=600', 'title' => 'Zesty Lemon Blend', 'desc' => 'Citrus Refreshment' ],
			[ 'image' => 'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&q=80&w=600', 'title' => 'Spicy Ginger',       'desc' => 'Warming & Healthy' ],
			[ 'image' => 'https://images.unsplash.com/photo-1595981267035-7b04ca84a82d?auto=format&fit=crop&q=80&w=600', 'title' => 'Cooling Mint',     'desc' => 'Ultimate Freshness' ],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_story_settings(): array {
		update_option( 'ch_story_settings', wp_json_encode( [
			'tag'        => 'The Story of Sugarcane',
			'headline'   => 'Beyond the <span class="accent">Juice</span>',
			'body_1'     => 'Sugarcane has been cherished for over 2,000 years, originating in South and Southeast Asia - particularly the Indian subcontinent - where it has been a cornerstone of Ayurvedic medicine, spiritual offerings, and everyday refreshment. Fresh ganna ras (sugarcane juice) remains one of the most beloved street drinks across India.',
			'body_2'     => 'At The Cane House, we bring this centuries-old tradition to the heart of the UK. Every glass honours that heritage - pressed live, served cool, with the same love and craft that has always made sugarcane juice special.',
			'quote'      => '"Sugarcane - one of nature\'s most generous gifts from the Indian subcontinent. Pure energy, pressed fresh."',
			'badge_text' => "2,000+\nYears\nof Cane",
			'facts'      => [
				[ 'icon' => '🍬', 'title' => 'Sugar & Jaggery',   'desc' => 'Khandsari & gur - traditional Indian sweeteners' ],
				[ 'icon' => '🫙', 'title' => 'Molasses',          'desc' => 'Rich syrup with deep mineral content' ],
				[ 'icon' => '⛽', 'title' => 'Ethanol',           'desc' => 'Clean-burning biofuel from fermentation' ],
				[ 'icon' => '🌱', 'title' => 'Eco Bagasse Fibre', 'desc' => 'Biodegradable by-product - fully sustainable' ],
			],
		] ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_reviews(): array {
		global $wpdb;
		$table = ch_theme_table( 'reviews' );
		$inserted = 0;

		$rows = [
			[
				'author_name' => 'Priya Sharma',
				'location'    => 'Verified Customer - Leicester, Belgrave',
				'review_text' => 'Reminds me of freshly pressed ganna ras from back home in Punjab! The ginger blend is absolutely incredible - just like the adrak-nimbu version you get at the roadside stalls. My whole family was so happy to have this at our Diwali celebration. Brought tears to my eyes!',
				'rating'      => 5.0,
				'result'      => 'Perfect for Diwali celebrations',
				'status'      => 'active',
			],
			[
				'author_name' => 'Mohammed Al-Rashid',
				'location'    => 'Event Client - Birmingham, Handsworth',
				'review_text' => 'We hired The Cane House for our Eid family gathering. Over 80 guests and everyone was asking about the juice stall! The live pressing in front of guests was such a crowd puller - my aunties from back home said it was just like the sugarcane wallahs in Lahore. 10/10 service.',
				'rating'      => 5.0,
				'result'      => 'Star attraction at the Eid gathering',
				'status'      => 'active',
			],
			[
				'author_name' => 'Ananya & Rahul Patel',
				'location'    => 'Wedding Clients - Wolverhampton',
				'review_text' => 'The highlight of our Indian wedding reception and Mehndi night! Our guests could not believe how fresh and natural it tasted - pressed live right in front of them. Our elders were nostalgic, saying it was just like the juice from the sugarcane machines in Gujarat. Absolutely recommended for desi weddings!',
				'rating'      => 5.0,
				'result'      => 'Star of the Mehndi night',
				'status'      => 'active',
			],
			[
				'author_name' => 'Sunita Reddy',
				'location'    => 'Verified Customer - Southall, West London',
				'review_text' => 'Finally, authentic fresh sugarcane juice in the UK! No artificial flavours, no added sugar - just pure ganna ras the way we used to have it in Hyderabad. The lemon-ginger blend is my absolute favourite. I send The Cane House to all my UK desi friends now. A true taste of home!',
				'rating'      => 5.0,
				'result'      => 'Authentic ganna ras taste of home',
				'status'      => 'active',
			],
			[
				'author_name' => 'Vikram Singh',
				'location'    => 'Festival Organiser - Manchester, Rusholme',
				'review_text' => 'Booked The Cane House for our Vaisakhi mela - 500+ attendees and the sugarcane stall was the longest queue all day! Professional, hygienic, and the taste was absolutely brilliant. Every sip felt like a celebration of our Punjabi heritage. Will definitely book again for our Diwali mela.',
				'rating'      => 5.0,
				'result'      => 'Longest queue at the Vaisakhi mela',
				'status'      => 'active',
			],
			[
				'author_name' => 'Sarah & James Thompson',
				'location'    => 'Verified Customer - Brighton',
				'review_text' => 'Tried The Cane House at a summer festival - absolutely loved the pineapple tropical blend! So refreshing, so natural. My partner had never tried sugarcane juice before but now we are completely hooked. The staff explained the whole process and the history of ganna ras in India. Such a lovely experience.',
				'rating'      => 5.0,
				'result'      => 'First-time sugarcane converts',
				'status'      => 'active',
			],
			[
				'author_name' => 'Deepa Krishnamurthy',
				'location'    => 'Verified Customer - Coventry, Foleshill',
				'review_text' => 'Growing up in Chennai, sugarcane juice was part of summer life. Finding The Cane House in the UK felt like such a gift! The pure cane variety is spot on - that slightly grassy, intensely sweet taste that takes you straight back. Thank you for bringing a piece of South India to our community.',
				'rating'      => 5.0,
				'result'      => 'Taste of Tamil Nadu in the UK',
				'status'      => 'active',
			],
			[
				'author_name' => 'Harpreet & Gurjit Dhillon',
				'location'    => 'Wedding Clients - Bradford',
				'review_text' => 'Our Sangeet night was completely transformed by The Cane House! All our Punjabi family members were delighted - the uncles and aunties kept going back for more. The Red Cane with Mint was the most popular combination. Professional, on time, and genuinely passionate about their craft.',
				'rating'      => 5.0,
				'result'      => 'Made the Sangeet night unforgettable',
				'status'      => 'active',
			],
		];

		foreach ( $rows as $row ) {
			$ok = $wpdb->insert( $table, $row, [ '%s', '%s', '%s', '%f', '%s', '%s' ] );
			if ( $ok ) {
				$inserted++;
			}
		}

		return [ 'inserted' => $inserted ];
	}

	private static function seed_faqs(): array {
		global $wpdb;
		$table    = ch_theme_table( 'faqs' );
		$inserted = 0;

		$rows = [
			[ 'topic' => 'General',   'question' => 'Do you add any sugar or preservatives?',                'answer' => 'No, absolutely not. Our sugarcane juice is 100% natural, pressed live from the stalk. The sweetness comes entirely from the natural sugars in the cane itself - just as it has been enjoyed across India, South Asia, and tropical cultures for over 2,000 years. Ganna ras in its purest form.',                                                                                  'status' => 'active', 'sort_order' => 1 ],
			[ 'topic' => 'General',   'question' => 'How long does the juice stay fresh?',                   'answer' => 'Fresh sugarcane juice is best enjoyed immediately after pressing - just like a fresh glass of ganna ras from a roadside stall in India! If kept chilled, it can stay fresh for up to 24 hours. We always recommend drinking it cool and fresh for the very best flavour.',                                                                                               'status' => 'active', 'sort_order' => 2 ],
			[ 'topic' => 'General',   'question' => 'Is the juice suitable for everyone?',                   'answer' => 'Yes! Fresh sugarcane juice is enjoyed by people of all ages and dietary backgrounds - it is naturally vegan, gluten-free, and dairy-free. In Ayurvedic medicine, sugarcane is classified as a sheetal (cooling) and balancing food. Please consume responsibly if you are managing blood sugar levels.',                                                             'status' => 'active', 'sort_order' => 3 ],
			[ 'topic' => 'Events',    'question' => 'What types of events can I hire you for?',              'answer' => 'We cater for all types of events including Indian weddings, Mehndi nights, Sangeet evenings, Baraat receptions, Eid parties, Diwali celebrations, Vaisakhi melas, birthdays, corporate gatherings, festivals, and community events across the UK. Our live pressing stall is always the star of the show!',                                                          'status' => 'active', 'sort_order' => 4 ],
			[ 'topic' => 'Events',    'question' => 'How much does it cost to hire for an event?',           'answer' => 'Pricing is customised based on your event size, location, duration, and the number of guests. We offer competitive packages for intimate private gatherings of 30 guests right up to large-scale melas and corporate events. Contact us for a personalised quote - we always work to accommodate your budget.',                                                           'status' => 'active', 'sort_order' => 5 ],
			[ 'topic' => 'Events',    'question' => 'How much notice do you need for event bookings?',       'answer' => 'We recommend booking at least 2–4 weeks in advance to secure your preferred date, especially during peak wedding and festival season (April–October) when our calendar fills quickly. Do reach out even at shorter notice and we will do our very best to accommodate you.',                                                                                          'status' => 'active', 'sort_order' => 6 ],
			[ 'topic' => 'Juice',     'question' => 'What is the difference between Yellow Cane and Red Cane?', 'answer' => 'Yellow Cane produces a lighter, more refreshing golden juice with a clean, mild sweetness - similar to the most common ganna ras you find across North India. Red Cane (+£0.50) is naturally richer with a deeper amber colour and a more intense, almost molasses-like sweetness. Both are 100% natural with no additives.',                                 'status' => 'active', 'sort_order' => 7 ],
			[ 'topic' => 'Juice',     'question' => 'What flavour blends do you offer?',                     'answer' => 'We offer Pure Cane (natural, included), Citrus Blends including Lemon, Ginger (adrak), Lemon & Ginger, and Mint (+£0.50 each), and Tropical Blends including Pineapple, Watermelon, Strawberry, and Blueberry Burst (+£1.00 each). The Ginger and Lemon & Ginger blends are especially popular with our South Asian customers - a nod to the classic nimbu-adrak ganna ras!', 'status' => 'active', 'sort_order' => 8 ],
			[ 'topic' => 'General',   'question' => 'Is your sugarcane sustainable?',                        'answer' => 'Yes! Sugarcane is one of the most sustainable crops on earth. Even our leftover fibre (bagasse - the same by-product used to make eco-friendly packaging in India) is completely biodegradable. We are committed to responsible, eco-conscious serving practices across all our events.',                                                                             'status' => 'active', 'sort_order' => 9 ],
			[ 'topic' => 'Franchise', 'question' => 'How can I become a franchise partner?',                 'answer' => 'We warmly welcome franchise enquiries from across the UK - especially from those with roots in South Asian communities where sugarcane has always been cherished. Whether you want to run a permanent stall, a mobile unit, or an events-focused operation in your city, we have a model for you. Call +44 7887 699 208 or use the contact form.',                         'status' => 'active', 'sort_order' => 10 ],
		];

		foreach ( $rows as $row ) {
			$ok = $wpdb->insert( $table, $row, [ '%s', '%s', '%s', '%s', '%d' ] );
			if ( $ok ) {
				$inserted++;
			}
		}

		return [ 'inserted' => $inserted ];
	}

	private static function seed_journal_page(): array {
		$slug = 'journal';
		$existing = get_page_by_path( $slug );
		if ( ! $existing ) {
			$id = wp_insert_post( [
				'post_title'   => 'The Cane Journal',
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			] );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_wp_page_template', 'page-blog.php' );
				return [ 'inserted' => 1, 'updated' => 0 ];
			}
		} else {
			update_post_meta( $existing->ID, '_wp_page_template', 'page-blog.php' );
		}
		return [ 'inserted' => 0, 'updated' => 0 ];
	}

	private static function seed_journal_posts(): array {
		$posts = [
			[
				'title'   => '5 Reasons Ganna Ras Is the Perfect Summer Drink',
				'content' => '<p>Sugarcane juice - known as ganna ras across South Asia - has been the go-to cooling drink for centuries. From roadside stalls in Delhi to melas in Leicester, it is a taste of home for millions.</p><h2>Naturally Cooling (Sheetal)</h2><p>In Ayurvedic tradition, sugarcane is classified as sheetal - a cooling food that reduces internal heat and calms the body. A glass of fresh ganna ras on a warm day does not just hydrate; it actively cools the system from within.</p><h2>Zero Added Sugar</h2><p>Unlike most commercial cold drinks, fresh sugarcane juice contains no added sugar, no preservatives, and no artificial flavours. The sweetness comes entirely from the natural sucrose within the stalk - pure and unadulterated.</p><h2>Rich in Electrolytes</h2><p>Sugarcane juice is naturally high in potassium, calcium, magnesium and iron - making it an excellent rehydration drink after activity or on hot festival days.</p><h2>Supports Liver Health</h2><p>Traditional medicine across India and South-East Asia has long used sugarcane juice as a liver tonic. Its alkaline nature helps maintain the body\'s pH balance and ease digestion.</p><h2>It Tastes Like Home</h2><p>For many in the South Asian diaspora in the UK, ganna ras is more than a drink - it is a memory. A school holiday, a summer in Punjab, a Vaisakhi mela. That emotional connection makes every glass of The Cane House juice that little bit more special.</p>',
				'excerpt' => 'From its Ayurvedic cooling properties to its zero-additive recipe, discover why fresh sugarcane juice is the ultimate summer drink.',
				'cat'     => 'Health & Wellness',
			],
			[
				'title'   => 'Nimbu-Adrak Ganna Ras: The Classic Blend Explained',
				'content' => '<p>Ask anyone from northern India about their favourite summer drink and the answer will often be the same: nimbu-adrak ganna ras - fresh sugarcane juice with lemon and ginger. This iconic combination has been pressed at roadside stalls from Amritsar to Ahmedabad for generations.</p><h2>Why This Blend Works</h2><p>The natural sweetness of sugarcane juice is balanced perfectly by the sharp acidity of fresh lemon (nimbu) and the warming kick of ginger (adrak). The result is a complex, layered drink - sweet, tangy and subtly spiced - that is deeply refreshing and endlessly drinkable.</p><h2>The Ginger Factor</h2><p>Fresh ginger adds far more than flavour. It is a natural digestive aid, anti-inflammatory, and warming spice that the body responds to positively. The combination of sugarcane\'s cooling properties and ginger\'s warming nature creates a beautifully balanced drink suitable year-round.</p><h2>Order It at The Cane House</h2><p>Our Lemon & Ginger blend is one of our most popular choices - especially with our South Asian customers who grew up with this flavour. Add it to any size for just 50p extra. We use only fresh lemon juice and whole ginger root, pressed on order.</p>',
				'excerpt' => 'The iconic lemon and ginger sugarcane blend has been pressed at stalls across India for generations. Here is why it works so perfectly.',
				'cat'     => 'Juice Knowledge',
			],
			[
				'title'   => 'Why Fresh Sugarcane Juice Is the Star of Every Desi Wedding',
				'content' => '<p>If you have attended a South Asian wedding, Mehndi night, or Baraat reception in the UK in recent years, there is a good chance you have seen a fresh sugarcane press at the event. Here\'s why The Cane House stall has become a fixture at desi celebrations from Leicester to Manchester.</p><h2>A Taste That Connects Generations</h2><p>Sugarcane juice carries deep cultural meaning for South Asian families. For grandparents, it evokes memories of India and Pakistan. For the younger generation, it is an exciting, authentic alternative to fizzy drinks. For everyone, it is a conversation starter.</p><h2>Live Pressing Is a Spectacle</h2><p>The sound of cane being fed through the press, the rush of golden juice into the cup, the fresh aroma - it creates a moment. Our stall draws a crowd at every event because the process is as enjoyable to watch as the drink is to taste.</p><h2>Catering for All Dietary Needs</h2><p>Fresh sugarcane juice is naturally vegan, gluten-free, dairy-free, and nut-free - making it one of the safest and most inclusive drinks to serve at any large gathering where dietary requirements are diverse.</p><h2>Book The Cane House for Your Event</h2><p>We cater for Mehndi nights, Sangeet evenings, Baraat receptions, Eid parties, Diwali celebrations, Vaisakhi melas, and much more. Contact us to discuss your event.</p>',
				'excerpt' => 'From Mehndi nights to Baraat receptions, fresh sugarcane juice has become the signature drink of South Asian celebrations in the UK.',
				'cat'     => 'Events & Culture',
			],
			[
				'title'   => 'The History of Ganna Ras in South Asian Culture',
				'content' => '<p>Sugarcane has been cultivated in the Indian subcontinent for over 4,000 years. Sanskrit texts as far back as 1500 BCE reference the use of sugarcane juice both as a food and a medicine. Here is a brief journey through the history of ganna ras.</p><h2>Ancient Roots</h2><p>The word "ganna" comes from Sanskrit (ikshu), and sugarcane cultivation spread from ancient India across the world - to Persia, Egypt, and eventually the Americas. India remains one of the world\'s largest producers of sugarcane today.</p><h2>Ayurvedic Medicine</h2><p>Ancient Ayurvedic texts classify sugarcane juice as an important medicinal food. It was used to treat urinary disorders, jaundice, and digestive complaints. Its sheetal (cooling) nature made it particularly valued during hot seasons.</p><h2>The Roadside Stall Tradition</h2><p>The iconic image of a roadside ganna ras wala - a vendor with a hand-cranked or motorised cane press - is one of the most enduring images of Indian urban life. From railway platforms in Mumbai to village fairs in Punjab, fresh sugarcane juice has always been accessible to all.</p><h2>Bringing That Tradition to the UK</h2><p>The Cane House was founded to bring that authentic roadside experience to the South Asian diaspora in Britain. Every glass we press is a connection to that centuries-old tradition.</p>',
				'excerpt' => 'Tracing the 4,000-year history of sugarcane juice in South Asian culture - from ancient Sanskrit texts to the iconic roadside press.',
				'cat'     => 'Events & Culture',
			],
			[
				'title'   => 'How to Build Your Perfect Juice: A Guide to Our Blends',
				'content' => '<p>One of the things that makes The Cane House unique is our custom build-your-juice system. Rather than choosing from a fixed menu, you start with your cane type and layer in the flavours you want. Here is how to navigate the options.</p><h2>Step 1: Choose Your Cane</h2><p><strong>Yellow Cane</strong> is our standard - the classic ganna ras you will recognise from India. A lighter golden colour, clean sweetness, and that unmistakable fresh flavour.<br><strong>Red Cane (+50p)</strong> is richer and deeper in colour, with a more intense sweetness that is slightly reminiscent of molasses. A premium experience for true cane connoisseurs.</p><h2>Step 2: Add a Citrus Blend</h2><p>Our citrus blends add brightness and zing. Lemon is the classic; Ginger (adrak) adds warmth and spice; Lemon & Ginger together is the iconic nimbu-adrak combination; Mint adds a cooling freshness that is especially popular in summer.</p><h2>Step 3: Go Tropical (Optional)</h2><p>For a more exotic flavour profile, our tropical blends (Pineapple, Watermelon, Strawberry, Blueberry Burst) layer fruit flavours into the cane base for a more complex, indulgent drink.</p><h2>Step 4: Pick Your Size</h2><p>From a chilled 200ml Small right up to our Group Sharing 1.5L - perfect for families and event tables. All sizes, all blends, pressed fresh to order.</p>',
				'excerpt' => 'New to The Cane House? This step-by-step guide walks you through our cane types, citrus blends, tropical flavours, and sizes.',
				'cat'     => 'Juice Knowledge',
			],
			[
				'title'   => 'Sugarcane Juice in Winter: Warming Blends for Cold Days',
				'content' => '<p>Sugarcane juice is traditionally associated with summer cooling, but in Ayurvedic practice it is considered a year-round drink - and with the right blends, it is just as enjoyable in the colder UK months.</p><h2>The Adrak (Ginger) Advantage</h2><p>Adding fresh ginger transforms a cooling drink into a warming one. The thermogenic properties of ginger activate the body\'s internal heat mechanisms, making a hot-weather drink feel perfectly at home on a crisp winter evening.</p><h2>Try the Ginger & Lemon Blend</h2><p>The Lemon & Ginger blend is our most popular year-round choice. The lemon provides Vitamin C and brightness, the ginger provides warmth and digestive support, and the sugarcane provides natural energy. It is a genuinely functional winter drink.</p><h2>Perfect for Indoor Events</h2><p>Winter is prime season for indoor South Asian events - Diwali parties, Christmas dinners, New Year\'s Eve celebrations. Our heated indoor stall setup is fully equipped for winter events. The live pressing generates its own warmth, and your guests will love the novelty of fresh sugarcane juice in the cold.</p>',
				'excerpt' => 'Think sugarcane juice is only for summer? With our Ginger and Lemon blends, it is a warming, functional drink for every season.',
				'cat'     => 'Health & Wellness',
			],
		];

		$count = 0;
		foreach ( $posts as $p ) {
			$existing = get_page_by_title( $p['title'], OBJECT, 'post' );
			if ( $existing ) continue;
			$post_id = wp_insert_post( [
				'post_title'   => $p['title'],
				'post_content' => $p['content'],
				'post_excerpt' => $p['excerpt'],
				'post_status'  => 'publish',
				'post_type'    => 'post',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				if ( ! empty( $p['cat'] ) ) wp_set_object_terms( $post_id, $p['cat'], 'category' );
				$count++;
			}
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	private static function seed_news_bar(): array {
		global $wpdb;
		$table    = ch_theme_table( 'news_bar' );
		$inserted = 0;

		$rows = [
			[ 'message' => '✦ Fresh ganna ras - no added sugar, no preservatives, pressed live at every order',          'status' => 'active', 'sort_order' => 1 ],
			[ 'message' => '✦ Now hiring for our Vaisakhi & Eid season - book your event stall today',                   'status' => 'active', 'sort_order' => 2 ],
			[ 'message' => '✦ Lemon & Ginger blend - the classic nimbu-adrak combination, now available year-round',     'status' => 'active', 'sort_order' => 3 ],
			[ 'message' => '✦ Franchise opportunities available in Southall, Leicester, Birmingham & Manchester',         'status' => 'active', 'sort_order' => 4 ],
			[ 'message' => '✦ New: Group Sharing 1.5L - perfect for family gatherings and Mehndi nights',                'status' => 'active', 'sort_order' => 5 ],
		];

		foreach ( $rows as $row ) {
			$ok = $wpdb->insert( $table, $row, [ '%s', '%s', '%d' ] );
			if ( $ok ) {
				$inserted++;
			}
		}

		return [ 'inserted' => $inserted ];
	}
}
