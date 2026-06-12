<?php
/**
 * admin/tabs/tab-import-export.php — Import / Export.
 *
 * Theme-level: export/import the theme's own settings (Home hero, sections,
 * featured guides, calculators) as JSON.
 * Plugin-level: bulk content (CSV) is handled by the CMS plugin's Data Import,
 * so we link to it instead of duplicating that big feature here.
 */

defined( 'ABSPATH' ) || exit;

$has_plugin_import = class_exists( 'AH_CSV_Importer' );
$plugin_import_url = admin_url( 'admin.php?page=ah-import' );
?>
<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Export theme settings (JSON)', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Download all theme settings (Home hero, sections, featured guides, calculators) as a JSON file — handy as a backup or to copy settings to another site.', ADN_TEXT_DOMAIN ); ?>
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="adn_export_settings">
		<?php wp_nonce_field( 'adn_export_settings' ); ?>
		<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Download settings JSON', ADN_TEXT_DOMAIN ); ?></button></p>
	</form>
</div>

<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Import theme settings (JSON)', ADN_TEXT_DOMAIN ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Upload a settings JSON exported above. Only recognised theme settings are applied; everything else in the file is ignored.', ADN_TEXT_DOMAIN ); ?>
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="adn_import_settings">
		<?php wp_nonce_field( 'adn_import_settings' ); ?>
		<p><input type="file" name="settings_file" accept="application/json,.json" required></p>
		<p><button type="submit" class="button button-secondary"><?php esc_html_e( 'Import settings', ADN_TEXT_DOMAIN ); ?></button></p>
	</form>
</div>

<div class="card" style="max-width:none;">
	<h2><?php esc_html_e( 'Content import (CSV)', ADN_TEXT_DOMAIN ); ?></h2>
	<?php if ( $has_plugin_import ) : ?>
		<p class="description">
			<?php esc_html_e( 'Bulk content — services, posts, reviews, FAQs, team, taxonomies, news bar and more — is imported through the CMS plugin\'s CSV importer. It is kept there (one place) so the theme does not duplicate it.', ADN_TEXT_DOMAIN ); ?>
		</p>
		<p><a class="button" href="<?php echo esc_url( $plugin_import_url ); ?>"><?php esc_html_e( 'Open Data Import (CSV) →', ADN_TEXT_DOMAIN ); ?></a></p>
	<?php else : ?>
		<p class="description">
			<?php esc_html_e( 'The CMS plugin (which provides the CSV Data Import tool) is not active. Activate it to import bulk content from CSV.', ADN_TEXT_DOMAIN ); ?>
		</p>
	<?php endif; ?>
</div>
