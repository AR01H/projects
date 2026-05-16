<?php
defined( 'ABSPATH' ) || exit;

$reviews = ah_get_reviews( 8 );

if ( empty( $reviews ) ) {
	$reviews = [
		[ 'name' => 'Sarah & Tom Mitchell',    'location' => 'Bought in Richmond',          'rating' => 5, 'text' => __( 'Advaith Homes found us the perfect family home off-market. We saved £28,000 against the asking price and they handled everything — surveys, solicitors, the lot. Couldn\'t recommend them more highly.', 'ah-theme' ),      'image_id' => 0, 'initials' => 'SM' ],
		[ 'name' => 'Priya Sharma',            'location' => 'First-time buyer, Surrey',     'rating' => 5, 'text' => __( 'As a first-time buyer I was terrified. The team held my hand through every step, explained everything in plain English, and got me a price £15k under asking. I felt truly looked after.', 'ah-theme' ),         'image_id' => 0, 'initials' => 'PS' ],
		[ 'name' => 'James Okafor',            'location' => 'Buy-to-let investor, London',  'rating' => 5, 'text' => __( 'I\'ve bought three investment properties through Advaith Homes. Each one has been sourced off-market with better yields than anything on Rightmove. The data-driven approach is genuinely impressive.', 'ah-theme' ),  'image_id' => 0, 'initials' => 'JO' ],
		[ 'name' => 'The Henderson Family',    'location' => 'Moved from Bristol to Bath',   'rating' => 5, 'text' => __( 'Relocating with three kids and a tight timeline — Advaith Homes made the impossible happen. They found us a school-catchment home in Bath within 6 weeks, well under our budget.', 'ah-theme' ),               'image_id' => 0, 'initials' => 'HF' ],
		[ 'name' => 'Ananya Krishnamurthy',    'location' => 'Bought in Wimbledon',          'rating' => 5, 'text' => __( 'The negotiation alone was worth every penny of their fee. They knocked 8% off the asking price using comparable data we never would have known to look for. Exceptional service.', 'ah-theme' ),             'image_id' => 0, 'initials' => 'AK' ],
		[ 'name' => 'David & Claire Parsons', 'location' => 'Upsized in Guildford',         'rating' => 5, 'text' => __( 'We\'d been searching for 18 months on our own. Advaith Homes found us the right property in 3 weeks — off-market, under budget, and exactly what we wanted. Wish we\'d called them sooner.', 'ah-theme' ),   'image_id' => 0, 'initials' => 'DP' ],
	];
}
?>
<section class="section testimonials-section" id="reviews-section">
  <div class="container">
    <div style="text-align:center;max-width:640px;margin:0 auto 48px">
      <div class="eyebrow reveal" style="color:var(--gold-600)"><?php esc_html_e( 'Client Stories', 'ah-theme' ); ?></div>
      <h2 class="reveal reveal-delay-1"><?php esc_html_e( 'What Our Clients Say', 'ah-theme' ); ?></h2>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( 'Real buyers. Real savings. Real stories of finding the right home with the right support.', 'ah-theme' ); ?>
      </p>
    </div>

    <div class="testimonials-slider reveal reveal-delay-2" id="ahTestimonialsSlider">
      <div class="testimonials-track" id="ahTestimonialsTrack">
        <?php foreach ( $reviews as $review ) :
          $name     = ah_val( $review, 'name' );
          $location = ah_val( $review, 'location' );
          $rating   = (int) ah_val( $review, 'rating', 5 );
          $text     = ah_val( $review, 'text' );
          $img_id   = ah_val( $review, 'image_id', 0 );
          $img      = $img_id ? ah_media_url( $img_id ) : '';
          $initials = ah_val( $review, 'initials', strtoupper( substr( $name, 0, 2 ) ) );
        ?>
          <div class="testimonial-card">
            <div class="testimonial-stars">
              <?php echo str_repeat( '★', max( 1, min( 5, $rating ) ) ); ?>
            </div>
            <blockquote class="testimonial-text">
              "<?php echo esc_html( $text ); ?>"
            </blockquote>
            <div class="testimonial-author">
              <div class="testimonial-avatar">
                <?php if ( $img ) : ?>
                  <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
                <?php else : ?>
                  <span class="testimonial-avatar__initials"><?php echo esc_html( $initials ); ?></span>
                <?php endif; ?>
              </div>
              <div>
                <div class="testimonial-name"><?php echo esc_html( $name ); ?></div>
                <div class="testimonial-location">📍 <?php echo esc_html( $location ); ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="testimonials-controls">
        <button class="testimonial-btn testimonial-btn--prev" id="ahTestPrev" aria-label="<?php esc_attr_e( 'Previous review', 'ah-theme' ); ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <div class="testimonials-dots" id="ahTestDots"></div>
        <button class="testimonial-btn testimonial-btn--next" id="ahTestNext" aria-label="<?php esc_attr_e( 'Next review', 'ah-theme' ); ?>">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    </div>

    <div class="reveal" style="text-align:center;margin-top:40px">
      <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="btn btn-outline btn--arrow">
        <?php esc_html_e( 'Read All Stories', 'ah-theme' ); ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </a>
    </div>
  </div>
</section>
