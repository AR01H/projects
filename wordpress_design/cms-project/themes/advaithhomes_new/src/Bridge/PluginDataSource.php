<?php
namespace Adn\Theme\Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin Data Source — Reads data from the CMS plugin via service interfaces.
 */
class PluginDataSource {

	public function getNavigation(): array {
		return \get_option( 'adn_navigation', [] );
	}

	public function getSettings( string $group = '' ): array {
		$all = \get_option( 'adn_site_settings', [] );
		if ( '' !== $group && isset( $all[ $group ] ) ) {
			return $all[ $group ];
		}
		return $all;
	}

	public function getSiteNotices(): array {
		if ( ! \class_exists( 'AH_Site_Notices_Model' ) ) {
			return [];
		}
		$model = new \AH_Site_Notices_Model();
		return $model->get_active();
	}

	public function getBanners(): array {
		if ( ! \class_exists( 'AH_Home_Banners_Model' ) ) {
			return [];
		}
		$model = new \AH_Home_Banners_Model();
		return $model->get_active();
	}

	public function getSpotlights(): array {
		if ( ! \class_exists( 'AH_Spotlights_Model' ) ) {
			return [];
		}
		$model = new \AH_Spotlights_Model();
		return $model->get_active();
	}

	public function getNewsBar(): array {
		if ( ! \class_exists( 'AH_Newsbar_Model' ) ) {
			return [];
		}
		$model = new \AH_Newsbar_Model();
		return $model->get_active();
	}

	public function getFeaturedIn(): array {
		if ( ! \class_exists( 'AH_Features_In_Model' ) ) {
			return [];
		}
		$model = new \AH_Features_In_Model();
		return $model->get_all();
	}

	public function getReviews( array $args = [] ): array {
		if ( ! \class_exists( 'AH_Reviews_Model' ) ) {
			return [];
		}
		$model = new \AH_Reviews_Model();
		return $model->get_all( $args );
	}

	public function getFaqs( string $slug = '' ): array {
		if ( ! \class_exists( 'AH_Faqs_Model' ) ) {
			return [];
		}
		$model = new \AH_Faqs_Model();
		if ( '' !== $slug ) {
			return $model->get_by_slug( $slug );
		}
		return $model->get_all();
	}

	public function getResources( array $args = [] ): array {
		if ( ! \class_exists( 'AH_Resources_Model' ) ) {
			return [];
		}
		$model = new \AH_Resources_Model();
		return $model->get_all( $args );
	}

	public function getPages( string $type = '' ): array {
		if ( ! \class_exists( 'AH_Pages_Model' ) ) {
			return [];
		}
		$model = new \AH_Pages_Model();
		if ( '' !== $type ) {
			return $model->get_by_type( $type );
		}
		return $model->get_all();
	}

	public function getTaxonomy( string $type = '' ): array {
		if ( ! \class_exists( 'AH_Taxonomy_Model' ) ) {
			return [];
		}
		$model = new \AH_Taxonomy_Model();
		if ( '' !== $type ) {
			return $model->get_by_type( $type );
		}
		return $model->get_all();
	}
}
