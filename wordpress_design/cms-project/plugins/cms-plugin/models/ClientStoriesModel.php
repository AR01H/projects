<?php
defined( 'ABSPATH' ) || exit;

/**
 * Client Stories / "Showcase Gallery" read layer - a header + photo gallery
 * feature spanning wp_ah_client_stories_header and wp_ah_client_gallery (see
 * admin/ClientStories.php, page=ah-client-stories). No single owning table,
 * so this doesn't extend AH_Model_Base like most models - it exists so

 * directly, matching how every other page gets its data through a model
 * (AH_Reviews_Model, etc.) instead of raw SQL in the template.
 */
class AH_Client_Stories_Model {

	/** The wp_ah_pages row id for the Showcase Gallery page, or 0 if not created yet. */
	public function get_page_id(): int {
		$page = ( new AH_Pages_Model() )->get_by_type( 'client_stories' );
		return $page ? (int) $page->id : 0;
	}

	public function get_header( int $page_id ): ?object {
		if ( ! $page_id ) return null;
		return AH_DB_Helper::get_by( AH_DB_Helper::table( 'client_stories_header' ), 'page_id', $page_id );
	}

	/** Active gallery images for a page, in display order. */
	public function get_active_gallery( int $page_id ): array {
		if ( ! $page_id ) return array();
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'client_gallery' ), array(
			'where'    => 'page_id = %d AND status = %s',
			'where_in' => array( $page_id, 'active' ),
			'order_by' => 'sort_order',
			'order'    => 'ASC',
			'limit'    => 200,
		) );
	}

	/** Active video links for a page, in display order. */
	public function get_active_videos( int $page_id ): array {
		if ( ! $page_id ) return array();
		return AH_DB_Helper::get_list( AH_DB_Helper::table( 'client_video_links' ), array(
			'where'    => 'page_id = %d AND status = %s',
			'where_in' => array( $page_id, 'active' ),
			'order_by' => 'sort_order',
			'order'    => 'ASC',
			'limit'    => 200,
		) );
	}

	/**
	 * Everything the Reviews page's Showcase Gallery section needs in one
	 * call: heading + description + images + videos, empty when the
	 * header's "Visible" toggle (Page Header tab) is off or the page/header
	 * doesn't exist yet.
	 *
	 * @return array{heading: string, description: string, images: array, videos: array}
	 */
	public function get_reviews_page_gallery(): array {
		$page_id = $this->get_page_id();
		$header  = $page_id ? $this->get_header( $page_id ) : null;
		if ( ! $header || empty( $header->is_visible ) ) {
			return array( 'heading' => '', 'description' => '', 'images' => array(), 'videos' => array() );
		}
		return array(
			'heading'     => (string) $header->heading,
			'description' => (string) $header->information,
			'images'      => $this->get_active_gallery( $page_id ),
			'videos'      => $this->get_active_videos( $page_id ),
		);
	}
}
