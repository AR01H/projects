<?php get_header(); ?>

<main class="main-content" style="background: #f8fafc; padding-top: 140px; padding-bottom: 80px;">
    <div class="container">
        <div style="max-width: 850px; margin: 0 auto;">
            
            <!-- Article Header -->
            <div style="text-align: center; margin-bottom: 50px;">
                <div style="margin-bottom: 20px;">
                    <span style="background: #fee2e2; color: #991b1b; padding: 5px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Breaking</span>
                    <span style="color: #64748b; font-size: 0.85rem; margin-left: 15px;"><?php echo get_the_date(); ?></span>
                </div>
                <h1 style="font-size: clamp(2rem, 4vw, 3rem); line-height: 1.2; margin-bottom: 25px; color: #0f172a; letter-spacing: -0.02em;">
                    <?php the_title(); ?>
                </h1>
            </div>

            <!-- Main Content Card -->
            <div style="background: white; border-radius: 24px; padding: 50px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
                
                <?php if (has_post_thumbnail()) : ?>
                    <div style="margin-bottom: 40px; border-radius: 16px; overflow: hidden;">
                        <?php the_post_thumbnail('full', ['style' => 'width: 100%; height: auto; display: block;']); ?>
                    </div>
                <?php endif; ?>

                <div class="prose" style="font-size: 1.15rem; line-height: 1.8; color: #334155;">
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; endif; ?>
                </div>

                <!-- Shared CTA -->
                <div style="margin-top: 60px; padding-top: 40px; border-top: 1px solid #f1f5f9; text-align: center;">
                    <p style="font-weight: 600; margin-bottom: 20px;">Want to discuss how this affects you?</p>
                    <a href="<?php echo home_url('/contact'); ?>" class="btn btn--primary">Book a free call</a>
                </div>
            </div>

            <!-- Back Link -->
            <div style="text-align: center; margin-top: 40px;">
                <a href="<?php echo home_url('/blog'); ?>" style="color: #64748b; text-decoration: none; font-weight: 600; font-size: 0.95rem;">← Back to Insights</a>
            </div>

        </div>
    </div>

    <!-- RELATED PODCASTS SECTION -->
    <?php 
    $podcast_query = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => [get_the_ID()],
        'meta_query' => [['key' => 'ah_card_style', 'value' => 'podcast']]
    ]);

    if ($podcast_query->have_posts()) : ?>
    <div style="margin-top: 80px; padding: 80px 0; background: white; border-top: 1px solid #f1f5f9;">
        <div class="container" style="max-width: 1100px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 50px;">
                <span style="background: var(--accent-light); color: var(--accent); padding: 5px 15px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;">Listen & Learn</span>
                <h2 style="font-family: var(--font-display); font-size: 2.8rem; margin-top: 15px; color: #0f172a;">Related Podcasts & Insights</h2>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
                <?php while ($podcast_query->have_posts()) : $podcast_query->the_post(); 
                    $p_tag = get_post_meta(get_the_ID(), 'ah_tag_text', true) ?: 'Expert Insight';
                    $p_color = get_post_meta(get_the_ID(), 'ah_tag_color', true) ?: '#8b5cf6';
                    $p_summary = get_post_meta(get_the_ID(), 'ah_mini_info', true);
                    $p_ref = get_post_meta(get_the_ID(), 'ah_episode_id', true);
                ?>
                    <div style="background: #f8fafc; border-radius: 24px; padding: 35px; border: 1px solid #f1f5f9; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                            <span style="background: <?php echo $p_color; ?>15; color: <?php echo $p_color; ?>; padding: 6px 14px; border-radius: 8px; font-size: 10px; font-weight: 800;"><?php echo esc_html($p_tag); ?></span>
                            <?php if ($p_ref) : ?><span style="font-size: 11px; color: #94a3b8; font-weight: 600;"><?php echo esc_html($p_ref); ?></span><?php endif; ?>
                        </div>
                        <h3 style="font-family: var(--font-display); font-size: 1.5rem; line-height: 1.3; margin-bottom: 15px;"><?php the_title(); ?></h3>
                        <p style="font-size: 0.95rem; color: #64748b; line-height: 1.6; margin-bottom: 25px;"><?php echo wp_trim_words($p_summary, 12); ?></p>
                        <a href="<?php the_permalink(); ?>" style="display: inline-block; width: 100%; text-align: center; padding: 14px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; color: #1e293b; font-weight: 700; font-size: 0.9rem; text-decoration: none;">View Episode</a>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>