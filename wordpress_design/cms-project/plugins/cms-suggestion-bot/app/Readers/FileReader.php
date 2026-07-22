<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Readers;

use CmsSuggestionBot\Contracts\ReaderInterface;
use CmsSuggestionBot\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Reads plain-text/Markdown files dropped into the plugin's /resources
 * folder (recursively) and normalizes them the same way PageReader/PostReader
 * normalize WordPress content, so external documentation can be indexed
 * alongside the site's own pages and posts.
 */
final class FileReader implements ReaderInterface {

	private const EXTENSIONS = array( 'txt', 'md' );

	public function __construct( private readonly string $resourcesDir ) {}

	public function type(): string {
		return 'file';
	}

	public function label(): string {
		return __( 'External Files (.txt, .md)', 'cms-suggestion-bot' );
	}

	public function isAvailable(): bool {
		return is_dir( $this->resourcesDir ) && is_readable( $this->resourcesDir );
	}

	public function count(): int {
		return count( $this->files() );
	}

	public function read( int $offset, int $limit ): array {
		$files = array_slice( $this->files(), $offset, $limit > 0 ? $limit : null );
		$rows  = array();

		foreach ( $files as $path ) {
			$rows[] = $this->normalize( $path );
		}

		return $rows;
	}

	/**
	 * @return array<int, string> Absolute paths, sorted for stable offsets across calls.
	 */
	private function files(): array {
		if ( ! $this->isAvailable() ) {
			return array();
		}

		$found = array();
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $this->resourcesDir, \FilesystemIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() ) {
				continue;
			}
			$ext = strtolower( $file->getExtension() );
			if ( in_array( $ext, self::EXTENSIONS, true ) ) {
				$found[] = $file->getPathname();
			}
		}

		sort( $found );

		return $found;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function normalize( string $path ): array {
		$relative = ltrim( str_replace( $this->resourcesDir, '', $path ), '/\\' );
		$content  = (string) file_get_contents( $path );
		$ext      = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		$title = $this->extractTitle( $content, $path, $ext );

		return array(
			'source_type'  => $this->type(),
			// Deterministic pseudo-ID from the file's path relative to /resources,
			// stable across reads as long as the file isn't renamed/moved.
			'source_id'    => crc32( $relative ),
			'title'        => $title,
			'slug'         => sanitize_title( pathinfo( $path, PATHINFO_FILENAME ) ),
			'url'          => '',
			'excerpt'      => Str::excerpt( $content, 40 ),
			'content'      => $content,
			'content_hash' => Str::hash( $content ),
			'word_count'   => Str::wordCount( $content ),
			'meta'         => array(
				'relative_path' => $relative,
				'extension'     => $ext,
				'modified_at'   => gmdate( 'Y-m-d H:i:s', (int) filemtime( $path ) ),
				'size_bytes'    => (int) filesize( $path ),
			),
			'status'       => 'active',
		);
	}

	private function extractTitle( string $content, string $path, string $ext ): string {
		if ( 'md' === $ext ) {
			$first_line = strtok( trim( $content ), "\n" );
			if ( is_string( $first_line ) && str_starts_with( trim( $first_line ), '#' ) ) {
				return trim( ltrim( trim( $first_line ), '#' ) );
			}
		}

		return pathinfo( $path, PATHINFO_FILENAME );
	}
}
