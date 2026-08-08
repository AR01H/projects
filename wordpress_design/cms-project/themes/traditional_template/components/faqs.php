<?php
/**
 * FAQ accordion - reusable across pages with DIFFERENT content per page.
 *
 * Data source is switchable so each page can show its own questions:
 *   page_sections.json -> { "component": "faqs", "args": { "source": "faqs_franchise" } }
 * Defaults to admin/data/faqs.json when no source is given.
 *
 * The source JSON may be either shape:
 *   1) a flat array   [ { question, answer }, ... ]            (heading from content.json)
 *   2) an object      { heading, items: [ { question, answer } ] }
 *
 * Renders nothing when there are no questions.
 */
defined( 'ABSPATH' ) || exit;

$faq_source = ( isset( $source ) && $source ) ? (string) $source : 'faqs';
$raw        = app_data( $faq_source );

if ( is_array( $raw ) && isset( $raw['items'] ) ) {
	$faqs    = (array) $raw['items'];
	$heading = $raw['heading'] ?? '';
} else {
	$faqs    = is_array( $raw ) ? $raw : array();
	$heading = '';
}

if ( empty( $faqs ) ) {
	return;
}

if ( '' === $heading ) {
	$content = app_data( 'content' )['faqs'] ?? array();
	$heading = $content['heading'] ?? __( 'Frequently Asked Questions', NT_TEXT_DOMAIN );
}
?>
<section class="app-faqs">
	<div class="app-container">
		<h2 class="app-section-title"><?php echo wp_kses_post( $heading ); ?></h2>
		<div class="app-faq-list">
			<?php foreach ( $faqs as $faq ) :
				$faq = (object) $faq; ?>
				<details class="app-faq-item">
					<summary class="app-faq-q"><?php echo esc_html( $faq->question ?? '' ); ?></summary>
					<div class="app-faq-a"><?php echo wp_kses_post( $faq->answer ?? '' ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
