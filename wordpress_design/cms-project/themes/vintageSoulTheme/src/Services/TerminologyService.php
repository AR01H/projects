<?php
namespace VintageSoul\Services;

use VintageSoul\DataProviders\JsonFileProvider;

defined( 'ABSPATH' ) || exit;

final class TerminologyService {

	public static function label( string $key, string $fallback = '' ): string {
		$labels = JsonFileProvider::read( 'config/terminology.json' );
		$value  = $labels[ $key ] ?? null;
		return is_string( $value ) && '' !== $value ? $value : $fallback;
	}
}
