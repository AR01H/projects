<?php
namespace Ah\Cms\Feature\Spotlights\Controller;

defined( 'ABSPATH' ) || exit;

class SpotlightsAdminController {

	public static function handle_delete_item(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorised' );
		}
		check_admin_referer( 'ah_del_sp_item' );

		$del_id  = (int) ( $_POST['delete_item'] ?? 0 );
		$term_id = (int) ( $_POST['term_id'] ?? 0 );

		$items_model = new \AH_Spotlights_Model();
		if ( $del_id ) {
			$items_model->delete( $del_id );
		}

		$redirect = add_query_arg(
			array( 'page' => 'ah-spotlights', 'tab' => 'items', 'term_id' => $term_id, 'deleted' => 1, 'deleted_id' => $del_id ),
			admin_url( 'admin.php' )
		);
		if ( ! headers_sent() ) {
			wp_safe_redirect( $redirect );
			exit;
		}
		echo '<script>window.location.href = ' . wp_json_encode( $redirect ) . ';</script>';
		exit;
	}

	public static function handle_delete_term(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorised' );
		}
		check_admin_referer( 'ah_del_sp_term' );

		$del_id = (int) ( $_POST['delete_term'] ?? 0 );

		$terms_model = new \AH_Spotlight_Terms_Model();
		if ( $del_id ) {
			$terms_model->delete_with_items( $del_id );
		}

		$redirect = add_query_arg(
			array( 'page' => 'ah-spotlights', 'tab' => 'terms', 'deleted' => 1, 'deleted_id' => $del_id ),
			admin_url( 'admin.php' )
		);
		if ( ! headers_sent() ) {
			wp_safe_redirect( $redirect );
			exit;
		}
		echo '<script>window.location.href = ' . wp_json_encode( $redirect ) . ';</script>';
		exit;
	}
}
