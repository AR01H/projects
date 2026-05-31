<?php
defined( 'ABSPATH' ) || exit;

/**
 * CH_Data
 * Seed data registry for the Cane House theme.
 *
 * Data source priority:
 *   1. CSV files in mock_data/csv/ - edit these before running mock install
 *   2. Hardcoded defaults below   - used when CSV files are absent
 *
 * CSV files live at: {theme}/mock_data/csv/{name}.csv
 *
 * Key-value CSVs (settings.csv, home-settings.csv, etc.) use two columns: key, value
 * Array CSVs use one row per item with named columns
 * hire-packages.csv uses semicolons to separate nested items inside the "items" column
 */
class CH_Data {

	// ── CSV loaders ───────────────────────────────────────────────────────────

	/**
	 * Loads a CSV from mock_data/csv/{name}.csv.
	 * Returns array of associative rows, or empty array when file is absent.
	 */
	public static function load_csv( string $name ): array {
		$path = get_template_directory() . "/mock_data/csv/{$name}.csv";
		if ( ! file_exists( $path ) ) {
			return [];
		}
		$fh = fopen( $path, 'r' );
		if ( $fh === false ) {
			return [];
		}
		$bom = fread( $fh, 3 );
		if ( $bom !== "\xef\xbb\xbf" ) {
			rewind( $fh );
		}
		$headers = fgetcsv( $fh );
		if ( ! $headers ) {
			fclose( $fh );
			return [];
		}
		$headers = array_map( 'trim', $headers );
		$rows    = [];
		while ( ( $row = fgetcsv( $fh ) ) !== false ) {
			if ( count( $row ) < count( $headers ) ) {
				continue;
			}
			// Truncate extra columns so array_combine never mismatches
			$rows[] = array_combine( $headers, array_map( 'trim', array_slice( $row, 0, count( $headers ) ) ) );
		}
		fclose( $fh );
		return $rows;
	}

	/**
	 * Loads a key-value CSV (columns: key, value) and returns [key => value] array.
	 */
	public static function load_kv_csv( string $name ): array {
		$rows = self::load_csv( $name );
		if ( ! $rows ) {
			return [];
		}
		$result = [];
		foreach ( $rows as $row ) {
			$k = trim( $row['key'] ?? '' );
			if ( $k !== '' ) {
				$result[ $k ] = $row['value'] ?? '';
			}
		}
		return $result;
	}

	// ── DB table data ─────────────────────────────────────────────────────────

	public static function reviews(): array {
		$rows = self::load_csv( 'reviews' );
		return $rows ?: self::default_reviews();
	}

	public static function faqs(): array {
		$rows = self::load_csv( 'faqs' );
		return $rows ?: self::default_faqs();
	}

	public static function news_bar(): array {
		$rows = self::load_csv( 'news_bar' );
		return $rows ?: self::default_news_bar();
	}

	public static function services(): array {
		$rows = self::load_csv( 'services' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'icon'        => $r['icon']        ?? '',
					'title'       => $r['title']       ?? '',
					'description' => $r['description'] ?? '',
					'details'     => $r['details']     ?? '',
					'image_url'   => $r['image_url']   ?? '',
					'status'      => $r['status']      ?? 'active',
					'sort_order'  => (int) ( $r['sort_order'] ?? 0 ),
				];
			}, $rows );
		}
		return self::default_services();
	}

	public static function about_team(): array {
		$rows = self::load_csv( 'about_team' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'name'       => $r['name']       ?? '',
					'role'       => $r['role']       ?? '',
					'bio'        => $r['bio']        ?? '',
					'image_url'  => $r['image_url']  ?? '',
					'status'     => $r['status']     ?? 'active',
					'sort_order' => (int) ( $r['sort_order'] ?? 0 ),
				];
			}, $rows );
		}
		return self::default_about_team();
	}

	// ── Site settings (key-value CSVs) ────────────────────────────────────────

	public static function settings(): array {
		$kv = self::load_kv_csv( 'settings' );
		return $kv ?: self::default_settings();
	}

	public static function home_settings(): array {
		$kv = self::load_kv_csv( 'home-settings' );
		return $kv ?: self::default_home_settings();
	}

	public static function contact_settings(): array {
		$kv = self::load_kv_csv( 'contact-settings' );
		if ( $kv ) {
			return array_merge( [ 'recipient_email' => get_option( 'admin_email' ) ], $kv );
		}
		return self::default_contact_settings();
	}

	public static function story_settings(): array {
		$kv = self::load_kv_csv( 'story-settings' );
		if ( $kv ) {
			if ( isset( $kv['badge_text'] ) ) {
				$kv['badge_text'] = str_replace( '\n', "\n", $kv['badge_text'] );
			}
			$kv['facts'] = self::story_facts();
			return $kv;
		}
		return self::default_story_settings();
	}

	// ── Array data ────────────────────────────────────────────────────────────

	public static function navigation(): array {
		$rows = self::load_csv( 'navigation' );
		return $rows ?: self::default_navigation();
	}

	/**
	 * Footer - assembled from footer-settings.csv (kv), footer-links.csv
	 * (grouped by column) and footer-legal.csv. Returns the nested structure
	 * used by ch_get_theme_footer() / ah_cms_footer.
	 */
	public static function footer(): array {
		$kv = self::load_kv_csv( 'footer-settings' );

		// Group links by their "column" value, preserving order.
		$columns = [];
		foreach ( self::load_csv( 'footer-links' ) as $r ) {
			$title = trim( $r['column'] ?? '' );
			$label = trim( $r['label'] ?? '' );
			if ( $title === '' || $label === '' ) continue;
			if ( ! isset( $columns[ $title ] ) ) {
				$columns[ $title ] = [ 'title' => $title, 'items' => [] ];
			}
			$columns[ $title ]['items'][] = [
				'label'     => $label,
				'url'       => $r['url'] ?? '#',
				'highlight' => filter_var( $r['highlight'] ?? false, FILTER_VALIDATE_BOOLEAN ),
			];
		}

		$legal = [];
		foreach ( self::load_csv( 'footer-legal' ) as $r ) {
			$label = trim( $r['label'] ?? '' );
			if ( $label === '' ) continue;
			$legal[] = [ 'label' => $label, 'url' => $r['url'] ?? '#' ];
		}

		return [
			'brand_description' => $kv['brand_description'] ?? '',
			'badge_text'        => $kv['badge_text'] ?? '',
			'copyright_suffix'  => $kv['copyright_suffix'] ?? 'Pressed Fresh. Served Cool.',
			'columns'           => array_values( $columns ),
			'cta'               => [
				'label' => $kv['cta_label'] ?? 'Send a Message 🌿',
				'url'   => $kv['cta_url']   ?? '#contact',
			],
			'contact'           => [
				'phone_note' => $kv['phone_note'] ?? '',
				'email_note' => $kv['email_note'] ?? '',
			],
			'legal_links'       => $legal,
			'social'            => [],
		];
	}

	/** Interactive Sugarcane Story cards. */
	public static function story_cards(): array {
		$rows = self::load_csv( 'story-cards' );
		if ( ! $rows ) return [];
		return array_map( static function ( $r ) {
			$split = static function ( $val ) {
				$val = (string) ( $val ?? '' );
				return $val === '' ? [] : array_values( array_filter( array_map( 'trim', explode( ';', $val ) ) ) );
			};
			return [
				'id'      => $r['id']      ?? '',
				'icon'    => $r['icon']    ?? '🌿',
				'label'   => $r['label']   ?? '',
				'heading' => $r['heading'] ?? '',
				'body'    => $r['body']    ?? '',
				'facts'   => $split( $r['facts']  ?? '' ),
				'images'  => $split( $r['images'] ?? '' ),
			];
		}, $rows );
	}

	/** Certification badges. */
	public static function certifications(): array {
		$rows = self::load_csv( 'certifications' );
		if ( ! $rows ) return [];
		return array_map( static function ( $r ) {
			return [
				'icon'  => $r['icon']  ?? '✅',
				'title' => $r['title'] ?? '',
				'desc'  => $r['desc']  ?? '',
				'badge' => $r['badge'] ?? '',
			];
		}, $rows );
	}

	public static function menu_sizes(): array {
		$rows = self::load_csv( 'menu-sizes' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'icon'     => $r['icon']     ?? '',
					'name'     => $r['name']     ?? '',
					'desc'     => $r['desc']     ?? '',
					'price'    => $r['price']    ?? '',
					'badge'    => $r['badge']    ?? '',
					'featured' => filter_var( $r['featured'] ?? false, FILTER_VALIDATE_BOOLEAN ),
				];
			}, $rows );
		}
		return self::default_menu_sizes();
	}

	public static function cane_types(): array {
		$rows = self::load_csv( 'cane-types' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'icon'     => $r['icon']     ?? '',
					'name'     => $r['name']     ?? '',
					'desc'     => $r['desc']     ?? '',
					'price'    => $r['price']    ?? '',
					'badge'    => $r['badge']    ?? '',
					'featured' => filter_var( $r['featured'] ?? false, FILTER_VALIDATE_BOOLEAN ),
				];
			}, $rows );
		}
		return self::default_cane_types();
	}

	public static function textures(): array {
		$rows = self::load_csv( 'textures' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'icon'     => $r['icon']     ?? '',
					'name'     => $r['name']     ?? '',
					'desc'     => $r['desc']     ?? '',
					'price'    => $r['price']    ?? '',
					'badge'    => $r['badge']    ?? '',
					'featured' => filter_var( $r['featured'] ?? false, FILTER_VALIDATE_BOOLEAN ),
				];
			}, $rows );
		}
		return self::default_textures();
	}

	public static function flavours(): array {
		$rows = self::load_csv( 'flavours' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'emoji'    => $r['emoji']    ?? '',
					'name'     => $r['name']     ?? '',
					'desc'     => $r['desc']     ?? '',
					'category' => $r['category'] ?? 'pure',
				];
			}, $rows );
		}
		return self::default_flavours();
	}

	public static function order_steps(): array {
		$rows = self::load_csv( 'order-steps' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'num'       => $r['num']       ?? '',
					'emoji'     => $r['emoji']     ?? '',
					'title'     => $r['title']     ?? '',
					'desc'      => $r['desc']      ?? '',
					'highlight' => filter_var( $r['highlight'] ?? false, FILTER_VALIDATE_BOOLEAN ),
				];
			}, $rows );
		}
		return self::default_order_steps();
	}

	public static function marquee_items(): array {
		$rows = self::load_csv( 'marquee-items' );
		if ( $rows ) {
			return array_column( $rows, 'item' );
		}
		return self::default_marquee_items();
	}

	public static function benefits(): array {
		$rows = self::load_csv( 'benefits' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'icon'  => $r['icon']  ?? '',
					'title' => $r['title'] ?? '',
					'desc'  => $r['desc']  ?? '',
				];
			}, $rows );
		}
		return self::default_benefits();
	}

	public static function hire_packages(): array {
		$rows = self::load_csv( 'hire-packages' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				$raw_items = $r['items'] ?? '';
				$items     = $raw_items !== '' ? array_map( 'trim', explode( ';', $raw_items ) ) : [];
				return [
					'icon'  => $r['icon']  ?? '',
					'title' => $r['title'] ?? '',
					'desc'  => $r['desc']  ?? '',
					'items' => $items,
				];
			}, $rows );
		}
		return self::default_hire_packages();
	}

	public static function hire_features(): array {
		$rows = self::load_csv( 'hire-features' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [ 'icon' => $r['icon'] ?? '', 'text' => $r['text'] ?? '' ];
			}, $rows );
		}
		return self::default_hire_features();
	}

	public static function franchise_locations(): array {
		$rows = self::load_csv( 'franchise-locations' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [ 'icon' => $r['icon'] ?? '📍', 'name' => $r['name'] ?? '' ];
			}, $rows );
		}
		return self::default_franchise_locations();
	}

	public static function juice_showcase(): array {
		$rows = self::load_csv( 'juice-showcase' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'image' => $r['image'] ?? '',
					'title' => $r['title'] ?? '',
					'desc'  => $r['desc']  ?? '',
				];
			}, $rows );
		}
		return self::default_juice_showcase();
	}

	public static function story_facts(): array {
		$rows = self::load_csv( 'story-facts' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'icon'  => $r['icon']  ?? '',
					'title' => $r['title'] ?? '',
					'desc'  => $r['desc']  ?? '',
				];
			}, $rows );
		}
		return self::default_story_facts();
	}

	public static function journal_posts(): array {
		$rows = self::load_csv( 'journal-posts' );
		if ( $rows ) {
			return array_map( static function ( $r ) {
				return [
					'title'   => $r['title']   ?? '',
					'excerpt' => $r['excerpt']  ?? '',
					'cat'     => $r['category'] ?? '',
					'content' => $r['content']  ?? '',
				];
			}, $rows );
		}
		return self::default_journal_posts();
	}

	// ── Defaults ──────────────────────────────────────────────────────────────

	private static function default_reviews(): array {
		return [
			[ 'author_name' => 'Priya Sharma',            'location' => 'Verified Customer - Leicester, Belgrave',   'review_text' => 'Reminds me of freshly pressed ganna ras from back home in Punjab! The ginger blend is absolutely incredible. My whole family was so happy to have this at our Diwali celebration.', 'rating' => '5.0', 'result' => 'Perfect for Diwali celebrations',    'status' => 'active' ],
			[ 'author_name' => 'Mohammed Al-Rashid',       'location' => 'Event Client - Birmingham, Handsworth',     'review_text' => 'We hired The Cane House for our Eid family gathering. Over 80 guests and everyone was asking about the juice stall! The live pressing was such a crowd puller. 10/10 service.',     'rating' => '5.0', 'result' => 'Star attraction at the Eid gathering',  'status' => 'active' ],
			[ 'author_name' => 'Ananya & Rahul Patel',     'location' => 'Wedding Clients - Wolverhampton',           'review_text' => 'The highlight of our Indian wedding reception and Mehndi night! Pressed live right in front of them. Absolutely recommended for desi weddings!',                                          'rating' => '5.0', 'result' => 'Star of the Mehndi night',              'status' => 'active' ],
			[ 'author_name' => 'Sunita Reddy',              'location' => 'Verified Customer - Southall, West London', 'review_text' => 'Finally authentic fresh sugarcane juice in the UK! Pure ganna ras the way we used to have it in Hyderabad. A true taste of home!',                                                    'rating' => '5.0', 'result' => 'Authentic ganna ras taste of home',     'status' => 'active' ],
			[ 'author_name' => 'Vikram Singh',              'location' => 'Festival Organiser - Manchester, Rusholme', 'review_text' => 'Booked The Cane House for our Vaisakhi mela - 500+ attendees and the sugarcane stall was the longest queue all day! Every sip felt like a celebration of our Punjabi heritage.',  'rating' => '5.0', 'result' => 'Longest queue at the Vaisakhi mela',    'status' => 'active' ],
			[ 'author_name' => 'Sarah & James Thompson',   'location' => 'Verified Customer - Brighton',              'review_text' => 'Tried The Cane House at a summer festival - absolutely loved the pineapple tropical blend! My partner had never tried sugarcane juice before but now we are completely hooked.',      'rating' => '5.0', 'result' => 'First-time sugarcane converts',          'status' => 'active' ],
			[ 'author_name' => 'Deepa Krishnamurthy',      'location' => 'Verified Customer - Coventry, Foleshill',   'review_text' => 'Growing up in Chennai sugarcane juice was part of summer life. Finding The Cane House in the UK felt like such a gift!',                                                             'rating' => '5.0', 'result' => 'Taste of Tamil Nadu in the UK',         'status' => 'active' ],
			[ 'author_name' => 'Harpreet & Gurjit Dhillon', 'location' => 'Wedding Clients - Bradford',                'review_text' => 'Our Sangeet night was completely transformed by The Cane House! The Red Cane with Mint was the most popular combination. Professional on time and genuinely passionate.',          'rating' => '5.0', 'result' => 'Made the Sangeet night unforgettable',  'status' => 'active' ],
		];
	}

	private static function default_faqs(): array {
		return [
			[ 'topic' => 'General',   'question' => 'Do you add any sugar or preservatives?',                   'answer' => 'No absolutely not. Our sugarcane juice is 100% natural pressed live from the stalk. The sweetness comes entirely from the natural sugars in the cane itself.',                                                                         'status' => 'active', 'sort_order' => '1'  ],
			[ 'topic' => 'General',   'question' => 'How long does the juice stay fresh?',                      'answer' => 'Fresh sugarcane juice is best enjoyed immediately after pressing. If kept chilled it can stay fresh for up to 24 hours.',                                                                                                             'status' => 'active', 'sort_order' => '2'  ],
			[ 'topic' => 'General',   'question' => 'Is the juice suitable for everyone?',                      'answer' => 'Yes! Fresh sugarcane juice is naturally vegan gluten-free and dairy-free. Please consume responsibly if you are managing blood sugar levels.',                                                                                        'status' => 'active', 'sort_order' => '3'  ],
			[ 'topic' => 'Events',    'question' => 'What types of events can I hire you for?',                 'answer' => 'We cater for Indian weddings Mehndi nights Sangeet evenings Baraat receptions Eid parties Diwali celebrations Vaisakhi melas birthdays corporate gatherings and community events across the UK.',                                    'status' => 'active', 'sort_order' => '4'  ],
			[ 'topic' => 'Events',    'question' => 'How much does it cost to hire for an event?',              'answer' => 'Pricing is customised based on your event size location duration and number of guests. Hire Us for a personalised quote.',                                                                                                         'status' => 'active', 'sort_order' => '5'  ],
			[ 'topic' => 'Events',    'question' => 'How much notice do you need for event bookings?',          'answer' => 'We recommend booking at least 2-4 weeks in advance especially during peak season (April-October).',                                                                                                                                  'status' => 'active', 'sort_order' => '6'  ],
			[ 'topic' => 'Juice',     'question' => 'What is the difference between Yellow Cane and Red Cane?', 'answer' => 'Yellow Cane produces a lighter golden juice with a clean mild sweetness. Red Cane (+£0.50) is naturally richer with a deeper amber colour and more intense sweetness.',                                                              'status' => 'active', 'sort_order' => '7'  ],
			[ 'topic' => 'Juice',     'question' => 'What flavour blends do you offer?',                        'answer' => 'Pure Cane (included) Citrus Blends - Lemon Ginger Lemon & Ginger Mint (+£0.50 each) - and Tropical Blends - Pineapple Watermelon Strawberry Blueberry Burst (+£1.00 each).',                                                       'status' => 'active', 'sort_order' => '8'  ],
			[ 'topic' => 'General',   'question' => 'Is your sugarcane sustainable?',                           'answer' => 'Yes! Sugarcane is one of the most sustainable crops on earth. Our leftover fibre (bagasse) is completely biodegradable.',                                                                                                            'status' => 'active', 'sort_order' => '9'  ],
		];
	}

	private static function default_news_bar(): array {
		return [
			[ 'message' => '✦ Fresh ganna ras - no added sugar no preservatives pressed live at every order',       'status' => 'active', 'sort_order' => '1' ],
			[ 'message' => '✦ Now hiring for our Vaisakhi & Eid season - book your event stall today',              'status' => 'active', 'sort_order' => '2' ],
			[ 'message' => '✦ Lemon & Ginger blend - the classic nimbu-adrak combination now available year-round', 'status' => 'active', 'sort_order' => '3' ],
			[ 'message' => '✦ Franchise opportunities available in Southall Leicester Birmingham & Manchester',      'status' => 'active', 'sort_order' => '4' ],
			[ 'message' => '✦ New: Group Sharing 1.5L - perfect for family gatherings and Mehndi nights',           'status' => 'active', 'sort_order' => '5' ],
		];
	}

	private static function default_settings(): array {
		return [
			'phone'         => '',
			'email'         => '',
			'address'       => '',
			'website'       => '',
			'whatsapp'      => '',
			'facebook_url'  => '',
			'instagram_url' => '',
			'tiktok_url'    => '',
			'youtube_url'   => '',
			'tagline'       => '',
		];
	}

	private static function default_home_settings(): array {
		return [
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
		];
	}

	private static function default_contact_settings(): array {
		return [
			'recipient_email' => get_option( 'admin_email' ),
			'subject_prefix'  => '[The Cane House Enquiry]',
			'thank_you_msg'   => "Thanks for your message! We'll be in touch shortly. Pressed Fresh. Served Cool. 🌿",
		];
	}

	private static function default_story_settings(): array {
		return [
			'tag'        => 'Story of Sugarcane',
			'headline'   => 'Beyond the <span class="accent">Juice</span>',
			'body_1'     => 'Sugarcane has been cherished for over 2,000 years, originating in South and Southeast Asia - particularly the Indian subcontinent - where it has been a cornerstone of Ayurvedic medicine, spiritual offerings, and everyday refreshment.',
			'body_2'     => 'At The Cane House, we bring this centuries-old tradition to the heart of the UK. Every glass honours that heritage - pressed live, served cool, with the same love and craft that has always made sugarcane juice special.',
			'quote'      => '"Sugarcane - one of nature\'s most generous gifts from the Indian subcontinent. Pure energy, pressed fresh."',
			'badge_text' => "2,000+\nYears\nof Cane",
			'facts'      => self::default_story_facts(),
		];
	}

	private static function default_navigation(): array {
		return [
			[ 'id' => 'how-to-order', 'label' => 'How To Order', 'url' => '#how-to-order', 'visible' => 'true', 'is_cta' => 'false' ],
			[ 'id' => 'build',        'label' => 'Build Juice',  'url' => '#build',         'visible' => 'true', 'is_cta' => 'false' ],
			[ 'id' => 'hire',         'label' => 'Hire Us',      'url' => '#hire',          'visible' => 'true', 'is_cta' => 'false' ],
			[ 'id' => 'franchise',    'label' => 'Franchise',    'url' => '#franchise',     'visible' => 'true', 'is_cta' => 'false' ],
			[ 'id' => 'faq',          'label' => 'FAQ',          'url' => '#faq',           'visible' => 'true', 'is_cta' => 'false' ],
			[ 'id' => 'journal',      'label' => 'Journal',      'url' => '/journal/',      'visible' => 'true', 'is_cta' => 'false' ],
			[ 'id' => 'contact-us',   'label' => 'Hire Us',   'url' => '#contact',       'visible' => 'true', 'is_cta' => 'true'  ],
		];
	}

	private static function default_menu_sizes(): array {
		return [
			[ 'icon' => '🥤', 'name' => 'Mini (250ml)',        'desc' => 'Quick refresh great for kids or first-timers', 'price' => '£4.00',  'badge' => '',           'featured' => false ],
			[ 'icon' => '🥤', 'name' => 'Regular (350ml)',      'desc' => 'Ideal single serving - balanced & refreshing', 'price' => '£5.50',  'badge' => 'Popular',    'featured' => true  ],
			[ 'icon' => '🧃', 'name' => 'Large (550ml)',        'desc' => 'For a longer more refreshing drink',           'price' => '£7.00',  'badge' => '',           'featured' => false ],
			[ 'icon' => '🫙', 'name' => 'Sharing Jug (750ml)', 'desc' => 'Great for two - perfect for sharing',          'price' => '£9.00',  'badge' => '',           'featured' => false ],
			[ 'icon' => '🍶', 'name' => 'Family Sharing (1L)', 'desc' => 'Perfect for families at gatherings',           'price' => '£14.50', 'badge' => '',           'featured' => false ],
			[ 'icon' => '🍾', 'name' => 'Group Sharing (1.5L)','desc' => 'Ideal for group gatherings & Mehndi nights',   'price' => '£19.50', 'badge' => 'Best Value', 'featured' => true  ],
		];
	}

	private static function default_cane_types(): array {
		return [
			[ 'icon' => '🌾', 'name' => 'Yellow Cane', 'desc' => 'Light golden fresh and refreshing',        'price' => '',       'badge' => 'Included', 'featured' => true  ],
			[ 'icon' => '🎋', 'name' => 'Red Cane',    'desc' => 'Naturally sweeter rich golden-amber tone', 'price' => '+£0.50', 'badge' => '',         'featured' => false ],
		];
	}

	private static function default_textures(): array {
		return [
			[ 'icon' => '🥢', 'name' => 'Classic', 'desc' => 'No Peel - light grassy traditional taste', 'price' => '',       'badge' => 'Included', 'featured' => true  ],
			[ 'icon' => '✨', 'name' => 'Smooth',  'desc' => 'With Peel - cleaner smoother finish',      'price' => '+£0.50', 'badge' => '',         'featured' => false ],
		];
	}

	private static function default_flavours(): array {
		return [
			[ 'emoji' => '🌿', 'name' => 'Pure Cane',      'desc' => 'Included - Clean & natural', 'category' => 'pure'     ],
			[ 'emoji' => '🍋', 'name' => 'Lemon',           'desc' => '+£0.50 · Citrus Blend',      'category' => 'citrus'   ],
			[ 'emoji' => '🫚', 'name' => 'Ginger',          'desc' => '+£0.50 · Citrus Blend',      'category' => 'citrus'   ],
			[ 'emoji' => '🌀', 'name' => 'Lemon & Ginger',  'desc' => '+£0.50 · Citrus Blend',      'category' => 'citrus'   ],
			[ 'emoji' => '🌱', 'name' => 'Mint',            'desc' => '+£0.50 · Citrus Blend',      'category' => 'citrus'   ],
			[ 'emoji' => '🍍', 'name' => 'Pineapple',       'desc' => '+£1.00 · Tropical Blend',    'category' => 'tropical' ],
			[ 'emoji' => '🍉', 'name' => 'Watermelon',      'desc' => '+£1.00 · Tropical Blend',    'category' => 'tropical' ],
			[ 'emoji' => '🍓', 'name' => 'Strawberry',      'desc' => '+£1.00 · Tropical Blend',    'category' => 'tropical' ],
			[ 'emoji' => '🫐', 'name' => 'Blueberry Burst', 'desc' => '+£1.00 · Tropical Blend',    'category' => 'tropical' ],
		];
	}

	private static function default_order_steps(): array {
		return [
			[ 'num' => '1', 'emoji' => '📏', 'title' => 'Select Size',    'desc' => 'Choose from Mini 250ml right up to Group Sharing 1.5L - perfect for every occasion',         'highlight' => false ],
			[ 'num' => '2', 'emoji' => '🌾', 'title' => 'Select Cane',    'desc' => 'Yellow Cane (light golden) or Red Cane (rich amber, +£0.50)',                                'highlight' => false ],
			[ 'num' => '3', 'emoji' => '🥤', 'title' => 'Select Texture', 'desc' => 'Classic No Peel for a grassy taste or Smooth With Peel for a cleaner finish (+£0.50)',      'highlight' => false ],
			[ 'num' => '4', 'emoji' => '🍋', 'title' => 'Select Flavour', 'desc' => 'Pure Cane (free) Citrus Blends (Lemon Ginger Mint +£0.50) or Tropical Blends (+£1.00)',    'highlight' => false ],
			[ 'num' => '5', 'emoji' => '🎉', 'title' => 'Enjoy!',         'desc' => 'Served chilled - no ice unless requested - pure fresh natural goodness in every sip',       'highlight' => true  ],
		];
	}

	private static function default_marquee_items(): array {
		return [ 'Pressed Fresh', 'Served Cool', 'No Added Sugar', 'No Preservatives', 'Pressed Live', 'Natural Goodness', 'Build Your Juice', 'Events & Hire', 'Ayurvedic Tradition', '2000+ Years of Cane' ];
	}

	private static function default_benefits(): array {
		return [
			[ 'icon' => '⚡', 'title' => 'Natural Energy Booster',      'desc' => 'Provides instant energy with natural sugars - a staple Ayurvedic revitaliser enjoyed across India for centuries. No additives no crash.' ],
			[ 'icon' => '💧', 'title' => 'Hydrating & Cooling',         'desc' => 'Perfect for warm days helping to refresh and rehydrate the body naturally. In Ayurveda sugarcane is classified as a cooling (sheetal) food.' ],
			[ 'icon' => '🌿', 'title' => 'Rich in Natural Nutrients',   'desc' => 'Contains antioxidants calcium potassium magnesium iron and essential electrolytes. No synthetic supplements needed.' ],
			[ 'icon' => '🫁', 'title' => 'Supports Digestion',         'desc' => 'Traditionally combined with lemon and ginger (adrak) to aid digestion - a remedy rooted in thousands of years of South Asian wellness wisdom.' ],
			[ 'icon' => '🛡️', 'title' => 'Boosts Immunity',            'desc' => 'Natural antioxidant flavonoids support overall wellness and immunity. Unlike fizzy drinks - clean fresh and nourishing.' ],
			[ 'icon' => '🌱', 'title' => 'Completely Natural & Vegan', 'desc' => 'No added sugar no preservatives no artificial colours. Just pure plant-based refreshment as nature intended.' ],
		];
	}

	private static function default_hire_packages(): array {
		return [
			[ 'icon' => '💒', 'title' => 'Weddings & Asian Celebrations', 'desc' => 'Add a traditional and healthy touch to your special day. We serve fresh juice live during your reception Mehndi night Sangeet or Baraat.',               'items' => [ 'Reception Drinks', 'Mehndi & Sangeet Night', 'Post-Ceremony Refreshment', 'Baraat Welcome Drinks' ] ],
			[ 'icon' => '🏢', 'title' => 'Corporate Events',              'desc' => 'Perfect for office parties wellness days and conferences. A healthy natural alternative to sugary sodas - show your team you care.',                         'items' => [ 'Office Wellness Days', 'Product Launches', 'Exhibitions & Trade Fairs', 'Team Away Days' ] ],
			[ 'icon' => '🎉', 'title' => 'Private Parties & Festivals',  'desc' => 'From Diwali parties to garden gatherings Eid celebrations to birthday bashes - we bring the vibe and freshness. Guests of all ages love it.', 'items' => [ 'Birthday Parties', 'Diwali & Eid Celebrations', 'Community Festivals & Melas', 'Garden & BBQ Events' ] ],
		];
	}

	private static function default_hire_features(): array {
		return [
			[ 'icon' => '🌿', 'text' => 'Pressed Live On-Site' ],
			[ 'icon' => '❄️', 'text' => 'Naturally Chilled' ],
			[ 'icon' => '🥤', 'text' => 'Unlimited Serving Options' ],
			[ 'icon' => '🛡️', 'text' => 'Fully Insured & Certified' ],
			[ 'icon' => '🚐', 'text' => 'Mobile Unit Available' ],
			[ 'icon' => '🌍', 'text' => 'UK-Wide Coverage' ],
		];
	}

	private static function default_franchise_locations(): array {
		return [
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
		];
	}

	private static function default_juice_showcase(): array {
		return [
			[ 'image' => 'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=600', 'title' => 'Pure Yellow Cane',   'desc' => 'Fresh & Naturally Sweet' ],
			[ 'image' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=600', 'title' => 'Zesty Lemon Blend', 'desc' => 'Citrus Refreshment'  ],
			[ 'image' => 'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&q=80&w=600', 'title' => 'Spicy Ginger',       'desc' => 'Warming & Healthy'   ],
			[ 'image' => 'https://images.unsplash.com/photo-1595981267035-7b04ca84a82d?auto=format&fit=crop&q=80&w=600', 'title' => 'Cooling Mint',     'desc' => 'Ultimate Freshness'  ],
		];
	}

	private static function default_story_facts(): array {
		return [
			[ 'icon' => '🍬', 'title' => 'Sugar & Jaggery',   'desc' => 'Khandsari & gur - traditional Indian sweeteners' ],
			[ 'icon' => '🫙', 'title' => 'Molasses',          'desc' => 'Rich syrup with deep mineral content' ],
			[ 'icon' => '⛽', 'title' => 'Ethanol',           'desc' => 'Clean-burning biofuel from fermentation' ],
			[ 'icon' => '🌱', 'title' => 'Eco Bagasse Fibre', 'desc' => 'Biodegradable by-product - fully sustainable' ],
		];
	}

	private static function default_journal_posts(): array {
		return [
			[ 'title' => '5 Reasons Ganna Ras Is the Perfect Summer Drink',         'excerpt' => 'From its Ayurvedic cooling properties to its zero-additive recipe discover why fresh sugarcane juice is the ultimate summer drink.', 'cat' => 'Health & Wellness', 'content' => '' ],
			[ 'title' => 'Nimbu-Adrak Ganna Ras: The Classic Blend Explained',      'excerpt' => 'The iconic lemon and ginger sugarcane blend has been pressed at stalls across India for generations. Here is why it works so perfectly.', 'cat' => 'Juice Knowledge',  'content' => '' ],
			[ 'title' => 'Why Fresh Sugarcane Juice Is the Star of Every Desi Wedding', 'excerpt' => 'From Mehndi nights to Baraat receptions fresh sugarcane juice has become the signature drink of South Asian celebrations in the UK.', 'cat' => 'Events & Culture', 'content' => '' ],
			[ 'title' => 'The History of Ganna Ras in South Asian Culture',         'excerpt' => 'Tracing the 4000-year history of sugarcane juice in South Asian culture - from ancient Sanskrit texts to the iconic roadside press.', 'cat' => 'Events & Culture', 'content' => '' ],
			[ 'title' => 'How to Build Your Perfect Juice: A Guide to Our Blends',  'excerpt' => 'New to The Cane House? This step-by-step guide walks you through our cane types citrus blends tropical flavours and sizes.', 'cat' => 'Juice Knowledge',  'content' => '' ],
			[ 'title' => 'Sugarcane Juice in Winter: Warming Blends for Cold Days', 'excerpt' => 'Think sugarcane juice is only for summer? With our Ginger and Lemon blends it is a warming functional drink for every season.', 'cat' => 'Health & Wellness', 'content' => '' ],
		];
	}

	private static function default_services(): array {
		return [
			[
				'icon'        => '🥤',
				'title'       => 'Fresh Juice Orders',
				'description' => 'Build your perfect sugarcane juice with our customizable menu. Choose from different cane types, textures, and delicious flavor blends.',
				'details'     => 'Available for immediate or advance orders. Delivered fresh to your location.',
				'image_url'   => 'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=600',
				'status'      => 'active',
				'sort_order'  => 1,
			],
			[
				'icon'        => '🎪',
				'title'       => 'Event & Stall Hire',
				'description' => 'Bring the live-pressed juice experience to your event. Our professional team will manage a fully-equipped juice stall at your venue.',
				'details'     => 'Perfect for weddings corporate events parties and festivals. Fully insured and certified.',
				'image_url'   => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&q=80&w=600',
				'status'      => 'active',
				'sort_order'  => 2,
			],
			[
				'icon'        => '📍',
				'title'       => 'Franchise Opportunities',
				'description' => 'Be part of the fresh juice revolution. Own your own Cane House franchise and bring live-pressed juice to your city.',
				'details'     => 'Complete business support training and marketing materials provided. Join our growing network.',
				'image_url'   => 'https://images.unsplash.com/photo-1556881286-fc6915169721?auto=format&fit=crop&q=80&w=600',
				'status'      => 'active',
				'sort_order'  => 3,
			],
			[
				'icon'        => '🎁',
				'title'       => 'Corporate Wellness',
				'description' => 'Elevate your workplace with fresh healthy juice options. Perfect for office wellness programs and team gatherings.',
				'details'     => 'Bulk orders available with special corporate rates. Delivered to your office.',
				'image_url'   => 'https://images.unsplash.com/photo-1595981267035-7b04ca84a82d?auto=format&fit=crop&q=80&w=600',
				'status'      => 'active',
				'sort_order'  => 4,
			],
		];
	}

	private static function default_about_team(): array {
		return [
			[
				'name'       => 'Akhilesh Ravuri',
				'role'       => 'Founder & CEO',
				'bio'        => 'Passionate about bringing authentic sugarcane juice to the UK. With years of experience in food service and a love for natural products Akhilesh founded The Cane House to share this amazing juice with everyone.',
				'image_url'  => 'https://i.pravatar.cc/300?u=founder',
				'status'     => 'active',
				'sort_order' => 1,
			],
			[
				'name'       => 'Sarah Johnson',
				'role'       => 'Operations Manager',
				'bio'        => 'Dedicated to ensuring every order is perfect. Sarah manages our supply chain quality control and customer satisfaction with enthusiasm and care.',
				'image_url'  => 'https://i.pravatar.cc/300?u=sarah',
				'status'     => 'active',
				'sort_order' => 2,
			],
			[
				'name'       => 'Mohammed Ali',
				'role'       => 'Events Coordinator',
				'bio'        => 'Expert in bringing The Cane House to life at events. Mohammed coordinates our event stalls making sure every guest has an amazing fresh juice experience.',
				'image_url'  => 'https://i.pravatar.cc/300?u=mohammed',
				'status'     => 'active',
				'sort_order' => 3,
			],
			[
				'name'       => 'Emma Wright',
				'role'       => 'Customer Experience Lead',
				'bio'        => 'Your friendly voice on the other end. Emma ensures every customer inquiry is handled with warmth and every event runs smoothly.',
				'image_url'  => 'https://i.pravatar.cc/300?u=emma',
				'status'     => 'active',
				'sort_order' => 4,
			],
		];
	}

}
