<?php
namespace Ah\Cms\Feature\SiteNotices\Controller;

defined( 'ABSPATH' ) || exit;

class SiteNoticesAdminController {

	public static function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		check_admin_referer( 'ah_save_site_notice', 'ah_sn_nonce' );

		$edit_id = (int) ( $_POST['edit_id'] ?? 0 );
		$errors  = array();

		$v_title = trim( sanitize_text_field( $_POST['title'] ?? '' ) );
		if ( $v_title === '' ) $errors[] = 'Title is required.';
		if ( ( $_POST['trigger_type'] ?? '' ) === '' ) $errors[] = 'Display Trigger must be selected.';
		if ( ( $_POST['scope'] ?? '' ) === 'slugs' && trim( $_POST['slugs'] ?? '' ) === '' ) $errors[] = 'Enter at least one page slug.';
		if ( ( $_POST['trigger_type'] ?? '' ) === 'delay' && (int) ( $_POST['trigger_delay'] ?? 0 ) < 1 ) $errors[] = 'Delay must be at least 1 second.';

		if ( ! empty( $errors ) ) {
			$back = add_query_arg( array(
				'page'   => 'ah-notices',
				'action' => $edit_id > 0 ? 'edit' : 'add',
				'id'     => $edit_id ?: null,
				'err'    => implode( '|', array_map( 'urlencode', $errors ) ),
			), admin_url( 'admin.php' ) );
			wp_safe_redirect( $back );
			exit;
		}

		global $wpdb;
		$model = new \AH_Site_Notices_Model();
		$model->save_notice( $edit_id, wp_unslash( $_POST ) );

		if ( $wpdb->last_error ) {
			wp_safe_redirect( add_query_arg( array(
				'page'   => 'ah-notices',
				'action' => $edit_id > 0 ? 'edit' : 'add',
				'id'     => $edit_id ?: null,
				'dberr'  => rawurlencode( $wpdb->last_error ),
			), admin_url( 'admin.php' ) ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( array( 'page' => 'ah-notices', 'flash' => 'saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_delete(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		check_admin_referer( 'ah_del_sn' );
		$model = new \AH_Site_Notices_Model();
		$model->delete( (int) ( $_GET['delete_id'] ?? 0 ) );
		wp_safe_redirect( add_query_arg( array( 'page' => 'ah-notices', 'flash' => 'deleted' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_toggle(): void {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
		check_admin_referer( 'ah_toggle_sn' );
		$model = new \AH_Site_Notices_Model();
		$row   = $model->find( (int) ( $_GET['toggle_id'] ?? 0 ) );
		if ( $row ) {
			$model->set_status( (int) $row->id, $row->status === 'active' ? 'inactive' : 'active' );
		}
		wp_safe_redirect( add_query_arg( array( 'page' => 'ah-notices', 'flash' => 'updated' ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
