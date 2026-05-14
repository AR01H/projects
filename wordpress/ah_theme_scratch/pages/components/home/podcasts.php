<?php
/**
 * Podcast / Tips Section Component
 * Displays a horizontal list of rich-UI cards.
 */

$podcast_query = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 4,
    'meta_key' => 'ah_card_style',
    'meta_value' => 'podcast'
]);

if ($podcast_query->have_posts()) :
?>
<section class="section" style="background: #1e293b; color: white; padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Dotted Pattern Simulation -->
    <div style="position: absolute; top:0; left:0; width:100%; height:100%; opacity: 0.05; background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 30px 30px;"></div>

    <div class="container" style="position: relative; z-index: 2;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-size: 2.5rem; color: white;">Latest Insights & Podcasts</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 30px;">
            <?php while ($podcast_query->have_posts()) : $podcast_query->the_post(); 
                $tag = get_post_meta(get_the_ID(), 'ah_tag_text', true) ?: 'UPDATE';
                $color = get_post_meta(get_the_ID(), 'ah_tag_color', true) ?: '#8b5cf6';
                $eid = get_post_meta(get_the_ID(), 'ah_episode_id', true);
                $btn = get_post_meta(get_the_ID(), 'ah_btn_label', true) ?: 'Listen';
                $summary = get_post_meta(get_the_ID(), 'ah_mini_info', true);
                $link = get_post_meta(get_the_ID(), 'ah_external_url', true) ?: get_permalink();
            ?>
                <div class="reveal" style="background: white; color: #334155; border-radius: 20px; padding: 30px; position: relative; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                    
                    <!-- Top Bar -->
                    <div style="width: 100%; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                        <span style="background: <?php echo esc_attr($color); ?>; color: white; padding: 4px 12px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase;">
                            <?php echo esc_html($tag); ?>
                        </span>
                        <span style="font-size: 11px; font-weight: 700; color: #94a3b8; font-family: monospace;">
                            <?php echo esc_html($eid); ?>
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 style="font-size: 1.1rem; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; color: #0f172a;">
                        <?php the_title(); ?>
                    </h3>

                    <!-- Mini Info -->
                    <p style="font-size: 0.9rem; line-height: 1.6; color: #64748b; margin-bottom: 30px; flex: 1;">
                        <?php echo $summary ? esc_html($summary) : wp_trim_words(get_the_content(), 15); ?>
                    </p>

                    <!-- Button -->
                    <a href="<?php echo esc_url($link); ?>" class="btn" style="background: #f97316; color: white; padding: 10px 30px; border-radius: 30px; font-weight: 700; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 10px;">
                        <?php echo esc_html($btn); ?> →
                    </a>

                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
<?php endif; ?>
