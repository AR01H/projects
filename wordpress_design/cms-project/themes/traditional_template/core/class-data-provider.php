<?php
/**
 * core/class-data-provider.php - Intermediate data fetching layer.
 *
 * This class acts as the single source of truth for all theme data.
 * It pulls from the custom tt_ database tables.
 */

defined( 'ABSPATH' ) || exit;

require_once dirname(__DIR__) . '/admin/Models/BaseModel.php';
require_once dirname(__DIR__) . '/admin/Models/FaqModel.php';
require_once dirname(__DIR__) . '/admin/Models/ReviewModel.php';
require_once dirname(__DIR__) . '/admin/Models/TeamModel.php';
require_once dirname(__DIR__) . '/admin/Models/LocationModel.php';
require_once dirname(__DIR__) . '/admin/Models/HistoryModel.php';

class App_Data_Provider {

	private static $cache = array();

	public static function get( $feature, $args = array() ) {
		$cache_key = $feature . '_' . md5( wp_json_encode( $args ) );

		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		$method_name = 'get_' . $feature;
		if ( method_exists( __CLASS__, $method_name ) ) {
			$data = self::$method_name( $args );
		} else if ( strpos($feature, 'form_') === 0 ) {
			$data = self::fetch_form( $feature );
		} else {
			$data = self::fetch_generic( $feature, $args );
		}

		self::$cache[ $cache_key ] = $data;
		return $data;
	}

	private static function fetch_form( $form_id ) {
		global $wpdb;
		$p = $wpdb->prefix . 'tt_';
		$form = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$p}forms WHERE id = %s", $form_id), ARRAY_A );
		if ( ! $form ) return array();

		$data = array(
			'id'         => $form['id'],
			'action'     => $form['action'],
			'form_label' => $form['form_label'],
			'submit'     => $form['submit_text'],
			'class'      => $form['css_class']
		);

		// Get steps
		$steps = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$p}form_steps WHERE form_id = %s ORDER BY sort_order ASC", $form_id), ARRAY_A );
		if ( ! empty( $steps ) ) {
			$data['steps'] = array();
			foreach ( $steps as $s ) {
				$step_data = array(
					'id'         => $s['id'],
					'title'      => $s['title'],
					'desc'       => $s['description'],
					'summary_id' => $s['summary_id'],
					'fields'     => array()
				);
				$fields = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$p}form_fields WHERE step_id = %d ORDER BY sort_order ASC", $s['id']), ARRAY_A );
				foreach ( $fields as $f ) {
					$field = array(
						'id'          => $f['field_id'],
						'name'        => $f['name'],
						'type'        => $f['type'],
						'label'       => $f['label'],
						'placeholder' => $f['placeholder'],
						'width'       => $f['width'],
						'required'    => (bool) $f['is_required'],
						'multi'       => (bool) $f['is_multi_select']
					);
					if ( ! empty( $f['options'] ) ) {
						$field['options'] = json_decode( $f['options'], true );
					}
					$step_data['fields'][] = $field;
				}
				$data['steps'][] = $step_data;
			}
		} else {
			// Flat form (no steps)
			$data['fields'] = array();
			$fields = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$p}form_fields WHERE form_id = %s AND step_id = 0 ORDER BY sort_order ASC", $form_id), ARRAY_A );
			foreach ( $fields as $f ) {
				$field = array(
					'id'          => $f['field_id'],
					'name'        => $f['name'],
					'type'        => $f['type'],
					'label'       => $f['label'],
					'placeholder' => $f['placeholder'],
					'width'       => $f['width'],
					'required'    => (bool) $f['is_required'],
					'multi'       => (bool) $f['is_multi_select']
				);
				if ( ! empty( $f['options'] ) ) {
					$field['options'] = json_decode( $f['options'], true );
				}
				$data['fields'][] = $field;
			}
		}

		return $data;
	}

	
	private static function fetch_generic( $feature, $args ) {
		global $wpdb;
		
		// Check for exact match or _items match first (from single json file migration)
		$val = $wpdb->get_var( $wpdb->prepare( "SELECT setting_value FROM {$wpdb->prefix}tt_settings WHERE setting_key = %s OR setting_key = %s LIMIT 1", $feature, $feature . '_items' ) );
		if ( $val ) {
			$decoded = json_decode( $val, true );
			if (json_last_error() === JSON_ERROR_NONE) {
				return $decoded;
			}
			return $val;
		}

		// Check if it's unpacked into multiple rows (e.g. feature_sub, feature_tag)
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT setting_key, setting_value FROM {$wpdb->prefix}tt_settings WHERE setting_key LIKE %s", $feature . '_%' ) );
		if ( !empty($results) ) {
			$data = array();
			foreach ($results as $row) {
				$key = str_replace($feature . '_', '', $row->setting_key);
				$val = $row->setting_value;
				// check if json
				$decoded = json_decode($val, true);
				$data[$key] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $val;
			}
			return $data;
		}
		

		
		// Fallback to empty array since JSON was removed
		return [];
	}

	private static function get_nav( $args ) {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}tt_nav ORDER BY sort_order ASC", ARRAY_A );
		if (!empty($results)) {
			// Format for frontend
			$nav = array();
			foreach ($results as $r) {
				$nav[] = array('label' => $r['label'], 'href' => $r['url']);
			}
			return $nav;
		}
		return self::fetch_generic('nav', $args);
	}
	
	private static function get_footer( $args ) {
		global $wpdb;
		// Combine footer settings and links if they were in a table
		return self::fetch_generic('footer', $args);
	}

	
	private static function get_drinks( $args ) {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}tt_drinks ORDER BY sort_order ASC", ARRAY_A );
		if (!empty($results)) {
			$data = self::fetch_generic('site_drinks', $args);
			$items = array();
			foreach ($results as $r) {
				$items[] = array('name' => $r['name'], 'desc' => $r['description'], 'image' => $r['image_url']);
			}
			$data['items'] = $items;
			return $data;
		}
		return self::fetch_generic('site_drinks', $args);
	}
	
	private static function get_events( $args ) {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}tt_events_features ORDER BY sort_order ASC", ARRAY_A );
		$data = self::fetch_generic('site_events', $args);
		if (!empty($results)) {
			$items = array();
			foreach ($results as $r) {
				$items[] = array('label' => $r['label'], 'icon' => $r['icon_url']);
			}
			$data['features'] = $items;
		}
		return $data;
	}

	private static function get_faqs( $args ) {
		if ( class_exists( 'TT_Faq_Model' ) ) {
			$faqs = TT_Faq_Model::get_all();
			if ( ! empty( $faqs ) ) {
				return array( 'faqs' => $faqs );
			}
		}
		return self::fetch_generic( 'faqs', $args );
	}

	private static function get_reviews( $args ) {
		if ( class_exists( 'TT_Review_Model' ) ) {
			$reviews = TT_Review_Model::get_all();
			if ( ! empty( $reviews ) ) {
				return $reviews;
			}
		}
		return self::fetch_generic( 'reviews', $args );
	}

	private static function get_team( $args ) {
		if ( class_exists( 'TT_Team_Model' ) ) {
			$team = TT_Team_Model::get_all();
			if ( ! empty( $team ) ) {
				// Output format matching original json
				return array(
					'header' => array('title'=>'Our Team','subtitle'=>'Meet the people behind it'),
					'team' => $team
				);
			}
		}
		return self::fetch_generic( 'team', $args );
	}

	private static function get_locations( $args ) {
		if ( class_exists( 'TT_Location_Model' ) ) {
			$locs = TT_Location_Model::get_all();
			if ( ! empty( $locs ) ) {
				// Convert index-based array to string keys like json if needed, or just return array.
				// The original json is an array of objects.
				return $locs;
			}
		}
		return self::fetch_generic( 'locations', $args );
	}

	private static function get_history( $args ) {
		if ( class_exists( 'TT_History_Model' ) ) {
			$hist = TT_History_Model::get_all();
			if ( ! empty( $hist ) ) {
				return array(
					'header' => array('title'=>'Our History'),
					'milestones' => $hist
				);
			}
		}
		return self::fetch_generic( 'history', $args );
	}
}
