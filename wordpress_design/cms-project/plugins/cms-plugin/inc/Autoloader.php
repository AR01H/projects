<?php
defined( 'ABSPATH' ) || exit;

class AH_Autoloader {

	private static array $map = array(
		// Core
		'AH_Theme_Setup'      => 'inc/ThemeSetup.php',
		'AH_Asset_Loader'     => 'inc/AssetLoader.php',

		// Database
		'AH_DB_Installer'     => 'database/DbInstaller.php',
		'AH_DB_Schema'        => 'database/DbSchema.php',
		'AH_DB_Foreign_Keys'  => 'database/DbForeignKeys.php',
		'AH_DB_Seed'          => 'database/DbSeed.php',
		'AH_DB_Migrations'    => 'database/DbMigrations.php',
		'AH_DB_Helper'        => 'database/DbHelper.php',

		// Models
		'AH_Model_Base'       => 'models/ModelBase.php',
		'AH_Settings_Model'   => 'models/SettingsModel.php',
		'AH_Media_Model'      => 'models/MediaModel.php',
		'AH_Pages_Model'      => 'models/PagesModel.php',
		'AH_Nav_Model'        => 'models/NavModel.php',
		'AH_Reviews_Model'    => 'models/ReviewsModel.php',
		'AH_Faqs_Model'       => 'models/FaqsModel.php',
		'AH_Posts_Model'      => 'models/PostsModel.php',
		'AH_Taxonomy_Model'        => 'models/TaxonomyModel.php',
		'AH_Taxonomy_Parent_Model' => 'models/TaxonomyParentModel.php',
		'AH_Content_Taxonomy_Model' => 'models/ContentTaxonomyModel.php',
		'AH_Related_Links_Model'    => 'models/RelatedLinksModel.php',
		'AH_Resources_Model'        => 'models/ResourcesModel.php',
		'AH_Static_Pages_Model'     => 'models/StaticPagesModel.php',
		'AH_Site_Notices_Model'    => 'models/SiteNoticesModel.php',
		'AH_Newsbar_Model'         => 'models/NewsbarModel.php',
		'AH_Spotlight_Terms_Model' => 'models/SpotlightTermsModel.php',
		'AH_Spotlights_Model'      => 'models/SpotlightsModel.php',
		'AH_Footer_Model'     => 'models/FooterModel.php',
		'AH_Audit_Model'      => 'models/AuditModel.php',
		'AH_Visitor_Model'    => 'models/VisitorModel.php',
		'AH_Rest_Routes'      => 'api/RestRoutes.php',

		// Helpers
		'AH_Slug_Helper'      => 'helper/SlugHelper.php',
		'AH_Pagination'       => 'helper/PaginationHelper.php',
		'AH_Validator'        => 'helper/Validator.php',
		'AH_Uploader'         => 'helper/Uploader.php',
		'AH_Notice_Helper'    => 'helper/NoticeHelper.php',
		'AH_Banners_Helper'   => 'helper/BannersHelper.php',

		// Admin
		'AH_Analytics_Model'           => 'models/AnalyticsModel.php',
		'AH_Analytics_Report_Model' => 'models/AnalyticsReportModel.php',
		'AH_Analytics_Result_Model' => 'models/AnalyticsResultModel.php',
		'AH_Events_Model'           => 'models/EventsModel.php',
		'AH_Home_Banners_Model'     => 'models/HomeBannerModel.php',
		'AH_Features_In_Model'      => 'models/FeaturesInModel.php',
		'AH_Newsletters_Model'      => 'models/NewsletterModel.php',
		'AH_Custom_Code_Model'      => 'models/CustomCodeModel.php',
		'AH_File_Links_Model'       => 'models/FileLinksModel.php',

		// Term Manager
		'AH_Term_Manager'           => 'inc/TermManager.php',

		'AH_Admin_Bootstrap'  => 'admin/AdminBootstrap.php',
		'AH_Admin_Menus'      => 'admin/menus/AdminMenus.php',
		'AH_Ajax_Handlers'    => 'admin/AjaxHandlers.php',
		'AH_Analytics_Ajax'   => 'admin/AnalyticsAjax.php',
		'AH_CSV_Importer'     => 'admin/CsvImporter.php',
		'Ah\\Cms\\Admin\\Components\\AdminComponents' => 'src/Admin/Components/AdminComponents.php',
		'AH_Form_Builder'     => 'inc/FormBuilder.php',
		'AH_Newsletter'       => 'inc/Newsletter.php',
		'AH_Workflow_Manager' => 'inc/WorkflowManager.php',
		'AH_Cache'            => 'inc/AhCache.php',

		// Services (migrated from ah-cms.php)
		'AH_Redirect_Service'        => 'src/Feature/Redirect/Service/AH_Redirect_Service.php',
		'AH_Custom_Code_Service'     => 'src/Feature/CustomCode/Service/AH_Custom_Code_Service.php',
		'AH_Builder_Page_Service'    => 'src/Feature/Pages/Service/AH_Builder_Page_Service.php',

		// Shortcodes (migrated from ah-cms.php)
		'AH_RelatedLinks_Shortcode'  => 'src/Feature/Pages/Shortcode/AH_RelatedLinks_Shortcode.php',
		'AH_StaticPage_Shortcode'    => 'src/Feature/Pages/Shortcode/AH_StaticPage_Shortcode.php',
		'AH_Resource_Shortcode'      => 'src/Feature/Resources/Shortcode/AH_Resource_Shortcode.php',
		'AH_Resources_Shortcode'     => 'src/Feature/Resources/Shortcode/AH_Resources_Shortcode.php',

		// REST Controllers (migrated from ah-cms.php)
		'AH_Analytics_Rest_Controller' => 'src/Feature/Analytics/Rest/AH_Analytics_Rest_Controller.php',
	);

	public static function register(): void {
		spl_autoload_register( array( self::class, 'load' ) );
	}

	public static function load( string $class ): void {
		// Check classmap first
		if ( isset( self::$map[ $class ] ) ) {
			$file = AH_THEME_DIR . '/' . self::$map[ $class ];
			if ( file_exists( $file ) ) {
				require_once $file;
				return;
			}
		}

		// PSR-4 autoloading for Ah\Cms\ namespace → src/
		if ( str_starts_with( $class, 'Ah\\Cms\\' ) ) {
			$relative = str_replace( '\\', '/', substr( $class, 7 ) ); // Strip "Ah\Cms\" prefix
			$file = AH_THEME_DIR . '/src/' . $relative . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}
}
