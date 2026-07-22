<?php
/**
 * core/class-data-provider.php - Intermediate data fetching layer.
 *
 * This class acts as the single source of truth for all theme data.
 * It caches requests in-memory per page load. If the CMS plugin is active,
 * it pulls from the database. If not, it falls back to local JSON files.
 * You can customize the queries directly in these methods.
 */

defined( 'ABSPATH' ) || exit;

class NT_Data_Provider {

	/**
	 * In-memory cache for the current request.
	 * @var array
	 */
	private static $cache = array();

	/**
	 * Main entry point for fetching a feature's data.
	 *
	 * @param string $feature The feature key (e.g., 'faqs', 'home_banner').
	 * @param array  $args    Optional args like limit or specific IDs.
	 * @return array
	 */
	public static function get( $feature, $args = array() ) {
		$cache_key = $feature . '_' . md5( wp_json_encode( $args ) );

		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$data = array();

		// Route to specific custom query methods if they exist.
		// This is where you can write raw SQL or complex logic later.
		$method_name = 'get_' . $feature;
		if ( method_exists( __CLASS__, $method_name ) ) {
			$data = self::$method_name( $args );
		} else {
			// Generic fallback logic
			$data = self::fetch_generic( $feature, $args );
		}

		self::$cache[ $cache_key ] = $data;
		return $data;
	}

	/**
	 * Generic fetching logic: try Plugin Model -> JSON fallback.
	 */
	private static function fetch_generic( $feature, $args ) {
		$model_map = array(
			'spotlights'   => 'AH_Spotlights_Model',
			'newsbar'      => 'AH_Newsbar_Model',
			'faqs'         => 'AH_FAQs_Model',
			'resources'    => 'AH_Resources_Model',
			'features_in'  => 'AH_Features_In_Model',
			'home_banner'  => 'AH_Home_Banners_Model',
			'site_notices' => 'AH_Site_Notices_Model',
			'navigation'   => 'AH_Nav_Model',
			'posts'        => 'AH_Posts_Model',
		);

		if ( false ) {
			$model_class = $model_map[ $feature ];
			$model       = new $model_class();

			if ( method_exists( $model, 'get_active' ) ) {
				$data = $model->get_active( $args['limit'] ?? 0 );
				if ( ! empty( $data ) ) {
					return $data;
				}
			} elseif ( method_exists( $model, 'get_all' ) ) {
				$data = $model->get_all();
				if ( ! empty( $data ) ) {
					return $data;
				}
			}
		}

		return self::fetch_json( $feature );
	}

	/**
	 * Read JSON data from the admin/data/ directory.
	 */
	private static function fetch_json( $feature ) {
		return nt_data( $feature );
	}

	// ----------------------------------------------------------------------
	// Custom Query Methods (Add your custom SQL or complex logic here)
	// ----------------------------------------------------------------------

	/**
	 * FAQs come from the CMS plugin's ah_faqs table (managed at
	 * wp-admin -> FAQs). Page-specific FAQs (attached to the seeded "Home"
	 * page) win; if none are set, fall back to Global FAQs; if the plugin
	 * isn't active, fall back to the JSON file.
	 */
	private static function get_faqs( $args ) {
		if ( class_exists( 'AH_Faqs_Model' ) ) {
			try {
				$model   = new AH_Faqs_Model();
				$page_id = 0;
				if ( class_exists( 'AH_Pages_Model' ) ) {
					$home    = ( new AH_Pages_Model() )->get_by_type( 'home' );
					$page_id = $home->id ?? 0;
				}
				$faqs = $page_id ? $model->get_for_page( $page_id ) : array();
				if ( empty( $faqs ) ) {
					$faqs = $model->get_global();
				}
				if ( ! empty( $faqs ) ) {
					return $faqs;
				}
			} catch ( Throwable $e ) {
				// Fall through to JSON.
			}
		}
		return self::fetch_json( 'faqs' );
	}

	/**
	 * Example: Custom logic for fetching Spotlights.
	 */
	private static function get_spotlights( $args ) {
		return self::fetch_generic( 'spotlights', $args );
	}
}
