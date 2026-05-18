<?php
defined( 'ABSPATH' ) || exit;
$faqs = ch_get_faqs( '', 12 );
?>

<section id="faq" class="ch-faq-section">
	<div class="ch-faq__header fade-up">
		<div class="ch-section-tag">Questions?</div>
		<h2 class="ch-section-title">Common <span class="accent">Queries</span></h2>
		<p class="ch-section-body">Everything you need to know about our fresh sugarcane juice and services.</p>
	</div>

	<div class="ch-faq-grid fade-up" role="list">
		<?php foreach ( $faqs as $i => $faq ) :
			$faq = (array) $faq;
		?>
			<div class="ch-faq-item<?php echo $i === 0 ? ' active' : ''; ?>" role="listitem">
				<button class="ch-faq-question" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>">
					<?php echo esc_html( $faq['question'] ?? '' ); ?>
					<div class="ch-faq-icon" aria-hidden="true">+</div>
				</button>
				<div class="ch-faq-answer">
					<p><?php echo esc_html( $faq['answer'] ?? '' ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
