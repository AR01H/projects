<?php
/**
 * components/sections/post_disclaimer.php
 *
 * Article disclaimer - static informational notice.
 * No props needed.
 */

defined( 'ABSPATH' ) || exit;

$_company = defined( 'COMPANY_NAME' ) ? COMPANY_NAME : 'Advaith Homes';
?>
<div class="article-disclaimer" role="note">
	<span class="disclaimer-icon" aria-hidden="true">ℹ️</span>
	<p>
		<strong><?php esc_html_e( 'Disclaimer:', ADN_TEXT_DOMAIN ); ?></strong>
		<?php printf(
			/* translators: %s = company name */
			esc_html__( 'The information provided by %s is for general guidance only and does not constitute financial, legal, or professional advice. Always consult a qualified professional before making property or financial decisions.', ADN_TEXT_DOMAIN ),
			esc_html( $_company )
		); ?>
	</p>
</div>
