<?php
/**
 * Admin view: Cleanup / wipe mock data
 */

defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/mock_data/seeder.php';

$counts  = PT_Theme_Seeder::table_counts();
$cleaned = isset( $_GET['cleaned'] ) ? (bool) $_GET['cleaned'] : false;
$msg     = isset( $_GET['msg'] )     ? urldecode( sanitize_text_field( $_GET['msg'] ) ) : '';
?>
<div class="wrap pt-admin-wrap">

	<div class="pt-admin-header">
		<div class="pt-admin-logo">PT</div>
		<div>
			<h1>Cleanup Data</h1>
			<p>Remove all mock / seeded content from the database tables.</p>
		</div>
	</div>

	<?php if ( $cleaned ) : ?>
		<div class="pt-notice pt-notice--ok"><?php echo esc_html( $msg ?: 'All mock data removed.' ); ?></div>
	<?php endif; ?>

	<!-- Current state -->
	<div class="pt-admin-cards" style="margin-bottom:24px">
		<?php foreach ( $counts as $label => $val ) :
			$label_nice = ucwords( str_replace( '_', ' ', $label ) );
			$cls = ( (int) $val > 0 ) ? 'pt-admin-card--warn' : 'pt-admin-card--ok';
		?>
		<div class="pt-admin-card <?php echo esc_attr( $cls ); ?>">
			<div class="pt-admin-card__label"><?php echo esc_html( $label_nice ); ?></div>
			<div class="pt-admin-card__value"><?php echo esc_html( $val ); ?></div>
			<div class="pt-admin-card__sub">rows in DB</div>
		</div>
		<?php endforeach; ?>
	</div>

	<div class="pt-admin-box">
		<h2>Delete All Content</h2>
		<div class="pt-notice pt-notice--err" style="margin-bottom:18px;">
			<strong>Warning:</strong> This deletes all rows from every theme table.
			This includes real content if you have added any. This action cannot be undone.
		</div>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		      onsubmit="return confirm('Delete all rows from all theme tables? This cannot be undone.')">
			<?php wp_nonce_field( 'pt_theme_cleanup' ); ?>
			<input type="hidden" name="action" value="pt_theme_cleanup">
			<button type="submit" class="button" style="background:#dc2626;color:#fff;border-color:#dc2626;">
				&#10005; Delete All Data
			</button>
			&nbsp;
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=pt-mock-data' ) ); ?>" class="button">
				Cancel
			</a>
		</form>
	</div>

</div>
