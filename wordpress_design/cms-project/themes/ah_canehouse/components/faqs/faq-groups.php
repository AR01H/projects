<?php
defined( 'ABSPATH' ) || exit;
$grouped = $args['grouped'] ?? [];
?>
<section style="padding:2rem;">
  <div class="container">
    <?php if ( empty( $grouped ) ) : ?>
      <p style="text-align:center;color:var(--client-color-15-muted);">No FAQs available yet. Please check back soon.</p>
    <?php else : ?>
      <?php foreach ( $grouped as $topic => $items ) : ?>
      <div class="ch-faqpage-group fade-up">
        <h2 class="ch-faqpage-topic"><?php echo esc_html( $topic ); ?></h2>
        <div class="ch-faq-grid" role="list">
          <?php foreach ( $items as $faq ) :
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
