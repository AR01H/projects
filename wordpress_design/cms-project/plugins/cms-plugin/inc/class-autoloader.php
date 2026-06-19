<?php
defined( 'ABSPATH' ) || exit;

class AH_Autoloader {

	private static array $map = array(
		// Core
		'AH_Theme_Setup'      => 'inc/class-theme-setup.php',
		'AH_Asset_Loader'     => 'inc/class-asset-loader.php',

		// Database
		'AH_DB_Installer'     => 'database/class-db-installer.php',
		'AH_DB_Schema'        => 'database/class-db-schema.php',
		'AH_DB_Foreign_Keys'  => 'database/class-db-foreign-keys.php',
		'AH_DB_Seed'          => 'database/class-db-seed.php',
		'AH_DB_Migrations'    => 'database/class-db-migrations.php',
		'AH_DB_Helper'        => 'database/class-db-helper.php',

		// Models
		'AH_Model_Base'       => 'models/class-model-base.php',
		'AH_Settings_Model'   => 'models/class-settings-model.php',
		'AH_Media_Model'      => 'models/class-media-model.php',
		'AH_Pages_Model'      => 'models/class-pages-model.php',
		'AH_Nav_Model'        => 'models/class-nav-model.php',
		'AH_Reviews_Model'    => 'models/class-reviews-model.php',
		'AH_Faqs_Model'       => 'models/class-faqs-model.php',
		'AH_Posts_Model'      => 'models/class-posts-model.php',
		'AH_Taxonomy_Model'        => 'models/class-taxonomy-model.php',
		'AH_Taxonomy_Parent_Model' => 'models/class-taxonomy-parent-model.php',
		'AH_Content_Taxonomy_Model' => 'models/class-content-taxonomy-model.php',
		'AH_Related_Links_Model'    => 'models/class-related-links-model.php',
		'AH_Static_Pages_Model'     => 'models/class-static-pages-model.php',
		'AH_Newsbar_Model'         => 'models/class-newsbar-model.php',
		'AH_Spotlight_Terms_Model' => 'models/class-spotlights-model.php',
		'AH_Spotlights_Model'      => 'models/class-spotlights-model.php',
		'AH_Footer_Model'     => 'models/class-footer-model.php',
		'AH_Audit_Model'      => 'models/class-audit-model.php',

		// Helpers
		'AH_Slug_Helper'      => 'helper/class-slug-helper.php',
		'AH_Pagination'       => 'helper/class-pagination-helper.php',
		'AH_Validator'        => 'helper/class-validator.php',
		'AH_Uploader'         => 'helper/class-uploader.php',
		'AH_Notice_Helper'    => 'helper/class-notice-helper.php',
		'AH_Banners_Helper'   => 'helper/class-banners-helper.php',

		// Admin
		'AH_Analytics_Report_Model' => 'models/class-analytics-model.php',
		'AH_Analytics_Result_Model' => 'models/class-analytics-model.php',

		'AH_Admin_Bootstrap'  => 'admin/class-admin-bootstrap.php',
		'AH_Admin_Menus'      => 'admin/menus/class-admin-menus.php',
		'AH_Ajax_Handlers'    => 'admin/ajax/class-ajax-handlers.php',
		'AH_Analytics_Ajax'   => 'admin/ajax/class-analytics-ajax.php',
		'AH_CSV_Importer'     => 'admin/import/class-csv-importer.php',
		'AH_Form_Builder'     => 'inc/class-form-builder.php',
		'AH_Newsletter'       => 'inc/class-newsletter.php',
		'AH_Rules_Engine'     => 'inc/class-rules-engine.php',
	);

	public static function register(): void {
		spl_autoload_register( array( self::class, 'load' ) );
	}

	public static function load( string $class ): void {
		if ( isset( self::$map[ $class ] ) ) {
			$file = AH_THEME_DIR . '/' . self::$map[ $class ];
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}
}
