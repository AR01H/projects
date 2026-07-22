<?php
/**
 * components/parts/sidebar_newsletter_signup.php - Compact newsletter signup in sidebar.
 *
 * Props: $newsletter { heading, description, placeholder, button_label, note }
 * Form is presentational until the REST route is wired.
 * Usage: adn_component( 'parts/sidebar_newsletter_signup', array( 'newsletter' => $ctx['sidebar']['newsletter'] ) );
 */

defined( 'ABSPATH' ) || exit;

$newsletter = isset( $newsletter ) && is_array( $newsletter ) ? $newsletter : array();

if ( empty( $newsletter ) ) {
	return;
}
?>
<div class="news-sb-box">
	<?php if ( ! empty( $newsletter['heading'] ) ) : ?>
		<div class="news-sb-title"><?php echo esc_html( $newsletter['heading'] ); ?></div>
	<?php endif; ?>

	<div class="sb-newsletter-body">
		<?php if ( ! empty( $newsletter['description'] ) ) : ?>
			<p class="sb-nl-desc"><?php echo esc_html( $newsletter['description'] ); ?></p>
		<?php endif; ?>

		<form class="sb-nl-form" onsubmit="return false;" aria-label="<?php echo esc_attr__( 'Newsletter signup', ADN_TEXT_DOMAIN ); ?>">
			<input
				type="email"
				class="sb-nl-input"
				placeholder="<?php echo esc_attr( isset( $newsletter['placeholder'] ) ? $newsletter['placeholder'] : '' ); ?>"
				aria-label="<?php echo esc_attr__( 'Email address', ADN_TEXT_DOMAIN ); ?>"
			/>
			<button type="submit" class="btn btn-accent sb-nl-btn">
				<?php echo esc_html( isset( $newsletter['button_label'] ) ? $newsletter['button_label'] : '' ); ?>
			</button>
		</form>

		<p class="sb-nl-note"><?php echo esc_html( ! empty( $newsletter['note'] ) ? $newsletter['note'] : SITE_NEWSLETTER_CONSENT_NOTE ); ?></p>
	</div>
</div>
