<?php
defined( 'ABSPATH' ) || exit;

$posts = ah_get_posts( 6 );

if ( empty( $posts ) ) {
	$posts = [
		[ 'title' => __( 'The Complete First-Time Buyer\'s Guide 2024', 'ah-theme' ),     'category' => __( 'Buying Guide', 'ah-theme' ), 'read_time' => '12 min', 'slug' => 'first-time-buyers-guide',     'image_id' => 0, 'img_url' => ah_unsplash( '1560518883-ce09059eeffa', 600, 400 ) ],
		[ 'title' => __( 'How to Buy Off-Market Properties in the UK', 'ah-theme' ),      'category' => __( 'Strategy', 'ah-theme' ),     'read_time' => '8 min',  'slug' => 'off-market-properties-uk',     'image_id' => 0, 'img_url' => ah_unsplash( '1573497019940-1c28c88b4f3e', 600, 400 ) ],
		[ 'title' => __( 'Stamp Duty 2024: What Buyers Need to Know', 'ah-theme' ),       'category' => __( 'Finance', 'ah-theme' ),      'read_time' => '6 min',  'slug' => 'stamp-duty-2024',              'image_id' => 0, 'img_url' => ah_unsplash( '1450101499163-c8848c66ca85', 600, 400 ) ],
		[ 'title' => __( 'Negotiating House Prices: Proven Tactics', 'ah-theme' ),        'category' => __( 'Negotiation', 'ah-theme' ),  'read_time' => '10 min', 'slug' => 'house-price-negotiation-tactics', 'image_id' => 0, 'img_url' => ah_unsplash( '1589829545856-d10d557cf95f', 600, 400 ) ],
		[ 'title' => __( 'Survey Types Explained: Which Do You Need?', 'ah-theme' ),      'category' => __( 'Legal', 'ah-theme' ),        'read_time' => '7 min',  'slug' => 'property-survey-types',        'image_id' => 0, 'img_url' => ah_unsplash( '1560520653-9e0e4c89eb11', 600, 400 ) ],
		[ 'title' => __( 'Buy-to-Let in 2024: Is It Still Worth It?', 'ah-theme' ),      'category' => __( 'Investment', 'ah-theme' ),   'read_time' => '9 min',  'slug' => 'buy-to-let-2024',              'image_id' => 0, 'img_url' => ah_unsplash( '1600596542815-ffad4c1539a9', 600, 400 ) ],
	];
}
?>
<section class="section" id="resources-section">
  <div class="container">
    <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:48px">
      <div>
        <div class="eyebrow reveal"><?php esc_html_e( 'Free Resources', 'ah-theme' ); ?></div>
        <h2 class="reveal reveal-delay-1" style="margin:0"><?php esc_html_e( 'Buying Guides & Expert Articles', 'ah-theme' ); ?></h2>
      </div>
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-outline btn--arrow reveal reveal-delay-2">
        <?php esc_html_e( 'All Articles', 'ah-theme' ); ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </a>
    </div>

    <div class="resources-grid">
      <?php foreach ( $posts as $i => $post ) :
        $title    = ah_val( $post, 'title' );
        $category = ah_val( $post, 'category' );
        $read     = ah_val( $post, 'read_time' );
        $slug     = ah_val( $post, 'slug' );
        $img_id   = ah_val( $post, 'image_id', 0 );
        $img      = $img_id ? ah_media_url( $img_id ) : ah_val( $post, 'img_url', ah_unsplash( '1560518883-ce09059eeffa', 600, 400 ) );
        $url      = $slug ? get_permalink( get_page_by_path( $slug, OBJECT, 'post' ) ) : home_url( '/guides/' );
        $delay    = [ '', 'reveal-delay-1', 'reveal-delay-2', '', 'reveal-delay-1', 'reveal-delay-2' ][ $i % 6 ];
      ?>
        <article class="resource-card reveal <?php echo esc_attr( $delay ); ?>">
          <a href="<?php echo esc_url( $url ); ?>" class="resource-card__img-wrap" tabindex="-1" aria-hidden="true">
            <img src="<?php echo esc_url( $img ); ?>"
                 alt="<?php echo esc_attr( $title ); ?>"
                 loading="lazy"
                 class="resource-card__img">
            <div class="resource-card__overlay">
              <span class="resource-card__cat"><?php echo esc_html( $category ); ?></span>
            </div>
          </a>
          <div class="resource-card__body">
            <div class="resource-card__meta">
              <span class="resource-card__cat-tag"><?php echo esc_html( $category ); ?></span>
              <?php if ( $read ) : ?>
                <span class="resource-card__read">⏱ <?php echo esc_html( $read ); ?></span>
              <?php endif; ?>
            </div>
            <h3 class="resource-card__title">
              <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
            </h3>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
