<?php
/**
 * Featured Properties Component
 * Dynamic 3D Carousel fetching data from 'property' CPT.
 */

$property_query = new WP_Query([
    'post_type' => 'property',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'display_status',
            'value' => 'Active',
            'compare' => '='
        ],
        [
            'key' => 'display_status',
            'compare' => 'NOT EXISTS' // Handle legacy items
        ]
    ]
]);
?>

<section class="section carousel-section">
    <div class="container">
      <div style="text-align:center;max-width:640px;margin:0 auto 40px">
        <div class="eyebrow reveal">Exclusive Portfolio</div>
        <h2 class="reveal reveal-delay-1">Our Featured Properties</h2>
        <p class="reveal reveal-delay-2">A selection of premium homes we have successfully secured for our clients.</p>
      </div>

      <div class="coverflow-container reveal reveal-delay-2">
        <button class="coverflow-btn prev" aria-label="Previous property">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
        </button>

        <div class="coverflow-slider">
          <?php if ($property_query->have_posts()) : while ($property_query->have_posts()) : $property_query->the_post(); 
            $price = get_post_meta(get_the_ID(), 'price', true);
            $location = get_post_meta(get_the_ID(), 'location', true);
            $saved = get_post_meta(get_the_ID(), 'saved', true);
            $image_url = get_post_meta(get_the_ID(), 'image_url', true);
            
            if (!$image_url && has_post_thumbnail()) {
                $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
            }
          ?>
            <div class="coverflow-item">
              <?php if ($image_url) : ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>">
              <?php else: ?>
                <div style="width: 100%; height: 100%; background: var(--slate-100); display: flex; align-items: center; justify-content: center;">🏠</div>
              <?php endif; ?>
              
              <div class="coverflow-item-content">
                <div class="stats-bar">
                  <span><?php echo esc_html($price); ?></span>
                  <span><?php echo esc_html($location); ?></span>
                  <span><?php echo esc_html($saved); ?></span>
                </div>
                <div class="property-hover-title" style="position: absolute; bottom: 50px; left: 20px; color: white; font-weight: 700; font-size: 1.2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                    <?php the_title(); ?>
                </div>
              </div>
            </div>
          <?php endwhile; wp_reset_postdata(); else: ?>
            <p>No featured properties found. Please add them in the admin.</p>
          <?php endif; ?>
        </div>

        <button class="coverflow-btn next" aria-label="Next property">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </div>
    </div>
</section>
