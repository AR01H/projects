<?php
/**
 * Template Name: FAQs
 */
defined( 'ABSPATH' ) || exit;
get_header();

$faqs     = ch_get_faqs( '', 200 );
$settings = ch_get_settings();
$phone    = $settings['phone'] ?? CONTACT_NUMBER;
$_hero    = CH_Shared_Data::section_heading( 'page_hero_faqs' );

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

<!-- ── Hero (Reusable Component) ────────────────────────────────────────────── -->
<?php get_template_part( 'components/page-hero', null, [
	'tag'      => $_hero['tag']     ?? '',
	'heading'  => $_hero['heading'] ?? '',
	'desc'     => $_hero['desc']    ?? '',
	'modifier' => 'ch-page-hero--sugarcane',
] ); ?>

<!-- ── FAQ groups ───────────────────────────────────────────────────────────── -->
<section style="padding:2rem;">
	<div class="container">
		<?php if ( empty( $grouped ) ) : ?>
			<p style="text-align:center;color:var(--client-color-15-muted);">No FAQs available yet. Please check back soon.</p>
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
<?php
get_template_part( 'components/contact-section' );
?>


</main>
<?php get_footer(); ?>
