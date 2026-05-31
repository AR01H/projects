<?php
/**
 * Reusable Privacy Policy Modal
 * Placed at body level (footer) so position:fixed works correctly.
 * Trigger: any element with id="ch-pp-trigger" or class="ch-pp-trigger".
 */
defined( 'ABSPATH' ) || exit;

$settings = ch_get_settings();
?>

<div id="ch-pp-modal" class="ch-pp-modal" role="dialog" aria-modal="true"
     aria-labelledby="ch-pp-modal-title" aria-hidden="true" style="display:none;">

	<div class="ch-pp-overlay" id="ch-pp-overlay"></div>

	<div class="ch-pp-box">

		<div class="ch-pp-header">
			<h3 id="ch-pp-modal-title">Privacy Policy</h3>
			<button type="button" class="ch-pp-close ch-bk-modal-close" id="ch-pp-close" aria-label="Close">&#10005;</button>
		</div>

		<div class="ch-pp-body">
			<p><strong>The Cane House</strong> is committed to protecting your privacy. This notice explains how we handle your personal data.</p>

			<h4>What we collect</h4>
			<p>When you submit our contact form we collect your name, email address, phone number, and message. We only collect what is necessary to respond to your enquiry.</p>

			<h4>How we use it</h4>
			<p>Your details are used solely to respond to your enquiry — event hire, franchise opportunities, or a general question. We do not use your data for automated marketing without your explicit consent.</p>

			<h4>Who we share it with</h4>
			<p>We will <strong>never sell or share your personal data</strong> with third parties for marketing. Data may be passed to email/CRM providers solely to facilitate communication, and they are bound by strict data-processing agreements.</p>

			<h4>How long we keep it</h4>
			<p>Enquiry data is retained for up to 12 months. You may request deletion at any time.</p>

			<h4>Your rights</h4>
			<p>Under UK GDPR you have the right to access, correct, or delete your personal data.</p>

		</div>

		<div class="ch-pp-footer">
			<button type="button" class="btn-lime ch-pp-close">Got it ✓</button>
		</div>

	</div><!-- .ch-pp-box -->
</div><!-- .ch-pp-modal -->
