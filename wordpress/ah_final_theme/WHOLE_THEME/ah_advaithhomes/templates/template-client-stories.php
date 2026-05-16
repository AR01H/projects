<?php
/**
 * Template Name: Client Stories
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$stories = ah_get_client_stories( 12 );

if ( empty( $stories ) ) {
	$stories = [
		[ 'title' => 'Surrey Estate',    'location' => 'Surrey',    'price' => '£1.2M',  'saving' => '£45,000', 'buyer_type' => 'Home Mover',          'excerpt' => 'We found this family a stunning 5-bed property 8% under asking — before it even hit Rightmove.', 'image_id' => 0, 'img_url' => ah_unsplash( '1600596542815-ffad4c1539a9', 800, 500 ) ],
		[ 'title' => 'Richmond Retreat', 'location' => 'Richmond',  'price' => '£850k',  'saving' => '£20,000', 'buyer_type' => 'First-Time Buyer',     'excerpt' => 'First-time buyer competing against 4 other offers. We negotiated on condition and completion time — won at asking but saved £20k in works.', 'image_id' => 0, 'img_url' => ah_unsplash( '1600585154340-be6161a56a0c', 800, 500 ) ],
		[ 'title' => 'London Penthouse', 'location' => 'Shoreditch', 'price' => '£1.5M', 'saving' => '£55,000', 'buyer_type' => 'Property Investor',    'excerpt' => 'Off-market acquisition sourced through our developer network. Listed at £1.55M; secured at £1.5M with 6-week completion.', 'image_id' => 0, 'img_url' => ah_unsplash( '1600566753190-17f0baa2a6c3', 800, 500 ) ],
		[ 'title' => 'Cornwall Cottage', 'location' => 'Cornwall',  'price' => '£950k',  'saving' => '£30,000', 'buyer_type' => 'Relocating',           'excerpt' => 'London professionals relocating to Cornwall. We assessed 11 properties remotely, shortlisted 3, and secured this one at 3% below asking.', 'image_id' => 0, 'img_url' => ah_unsplash( '1600047509807-ba8f99d2cdde', 800, 500 ) ],
	];
}
?>
<main id="main-content">

  <!-- Page Hero -->
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow reveal" style="color:var(--gold-600)"><?php esc_html_e( 'Proof It Works', 'ah-theme' ); ?></div>
      <h1 class="reveal reveal-delay-1"><?php esc_html_e( 'Client Stories', 'ah-theme' ); ?></h1>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( 'Real buyers, real savings, real homes. Here\'s what happens when an expert fights in your corner.', 'ah-theme' ); ?>
      </p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="stories-grid">
        <?php foreach ( $stories as $i => $story ) :
          $title     = ah_val( $story, 'title' );
          $location  = ah_val( $story, 'location' );
          $price     = ah_val( $story, 'price' );
          $saving    = ah_val( $story, 'saving' );
          $btype     = ah_val( $story, 'buyer_type' );
          $excerpt   = ah_val( $story, 'excerpt' );
          $img_id    = ah_val( $story, 'image_id', 0 );
          $img       = $img_id ? ah_media_url( $img_id ) : ah_val( $story, 'img_url', ah_unsplash( '1600596542815-ffad4c1539a9', 800, 500 ) );
          $delay     = [ '', 'reveal-delay-1', 'reveal-delay-2' ][ $i % 3 ];
        ?>
          <div class="story-card reveal <?php echo esc_attr( $delay ); ?>">
            <div class="story-card__img-wrap">
              <img src="<?php echo esc_url( $img ); ?>"
                   alt="<?php echo esc_attr( $title ); ?>"
                   loading="lazy"
                   class="story-card__img">
              <?php if ( $btype ) : ?>
                <span class="story-card__type"><?php echo esc_html( $btype ); ?></span>
              <?php endif; ?>
            </div>
            <div class="story-card__body">
              <div class="story-card__location">📍 <?php echo esc_html( $location ); ?></div>
              <h3 class="story-card__title"><?php echo esc_html( $title ); ?></h3>
              <?php if ( $excerpt ) : ?>
                <p class="story-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
              <?php endif; ?>
              <div class="story-card__stats">
                <?php if ( $price ) : ?>
                  <div class="story-stat">
                    <div class="story-stat__label"><?php esc_html_e( 'Secured At', 'ah-theme' ); ?></div>
                    <div class="story-stat__val"><?php echo esc_html( $price ); ?></div>
                  </div>
                <?php endif; ?>
                <?php if ( $saving ) : ?>
                  <div class="story-stat story-stat--green">
                    <div class="story-stat__label"><?php esc_html_e( 'Client Saved', 'ah-theme' ); ?></div>
                    <div class="story-stat__val">✓ <?php echo esc_html( $saving ); ?></div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php get_template_part( 'components/reviews' ); ?>
  <?php get_template_part( 'components/cta' ); ?>

</main>
<?php get_template_part( 'parts/footer' ); ?>
