<?php
/**
 * components/parts/stay_informed_bar.php - Newsletter strip.
 * Props: $stay_informed { icon, title, description, placeholder, button_label, note }
 */

defined( 'ABSPATH' ) || exit;

$si = isset( $stay_informed ) && is_array( $stay_informed ) ? $stay_informed : array();
?>
<div class="sib-inner">

	<div class="sib-left">
		<span class="sib-pill">Free Newsletter</span>
		<h2 class="sib-title">
			<?php echo esc_html( ! empty( $si['title'] ) ? $si['title'] : 'Stay Ahead of the Market' ); ?>
		</h2>
		<p class="sib-desc">
			<?php echo esc_html( ! empty( $si['description'] ) ? $si['description'] : 'Get expert guides, market updates and home-buying tips delivered straight to your inbox.' ); ?>
		</p>
		<ul class="sib-perks">
			<li><span class="sib-check">✓</span> Weekly property market insights</li>
			<li><span class="sib-check">✓</span> First-time buyer guides &amp; checklists</li>
			<li><span class="sib-check">✓</span> No spam — unsubscribe anytime</li>
		</ul>
	</div>

	<div class="sib-right">
		<div class="sib-card">
			<p class="sib-card-label">Join <?php echo esc_html( defined( 'SITE_SUBSCRIBER_COUNT' ) ? SITE_SUBSCRIBER_COUNT : '2,400+' ); ?> subscribers</p>
			<form class="sib-form" onsubmit="return false;" novalidate>
				<div class="sib-field-row">
					<div class="sib-field">
						<label class="sib-field-label" for="sib-name">First name</label>
						<input id="sib-name" type="text" class="sib-input" placeholder="Your name" autocomplete="given-name">
					</div>
					<div class="sib-field">
						<label class="sib-field-label" for="sib-email">Email address</label>
						<input id="sib-email" type="email" class="sib-input" placeholder="<?php echo esc_attr( defined( 'SITE_PLACEHOLDER_NEWSLETTER' ) ? SITE_PLACEHOLDER_NEWSLETTER : 'you@example.com' ); ?>" autocomplete="email" required>
					</div>
				</div>
				<button type="submit" class="sib-btn">
					<?php echo esc_html( ! empty( $si['button_label'] ) ? $si['button_label'] : ( defined( 'SITE_BTN_SUBSCRIBE' ) ? SITE_BTN_SUBSCRIBE : 'Subscribe' ) ); ?>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
				</button>
				<p class="sib-privacy">
					<?php echo esc_html( ! empty( $si['note'] ) ? $si['note'] : 'No spam. Unsubscribe anytime. We respect your privacy.' ); ?>
				</p>
			</form>
		</div>
	</div>

</div>
