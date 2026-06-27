<?php
/**
 * Template Name: Terms of Use
 *
 * pages/page-terms.php - Terms of Use / Terms & Conditions page.
 */

defined( 'ABSPATH' ) || exit;

$_chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

adn_page_open( array(
	'chrome'     => $_chrome,
	'breadcrumb' => array(
		array( 'label' => 'Home', 'url' => home_url( '/' ) ),
		array( 'label' => 'Terms of Use', 'url' => null ),
	),
) );
?>

<div class="legal-page-wrap">
	<div class="container">
		<div class="legal-page-inner">

			<header class="legal-page-header">
				<h1><?php esc_html_e( 'Terms of Use', ADN_TEXT_DOMAIN ); ?></h1>
				<p class="legal-last-updated">
					<?php esc_html_e( 'Last updated: June 2025', ADN_TEXT_DOMAIN ); ?>
				</p>
			</header>

			<div class="legal-content">

				<div class="legal-section">
					<h2><?php esc_html_e( '1. Acceptance of Terms', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php esc_html_e( 'By accessing and using the Advaith Homes website ("the Site"), you accept and agree to be bound by these Terms of Use. If you do not agree to these terms, please do not use the Site.', ADN_TEXT_DOMAIN ); ?></p>
</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '2. Use of the Site', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php esc_html_e( 'The Site is provided for informational purposes only. The content on this Site is not intended to constitute financial, legal, or professional property advice. You should seek independent professional advice before making any property-related decisions.', ADN_TEXT_DOMAIN ); ?></p>
					<ul>
						<li><?php esc_html_e( 'You must use the Site only for lawful purposes.', ADN_TEXT_DOMAIN ); ?></li>
						<li><?php esc_html_e( 'You must not use the Site in any way that is unlawful or harmful.', ADN_TEXT_DOMAIN ); ?></li>
						<li><?php esc_html_e( 'You must not attempt to gain unauthorised access to any part of the Site.', ADN_TEXT_DOMAIN ); ?></li>
					</ul>
				</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '3. Intellectual Property', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php esc_html_e( 'All content on this Site, including text, graphics, logos, and images, is the property of Advaith Homes or its content suppliers and is protected by applicable intellectual property laws. You may not reproduce, distribute, or create derivative works without our express written permission.', ADN_TEXT_DOMAIN ); ?></p>
				</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '4. Third-Party Links', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php esc_html_e( 'The Site may contain links to third-party websites. These links are provided for your convenience only. We have no control over the content of those websites and accept no responsibility for them or for any loss or damage that may arise from your use of them.', ADN_TEXT_DOMAIN ); ?></p>
				</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '5. Expert Referrals', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php esc_html_e( 'Advaith Homes may connect you with third-party professionals including solicitors, surveyors, mortgage advisers, and other property experts. We do not endorse any specific professional and are not responsible for the services they provide. Any engagement you enter into with a referred professional is solely between you and that professional.', ADN_TEXT_DOMAIN ); ?></p>
				</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '6. Limitation of Liability', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php esc_html_e( 'To the fullest extent permitted by law, Advaith Homes shall not be liable for any direct, indirect, incidental, or consequential loss or damage arising from your use of, or inability to use, this Site or any content on it.', ADN_TEXT_DOMAIN ); ?></p>
				</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '7. Privacy', ADN_TEXT_DOMAIN ); ?></h2>
					<p>
						<?php
						$_pp_url = esc_url( home_url( '/privacy-policy/' ) );
						printf(
							esc_html__( 'Your use of this Site is also governed by our %1$sPrivacy Policy%2$s, which is incorporated into these Terms of Use by reference.', ADN_TEXT_DOMAIN ),
							'<a href="' . $_pp_url . '">',
							'</a>'
						);
						?>
					</p>
				</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '8. Changes to These Terms', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php esc_html_e( 'We reserve the right to update these Terms of Use at any time. Changes will be posted on this page with an updated date. Your continued use of the Site after any changes constitutes your acceptance of the new terms.', ADN_TEXT_DOMAIN ); ?></p>
				</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '9. Governing Law', ADN_TEXT_DOMAIN ); ?></h2>
					<p><?php esc_html_e( 'These Terms of Use are governed by and construed in accordance with the laws of England and Wales. Any disputes arising from these terms shall be subject to the exclusive jurisdiction of the courts of England and Wales.', ADN_TEXT_DOMAIN ); ?></p>
				</div>

				<div class="legal-section">
					<h2><?php esc_html_e( '10. Contact Us', ADN_TEXT_DOMAIN ); ?></h2>
					<p>
						<?php
						$_contact_url = esc_url( home_url( '/contact/' ) );
						printf(
							esc_html__( 'If you have any questions about these Terms of Use, please %1$scontact us%2$s.', ADN_TEXT_DOMAIN ),
							'<a href="' . $_contact_url . '">',
							'</a>'
						);
						?>
					</p>
				</div>

			</div>

		</div>
	</div>
</div>

<?php adn_page_close( array( 'chrome' => $_chrome ) ); ?>
