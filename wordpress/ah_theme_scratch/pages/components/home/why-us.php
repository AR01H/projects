<?php
// We can query the 'benefit' CPT we seeded earlier
$benefits_query = new WP_Query([
    'post_type' => 'benefit',
    'posts_per_page' => -1,
    'order' => 'ASC'
]);

$why_title = get_option('ah_why_title', 'The Home Buying System Is Stacked Against You');
$why_desc = get_option('ah_why_desc', 'Buying a home is one of the most significant decisions you\'ll ever make...');
?>
<section class="section" id="why-us-section">
    <div class="container">
      <div style="text-align:center;max-width:700px;margin:0 auto">
        <div class="eyebrow reveal">Why You Need Us</div>
        <h2 class="reveal reveal-delay-1"><?php echo esc_html($why_title); ?></h2>
        <p class="reveal reveal-delay-2" id="why-us-intro"><?php echo wp_kses_post($why_desc); ?></p>
      </div>

      <div class="why-grid" id="why-grid">
        <?php if ($benefits_query->have_posts()) : ?>
            <?php $delay = 1; ?>
            <?php while ($benefits_query->have_posts()) : $benefits_query->the_post(); ?>
                <?php 
                    $icon = get_post_meta(get_the_ID(), 'icon', true) ?: '✓';
                    $delay_class = 'reveal-delay-' . ($delay % 3 == 0 ? 3 : $delay % 3);
                ?>
                <div class="why-card reveal <?php echo esc_attr($delay_class); ?>">
                    <div class="why-card__icon"><?php echo esc_html($icon); ?></div>
                    <h4><?php the_title(); ?></h4>
                    <p><?php echo wp_strip_all_tags(get_the_content()); ?></p>
                </div>
                <?php $delay++; ?>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php endif; ?>
      </div>

      <!-- Learn More CTA -->
      <div class="reveal" style="text-align:center;margin-top:40px">
        <a href="<?php echo esc_url(home_url('/why-us')); ?>" class="btn btn-primary reveal visible">
          <span>Learn More</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
        </a>
      </div>
    </div>
</section>
