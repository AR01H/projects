<?php
/**
 * templates/admin/dashboard.php
 *
 * @var array<int, array{title:string, value:string, sub?:string}> $cards
 * @var array<int, array<string, mixed>>                           $cache_by_type
 */

use CmsSuggestionBot\Admin\View;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'CMS Suggestion Bot - Dashboard', 'cms-suggestion-bot' ); ?></h1>

	<?php View::cardGrid( $cards ); ?>

	<?php if ( ! empty( $cache_by_type ) ) : ?>
		<h2 class="csb-section-heading"><?php esc_html_e( 'Cache by Source Type', 'cms-suggestion-bot' ); ?></h2>
		<?php
		View::table(
			array( __( 'Source Type', 'cms-suggestion-bot' ), __( 'Entries', 'cms-suggestion-bot' ), __( 'Words', 'cms-suggestion-bot' ) ),
			array_map(
				static fn( array $row ) => array(
					esc_html( $row['source_type'] ),
					esc_html( number_format_i18n( (int) $row['total'] ) ),
					esc_html( number_format_i18n( (int) $row['words'] ) ),
				),
				$cache_by_type
			)
		);
		?>
	<?php endif; ?>
</div>
