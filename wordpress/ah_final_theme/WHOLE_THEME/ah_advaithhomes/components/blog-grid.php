<?php
defined( 'ABSPATH' ) || exit;

// Used on dedicated blog listing pages — honours query pagination
$paged    = max( 1, get_query_var( 'paged' ) );
$per_page = 9;
$posts    = ah_get_posts( $per_page, $paged );
$total    = 0;

if ( empty( $posts ) ) {
	$posts = [
		[ 'title' => __( 'The Complete First-Time Buyer\'s Guide 2024', 'ah-theme' ), 'category' => __( 'Buying Guide', 'ah-theme' ), 'excerpt' => __( 'Everything you need to know about buying your first home — from saving your deposit to collecting the keys.', 'ah-theme' ), 'date' => '12 Jan 2024', 'read_time' => '12 min', 'slug' => 'first-time-buyers-guide',      'image_id' => 0, 'img_url' => ah_unsplash( '1560518883-ce09059eeffa', 600, 400 ) ],
		[ 'title' => __( 'How to Buy Off-Market Properties in the UK', 'ah-theme' ),  'category' => __( 'Strategy', 'ah-theme' ),     'excerpt' => __( 'Off-market deals can mean less competition and better prices. Here\'s how buying agents source them.', 'ah-theme' ),          'date' => '5 Feb 2024',  'read_time' => '8 min',  'slug' => 'off-market-properties-uk',      'image_id' => 0, 'img_url' => ah_unsplash( '1573497019940-1c28c88b4f3e', 600, 400 ) ],
		[ 'title' => __( 'Stamp Duty 2024: What Buyers Need to Know', 'ah-theme' ),  'category' => __( 'Finance', 'ah-theme' ),      'excerpt' => __( 'Stamp duty thresholds changed in April 2024. Here\'s what that means for your purchase and how to plan.', 'ah-theme' ),       'date' => '20 Feb 2024', 'read_time' => '6 min',  'slug' => 'stamp-duty-2024',               'image_id' => 0, 'img_url' => ah_unsplash( '1450101499163-c8848c66ca85', 600, 400 ) ],
		[ 'title' => __( 'Negotiating House Prices: Proven Tactics', 'ah-theme' ),   'category' => __( 'Negotiation', 'ah-theme' ),  'excerpt' => __( 'Most buyers leave money on the table. Here\'s how professional negotiators consistently save 5–10%.', 'ah-theme' ),         'date' => '1 Mar 2024',  'read_time' => '10 min', 'slug' => 'house-price-negotiation-tactics', 'image_id' => 0, 'img_url' => ah_unsplash( '1589829545856-d10d557cf95f', 600, 400 ) ],
		[ 'title' => __( 'Survey Types Explained: Which Do You Need?', 'ah-theme' ), 'category' => __( 'Legal', 'ah-theme' ),        'excerpt' => __( 'RICS Condition Report vs HomeBuyer Report vs Building Survey — the differences matter more than you think.', 'ah-theme' ),   'date' => '14 Mar 2024', 'read_time' => '7 min',  'slug' => 'property-survey-types',         'image_id' => 0, 'img_url' => ah_unsplash( '1560520653-9e0e4c89eb11', 600, 400 ) ],
		[ 'title' => __( 'Buy-to-Let in 2024: Is It Still Worth It?', 'ah-theme' ), 'category' => __( 'Investment', 'ah-theme' ),   'excerpt' => __( 'Rising mortgage costs and tighter regulations have changed the buy-to-let landscape. Here\'s the honest picture.', 'ah-theme' ), 'date' => '3 Apr 2024',  'read_time' => '9 min',  'slug' => 'buy-to-let-2024',               'image_id' => 0, 'img_url' => ah_unsplash( '1600596542815-ffad4c1539a9', 600, 400 ) ],
	];
}
?>
<div class="blog-grid" id="ahBlogGrid">
  <?php foreach ( $posts as $i => $post ) :
    $title    = ah_val( $post, 'title' );
    $category = ah_val( $post, 'category' );
    $excerpt  = ah_val( $post, 'excerpt' );
    $raw_date = ah_field( $post, 'created_at', ah_field( $post, 'date', '' ) );
    $date     = $raw_date ? date_i18n( 'j M Y', strtotime( (string) $raw_date ) ) : '';
    $read     = ah_val( $post, 'read_time' );
    $slug     = ah_val( $post, 'slug' );
    $img_id   = ah_field( $post, 'featured_image_id', ah_field( $post, 'image_id', 0 ) );
    $img      = $img_id ? ah_media_url( (int) $img_id ) : ah_val( $post, 'img_url', ah_unsplash( '1560518883-ce09059eeffa', 600, 400 ) );
    $url      = $slug ? home_url( '/blog/' . $slug . '/' ) : home_url( '/blog/' );
    $delay    = [ '', 'reveal-delay-1', 'reveal-delay-2' ][ $i % 3 ];
  ?>
    <article class="blog-card reveal <?php echo esc_attr( $delay ); ?>">
      <a href="<?php echo esc_url( $url ); ?>" class="blog-card__img-wrap" tabindex="-1">
        <img src="<?php echo esc_url( $img ); ?>"
             alt="<?php echo esc_attr( $title ); ?>"
             loading="lazy"
             class="blog-card__img">
        <span class="blog-card__cat"><?php echo esc_html( $category ); ?></span>
      </a>
      <div class="blog-card__body">
        <div class="blog-card__meta">
          <?php if ( $date ) : ?>
            <time class="blog-card__date"><?php echo esc_html( $date ); ?></time>
          <?php endif; ?>
          <?php if ( $read ) : ?>
            <span class="blog-card__read">⏱ <?php echo esc_html( $read ); ?></span>
          <?php endif; ?>
        </div>
        <h3 class="blog-card__title">
          <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
        </h3>
        <?php if ( $excerpt ) : ?>
          <p class="blog-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
        <?php endif; ?>
        <a href="<?php echo esc_url( $url ); ?>" class="blog-card__link">
          <?php esc_html_e( 'Read Article', 'ah-theme' ); ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<?php ah_pagination( $total, $per_page, $paged ); ?>
