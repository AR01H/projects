<?php
/**
 * Template Name: FAQ Page
 */
get_header();

$all_faqs = ah_get_faqs( '', 100 );

// Group by topic
$grouped = [];
foreach ( $all_faqs as $faq ) {
	$topic = $faq->topic ?? 'General';
	$grouped[ $topic ][] = $faq;
}

$topic_icons = [
	'buying'      => '🏠',
	'finance'     => '💰',
	'legal'       => '⚖️',
	'process'     => '📋',
	'contact'     => '📞',
	'general'     => '❓',
];
?>

<!-- ── Page Hero ─────────────────────────────────────────────────────────── -->
<section class="page-hero page-hero--sm" aria-label="FAQ">
  <div class="container">
    <div class="page-hero__copy text-center" style="max-width:640px;margin-inline:auto" data-aos="fade-up">
      <span class="section__eyebrow">Frequently Asked Questions</span>
      <h1 class="page-hero__title">Your Questions,<br><em>Answered Honestly</em></h1>
      <p class="page-hero__desc">
        Everything you need to know about working with a buyer's agent — how we work,
        what we cost, and what you can expect at every step.
      </p>
    </div>
  </div>
</section>

<!-- ── FAQ Groups ────────────────────────────────────────────────────────── -->
<section class="section" aria-label="All FAQs">
  <div class="container container--md">

    <?php if ( empty( $grouped ) ) : ?>
      <p class="text-center" style="color:var(--text-muted)">No FAQs published yet.</p>
    <?php else : ?>

      <?php foreach ( $grouped as $topic => $faqs ) :
        $icon = $topic_icons[ strtolower( $topic ) ] ?? '❓';
      ?>
      <div style="margin-bottom:48px" data-aos="fade-up">
        <h2 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:10px">
          <span><?php echo esc_html( $icon ); ?></span>
          <?php echo esc_html( ucfirst( $topic ) ); ?>
        </h2>
        <?php foreach ( $faqs as $i => $faq ) : ?>
        <div class="faq" data-aos="fade-up" data-delay="<?php echo min( $i * 40, 240 ); ?>">
          <button class="faq__q" aria-expanded="false">
            <?php echo esc_html( $faq->question ); ?>
            <span class="faq__icon" aria-hidden="true">+</span>
          </button>
          <div class="faq__a" role="region">
            <div class="faq__a-inner"><?php echo wp_kses_post( $faq->answer ); ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>

    <?php endif; ?>

    <!-- Still have a question? -->
    <div class="text-center" style="margin-top:40px;padding:40px;background:var(--bg-alt);border-radius:var(--r-xl);border:1px solid var(--border)" data-aos="fade-up">
      <div style="font-size:2rem;margin-bottom:12px">💬</div>
      <h3 style="font-family:var(--font-display);font-size:1.3rem;font-weight:700;margin-bottom:8px">
        Still have a question?
      </h3>
      <p style="color:var(--text-secondary);font-size:.9rem;margin-bottom:20px">
        We're happy to answer anything — no obligation, no sales pressure.
      </p>
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary">
        Ask Us Directly →
      </a>
    </div>
  </div>
</section>

<?php
get_template_part( 'components/cta-section', null, [
  'title'     => 'Ready to Start?<br><em>Let\'s Talk.</em>',
  'desc'      => 'Book a free consultation. No obligation, no pressure — just straight answers about how we can help you buy smarter.',
  'cta_label' => 'Book a Free Call →',
  'cta_url'   => home_url( '/contact/' ),
  'sec_label' => 'Browse Our Guides',
  'sec_url'   => home_url( '/guides/' ),
] );

get_footer();
