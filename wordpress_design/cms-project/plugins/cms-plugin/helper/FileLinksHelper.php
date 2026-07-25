<?php
defined( 'ABSPATH' ) || exit;

/**
 * Helper functions for File Links admin page.
 */
class AH_File_Links_Helper {

	/**
	 * Format bytes to human-readable size.
	 */
	public static function human_size( int $bytes ): string {
		if ( $bytes >= 1073741824 ) return round( $bytes / 1073741824, 1 ) . ' GB';
		if ( $bytes >= 1048576 )    return round( $bytes / 1048576,    1 ) . ' MB';
		if ( $bytes >= 1024 )       return round( $bytes / 1024,       1 ) . ' KB';
		return $bytes . ' B';
	}

	/**
	 * Get label, icon, and color for a MIME type.
	 */
	public static function type_meta( string $mime ): array {
		if ( str_starts_with( $mime, 'image/' ) )        return [ 'label' => 'Image',    'icon' => 'format-image',      'color' => '#7c3aed' ];
		if ( $mime === 'application/pdf' )               return [ 'label' => 'PDF',      'icon' => 'media-document',    'color' => '#dc2626' ];
		if ( str_starts_with( $mime, 'video/' ) )        return [ 'label' => 'Video',    'icon' => 'video-alt3',        'color' => '#2563eb' ];
		if ( str_starts_with( $mime, 'audio/' ) )        return [ 'label' => 'Audio',    'icon' => 'format-audio',      'color' => '#d97706' ];
		if ( str_starts_with( $mime, 'text/' ) )         return [ 'label' => 'Text',     'icon' => 'text',              'color' => '#16a34a' ];
		if ( in_array( $mime, [ 'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed' ], true ) )
		                                                 return [ 'label' => 'Archive',  'icon' => 'media-archive',     'color' => '#64748b' ];
		if ( str_contains( $mime, 'spreadsheet' ) || str_contains( $mime, 'excel' ) || str_contains( $mime, 'csv' ) )
		                                                 return [ 'label' => 'Sheet',    'icon' => 'media-spreadsheet', 'color' => '#16a34a' ];
		if ( str_contains( $mime, 'word' ) || str_contains( $mime, 'document' ) )
		                                                 return [ 'label' => 'Doc',      'icon' => 'media-text',        'color' => '#1d4ed8' ];
		if ( str_contains( $mime, 'presentation' ) || str_contains( $mime, 'powerpoint' ) )
		                                                 return [ 'label' => 'Slides',   'icon' => 'slides',            'color' => '#ea580c' ];
		                                                 return [ 'label' => 'File',     'icon' => 'media-default',     'color' => '#64748b' ];
	}

	/**
	 * Get the public URL for a file path.
	 */
	public static function get_url( string $file_path ): string {
		$upload = wp_upload_dir();
		return trailingslashit( $upload['baseurl'] ) . 'ah-files/' . ltrim( $file_path, '/' );
	}

	/**
	 * Get the disk path for a file path.
	 */
	public static function get_disk_path( string $file_path ): string {
		$upload = wp_upload_dir();
		return trailingslashit( $upload['basedir'] ) . 'ah-files/' . ltrim( $file_path, '/' );
	}

	/**
	 * Get the base upload directory for file links.
	 */
	public static function get_base_dir(): string {
		$upload = wp_upload_dir();
		return trailingslashit( $upload['basedir'] ) . 'ah-files';
	}
}
