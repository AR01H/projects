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
$raw        = nt_data( $faq_source );

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
	$content = nt_data( 'content' )['faqs'] ?? array();
	$heading = $content['heading'] ?? __( 'Frequently Asked Questions', NT_TEXT_DOMAIN );
}
?>
<section class="nt-faqs">
	<div class="nt-container">
		<h2 class="nt-section-title"><?php echo wp_kses_post( $heading ); ?></h2>
		<div class="nt-faq-list">
			<?php foreach ( $faqs as $faq ) :
				$faq = (object) $faq; ?>
				<details class="nt-faq-item">
					<summary class="nt-faq-q"><?php echo esc_html( $faq->question ?? '' ); ?></summary>
					<div class="nt-faq-a"><?php echo wp_kses_post( $faq->answer ?? '' ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
