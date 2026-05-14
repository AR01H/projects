<?php
/**
 * Template Name: Market News
 */
get_header(); ?>

<main class="main-content" style="background: #f8fafc; padding-top: 140px; padding-bottom: 100px;">
    <div class="container">
        <!-- News Header -->
        <div style="text-align: center; margin-bottom: 60px;">
            <span style="background: #fee2e2; color: #ef4444; padding: 6px 15px; border-radius: 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Market Intelligence</span>
            <h1 style="font-family: var(--font-display); font-size: clamp(2.5rem, 5vw, 4rem); color: #0f172a; margin-top: 20px;">Latest Breaking News</h1>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 20px auto 0;">Real-time updates on UK property laws, stamp duty, and market trends.</p>
        </div>

        <?php
        $news_query = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 12,
            'meta_query' => [
                [
                    'key' => 'ah_post_type',
                    'value' => 'news'
                ]
            ]
        ]);

        if ($news_query->have_posts()) : ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
                <?php while ($news_query->have_posts()) : $news_query->the_post(); 
                    $pid = get_the_ID();
                    $tag = get_post_meta($pid, 'ah_tag_text', true) ?: 'MARKET';
                    $color = get_post_meta($pid, 'ah_tag_color', true) ?: '#ef4444';
                    $summary = get_post_meta($pid, 'ah_mini_info', true);
                    $style = get_post_meta($pid, 'ah_card_style', true) ?: 'standard';

                    if ($style === 'mini') : ?>
                        <!-- MINI HINT CARD -->
                        <article style="background: white; border-radius: 16px; padding: 25px; border-left: 5px solid <?php echo $color; ?>; box-shadow: var(--shadow-sm); transition: all 0.3s ease;">
                            <div style="margin-bottom: 10px; font-size: 11px; font-weight: 800; color: <?php echo $color; ?>; text-transform: uppercase;">💡 Quick Hint</div>
                            <h2 style="font-family: var(--font-display); font-size: 1.3rem; margin-bottom: 10px; line-height: 1.2;"><a href="<?php the_permalink(); ?>" style="color:#0f172a; text-decoration:none;"><?php the_title(); ?></a></h2>
                            <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin: 0;"><?php echo wp_trim_words($summary, 15); ?></p>
                        </article>
                    <?php else : ?>
                        <!-- STANDARD NEWS CARD -->
                        <article style="background: white; border-radius: 24px; overflow: hidden; box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; display: flex; flex-direction: column; transition: transform 0.3s ease;">
                            <?php if (has_post_thumbnail()) : ?>
                                <div style="height: 200px; overflow: hidden;">
                                    <?php the_post_thumbnail('medium_large', ['style' => 'width: 100%; height: 100%; object-fit: cover;']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div style="padding: 30px; flex-grow: 1; display: flex; flex-direction: column;">
                                <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="background: <?php echo $color; ?>; color: white; padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 800; text-transform: uppercase;"><?php echo esc_html($tag); ?></span>
                                    <span style="color: #94a3b8; font-size: 12px; font-weight: 600;"><?php echo get_the_date(); ?></span>
                                </div>
                                
                                <h2 style="font-family: var(--font-display); font-size: 1.6rem; color: #0f172a; line-height: 1.3; margin-bottom: 15px;">
                                    <a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none;"><?php the_title(); ?></a>
                                </h2>
                                
                                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 25px;">
                                    <?php echo wp_trim_words($summary ?: get_the_excerpt(), 18); ?>
                                </p>
                                
                                <div style="margin-top: auto;">
                                    <a href="<?php the_permalink(); ?>" style="color: #ef4444; font-weight: 700; font-size: 0.9rem; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                                        Read Full Update <span>→</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endif; ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else : ?>
            <div style="text-align: center; padding: 100px 0;">
                <p style="color: #64748b; font-size: 1.2rem;">No market updates available at the moment. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
