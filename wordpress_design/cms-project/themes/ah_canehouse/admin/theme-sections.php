<?php
defined( 'ABSPATH' ) || exit;
if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorised' );

$raw = get_option( 'ch_section_visibility', [] );
if ( is_string( $raw ) ) $raw = json_decode( $raw, true ) ?: [];
$vis = (array) $raw;

$sections = [
	'news_ticker'  => '📰 News Ticker / Top Bar',
	'hero'         => '🌿 Hero Section',
	'marquee'      => '🎞️ Marquee Banner',
	'story_cards'  => '🌾 Sugarcane Story Cards (interactive tabs)',
	'how_to_order' => '📋 How to Order Steps',
	'booking'      => '🎫 Booking Wizard (multi-step order form)',
	'reviews'      => '⭐ Customer Reviews',
	'menu_builder' => '🧃 Build Your Juice (Menu)',
	'benefits'     => '💪 Benefits',
	'story'        => '📖 Story Section',
	'hire'         => '🎪 Event Hire Section',
	'certifications' => '🏛️ Certifications / Food Safety Registered',
	'franchise'    => '📍 Franchise Section',
	'faqs'         => '❓ FAQ Section',
	'contact'      => '📬 Contact Section',
];
?>
<div class="wrap ch-admin-wrap">
	<h1>⚙️ Section Controls</h1>

	<?php if ( isset( $_GET['saved'] ) ) : ?>
		<div class="ch-notice ch-notice--success">✅ Section visibility saved.</div>
	<?php endif; ?>

	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<?php wp_nonce_field( 'ch_theme_sections' ); ?>
		<input type="hidden" name="action" value="ch_theme_sections">

		<div class="ch-card">
			<h2>Show/Hide Homepage Sections</h2>
			<p style="margin-bottom:1rem;color:#666;font-size:.85rem;">Toggle which sections appear on the homepage. Changes take effect immediately.</p>
			<div class="ch-section-grid">
				<?php foreach ( $sections as $key => $label ) :
					$checked = isset( $vis[ $key ] ) ? (bool) $vis[ $key ] : true;
				?>
					<div class="ch-section-item">
						<input type="checkbox" id="section_<?php echo esc_attr( $key ); ?>"
							name="section_<?php echo esc_attr( $key ); ?>" value="1"
							<?php checked( $checked ); ?>>
						<label for="section_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php submit_button( 'Save Section Visibility', 'primary', 'submit', false ); ?>
	</form>
</div>
