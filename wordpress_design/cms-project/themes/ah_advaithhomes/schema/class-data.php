<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH_Data
 * Central registry of taxonomy types/terms and extra pages used during mock install.
 *
 * Data source priority:
 *   1. CSV files in mock_data/csv/ — edit these for easy customisation before running mock install
 *   2. Hardcoded defaults below — used when CSVs are absent
 *
 * Taxonomy data populates the CMS plugin tables visible at:
 *   admin.php?page=ah-taxonomy
 *
 * This class has zero dependency on the CMS plugin — the seeder handles plugin checks.
 */
class AH_Data {

	// ── Public API ────────────────────────────────────────────────────────────

	/**
	 * Returns all taxonomy types, each with a 'terms' sub-array.
	 * Merges from CSV if taxonomy-types.csv and taxonomy-terms.csv both exist.
	 */
	public static function taxonomy_types(): array {
		$types = self::load_csv( 'taxonomy-types' );
		$terms = self::load_csv( 'taxonomy-terms' );

		if ( $types && $terms ) {
			return self::merge_types_and_terms( $types, $terms );
		}

		return self::default_taxonomy_types();
	}

	/**
	 * Returns extra pages to create during mock install (policy, legal, utility pages).
	 * Loads from pages.csv if available.
	 * Format: [ 'slug' => [ 'title', 'template', 'content' ], ... ]
	 */
	public static function extra_pages(): array {
		$rows = self::load_csv( 'pages' );
		if ( $rows ) {
			$pages = [];
			foreach ( $rows as $row ) {
				$slug = $row['slug'] ?? '';
				if ( ! $slug ) continue;
				$pages[ $slug ] = [
					'title'    => $row['title']    ?? $slug,
					'template' => $row['template'] ?? '',
					'content'  => $row['content']  ?? '',
				];
			}
			return $pages;
		}
		return self::default_extra_pages();
	}

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
		// Strip UTF-8 BOM if present
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
			$rows[] = array_combine( $headers, array_map( 'trim', $row ) );
		}
		fclose( $fh );
		return $rows;
	}

	// ── Defaults ──────────────────────────────────────────────────────────────

	private static function default_taxonomy_types(): array {
		return [
			[
				'name'        => 'Highlight Names',
				'slug'        => 'highlight-names',
				'description' => 'Label content as Featured, Popular, New, Hot Deal, etc.',
				'terms'       => [
					[ 'name' => 'Featured',      'slug' => 'featured',      'sort_order' => 1 ],
					[ 'name' => 'Popular',        'slug' => 'popular',        'sort_order' => 2 ],
					[ 'name' => 'New',            'slug' => 'new',            'sort_order' => 3 ],
					[ 'name' => 'Hot Deal',       'slug' => 'hot-deal',       'sort_order' => 4 ],
					[ 'name' => 'Exclusive',      'slug' => 'exclusive',      'sort_order' => 5 ],
					[ 'name' => 'Off-Market',     'slug' => 'off-market',     'sort_order' => 6 ],
					[ 'name' => 'Price Reduced',  'slug' => 'price-reduced',  'sort_order' => 7 ],
					[ 'name' => 'Under Offer',    'slug' => 'under-offer',    'sort_order' => 8 ],
				],
			],
			[
				'name'        => 'Category',
				'slug'        => 'category',
				'description' => 'Content category groupings for articles, guides, and resources.',
				'terms'       => [
					[ 'name' => 'Buying Guides',        'slug' => 'buying-guides',        'sort_order' => 1 ],
					[ 'name' => 'Finance & Mortgages',  'slug' => 'finance-mortgages',    'sort_order' => 2 ],
					[ 'name' => 'Legal & Conveyancing', 'slug' => 'legal-conveyancing',   'sort_order' => 3 ],
					[ 'name' => 'Market Updates',       'slug' => 'market-updates',       'sort_order' => 4 ],
					[ 'name' => 'First-Time Buyers',    'slug' => 'first-time-buyers',    'sort_order' => 5 ],
					[ 'name' => 'Investors',            'slug' => 'investors',            'sort_order' => 6 ],
					[ 'name' => 'Relocation',           'slug' => 'relocation',           'sort_order' => 7 ],
					[ 'name' => 'Luxury Properties',    'slug' => 'luxury-properties',    'sort_order' => 8 ],
					[ 'name' => 'Property News',        'slug' => 'property-news',        'sort_order' => 9 ],
					[ 'name' => 'Tips & Advice',        'slug' => 'tips-advice',          'sort_order' => 10 ],
				],
			],
			[
				'name'        => 'Tags',
				'slug'        => 'tags',
				'description' => 'Free-form content tags for cross-referencing topics.',
				'terms'       => [
					[ 'name' => 'London',          'slug' => 'london',          'sort_order' => 1 ],
					[ 'name' => 'South East',      'slug' => 'south-east',      'sort_order' => 2 ],
					[ 'name' => 'North West',      'slug' => 'north-west',      'sort_order' => 3 ],
					[ 'name' => 'Midlands',        'slug' => 'midlands',        'sort_order' => 4 ],
					[ 'name' => 'Scotland',        'slug' => 'scotland',        'sort_order' => 5 ],
					[ 'name' => 'Wales',           'slug' => 'wales',           'sort_order' => 6 ],
					[ 'name' => 'Apartment',       'slug' => 'apartment',       'sort_order' => 7 ],
					[ 'name' => 'Detached',        'slug' => 'detached',        'sort_order' => 8 ],
					[ 'name' => 'Semi-Detached',   'slug' => 'semi-detached',   'sort_order' => 9 ],
					[ 'name' => 'Terraced',        'slug' => 'terraced',        'sort_order' => 10 ],
					[ 'name' => 'New Build',       'slug' => 'new-build',       'sort_order' => 11 ],
					[ 'name' => 'Period Property', 'slug' => 'period-property', 'sort_order' => 12 ],
					[ 'name' => 'Stamp Duty',      'slug' => 'stamp-duty',      'sort_order' => 13 ],
					[ 'name' => 'Mortgage',        'slug' => 'mortgage',        'sort_order' => 14 ],
					[ 'name' => 'Conveyancing',    'slug' => 'conveyancing',    'sort_order' => 15 ],
				],
			],
			[
				'name'        => 'DataProtected',
				'slug'        => 'data-protected',
				'description' => 'GDPR / data-handling classification for content visibility.',
				'terms'       => [
					[ 'name' => 'Public',         'slug' => 'public',         'sort_order' => 1 ],
					[ 'name' => 'Members Only',   'slug' => 'members-only',   'sort_order' => 2 ],
					[ 'name' => 'Internal Only',  'slug' => 'internal-only',  'sort_order' => 3 ],
					[ 'name' => 'GDPR Protected', 'slug' => 'gdpr-protected', 'sort_order' => 4 ],
					[ 'name' => 'Restricted',     'slug' => 'restricted',     'sort_order' => 5 ],
				],
			],
			[
				'name'        => 'Common',
				'slug'        => 'common',
				'description' => 'Shared cross-content labels used for linking and grouping.',
				'terms'       => [
					[ 'name' => 'Related Articles',  'slug' => 'related-articles',  'sort_order' => 1 ],
					[ 'name' => 'Useful Links',       'slug' => 'useful-links',       'sort_order' => 2 ],
					[ 'name' => 'External Resources', 'slug' => 'external-resources', 'sort_order' => 3 ],
					[ 'name' => 'Downloads',          'slug' => 'downloads',          'sort_order' => 4 ],
					[ 'name' => 'FAQs',               'slug' => 'faqs-label',         'sort_order' => 5 ],
				],
			],
			[
				'name'        => 'Location',
				'slug'        => 'location',
				'description' => 'Geographic area classifications for property and content.',
				'terms'       => [
					[ 'name' => 'Greater London',  'slug' => 'greater-london',  'sort_order' => 1 ],
					[ 'name' => 'Central London',  'slug' => 'central-london',  'sort_order' => 2 ],
					[ 'name' => 'North London',    'slug' => 'north-london',    'sort_order' => 3 ],
					[ 'name' => 'South London',    'slug' => 'south-london',    'sort_order' => 4 ],
					[ 'name' => 'East London',     'slug' => 'east-london',     'sort_order' => 5 ],
					[ 'name' => 'West London',     'slug' => 'west-london',     'sort_order' => 6 ],
					[ 'name' => 'Surrey',          'slug' => 'surrey',          'sort_order' => 7 ],
					[ 'name' => 'Kent',            'slug' => 'kent',            'sort_order' => 8 ],
					[ 'name' => 'Essex',           'slug' => 'essex',           'sort_order' => 9 ],
					[ 'name' => 'Hertfordshire',   'slug' => 'hertfordshire',   'sort_order' => 10 ],
					[ 'name' => 'Manchester',      'slug' => 'manchester',      'sort_order' => 11 ],
					[ 'name' => 'Birmingham',      'slug' => 'birmingham',      'sort_order' => 12 ],
					[ 'name' => 'Leeds',           'slug' => 'leeds',           'sort_order' => 13 ],
					[ 'name' => 'Bristol',         'slug' => 'bristol',         'sort_order' => 14 ],
					[ 'name' => 'Nationwide',      'slug' => 'nationwide',      'sort_order' => 15 ],
				],
			],
			[
				'name'        => 'Property Type',
				'slug'        => 'property-type',
				'description' => 'Classification of property by structural type.',
				'terms'       => [
					[ 'name' => 'Apartment / Flat',     'slug' => 'apartment-flat',      'sort_order' => 1 ],
					[ 'name' => 'Detached House',       'slug' => 'detached-house',      'sort_order' => 2 ],
					[ 'name' => 'Semi-Detached House',  'slug' => 'semi-detached-house', 'sort_order' => 3 ],
					[ 'name' => 'Terraced House',       'slug' => 'terraced-house',      'sort_order' => 4 ],
					[ 'name' => 'Townhouse',            'slug' => 'townhouse',           'sort_order' => 5 ],
					[ 'name' => 'Bungalow',             'slug' => 'bungalow',            'sort_order' => 6 ],
					[ 'name' => 'New Build',            'slug' => 'new-build-type',      'sort_order' => 7 ],
					[ 'name' => 'Period Property',      'slug' => 'period-property-type','sort_order' => 8 ],
					[ 'name' => 'Off-Plan',             'slug' => 'off-plan',            'sort_order' => 9 ],
					[ 'name' => 'Commercial',           'slug' => 'commercial',          'sort_order' => 10 ],
				],
			],
			[
				'name'        => 'Buyer Type',
				'slug'        => 'buyer-type',
				'description' => 'Target buyer profile for content and service tagging.',
				'terms'       => [
					[ 'name' => 'First-Time Buyer',   'slug' => 'first-time-buyer',   'sort_order' => 1 ],
					[ 'name' => 'Home Mover',          'slug' => 'home-mover',          'sort_order' => 2 ],
					[ 'name' => 'Property Investor',   'slug' => 'property-investor',   'sort_order' => 3 ],
					[ 'name' => 'Remortgager',         'slug' => 'remortgager',         'sort_order' => 4 ],
					[ 'name' => 'International Buyer', 'slug' => 'international-buyer', 'sort_order' => 5 ],
					[ 'name' => 'Cash Buyer',          'slug' => 'cash-buyer',          'sort_order' => 6 ],
					[ 'name' => 'BTL Investor',        'slug' => 'btl-investor',        'sort_order' => 7 ],
				],
			],
		];
	}

	private static function default_extra_pages(): array {
		return [
			'privacy-policy'   => [ 'title' => 'Privacy Policy',   'template' => 'page-policy.php',   'content' => '' ],
			'cookie-policy'    => [ 'title' => 'Cookie Policy',    'template' => 'page-policy.php',   'content' => '' ],
			'terms-of-service' => [ 'title' => 'Terms of Service', 'template' => 'page-policy.php',   'content' => '' ],
			'disclaimer'       => [ 'title' => 'Disclaimer',       'template' => 'page-policy.php',   'content' => '' ],
			'accessibility'    => [ 'title' => 'Accessibility',    'template' => 'page-policy.php',   'content' => '' ],
			'thank-you'        => [ 'title' => 'Thank You',        'template' => 'page-thank-you.php','content' => '' ],
			'area-guides'      => [ 'title' => 'Area Guides',      'template' => 'page-area-guides.php','content' => '' ],
			'sitemap'          => [ 'title' => 'Sitemap',          'template' => 'page-sitemap.php',  'content' => '' ],
		];
	}

	// ── CSV + default merger ──────────────────────────────────────────────────

	private static function merge_types_and_terms( array $type_rows, array $term_rows ): array {
		$result    = [];
		$order_map = [];

		foreach ( $type_rows as $row ) {
			$slug = $row['slug'] ?? '';
			if ( ! $slug ) continue;
			$result[ $slug ] = [
				'name'        => $row['name']        ?? $slug,
				'slug'        => $slug,
				'description' => $row['description'] ?? '',
				'terms'       => [],
			];
			$order_map[ $slug ] = 0;
		}

		foreach ( $term_rows as $row ) {
			$type_slug = $row['type_slug'] ?? '';
			$term_slug = $row['slug']      ?? '';
			if ( ! $type_slug || ! $term_slug || ! isset( $result[ $type_slug ] ) ) continue;
			$order_map[ $type_slug ]++;
			$result[ $type_slug ]['terms'][] = [
				'name'        => $row['name']        ?? $term_slug,
				'slug'        => $term_slug,
				'description' => $row['description'] ?? '',
				'sort_order'  => (int) ( $row['sort_order'] ?? $order_map[ $type_slug ] ),
			];
		}

		return array_values( $result );
	}
}
