<?php
defined( 'ABSPATH' ) || exit;

class AH_Uploader {

	private static array $allowed_mime = array(
		'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
		'application/pdf',
		'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime',
	);

	/**
	 * Handle a file upload and save it to ah_media table.
	 *
	 * @param string $field_name  $_FILES key
	 * @return int|WP_Error  Media record ID on success, WP_Error on failure.
	 */
	public static function upload( string $field_name ): int|WP_Error {
		if ( ! isset( $_FILES[ $field_name ] ) || $_FILES[ $field_name ]['error'] !== UPLOAD_ERR_OK ) {
			return new WP_Error( 'no_file', __( 'No file uploaded or upload error.', 'ah-theme' ) );
		}

		$file     = $_FILES[ $field_name ];
		$tmp_name = $file['tmp_name'];
		$name     = sanitize_file_name( $file['name'] );
		$mime     = mime_content_type( $tmp_name );

		if ( ! in_array( $mime, self::$allowed_mime, true ) ) {
			return new WP_Error( 'bad_mime', __( 'File type not allowed.', 'ah-theme' ) );
		}

		// Use WP upload dir
		$upload_dir = wp_upload_dir();
		$dest_dir   = $upload_dir['basedir'] . '/ah-media/' . date( 'Y/m' );
		if ( ! wp_mkdir_p( $dest_dir ) ) {
			return new WP_Error( 'mkdir', __( 'Could not create upload directory.', 'ah-theme' ) );
		}

		// Unique filename
		$ext      = pathinfo( $name, PATHINFO_EXTENSION );
		$base     = pathinfo( $name, PATHINFO_FILENAME );
		$filename = sanitize_title( $base ) . '-' . wp_generate_password( 6, false ) . '.' . $ext;
		$dest     = $dest_dir . '/' . $filename;

		if ( ! move_uploaded_file( $tmp_name, $dest ) ) {
			return new WP_Error( 'move', __( 'Could not save uploaded file.', 'ah-theme' ) );
		}

		$rel_path = str_replace( $upload_dir['basedir'], '', $dest );
		$url      = $upload_dir['baseurl'] . $rel_path;

		$image_size = array( 0, 0 );
		if ( str_starts_with( $mime, 'image/' ) ) {
			$image_size = getimagesize( $dest ) ?: array( 0, 0 );
		}

		$media_model = new AH_Media_Model();
		$id          = $media_model->create( array(
			'file_name'   => $filename,
			'file_path'   => $rel_path,
			'file_url'    => $url,
			'mime_type'   => $mime,
			'file_size'   => filesize( $dest ),
			'width'       => $image_size[0],
			'height'      => $image_size[1],
			'uploaded_by' => get_current_user_id() ?: null,
		) );

		return $id ?: new WP_Error( 'db', __( 'Could not save media record.', 'ah-theme' ) );
	}
}
