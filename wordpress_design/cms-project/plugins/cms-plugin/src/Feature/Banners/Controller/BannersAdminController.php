<?php
namespace Ah\Cms\Feature\Banners\Controller;

defined( 'ABSPATH' ) || exit;

class BannersAdminController {

	public static function handle_save(): void {
		check_admin_referer( 'ah_banners_save' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

		require_once AH_THEME_DIR . '/helper/BannersHelper.php';

		$rows     = isset( $_POST['banners'] ) && is_array( $_POST['banners'] ) ? wp_unslash( $_POST['banners'] ) : array();
		$banners  = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) continue;
			$banners[] = array(
				'image'        => $row['image']        ?? '',
				'image_mobile' => $row['image_mobile'] ?? '',
				'subtitle'    => $row['subtitle']    ?? '',
				'title'       => $row['title']       ?? '',
				'description' => $row['description']  ?? '',
				'btn_text'    => $row['btn_text']    ?? '',
				'btn_url'     => $row['btn_url']     ?? '',
				'btn_target'  => $row['btn_target']  ?? '_self',
				'text_align'  => $row['text_align']  ?? 'center',
				'text_pos'    => $row['text_pos']    ?? 'middle',
				'overlay'     => $row['overlay']     ?? '',
				'status'      => $row['status']      ?? 'active',
			);
		}

		\AH_Banners_Helper::save_all( $banners );
		\AH_Banners_Helper::save_autoplay( (int) ( $_POST['autoplay_ms'] ?? 5000 ) );

		if ( class_exists( 'AH_DB_Helper' ) ) {
			\AH_DB_Helper::log_action( 'update', 'home_banners', 0, array( 'count' => count( $banners ) ) );
		}

		wp_redirect( add_query_arg( array( 'page' => 'ah-banners', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function render(): void {
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( 'Access denied.' );
		}

		?>
		<div class="wrap">
			<h1>Home Banners</h1>
			<p>Manage your home page banners.</p>
		</div>
		<?php
	}
}
