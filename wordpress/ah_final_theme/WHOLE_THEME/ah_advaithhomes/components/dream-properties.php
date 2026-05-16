<?php
defined( 'ABSPATH' ) || exit;

$cards = [
	[
		'icon'  => '🏡',
		'tag'   => __( 'First-Time Buyers', 'ah-theme' ),
		'title' => __( 'Making Your First Purchase Stress-Free', 'ah-theme' ),
		'text'  => __( 'We guide you through every step — from mortgage-in-principle to keys in hand. No jargon, no surprises, just expert support when you need it most.', 'ah-theme' ),
		'img'   => ah_unsplash( '1560520653-9e0e4c89eb11', 600, 400 ),
		'link'  => home_url( '/first-time-buyers/' ),
	],
	[
		'icon'  => '🔑',
		'tag'   => __( 'Home Movers', 'ah-theme' ),
		'title' => __( 'Upgrade Without the Stress of the Open Market', 'ah-theme' ),
		'text'  => __( 'Moving up the ladder? We find you the right property, negotiate a sharp price, and coordinate around your sale — so both sides complete smoothly.', 'ah-theme' ),
		'img'   => ah_unsplash( '1573497019940-1c28c88b4f3e', 600, 400 ),
		'link'  => home_url( '/home-movers/' ),
	],
	[
		'icon'  => '📈',
		'tag'   => __( 'Property Investors', 'ah-theme' ),
		'title' => __( 'Data-Driven Acquisitions That Perform', 'ah-theme' ),
		'text'  => __( 'Buy-to-let, HMO, or portfolio growth — we source high-yield properties off-market, run the numbers, and negotiate hard so your investment starts in profit.', 'ah-theme' ),
		'img'   => ah_unsplash( '1450101499163-c8848c66ca85', 600, 400 ),
		'link'  => home_url( '/property-investors/' ),
	],
];
?>
<section class="section dream-section">
  <div class="container">
    <div style="text-align:center;max-width:640px;margin:0 auto 56px">
      <div class="eyebrow reveal" style="color:var(--gold-400)"><?php esc_html_e( 'Who We Help', 'ah-theme' ); ?></div>
      <h2 class="reveal reveal-delay-1" style="color:#fff">
        <?php esc_html_e( 'We Find Your Dream Property — Whatever Stage You\'re At', 'ah-theme' ); ?>
      </h2>
    </div>

    <div class="dream-grid">
      <?php foreach ( $cards as $i => $card ) :
        $delay = [ '', 'reveal-delay-1', 'reveal-delay-2' ][ $i ];
      ?>
        <div class="dream-card reveal <?php echo esc_attr( $delay ); ?>">
          <div class="dream-card__img-wrap">
            <img src="<?php echo esc_url( $card['img'] ); ?>"
                 alt="<?php echo esc_attr( $card['tag'] ); ?>"
                 loading="lazy"
                 class="dream-card__img">
          </div>
          <div class="dream-card__body">
            <div class="dream-card__tag">
              <span><?php echo esc_html( $card['icon'] ); ?></span>
              <?php echo esc_html( $card['tag'] ); ?>
            </div>
            <h3 class="dream-card__title"><?php echo esc_html( $card['title'] ); ?></h3>
            <p class="dream-card__text"><?php echo esc_html( $card['text'] ); ?></p>
            <a href="<?php echo esc_url( $card['link'] ); ?>" class="btn btn-outline-gold btn--arrow">
              <?php esc_html_e( 'Learn More', 'ah-theme' ); ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
