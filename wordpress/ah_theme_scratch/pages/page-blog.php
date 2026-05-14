<?php
/**
 * Template Name: Blog Page Component
 * Description: Modular component to display a grid of blog posts and quick news links.
 */

$page_eyebrow = "Blog & Insights";
$page_title = "Latest Housing Market Updates";
$page_desc = "Stay ahead of the curve with our expert analysis on UK property trends, mortgage rates, and negotiation strategies.";

$query = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 12,
    'post_status' => 'publish'
]);

// Fetch Quick News (Sidebar)
$quick_news_raw = get_option('ah_quick_news_json', '');
$quick_news_lines = explode("\n", $quick_news_raw);
$quick_news = [];
foreach ($quick_news_lines as $line) {
    $parts = explode('|', $line);
    if (count($parts) >= 2) {
        $quick_news[] = [
            'date' => trim($parts[0]),
            'headline' => trim($parts[1]),
            'url' => isset($parts[2]) ? trim($parts[2]) : '#',
            'source' => isset($parts[3]) ? trim($parts[3]) : 'Market Update'
        ];
    }
}
?>

<div class="container" style="padding-top: 40px; padding-bottom: 80px;">
    <div style="max-width: 800px; margin: 0 auto 60px; text-align: center;">
        <div class="eyebrow reveal" style="color:var(--gold-600)"><?php echo esc_html($page_eyebrow); ?></div>
        <h1 class="reveal reveal-delay-1" style="margin-bottom: 20px; font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; letter-spacing: -0.02em;">
            <?php echo esc_html($page_title); ?>
        </h1>
        <p class="reveal reveal-delay-2" style="font-size: 1.1rem; line-height: 1.8; color: var(--slate-700);">
            <?php echo esc_html($page_desc); ?>
        </p>
    </div>

    <!-- 2-Way Layout -->
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 60px;">
        
        <!-- Way 1: Main Blog Posts -->
        <div>
            <h2 style="margin-bottom: 30px; font-size: 1.8rem; border-bottom: 2px solid var(--border); padding-bottom: 10px;">Expert Analysis & Articles</h2>
            <div class="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); 
                    $pid = get_the_ID();
                    $post_type = get_post_meta($pid, 'ah_post_type', true);
                    $mini_info = get_post_meta($pid, 'ah_mini_info', true);
                    $style = get_post_meta($pid, 'ah_card_style', true) ?: 'standard';
                    $color = get_post_meta($pid, 'ah_tag_color', true) ?: '#0f172a';
                    $external_url = get_post_meta($pid, 'ah_external_url', true);
                    $new_tab = get_post_meta($pid, 'ah_open_new_tab', true);
                    $link = $external_url ?: get_permalink();
                    $target = $new_tab ? 'target="_blank" rel="noopener"' : '';

                    if ($style === 'mini') : ?>
                        <!-- MINI HINT CARD -->
                        <article class="blog-card reveal" style="background: white; border-radius: 12px; padding: 20px; border-left: 4px solid <?php echo $color; ?>; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: center;">
                            <div style="font-size: 10px; font-weight: 800; color: <?php echo $color; ?>; text-transform: uppercase; margin-bottom: 8px;">💡 Expert Hint</div>
                            <h3 style="font-size: 1.1rem; margin-bottom: 10px; line-height: 1.3;"><a href="<?php echo esc_url($link); ?>" style="color:var(--slate-900); text-decoration:none;"><?php the_title(); ?></a></h3>
                            <p style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.5; margin: 0;"><?php echo $mini_info ?: wp_trim_words(get_the_content(), 12); ?></p>
                        </article>
                    <?php else : ?>
                        <!-- STANDARD BLOG CARD -->
                        <article class="blog-card reveal" style="background: white; border-radius: 15px; overflow: hidden; border: 1px solid var(--border); display: flex; flex-direction: column;">
                            <a href="<?php echo esc_url($link); ?>" <?php echo $target; ?> style="height: 200px; display: block; overflow: hidden; position: relative;">
                                <?php if ($post_type === 'news') : ?>
                                    <span style="position: absolute; top: 15px; left: 15px; background: #ef4444; color: white; padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 800; z-index: 10;">🚨 BREAKING NEWS</span>
                                <?php endif; ?>
                                <?php if (has_post_thumbnail()) : the_post_thumbnail('medium_large', ['style' => 'width:100%; height:100%; object-fit:cover;']); else: ?>
                                    <div style="width:100%; height:100%; background:#f5f5f5; display:flex; align-items:center; justify-content:center;">✍️</div>
                                <?php endif; ?>
                            </a>
                            <div style="padding: 24px; flex: 1; display: flex; flex-direction: column;">
                                <div style="font-size: 0.75rem; color: var(--slate-500); margin-bottom: 10px;"><?php echo get_the_date(); ?></div>
                                <h3 style="font-size: 1.25rem; margin-bottom: 12px; line-height: 1.4;"><?php the_title(); ?></h3>
                                
                                <p style="font-size: 0.95rem; color: var(--slate-600); line-height: 1.6; margin-bottom: 20px; flex: 1;">
                                    <?php echo $mini_info ? esc_html($mini_info) : wp_trim_words(get_the_content(), 15); ?>
                                </p>
                                
                                <a href="<?php echo esc_url($link); ?>" <?php echo $target; ?> style="color: var(--slate-900); font-weight: 700; font-size: 0.9rem; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                    <?php echo $external_url ? 'View Full Story ↗' : 'Read Article →'; ?>
                                </a>
                            </div>
                        </article>
                    <?php endif; ?>
                <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>
        </div>

        <!-- Way 2: Quick Market News (Sidebar) -->
        <aside>
            <h2 style="margin-bottom: 30px; font-size: 1.8rem; border-bottom: 2px solid var(--border); padding-bottom: 10px;">Market News</h2>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($quick_news as $news) : ?>
                    <div class="reveal" style="padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                        <div style="font-size: 0.7rem; color: var(--slate-500); margin-bottom: 5px;"><?php echo esc_html($news['date']); ?> • <?php echo esc_html($news['source']); ?></div>
                        <h4 style="margin: 0 0 8px; font-size: 0.95rem; line-height: 1.4;">
                            <a href="<?php echo esc_url($news['url']); ?>" target="_blank" style="color: var(--slate-900); text-decoration: none; transition: color 0.2s;">
                                <?php echo esc_html($news['headline']); ?>
                            </a>
                        </h4>
                        <a href="<?php echo esc_url($news['url']); ?>" target="_blank" style="font-size: 0.8rem; color: var(--client-color-600); font-weight: 600;">Full Story ↗</a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($quick_news)) : ?><p style="font-size: 0.9rem; color: var(--slate-500);">No quick news updates at this time.</p><?php endif; ?>
            </div>
        </aside>

    </div>
</div>

<?php get_template_part('pages/components/home/cta'); ?>
