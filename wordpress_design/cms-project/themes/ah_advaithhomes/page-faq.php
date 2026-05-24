<?php
/**
 * Template Name: FAQ Page
 */
get_header();

$all_faqs = ah_get_faqs( 100 );

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

<?php get_template_part( 'components/page-header', null, [
  'eyebrow'    => 'Frequently Asked Questions',
  'title'      => 'Your Questions,',
  'title_em'   => 'Answered Honestly',
  'desc'       => 'Everything you need to know about working with a buyer\'s agent - how we work, what we cost, and what you can expect at every step.',
  'breadcrumb' => [
    [ 'Home', home_url( '/' ) ],
    [ 'FAQ',  '' ],
  ],
] ); ?>



<!-- ── FAQ Groups ────────────────────────────────────────────────────────── -->
<section class="section" aria-label="<?php echo esc_attr( TXT_ALL_FAQS ); ?>">
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
            <span class="faq__icon" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="2 4 6 8 10 4"/></svg></span>
          </button>
          <div class="faq__a" role="region">
            <div class="faq__a-inner"><?php echo wp_kses_post( $faq->answer ); ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>

    <?php endif; ?>
  </div>
</section>

<?php
get_template_part( 'components/cta-section', null, [] );

get_footer();
