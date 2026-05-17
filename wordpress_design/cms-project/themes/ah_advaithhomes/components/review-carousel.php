<?php
defined( 'ABSPATH' ) || exit;

$reviews = ah_get_reviews( 6 );
if ( empty( $reviews ) ) return;

$rating_num  = '4.9';
$client_stat = '500+';
foreach ( ah_get_site_stats() as $s ) {
	$s = is_object( $s ) ? (array) $s : $s;
	$n = $s['num'] ?? '';
	if ( strpos( $n, '★' ) !== false ) $rating_num  = rtrim( str_replace( '★', '', $n ) );
	if ( strpos( $n, '500' ) !== false ) $client_stat = $n;
}

$avatars = [ '👩‍💼', '👨‍💼', '👩‍🏫', '👨‍💻', '👩‍🔬', '👨‍🏗️' ];
?>
<section class="section" aria-label="Client success stories" style="background-color: var(--client-color-50);">
  <div class="container">
    <div class="section__header text-center" data-aos="fade-up">
      <span class="section__eyebrow">Client Stories</span>
      <h2 class="section__title">Explore Our Success Stories</h2>
      <p class="section__desc" style="margin-inline:auto">
        <?php echo esc_html( $client_stat ); ?>+ buyers have trusted us. Here's what they say.
      </p>
    </div>

    <div class="stories-carousel-wrap" data-carousel-wrap data-autoplay="5000">

      <!-- Left: 3D card stack -->
      <div class="stories-carousel__stage">
        <div class="carousel-3d" id="storiesCarousel">
          <?php foreach ( $reviews as $i => $rev ) :
            $rev = is_object( $rev ) ? $rev : (object) $rev;
            $avatar = $avatars[ $i % count( $avatars ) ];
          ?>
          <div class="carousel-3d__slide" data-pos="<?php echo $i === 0 ? '0' : $i; ?>">
            <div class="story-card">
              <div class="story-card__avatar">
                <?php if ( ! empty( $rev->photo_url ) ) : ?>
                  <img src="<?php echo esc_url( $rev->photo_url ); ?>" alt="<?php echo esc_attr( $rev->author_name ?? '' ); ?>">
                <?php else : ?>
                  <?php echo $avatar; ?>
                <?php endif; ?>
              </div>
              <p class="story-card__quote">"<?php echo esc_html( $rev->review_text ?? $rev->body ?? '' ); ?>"</p>
              <div class="story-card__name">
                <?php echo esc_html( $rev->author_name ?? $rev->name ?? 'Anonymous' ); ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right: Story detail panel -->
      <div class="stories-carousel__detail">
        <?php foreach ( $reviews as $i => $rev ) :
          $rev = is_object( $rev ) ? $rev : (object) $rev;
        ?>
        <div class="story-detail" data-carousel-detail>
          <div class="story-detail__location">
            <?php echo esc_html( $rev->location ?? 'United Kingdom' ); ?>
          </div>
          <h3 class="story-detail__title">
            <?php echo esc_html( $rev->author_name ?? 'Client Story' ); ?>
          </h3>
          <p class="story-detail__quote">
            "<?php echo esc_html( $rev->review_text ?? $rev->body ?? '' ); ?>"
          </p>
          <?php if ( ! empty( $rev->result ) ) : ?>
          <div>
            <div class="story-detail__author-name"><?php echo esc_html( $rev->author_name ?? '' ); ?></div>
            <div class="story-detail__result"><?php echo esc_html( $rev->result ); ?></div>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <div class="stories-carousel-nav">
          <button class="carousel-nav-btn" data-carousel-prev aria-label="Previous story">←</button>
          <span style="font-size:.82rem;color:var(--text-muted);text-align:center;display:flex;align-items: center;" data-carousel-counter>1 / <?php echo count( $reviews ); ?></span>
          <button class="carousel-nav-btn" data-carousel-next aria-label="Next story">→</button>
          <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="btn btn-outline btn-sm" style="margin-left:auto">
            All stories →
          </a>
        </div>
      </div>
    </div>

    <!-- Overall rating row -->
    <div style="display:flex;align-items:center;justify-content:center;gap:16px;margin-top:48px">
      <?php ah_stars( (float) $rating_num ); ?>
      <span style="font-size:1rem;font-weight:600"><?php echo esc_html( $rating_num ); ?> / 5</span>
      <span style="color:var(--text-muted);font-size:.875rem">from <?php echo esc_html( $client_stat ); ?> verified clients</span>
    </div>
  </div>
</section>
