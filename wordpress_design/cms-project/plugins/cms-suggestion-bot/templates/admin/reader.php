<?php
/**
 * templates/admin/reader.php
 *
 * @var array<int, array{type:string,label:string,available:bool,count:int}> $sources
 * @var array<int, array<string, mixed>>                                     $runs
 */

use CmsSuggestionBot\Admin\View;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Reader', 'cms-suggestion-bot' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Content types the Reader can scan. Run a scan from Admin Tools -> Generate Cache.', 'cms-suggestion-bot' ); ?></p>

	<?php
	View::table(
		array( __( 'Source Type', 'cms-suggestion-bot' ), __( 'Label', 'cms-suggestion-bot' ), __( 'Available', 'cms-suggestion-bot' ), __( 'Records', 'cms-suggestion-bot' ) ),
		array_map(
			static fn( array $s ) => array(
				esc_html( $s['type'] ),
				esc_html( $s['label'] ),
				$s['available'] ? '✅' : '—',
				esc_html( number_format_i18n( $s['count'] ) ),
			),
			$sources
		)
	);
	?>

	<h2 class="csb-section-heading"><?php esc_html_e( 'Recent Scan Runs', 'cms-suggestion-bot' ); ?></h2>
	<?php
	View::table(
		array( __( 'Type', 'cms-suggestion-bot' ), __( 'Status', 'cms-suggestion-bot' ), __( 'Total', 'cms-suggestion-bot' ), __( 'Processed', 'cms-suggestion-bot' ), __( 'Started', 'cms-suggestion-bot' ), __( 'Finished', 'cms-suggestion-bot' ) ),
		array_map(
			static fn( array $r ) => array(
				esc_html( $r['reader_type'] ),
				esc_html( $r['status'] ),
				esc_html( number_format_i18n( (int) $r['total'] ) ),
				esc_html( number_format_i18n( (int) $r['processed'] ) ),
				esc_html( (string) $r['started_at'] ),
				esc_html( (string) ( $r['finished_at'] ?? '' ) ),
			),
			$runs
		),
		__( 'No scans have run yet.', 'cms-suggestion-bot' )
	);
	?>
</div>
