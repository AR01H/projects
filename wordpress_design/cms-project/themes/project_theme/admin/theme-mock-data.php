<?php
/**
 * Admin view: Install Mock Data
 */

defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/mock_data/seeder.php';

$counts   = PT_Theme_Seeder::table_counts();
$seeded   = isset( $_GET['seeded'] ) ? (int) $_GET['seeded'] : -1;
$msg      = isset( $_GET['msg'] )    ? urldecode( sanitize_text_field( $_GET['msg'] ) ) : '';
?>
<div class="wrap pt-admin-wrap">

	<div class="pt-admin-header">
		<div class="pt-admin-logo">PT</div>
		<div>
			<h1>Install Mock Data</h1>
			<p>Populate tables with realistic sample content for development and testing.</p>
		</div>
	</div>

	<?php if ( $seeded === 1 ) : ?>
		<div class="pt-notice pt-notice--ok"><?php echo esc_html( $msg ?: 'Mock data installed.' ); ?></div>
	<?php elseif ( $seeded === 0 ) : ?>
		<div class="pt-notice pt-notice--err"><?php echo esc_html( $msg ?: 'Install failed. Check server logs.' ); ?></div>
	<?php endif; ?>

	<!-- Status cards -->
	<div class="pt-admin-cards" style="margin-bottom:24px">
		<?php foreach ( $counts as $label => $val ) :
			$label_nice = ucwords( str_replace( '_', ' ', $label ) );
			$cls = ( (int) $val > 0 ) ? 'pt-admin-card--ok' : 'pt-admin-card--warn';
		?>
		<div class="pt-admin-card <?php echo esc_attr( $cls ); ?>">
			<div class="pt-admin-card__label"><?php echo esc_html( $label_nice ); ?></div>
			<div class="pt-admin-card__value"><?php echo esc_html( $val ); ?></div>
			<div class="pt-admin-card__sub">rows in DB</div>
		</div>
		<?php endforeach; ?>
	</div>

	<!-- Install all -->
	<div class="pt-admin-box">
		<h2>Install All Mock Data</h2>
		<p style="color:#64748b;font-size:.875rem;margin-bottom:18px;">
			Inserts sample rows into every table. Existing rows are skipped (safe to re-run).
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'pt_theme_seed' ); ?>
			<input type="hidden" name="action" value="pt_theme_seed">
			<input type="hidden" name="seed_type" value="all">
			<button type="submit" class="button button-primary button-hero">
				&#8679; Install All Mock Data
			</button>
		</form>
	</div>

	<!-- Schema only -->
	<div class="pt-admin-box">
		<h2>Schema Only</h2>
		<p style="color:#64748b;font-size:.875rem;margin-bottom:18px;">
			Runs <code>dbDelta</code> to create or update DB tables — no demo rows inserted.
			Run this after a theme update when column changes are expected.
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'pt_theme_seed' ); ?>
			<input type="hidden" name="action" value="pt_theme_seed">
			<input type="hidden" name="seed_type" value="schema">
			<button type="submit" class="button button-hero">
				&#10003; Install Schema Only
			</button>
		</form>
	</div>

	<!-- Individual content types -->
	<div class="pt-admin-box">
		<h2>Seed Individual Types</h2>
		<p style="color:#64748b;font-size:.875rem;margin-bottom:18px;">
			Choose which content types to seed. Existing rows are skipped.
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'pt_theme_seed' ); ?>
			<input type="hidden" name="action" value="pt_theme_seed">
			<input type="hidden" name="seed_type" value="selected">
			<div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px">
				<label style="display:flex;align-items:center;gap:10px;font-size:.9rem;">
					<input type="checkbox" name="seed_types[]" value="stories" checked>
					<strong>Stories</strong> — 5 sample client case studies
				</label>
			</div>
			<button type="submit" class="button button-primary">
				Seed Selected
			</button>
		</form>
	</div>

</div>
