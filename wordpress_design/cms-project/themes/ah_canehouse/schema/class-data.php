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

	// FAQs are owned by the CMS plugin — the theme no longer stores or seeds them.

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
	// All demo content now lives in mock_data/csv/*.csv (the single source of
	// truth). These fallbacks return EMPTY so nothing is hardcoded in the theme.
	// Key-value settings keep their keys (with blank values) so templates that
	// read a specific key never hit an undefined index.

	private static function default_reviews(): array { return []; }

	private static function default_news_bar(): array { return []; }

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
			'hero_tag'        => '',
			'hero_headline'   => '',
			'hero_brand'      => '',
			'hero_desc'       => '',
			'hero_cta_label'  => '',
			'hero_cta_url'    => '',
			'hero_cta2_label' => '',
			'hero_cta2_url'   => '',
			'hero_badge_1'    => '',
			'hero_badge_2'    => '',
			'hero_badge_3'    => '',
			'hero_badge_4'    => '',
		];
	}

	private static function default_contact_settings(): array {
		return [
			'recipient_email' => get_option( 'admin_email' ),
			'subject_prefix'  => '',
			'thank_you_msg'   => '',
		];
	}

	private static function default_story_settings(): array {
		return [
			'tag'        => '',
			'headline'   => '',
			'body_1'     => '',
			'body_2'     => '',
			'quote'      => '',
			'badge_text' => '',
			'facts'      => [],
		];
	}

	private static function default_navigation(): array { return []; }

	private static function default_menu_sizes(): array { return []; }

	private static function default_cane_types(): array { return []; }

	private static function default_textures(): array { return []; }

	private static function default_flavours(): array { return []; }

	private static function default_order_steps(): array { return []; }

	private static function default_marquee_items(): array { return []; }

	private static function default_benefits(): array { return []; }

	private static function default_hire_packages(): array { return []; }

	private static function default_hire_features(): array { return []; }

	private static function default_franchise_locations(): array { return []; }

	private static function default_juice_showcase(): array { return []; }

	private static function default_story_facts(): array { return []; }

	private static function default_journal_posts(): array { return []; }

	private static function default_services(): array { return []; }

	private static function default_about_team(): array { return []; }

}
