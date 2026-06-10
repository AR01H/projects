<?php
defined( 'ABSPATH' ) || exit;
$_d = CH_Shared_Data::section_heading( 'faq' );
// FAQs come from the CMS plugin (AH_Faqs_Model) via ch_get_faqs().
$faqs_all   = ch_get_faqs( '', 100 );
$home_limit = ch_home_limit( 'faqs', 6 );
$faqs       = $home_limit > 0 ? array_slice( $faqs_all, 0, $home_limit ) : $faqs_all;
$has_more   = $home_limit > 0 && count( $faqs_all ) > $home_limit;

if(count($faqs) <= 0) {
    return '';
}

?>

<section id="faq" class="ch-faq-section">
	<?php get_template_part( 'components/section-header', null, [
		'tag'           => $_d['tag']   ?? '',
		'title'         => $_d['title'] ?? '',
		'body'          => $_d['body']  ?? '',
		'wrapper_class' => 'ch-faq__header',
	] ); ?>

	<div class="ch-faq-grid fade-up" role="list">
		<?php foreach ( $faqs as $i => $faq ) :
			$faq = (array) $faq;
		?>
			<div class="ch-faq-item" role="listitem">
				<button class="ch-faq-question" aria-expanded="false">
					<?php echo esc_html( $faq['question'] ?? '' ); ?>
					<svg class="ch-faq-icon" aria-hidden="true" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="6 9 12 15 18 9"></polyline>
					</svg>
				</button>
				<div class="ch-faq-answer">
					<p><?php echo esc_html( $faq['answer'] ?? '' ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $has_more ) ch_more_button( home_url( '/faqs/' ), 'View All ' . count( $faqs_all ) . ' FAQs →' ); ?>
</section>
