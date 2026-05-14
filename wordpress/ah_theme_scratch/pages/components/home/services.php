<?php
// Query the 'service' CPT
$services_query = new WP_Query([
    'post_type' => 'service',
    'posts_per_page' => 6,
    'order' => 'ASC',
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => 'display_status',
            'value' => 'Active',
            'compare' => '='
        ],
        [
            'key' => 'display_status',
            'compare' => 'NOT EXISTS'
        ]
    ]
]);
?>
<section class="feature-section">
    <div class="container">
      <div style="text-align: center; margin-bottom: 60px;">
        <div class="eyebrow reveal" style="color:var(--accent); justify-content: center; margin-bottom: 15px;">Our
          Expertise
        </div>
        <h2 class="reveal reveal-delay-1" style="font-size: clamp(1.8rem, 4vw, 2.8rem);">Find Your Dream Property with
          <span style="color:var(--accent)">ADVAITH HOMES</span>
        </h2>
      </div>

      <div class="feature-grid">
        <?php if ($services_query->have_posts()) : ?>
            <?php $delay = 1; ?>
            <?php while ($services_query->have_posts()) : $services_query->the_post(); ?>
                <?php 
                    $image_url = get_post_meta(get_the_ID(), 'image_url', true);
                    $delay_class = 'reveal-delay-' . ($delay % 3 == 0 ? 3 : $delay % 3);
                ?>
                <div class="feature-card reveal <?php echo esc_attr($delay_class); ?>">
                  <div class="feature-card__img-wrap">
                    <?php if ($image_url) : ?>
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" class="feature-card__img">
                    <?php else: ?>
                        <?php the_post_thumbnail('medium_large', ['class' => 'feature-card__img']); ?>
                    <?php endif; ?>
                  </div>
                  <h3 class="feature-card__title"><?php the_title(); ?></h3>
                  <p class="feature-card__desc"><?php echo wp_strip_all_tags(get_the_content()); ?></p>
                </div>
                <?php $delay++; ?>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else: ?>
            <p>No services found.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
