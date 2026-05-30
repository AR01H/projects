<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

$settings  = ch_get_settings();
$reviews   = ch_get_reviews( 100 );
$faqs      = ch_get_faqs( '', 100 );
global $wpdb;
$submissions_table = $wpdb->prefix . 'ch_contact_submissions';
$submission_count  = $wpdb->get_var( "SHOW TABLES LIKE '{$submissions_table}'" ) === $submissions_table
	? (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$submissions_table}`" ) : 0;
?>
<div class="wrap ch-admin-wrap">
	<h1>🌿 The Cane House - CMS Dashboard</h1>

	<div class="ch-card">
		<h2>Site Overview</h2>
		<div class="ch-stat-grid">
			<div class="ch-stat">
				<div class="ch-stat__num"><?php echo count( $reviews ); ?></div>
				<div class="ch-stat__label">Customer Reviews</div>
			</div>
			<div class="ch-stat">
				<div class="ch-stat__num"><?php echo count( $faqs ); ?></div>
				<div class="ch-stat__label">FAQ Entries</div>
			</div>
			<div class="ch-stat">
				<div class="ch-stat__num"><?php echo $submission_count; ?></div>
				<div class="ch-stat__label">Enquiry Submissions</div>
			</div>
			<div class="ch-stat">
				<div class="ch-stat__num"><?php echo count( ch_get_menu_sizes() ); ?></div>
				<div class="ch-stat__label">Menu Sizes</div>
			</div>
			<div class="ch-stat">
				<div class="ch-stat__num"><?php echo count( ch_get_flavours() ); ?></div>
				<div class="ch-stat__label">Flavours</div>
			</div>
			<div class="ch-stat">
				<div class="ch-stat__num"><?php echo count( ch_get_franchise_locations() ); ?></div>
				<div class="ch-stat__label">Franchise Locations</div>
			</div>
		</div>
	</div>

	<div class="ch-card">
		<h2>Quick Actions</h2>
		<div style="display:flex;gap:1rem;flex-wrap:wrap;">
			<a href="<?php echo admin_url( 'admin.php?page=ch-theme-sections' ); ?>" class="button button-primary">⚙️ Section Controls</a>
			<a href="<?php echo admin_url( 'admin.php?page=ch-theme-content' ); ?>" class="button button-primary">📝 Content &amp; Menu</a>
			<?php if ( class_exists( 'AH_Admin_Bootstrap' ) ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=ah-navigation' ); ?>" class="button button-primary">🧭 Navigation &amp; Footer (Plugin)</a>
			<?php endif; ?>
			<a href="<?php echo admin_url( 'admin.php?page=ch-theme-settings' ); ?>" class="button button-primary">🔧 Site Settings</a>
			<a href="<?php echo admin_url( 'admin.php?page=ch-theme-submissions' ); ?>" class="button">📥 Enquiry Submissions</a>
			<a href="<?php echo admin_url( 'admin.php?page=ch-theme-mock' ); ?>" class="button">🌱 Install Mock Data</a>
		</div>
	</div>

	<div class="ch-card">
		<h2>Current Site Settings</h2>
		<table class="widefat striped" style="max-width:600px;">
			<tbody>
				<?php foreach ( [ 'phone' => 'Phone', 'email' => 'Email', 'address' => 'Address', 'website' => 'Website', 'tagline' => 'Tagline' ] as $key => $label ) : ?>
					<tr>
						<td style="font-weight:600;width:140px;"><?php echo esc_html( $label ); ?></td>
						<td><?php echo esc_html( $settings[ $key ] ?? '-' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php if ( class_exists( 'AH_Admin_Bootstrap' ) ) : ?>
		<div class="ch-card" style="border-color:#c8e830;background:#f9ffec;">
			<h2>✅ CMS Plugin Active</h2>
			<p>The CMS Admin plugin is active. Reviews, FAQs, and other content can be managed via <strong>CMS ADMIN</strong> in the sidebar. The Cane House theme reads from the same shared database tables.</p>
		</div>
	<?php else : ?>
		<div class="ch-card" style="border-color:#ffc107;background:#fffbec;">
			<h2>⚠️ CMS Plugin Not Active</h2>
			<p>The CMS Admin plugin (<code>plugin1</code>) is not active. Install and activate it to enable full CMS functionality. Without it, the theme uses mock/seeded data only.</p>
		</div>
	<?php endif; ?>
</div>
