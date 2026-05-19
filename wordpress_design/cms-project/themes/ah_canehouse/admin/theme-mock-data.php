<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );
?>
<div class="wrap ch-admin-wrap">
	<h1>🌱 Install Mock / Demo Data</h1>

	<?php if ( isset( $_GET['seeded'] ) ) : ?>
		<div class="ch-notice ch-notice--success">
			✅ <?php echo esc_html( urldecode( $_GET['msg'] ?? 'Mock data seeded successfully.' ) ); ?>
		</div>
	<?php endif; ?>

	<div class="ch-card">
		<h2>What this will seed</h2>
		<ul style="list-style:disc;padding-left:1.5rem;line-height:2;">
			<li>🌿 <strong>6 authentic Indian-context customer reviews</strong> (Priya Sharma, Mohammed Al-Rashid, Ananya Patel, etc.)</li>
			<li>❓ <strong>10 FAQ entries</strong> covering juice, events, franchise, and sustainability</li>
			<li>📰 <strong>Marquee/news bar items</strong> with The Cane House messaging</li>
			<li>🔧 <strong>WordPress options</strong> - hero settings, menu sizes, flavours, cane types, order steps, benefits, franchise locations, hire packages</li>
			<li>📍 <strong>Franchise location marquee</strong> with UK Indian-community cities (Southall, Belgrave, Rusholme, Manningham, Handsworth etc.)</li>
			<li>📬 <strong>Contact submissions table</strong> (created if not exists)</li>
		</ul>
		<p style="margin-top:1rem;font-size:.85rem;color:#666;">
			⚠️ Running the seeder multiple times may duplicate DB rows (reviews, FAQs). Use <strong>Cleanup Data</strong> first if re-seeding.
		</p>
	</div>

	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<?php wp_nonce_field( 'ch_theme_seed' ); ?>
		<input type="hidden" name="action" value="ch_theme_seed">
		<?php submit_button( '🌱 Install Mock Data Now', 'primary', 'submit', false ); ?>
	</form>
</div>
