<?php
defined( 'ABSPATH' ) || exit;
$services = $args['services'] ?? [];
$faqs     = $args['faqs']     ?? [];
$team     = $args['team']     ?? [];
$reviews  = $args['reviews']  ?? [];

$atlas_service_url = static function ( $service ): string {
	$slug = sanitize_title( $service->slug ?? '' );
	return $slug !== '' ? home_url( '/services/' . $slug . '/' ) : home_url( '/services/' );
};
?>
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_STRUCTURED_CONTENT ); ?>">
  <div class="container">
    <div class="section__header">
      <span class="section__eyebrow">Structured Content</span>
      <h2 class="section__title">Services, FAQs, People, and Social Proof</h2>
    </div>
    <div class="atlas-two-col">
      <div class="atlas-card" data-aos="fade-up">
        <h3>Services</h3>
        <ul class="atlas-list">
          <?php if ( $services ) : foreach ( $services as $service ) :
            $service_url = $atlas_service_url( $service );
          ?>
            <li>
              <strong><a href="<?php echo esc_url( $service_url ); ?>"><?php echo esc_html( $service->title ?? '' ); ?></a></strong>
              <?php if ( ! empty( $service->slug ) ) : ?>
                <div class="atlas-muted"><a href="<?php echo esc_url( $service_url ); ?>">/<?php echo esc_html( trim( (string) $service->slug, '/' ) ); ?>/</a></div>
              <?php endif; ?>
              <div class="atlas-muted"><?php echo esc_html( $service->short_desc ?? $service->summary ?? '' ); ?></div>
            </li>
          <?php endforeach; else : ?>
            <li class="atlas-muted">No services found.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="atlas-card" data-aos="fade-up" data-delay="100">
        <h3>FAQs</h3>
        <ul class="atlas-list">
          <?php if ( $faqs ) : foreach ( $faqs as $faq ) : ?>
            <li>
              <strong><?php echo esc_html( $faq->question ?? '' ); ?></strong>
              <div class="atlas-muted"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $faq->answer ?? '' ), 20 ) ); ?></div>
            </li>
          <?php endforeach; else : ?>
            <li class="atlas-muted">No FAQs found.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="atlas-card" data-aos="fade-up">
        <h3>Team</h3>
        <ul class="atlas-list">
          <?php if ( $team ) : foreach ( $team as $member ) : ?>
            <li>
              <strong><?php echo esc_html( $member->name ?? '' ); ?></strong>
              <div class="atlas-muted"><?php echo esc_html( $member->role ?? '' ); ?></div>
            </li>
          <?php endforeach; else : ?>
            <li class="atlas-muted">No team members found.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="atlas-card" data-aos="fade-up" data-delay="100">
        <h3>Reviews</h3>
        <ul class="atlas-list">
          <?php if ( $reviews ) : foreach ( $reviews as $review ) : ?>
            <li>
              <strong><?php echo esc_html( $review->reviewer_name ?? '' ); ?></strong>
              <div class="atlas-muted"><?php echo esc_html( $review->reviewer_title ?? '' ); ?></div>
              <div class="atlas-muted"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $review->review_text ?? '' ), 18 ) ); ?></div>
            </li>
          <?php endforeach; else : ?>
            <li class="atlas-muted">No reviews found.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</section>
