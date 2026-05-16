<?php
defined( 'ABSPATH' ) || exit;

$faqs = ah_get_faqs();

if ( empty( $faqs ) ) {
	$faqs = [
		[ 'question' => __( 'What exactly does a buying agent do?', 'ah-theme' ),                'answer' => __( 'A buying agent works exclusively for you — the buyer. We search for properties (including off-market ones), assess them objectively, negotiate hard on price, and guide you through surveys, legals, and completion. Unlike an estate agent, our interests are fully aligned with yours.', 'ah-theme' ) ],
		[ 'question' => __( 'How much does Advaith Homes charge?', 'ah-theme' ),                 'answer' => __( 'We typically charge a small retainer to begin the search, then a success fee on completion — usually 1–1.5% of the purchase price. Most clients save far more than our fee through lower negotiated prices and avoided mistakes. Book a free discovery call for a tailored quote.', 'ah-theme' ) ],
		[ 'question' => __( 'Do you work with first-time buyers?', 'ah-theme' ),                 'answer' => __( 'Absolutely. First-time buyers are among our most valued clients — the system is most confusing when you have no prior experience. We explain everything in plain English, hold your hand through every step, and make sure you never feel out of your depth.', 'ah-theme' ) ],
		[ 'question' => __( 'Can you find properties not listed on Rightmove or Zoopla?', 'ah-theme' ), 'answer' => __( 'Yes — this is one of our biggest advantages. We have relationships with local estate agents, developers, and landlords that give us access to off-market opportunities before they reach public portals. Often these are better value with less competition.', 'ah-theme' ) ],
		[ 'question' => __( 'How long does the property search take?', 'ah-theme' ),             'answer' => __( 'It varies, but most clients find their ideal property within 4–12 weeks. We move at your pace, and won\'t rush you into a purchase that doesn\'t fully meet your brief. Some clients find the right home in days; others take a few months — quality matters more than speed.', 'ah-theme' ) ],
		[ 'question' => __( 'Do I need to be in the UK for viewings?', 'ah-theme' ),            'answer' => __( 'Not necessarily. We regularly work with overseas buyers and expats relocating to the UK. We can conduct viewings on your behalf, provide detailed video walkthroughs, and give you our honest assessment — so you only need to travel for properties we\'re confident about.', 'ah-theme' ) ],
		[ 'question' => __( 'What areas do you cover?', 'ah-theme' ),                           'answer' => __( 'We primarily operate across London, the Home Counties, and major English cities. We have a strong network in Surrey, Richmond, Wimbledon, Bath, Bristol, and Birmingham. Contact us to discuss your target area — if we can\'t help directly, we\'ll refer you to someone who can.', 'ah-theme' ) ],
	];
}
?>
<section class="section faq-section" id="faqs-section">
  <div class="container">
    <div style="max-width:760px;margin:0 auto">
      <div style="text-align:center;margin-bottom:48px">
        <div class="eyebrow reveal"><?php esc_html_e( 'FAQ', 'ah-theme' ); ?></div>
        <h2 class="reveal reveal-delay-1"><?php esc_html_e( 'Common Questions Answered', 'ah-theme' ); ?></h2>
        <p class="reveal reveal-delay-2">
          <?php esc_html_e( 'Everything you need to know about working with a buying agent.', 'ah-theme' ); ?>
        </p>
      </div>

      <div class="faq-list reveal reveal-delay-2" id="ahFaqList">
        <?php foreach ( $faqs as $i => $faq ) :
          $q = ah_val( $faq, 'question' );
          $a = ah_val( $faq, 'answer' );
        ?>
          <div class="faq-item" data-faq-index="<?php echo $i; ?>">
            <button class="faq-trigger" aria-expanded="false" aria-controls="faq-answer-<?php echo $i; ?>">
              <span><?php echo esc_html( $q ); ?></span>
              <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>
            <div class="faq-answer" id="faq-answer-<?php echo $i; ?>" role="region">
              <div class="faq-answer__inner">
                <p><?php echo esc_html( $a ); ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="reveal" style="text-align:center;margin-top:40px">
        <p style="color:var(--text-muted);margin-bottom:16px">
          <?php esc_html_e( 'Still have questions?', 'ah-theme' ); ?>
        </p>
        <a href="<?php echo esc_url( home_url( '/free-consultation/' ) ); ?>" class="btn btn-primary">
          <?php esc_html_e( 'Book a Free Discovery Call', 'ah-theme' ); ?>
        </a>
      </div>
    </div>
  </div>
</section>
