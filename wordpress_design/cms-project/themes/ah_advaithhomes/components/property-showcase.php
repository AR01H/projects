<?php
defined( 'ABSPATH' ) || exit;

$properties = ah_get_properties( 6 );
if ( empty( $properties ) ) return;

$gradients = [
	'linear-gradient(135deg,#1e293b,#334155)',
	'linear-gradient(135deg,#312e81,#1e1b4b)',
	'linear-gradient(135deg,#064e3b,#065f46)',
	'linear-gradient(135deg,#7c2d12,#92400e)',
	'linear-gradient(135deg,#1e3a5f,#1d4ed8)',
	'linear-gradient(135deg,#3b0764,#6d28d9)',
];
?>
<section class="section section--pattern" aria-label="Featured properties">
  <div class="container">
    <div class="section__header text-center" data-aos="fade-up">
      <span class="section__eyebrow">Recent Acquisitions</span>
      <h2 class="section__title">Properties We've Secured for Clients</h2>
      <p class="section__desc" style="margin-inline:auto">
        A snapshot of recent purchases - the locations, prices, and savings our team delivered.
      </p>
    </div>

    <div class="property-showcase-wrap" data-carousel-wrap>

      <div class="carousel-3d" id="propertyCarousel">
        <?php foreach ( $properties as $i => $prop ) :
          $prop = is_object( $prop ) ? (array) $prop : $prop;
          $grad = $gradients[ $i % count( $gradients ) ];
          $emoji = $prop['emoji'] ?? '🏡';
        ?>
        <div class="carousel-3d__slide" data-pos="<?php echo $i === 0 ? '0' : $i; ?>">
          <div class="property-card-3d">
            <div class="property-card-3d__img">
              <?php if ( ! empty( $prop['image_url'] ) ) : ?>
                <img src="<?php echo esc_url( $prop['image_url'] ); ?>" alt="<?php echo esc_attr( $prop['location'] ?? '' ); ?>">
              <?php else : ?>
                <div class="property-card-3d__img-placeholder" style="background:<?php echo esc_attr( $grad ); ?>">
                  <span style="font-size:4rem"><?php echo $emoji; ?></span>
                </div>
              <?php endif; ?>
              <div class="property-card-3d__overlay">
                <span class="property-card-3d__price"><?php echo esc_html( $prop['price'] ?? '' ); ?></span>
                <span class="property-card-3d__loc"><?php echo esc_html( $prop['location'] ?? '' ); ?></span>
                <span class="property-card-3d__saved"><?php echo esc_html( $prop['saved'] ?? '' ); ?></span>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="property-showcase-nav">
        <button class="carousel-nav-btn" data-carousel-prev aria-label="Previous property">←</button>
        <span style="font-size:.82rem;color:var(--text-muted)" data-carousel-counter>1 / <?php echo count( $properties ); ?></span>
        <button class="carousel-nav-btn" data-carousel-next aria-label="Next property">→</button>
      </div>

    </div>

    <!-- Active property detail (shown below carousel, updated by JS) -->
    <div style="margin-top:32px">
      <?php foreach ( $properties as $i => $prop ) :
        $prop = is_object( $prop ) ? (array) $prop : $prop;
      ?>
      <div data-carousel-detail style="<?php echo $i === 0 ? '' : 'display:none'; ?>">
        <div style="display:flex;align-items:center;justify-content:center;gap:24px;flex-wrap:wrap">
          <?php if ( ! empty( $prop['type'] ) ) : ?>
          <div style="text-align:center">
            <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted)">Type</div>
            <div style="font-size:1rem;font-weight:600;color:var(--text-primary)"><?php echo esc_html( $prop['type'] ); ?></div>
          </div>
          <?php endif; ?>
          <?php if ( ! empty( $prop['beds'] ) ) : ?>
          <div style="text-align:center">
            <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted)">Bedrooms</div>
            <div style="font-size:1rem;font-weight:600;color:var(--text-primary)"><?php echo esc_html( $prop['beds'] ); ?></div>
          </div>
          <?php endif; ?>
          <?php if ( ! empty( $prop['area'] ) ) : ?>
          <div style="text-align:center">
            <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted)">Area</div>
            <div style="font-size:1rem;font-weight:600;color:var(--text-primary)"><?php echo esc_html( $prop['area'] ); ?></div>
          </div>
          <?php endif; ?>
          <?php if ( ! empty( $prop['result'] ) ) : ?>
          <div style="text-align:center">
            <div style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted)">Result</div>
            <div style="font-size:.9rem;font-weight:600;color:var(--accent)"><?php echo esc_html( $prop['result'] ); ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top:32px">
      <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn-primary">
        Find My Property →
      </a>
    </div>
  </div>
</section>
