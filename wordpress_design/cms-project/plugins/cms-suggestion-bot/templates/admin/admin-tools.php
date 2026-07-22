<?php
/**
 * templates/admin/admin-tools.php
 *
 * @var array<int, array{type:string,label:string,available:bool}> $sources
 * @var array<int, array<string, mixed>>                           $stats
 * @var string                                                     $notice
 */

use CmsSuggestionBot\Admin\View;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Admin Tools', 'cms-suggestion-bot' ); ?></h1>

	<?php View::notice( $notice ); ?>

	<div class="csb-grid-cards">

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Generate Cache', 'cms-suggestion-bot' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Choose which content types to read and cache.', 'cms-suggestion-bot' ); ?></p>
			<p>
				<?php foreach ( $sources as $source ) : ?>
					<label class="csb-label-block--sm">
						<input type="checkbox" class="csb-gen-type" value="<?php echo esc_attr( $source['type'] ); ?>" <?php disabled( ! $source['available'] ); ?>>
						<?php echo esc_html( $source['label'] ); ?>
						<?php if ( ! $source['available'] ) : ?> <em>(<?php esc_html_e( 'unavailable', 'cms-suggestion-bot' ); ?>)</em><?php endif; ?>
					</label>
				<?php endforeach; ?>
			</p>
			<button type="button" class="button button-primary" id="csb-generate-cache"><?php esc_html_e( 'Generate Cache', 'cms-suggestion-bot' ); ?></button>
			<button type="button" class="button" id="csb-generate-all"><?php esc_html_e( 'Generate Everything', 'cms-suggestion-bot' ); ?></button>
			<div class="csb-tool-result" id="csb-generate-result"></div>
		</div>

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Destroy Cache', 'cms-suggestion-bot' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Delete only the cache. Configuration is kept.', 'cms-suggestion-bot' ); ?></p>
			<button type="button" class="button button-secondary" id="csb-destroy-cache"><?php esc_html_e( 'Destroy Cache', 'cms-suggestion-bot' ); ?></button>
			<div class="csb-tool-result" id="csb-destroy-result"></div>
		</div>

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Rebuild Cache', 'cms-suggestion-bot' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Destroy the cache, then generate it again from scratch.', 'cms-suggestion-bot' ); ?></p>
			<button type="button" class="button button-secondary" id="csb-rebuild-cache"><?php esc_html_e( 'Rebuild Cache', 'cms-suggestion-bot' ); ?></button>
			<div class="csb-tool-result" id="csb-rebuild-result"></div>
		</div>

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Clear Knowledge', 'cms-suggestion-bot' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Permanently removes every Knowledge Base entry.', 'cms-suggestion-bot' ); ?></p>
			<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'This will delete all knowledge base entries. Continue?', 'cms-suggestion-bot' ) ); ?>');">
				<?php wp_nonce_field( 'csb_admin_tools' ); ?>
				<button type="submit" name="csb_tool_action" value="clear_knowledge" class="button button-secondary"><?php esc_html_e( 'Clear Knowledge', 'cms-suggestion-bot' ); ?></button>
			</form>
		</div>

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Repair Database', 'cms-suggestion-bot' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Runs REPAIR TABLE on every cms_sug_bot_* table.', 'cms-suggestion-bot' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'csb_admin_tools' ); ?>
				<button type="submit" name="csb_tool_action" value="repair_db" class="button button-secondary"><?php esc_html_e( 'Repair Database', 'cms-suggestion-bot' ); ?></button>
			</form>
		</div>

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Optimize Tables', 'cms-suggestion-bot' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Runs OPTIMIZE TABLE on every cms_sug_bot_* table.', 'cms-suggestion-bot' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'csb_admin_tools' ); ?>
				<button type="submit" name="csb_tool_action" value="optimize_tables" class="button button-secondary"><?php esc_html_e( 'Optimize Tables', 'cms-suggestion-bot' ); ?></button>
			</form>
		</div>

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Export Cache', 'cms-suggestion-bot' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Download the full cache as a JSON file.', 'cms-suggestion-bot' ); ?></p>
			<a class="button button-secondary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'csb_action', 'export_cache' ), 'csb_export_cache' ) ); ?>"><?php esc_html_e( 'Export Cache', 'cms-suggestion-bot' ); ?></a>
		</div>

		<div class="card">
			<h2 class="title"><?php esc_html_e( 'Import Cache', 'cms-suggestion-bot' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Upload a previously exported JSON file.', 'cms-suggestion-bot' ); ?></p>
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'csb_admin_tools' ); ?>
				<input type="file" name="csb_import_file" accept="application/json">
				<button type="submit" name="csb_tool_action" value="import_cache" class="button button-secondary"><?php esc_html_e( 'Import Cache', 'cms-suggestion-bot' ); ?></button>
			</form>
		</div>

	</div>

	<h2 class="csb-section-heading"><?php esc_html_e( 'View Statistics', 'cms-suggestion-bot' ); ?></h2>
	<?php
	View::table(
		array( __( 'Source Type', 'cms-suggestion-bot' ), __( 'Entries', 'cms-suggestion-bot' ), __( 'Words', 'cms-suggestion-bot' ) ),
		array_map(
			static fn( array $row ) => array(
				esc_html( $row['source_type'] ),
				esc_html( number_format_i18n( (int) $row['total'] ) ),
				esc_html( number_format_i18n( (int) $row['words'] ) ),
			),
			$stats
		)
	);
	?>
</div>

<style>.csb-tool-result{margin-top:8px;font-size:12px;padding:6px 10px;border-radius:4px;display:none;}
.csb-tool-result.ok{display:block;background:#f0fdf4;color:#15803d;}
.csb-tool-result.err{display:block;background:#fef2f2;color:#b91c1c;}</style>
<script>
jQuery(function ($) {
	var nonce = '<?php echo esc_js( wp_create_nonce( 'csb_admin' ) ); ?>';

	function run( action, extra, $btn, $result ) {
		$btn.prop( 'disabled', true );
		$result.removeClass( 'ok err' ).hide();
		$.post( ajaxurl, $.extend( { action: action, nonce: nonce }, extra || {} ), function ( res ) {
			$btn.prop( 'disabled', false );
			if ( res.success ) {
				$result.addClass( 'ok' ).text( res.data.message );
			} else {
				$result.addClass( 'err' ).text( ( res.data && res.data.message ) || 'Error.' );
			}
		} ).fail( function () {
			$btn.prop( 'disabled', false );
			$result.addClass( 'err' ).text( 'Request failed.' );
		} );
	}

	$( '#csb-generate-cache' ).on( 'click', function () {
		var types = $( '.csb-gen-type:checked' ).map( function () { return this.value; } ).get();
		run( '<?php echo esc_js( CSB_AJAX_GENERATE_CACHE ); ?>', { types: types }, $( this ), $( '#csb-generate-result' ) );
	} );
	$( '#csb-generate-all' ).on( 'click', function () {
		run( '<?php echo esc_js( CSB_AJAX_GENERATE_CACHE ); ?>', { types: [ 'everything' ] }, $( this ), $( '#csb-generate-result' ) );
	} );
	$( '#csb-destroy-cache' ).on( 'click', function () {
		if ( ! confirm( '<?php echo esc_js( __( 'Delete the entire cache?', 'cms-suggestion-bot' ) ); ?>' ) ) return;
		run( '<?php echo esc_js( CSB_AJAX_DESTROY_CACHE ); ?>', {}, $( this ), $( '#csb-destroy-result' ) );
	} );
	$( '#csb-rebuild-cache' ).on( 'click', function () {
		run( '<?php echo esc_js( CSB_AJAX_REBUILD_CACHE ); ?>', {}, $( this ), $( '#csb-rebuild-result' ) );
	} );
});
</script>
