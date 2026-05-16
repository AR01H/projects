<?php
defined( 'ABSPATH' ) || exit;

$cards   = ah_static_why_us_cards();
$consult = ah_get_settings()['consultation_url'] ?? home_url( '/free-consultation/' );
?>
<section class="section" id="why-us-section">
  <div class="container">
    <div style="text-align:center;max-width:700px;margin:0 auto">
      <div class="eyebrow reveal"><?php esc_html_e( 'Why You Need Us', 'ah-theme' ); ?></div>
      <h2 class="reveal reveal-delay-1"><?php esc_html_e( 'The Home Buying System Is Stacked Against You', 'ah-theme' ); ?></h2>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( "Buying a home is one of the most significant decisions you'll ever make. Having professional support can truly transform the experience — an expert provides objective insights, helps you avoid emotional decisions and common pitfalls, and gives you access to exclusive opportunities.", 'ah-theme' ); ?>
      </p>
    </div>

    <div class="why-grid">
      <?php
      $delays = [ '', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3' ];
      foreach ( $cards as $i => $card ) :
        $delay = $delays[ $i % count($delays) ];
      ?>
        <div class="why-card reveal <?php echo esc_attr( $delay ); ?>">
          <div class="why-card__icon"><?php echo esc_html( $card['icon'] ); ?></div>
          <h4><?php echo esc_html( $card['title'] ); ?></h4>
          <p><?php echo esc_html( $card['text'] ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="reveal" style="text-align:center;margin-top:40px">
      <a href="<?php echo esc_url( home_url( '/why-us/' ) ); ?>" class="btn btn-primary btn--arrow">
        <?php esc_html_e( 'Learn More', 'ah-theme' ); ?>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </a>
    </div>
  </div>
</section>
