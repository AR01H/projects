<?php
defined( 'ABSPATH' ) || exit;
class App_Db_Schema {
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		$p = $wpdb->prefix . 'tt_';
		$tables = array(
			// Core content
			"CREATE TABLE IF NOT EXISTS {$p}settings ( setting_key VARCHAR(191) NOT NULL, setting_value LONGTEXT NOT NULL, PRIMARY KEY (setting_key) ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}faqs ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, question TEXT NOT NULL, answer TEXT NOT NULL, page_type VARCHAR(50) DEFAULT 'global', sort_order INT DEFAULT 0, status ENUM('active','inactive') DEFAULT 'active' ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}reviews ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, reviewer_name VARCHAR(200) NOT NULL, review_text TEXT NOT NULL, source VARCHAR(100) DEFAULT 'Google', page_type VARCHAR(50) DEFAULT 'global', sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}locations ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, address TEXT NOT NULL, phone VARCHAR(50) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, map_url TEXT DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}history ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, year VARCHAR(10) NOT NULL, title VARCHAR(200) NOT NULL, description TEXT NOT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}team ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, role VARCHAR(200) NOT NULL, bio TEXT NOT NULL, image_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}nav ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, label VARCHAR(200) NOT NULL, url VARCHAR(500) NOT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}drinks ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, description TEXT NOT NULL, image_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}events_features ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, label VARCHAR(200) NOT NULL, icon_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			// New repeater tables
			"CREATE TABLE IF NOT EXISTS {$p}gallery ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) DEFAULT NULL, image_url VARCHAR(500) NOT NULL, alt VARCHAR(200) DEFAULT NULL, category VARCHAR(100) DEFAULT 'general', section VARCHAR(50) DEFAULT 'gallery', sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}hire_packages ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, description TEXT NOT NULL, price VARCHAR(100) DEFAULT NULL, features LONGTEXT DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}pricing_tiers ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, price VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, features LONGTEXT DEFAULT NULL, is_featured TINYINT(1) DEFAULT 0, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}opening_hours ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, day_label VARCHAR(100) NOT NULL, open_time VARCHAR(50) DEFAULT NULL, close_time VARCHAR(50) DEFAULT NULL, is_closed TINYINT(1) DEFAULT 0, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}ticker_items ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, content TEXT NOT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}service_tabs ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, label VARCHAR(200) NOT NULL, icon_url VARCHAR(500) DEFAULT NULL, content LONGTEXT DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}values ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, description TEXT NOT NULL, icon_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}info_cards ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, description TEXT NOT NULL, icon_url VARCHAR(500) DEFAULT NULL, link_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}flavours ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, category VARCHAR(100) DEFAULT 'flavour', description TEXT DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}delivery_products ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, description TEXT DEFAULT NULL, image_url VARCHAR(500) DEFAULT NULL, price VARCHAR(100) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}certifications ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, image_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}logo_strip ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) DEFAULT NULL, image_url VARCHAR(500) NOT NULL, link_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}process_steps ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, description TEXT NOT NULL, icon_url VARCHAR(500) DEFAULT NULL, step_number INT DEFAULT 0, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}quick_links ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, label VARCHAR(200) NOT NULL, url VARCHAR(500) NOT NULL, column_name VARCHAR(100) DEFAULT 'main', icon_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}downloads ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, file_url VARCHAR(500) NOT NULL, description TEXT DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}blocks ( id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, block_key VARCHAR(100) NOT NULL UNIQUE, title VARCHAR(200) NOT NULL, subtitle TEXT DEFAULT NULL, icon_url VARCHAR(500) DEFAULT NULL, link_url VARCHAR(500) DEFAULT NULL, sort_order INT DEFAULT 0 ) $charset",
			"CREATE TABLE IF NOT EXISTS {$p}stats ( stat_key VARCHAR(100) NOT NULL, stat_value VARCHAR(200) NOT NULL, label VARCHAR(200) DEFAULT NULL, PRIMARY KEY (stat_key) ) $charset",
		);
		foreach ( $tables as $sql ) { dbDelta( $sql . ';' ); }
	}
}
