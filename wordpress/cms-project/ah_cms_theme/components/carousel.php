<?php
defined( 'ABSPATH' ) || exit;

$stories = ah_get_client_stories( 6 );

// Fallback property data
if ( empty( $stories ) ) {
	$stories = [
		[ 'title' => 'Surrey Estate',    'location' => 'Surrey',    'price' => '£1.2M', 'saving' => 'Saved £45k', 'image_id' => 0, 'img_url' => ah_unsplash('1600596542815-ffad4c1539a9', 800, 600) ],
		[ 'title' => 'Richmond Retreat', 'location' => 'Richmond',  'price' => '£850k',  'saving' => 'Saved £20k', 'image_id' => 0, 'img_url' => ah_unsplash('1600585154340-be6161a56a0c', 800, 600) ],
		[ 'title' => 'London Penthouse', 'location' => 'London',    'price' => '£1.5M',  'saving' => 'Saved £55k', 'image_id' => 0, 'img_url' => ah_unsplash('1600566753190-17f0baa2a6c3', 800, 600) ],
		[ 'title' => 'Cornwall Cottage', 'location' => 'Cornwall',  'price' => '£950k',  'saving' => 'Saved £30k', 'image_id' => 0, 'img_url' => ah_unsplash('1600047509807-ba8f99d2cdde', 800, 600) ],
	];
}
?>
<section class="section carousel-section">
  <div class="container">
    <div style="text-align:center;max-width:640px;margin:0 auto 40px">
      <div class="eyebrow reveal" style="color:var(--gold-600)"><?php esc_html_e( 'Exclusive Portfolio', 'ah-theme' ); ?></div>
      <h2 class="reveal reveal-delay-1"><?php esc_html_e( 'Our Featured Properties', 'ah-theme' ); ?></h2>
      <p class="reveal reveal-delay-2"><?php esc_html_e( 'A selection of premium homes we have successfully secured for our clients.', 'ah-theme' ); ?></p>
    </div>

    <div class="coverflow-container reveal reveal-delay-2">
      <button class="coverflow-btn prev" aria-label="<?php esc_attr_e( 'Previous property', 'ah-theme' ); ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
      </button>

      <div class="coverflow-slider">
        <?php foreach ( $stories as $story ) :
          $loc    = is_object($story) ? ($story->location ?? '') : ($story['location'] ?? '');
          $price  = is_object($story) ? ($story->price ?? '') : ($story['price'] ?? '');
          $saving = is_object($story) ? ($story->saving ?? '') : ($story['saving'] ?? '');
          $img_id = is_object($story) ? ($story->image_id ?? 0) : ($story['image_id'] ?? 0);
          $img    = $img_id ? ah_media_url($img_id) : ($story['img_url'] ?? ah_unsplash('1600596542815-ffad4c1539a9'));
          $title  = is_object($story) ? ($story->title ?? '') : ($story['title'] ?? '');
        ?>
          <div class="coverflow-item">
            <img src="<?php echo esc_url( $img ); ?>"
                 alt="<?php echo esc_attr( $title ); ?>"
                 loading="lazy">
            <div class="coverflow-item-content">
              <div class="stats-bar">
                <span><?php echo esc_html( $price ); ?></span>
                <span><?php echo esc_html( $loc ); ?></span>
                <span><?php echo esc_html( $saving ); ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <button class="coverflow-btn next" aria-label="<?php esc_attr_e( 'Next property', 'ah-theme' ); ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </button>
    </div>
  </div>
</section>
