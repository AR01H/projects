<?php
/**
 * Template Name: Services Page Component
 * Description: The modular component for the Services page with hardcoded variables.
 */

$page_eyebrow = "Our Services";
$page_title = "Expert Guidance from Search to Completion";
$page_desc = "We provide end-to-end property buying services tailored to your exact needs, ensuring you save time, money, and stress at every step.";

// Reusing the services custom post type we created!
$services_query = new WP_Query([
    'post_type' => 'service',
    'posts_per_page' => -1,
    'order' => 'ASC'
]);
?>

<div class="container">
    <div style="max-width: 800px; margin: 0 auto 60px; text-align: center;">
        <div class="eyebrow reveal" style="color:var(--gold-600)"><?php echo esc_html($page_eyebrow); ?></div>
        <h1 class="reveal reveal-delay-1" style="margin-bottom: 20px; font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; letter-spacing: -0.02em;">
            <?php echo esc_html($page_title); ?>
        </h1>
        <p class="reveal reveal-delay-2" style="font-size: 1.1rem; line-height: 1.8; color: var(--slate-700);">
            <?php echo esc_html($page_desc); ?>
        </p>
    </div>

    <div class="services-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 60px;">
        <?php if ($services_query->have_posts()) : while ($services_query->have_posts()) : $services_query->the_post(); 
            $image_url = get_post_meta(get_the_ID(), 'image_url', true);
        ?>
            <div class="service-card reveal">
                <?php if ($image_url) : ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 12px; margin-bottom: 20px;">
                <?php endif; ?>
                <h3 style="margin-bottom: 15px;"><?php the_title(); ?></h3>
                <p style="color: var(--slate-600); line-height: 1.6;"><?php echo wp_strip_all_tags(get_the_content()); ?></p>
            </div>
        <?php endwhile; wp_reset_postdata(); else: ?>
            <p>No services found in the database. Please add them in the WP Admin.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Reusing the CTA Component -->
<?php get_template_part('pages/components/home/cta'); ?>
