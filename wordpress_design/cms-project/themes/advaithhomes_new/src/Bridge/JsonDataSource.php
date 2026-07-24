<?php
namespace Adn\Theme\Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * JSON Data Source — Reads data from JSON files in the data directory.
 */
class JsonDataSource {

	private string $dataDir;

	public function __construct( string $dataDir ) {
		$this->dataDir = $dataDir;
	}

	/**
	 * Load a JSON file from the data directory.
	 */
	public function load( string $name ): array {
		$file = $this->dataDir . '/advaith/json/' . $name . '.json';

		if ( ! \file_exists( $file ) ) {
			return [];
		}

		$content = \file_get_contents( $file );
		$data = \json_decode( $content, true );

		return \is_array( $data ) ? $data : [];
	}

	/**
	 * Load a CSV file and return as array of rows.
	 */
	public function loadCsv( string $name ): array {
		$file = $this->dataDir . '/advaith/csv/' . $name . '.csv';

		if ( ! \file_exists( $file ) ) {
			return [];
		}

		$rows = [];
		$handle = \fopen( $file, 'r' );
		if ( $handle ) {
			$headers = \fgetcsv( $handle );
			while ( ( $row = \fgetcsv( $handle ) ) !== false ) {
				$rows[] = \array_combine( $headers, $row );
			}
			\fclose( $handle );
		}

		return $rows;
	}

	/**
	 * Load an HTML file and return its content.
	 */
	public function loadHtml( string $name ): string {
		$file = $this->dataDir . '/advaith/html/' . $name . '.html';

		if ( ! \file_exists( $file ) ) {
			return '';
		}

		return \file_get_contents( $file );
	}
}
