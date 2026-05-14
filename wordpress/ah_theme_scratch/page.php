<?php get_header(); ?>

<main style="min-height: 70vh;">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        
        <?php 
        // Get the current page slug
        $post_slug = $post->post_name;
        
        // Check if a specific modular component exists for this page slug
        // E.g. pages/page-services.php
        $template_path = locate_template("pages/page-{$post_slug}.php");
        
        if ($template_path) {
            // Load the modular, variable-driven file for this specific page
            get_template_part("pages/page", $post_slug);
        } else {
            // Fallback generic styled page layout if no specific template exists
        ?>
            <div class="container">
                <div style="max-width: 800px; margin: 0 auto 60px;">
                    <div class="eyebrow reveal" style="color:var(--gold-600)">Advaith Homes</div>
                    <h1 class="reveal reveal-delay-1" style="margin-bottom: 30px; font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; letter-spacing: -0.02em;">
                        <?php the_title(); ?>
                    </h1>
                    <div class="page-content reveal reveal-delay-2" style="font-size: 1.1rem; line-height: 1.8; color: var(--slate-700);">
                        <?php 
                        if (empty(get_the_content())) {
                            echo "<p>This is a placeholder page for <strong>" . esc_html(get_the_title()) . "</strong>. You can edit this content from the WordPress Admin Dashboard, or create a developer template at <code>pages/page-{$post_slug}.php</code> to hardcode variables.</p>";
                        } else {
                            the_content(); 
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Standard Page CTA -->
            <?php get_template_part('pages/components/home/cta'); ?>
            
        <?php } ?>

    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>