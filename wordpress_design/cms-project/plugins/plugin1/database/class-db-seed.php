<?php
defined( 'ABSPATH' ) || exit;

/**
 * Default data seeded on fresh install.
 * All inserts are idempotent (skip if already exists).
 * Never put CREATE TABLE or ALTER here.
 */
class AH_DB_Seed {

	public static function run(): void {
		global $wpdb;
		$p = $wpdb->prefix;

		self::admin_roles( $p );
		self::taxonomy_types( $p );
		self::site_settings( $p );
		self::nav_menus( $p );
		self::pages( $p );
	}

	private static function admin_roles( string $p ): void {
		global $wpdb;
		if ( ! $wpdb->get_var( "SELECT id FROM {$p}ah_admin_roles WHERE name = 'super_admin'" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->insert( "{$p}ah_admin_roles", array(
				'name'        => 'super_admin',
				'permissions' => json_encode( array( '*' ) ),
			) );
		}
	}

	private static function taxonomy_types( string $p ): void {
		global $wpdb;
		foreach ( array(
			array( 'name' => 'Category', 'slug' => 'category', 'description' => 'Content categories' ),
			array( 'name' => 'Tag',      'slug' => 'tag',      'description' => 'Content tags'       ),
			array( 'name' => 'Subtag',   'slug' => 'subtag',   'description' => 'Content subtags'    ),
		) as $type ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$p}ah_taxonomy_types WHERE slug = %s", $type['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_taxonomy_types", $type );
			}
		}
	}

	private static function site_settings( string $p ): void {
		global $wpdb;
		foreach ( array(
			array( 'setting_key' => 'site_name',      'setting_val' => 'Advith Homes',              'field_type' => 'text',     'group_name' => 'general', 'label' => 'Site Name'        ),
			array( 'setting_key' => 'site_logo',       'setting_val' => '',                           'field_type' => 'image',    'group_name' => 'general', 'label' => 'Site Logo'        ),
			array( 'setting_key' => 'footer_tagline',  'setting_val' => 'Your trusted home partner.', 'field_type' => 'textarea', 'group_name' => 'general', 'label' => 'Footer Tagline'   ),
			array( 'setting_key' => 'whatsapp_number', 'setting_val' => '',                           'field_type' => 'phone',    'group_name' => 'contact', 'label' => 'WhatsApp Number'  ),
			array( 'setting_key' => 'contact_email',   'setting_val' => '',                           'field_type' => 'email',    'group_name' => 'contact', 'label' => 'Contact Email'    ),
			array( 'setting_key' => 'contact_phone',   'setting_val' => '',                           'field_type' => 'phone',    'group_name' => 'contact', 'label' => 'Contact Phone'    ),
			array( 'setting_key' => 'google_maps_url', 'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'contact', 'label' => 'Google Maps URL'  ),
			array( 'setting_key' => 'facebook_url',    'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'Facebook URL'     ),
			array( 'setting_key' => 'instagram_url',   'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'Instagram URL'    ),
			array( 'setting_key' => 'twitter_url',     'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'Twitter URL'      ),
			array( 'setting_key' => 'linkedin_url',    'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'LinkedIn URL'     ),
			array( 'setting_key' => 'youtube_url',     'setting_val' => '',                           'field_type' => 'url',      'group_name' => 'social',  'label' => 'YouTube URL'      ),
		) as $s ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$p}ah_site_settings WHERE setting_key = %s", $s['setting_key'] ) ) ) {
				$wpdb->insert( "{$p}ah_site_settings", $s );
			}
		}
	}

	private static function nav_menus( string $p ): void {
		global $wpdb;
		foreach ( array(
			array( 'name' => 'Primary Menu', 'slug' => 'primary' ),
			array( 'name' => 'Footer Menu',  'slug' => 'footer'  ),
		) as $menu ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$p}ah_nav_menus WHERE slug = %s", $menu['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_nav_menus", $menu );
			}
		}
	}

	private static function pages( string $p ): void {
		global $wpdb;
		foreach ( array(
			array( 'title' => 'Home',           'slug' => 'home',           'page_type' => 'home'           ),
			array( 'title' => 'About',          'slug' => 'about',          'page_type' => 'about'          ),
			array( 'title' => 'Services',       'slug' => 'services',       'page_type' => 'services'       ),
			array( 'title' => 'Contact',        'slug' => 'contact',        'page_type' => 'contact'        ),
			array( 'title' => 'Client Stories', 'slug' => 'client-stories', 'page_type' => 'client_stories' ),
			array( 'title' => 'Blog',           'slug' => 'blog',           'page_type' => 'blog_listing'   ),
			array( 'title' => 'News',           'slug' => 'news',           'page_type' => 'news_listing'   ),
		) as $pg ) {
			if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$p}ah_pages WHERE slug = %s", $pg['slug'] ) ) ) {
				$wpdb->insert( "{$p}ah_pages", $pg );
			}
		}
	}
}
