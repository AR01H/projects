<?php
/**
 * Template Name: FAQs
 */
defined( 'ABSPATH' ) || exit;
get_header();

$faqs     = ch_get_faqs( '', 200 );
$settings = ch_get_settings();
$phone    = $settings['phone'] ?? CONTACT_NUMBER;

// Group by topic (preserve order of first appearance)
$grouped = [];
foreach ( $faqs as $faq ) {
	$faq   = (array) $faq;
	$topic = $faq['topic'] ?? 'General';
	if ( $topic === '' ) $topic = 'General';
	$grouped[ $topic ][] = $faq;
}
?>

<main class="ch-main" id="main-content">

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<section class="ch-page-hero">
	<div class="container">
		<div class="fade-up" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
			<div class="section-tag">Got Questions?</div>
			<h1 class="ch-page-hero__title">Frequently Asked <em>Questions</em></h1>
			<p class="ch-page-hero__desc">Everything you need to know about The Cane House - our juice, events, hire, and more.</p>
		</div>
	</div>
</section>

<!-- ── FAQ groups ───────────────────────────────────────────────────────────── -->
<section style="background:var(--ch-green-bg);padding:5rem 2rem;">
	<div class="container">
		<?php if ( empty( $grouped ) ) : ?>
			<p style="text-align:center;color:var(--ch-text-muted);">No FAQs available yet. Please check back soon.</p>
		<?php else : ?>
			<?php foreach ( $grouped as $topic => $items ) : ?>
				<div class="ch-faqpage-group fade-up">
					<h2 class="ch-faqpage-topic"><?php echo esc_html( $topic ); ?></h2>
					<div class="ch-faq-grid" role="list">
						<?php foreach ( $items as $i => $faq ) :
							$faq = (array) $faq;
						?>
							<div class="ch-faq-item" role="listitem">
								<button class="ch-faq-question" aria-expanded="false">
									<?php echo esc_html( $faq['question'] ?? '' ); ?>
									<div class="ch-faq-icon" aria-hidden="true">+</div>
								</button>
								<div class="ch-faq-answer">
									<p><?php echo esc_html( $faq['answer'] ?? '' ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>

<!-- ── CTA ──────────────────────────────────────────────────────────────────── -->
<section class="ch-inner-cta">
	<div class="container">
		<div class="ch-inner-cta__box fade-up">
			<h2>Still Have a Question?</h2>
			<p>Can't find what you're looking for? Get in touch and we'll be happy to help.</p>
			<div class="ch-inner-cta__btns">
				<a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn-lime">📩 Contact Us</a>
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" class="btn-outline ch-btn-outline-light"><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

</main>
<?php get_footer(); ?>
