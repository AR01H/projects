<?php
/**
 * Template Name: Buying Advice Hub
 */
get_header(); 

$guides_raw = get_option('ah_buying_guides_json', "");
$lines = explode("\n", $guides_raw);
$guides = [];
foreach ($lines as $line) {
    $parts = explode('|', $line);
    if (count($parts) >= 2) {
        $guides[] = [
            'icon'  => trim($parts[0]),
            'title' => trim($parts[1]),
            'desc'  => isset($parts[2]) ? trim($parts[2]) : '',
            'url'   => isset($parts[3]) ? trim($parts[3]) : '#'
        ];
    }
}

// Default items if empty (MoveIQ style)
if (empty($guides)) {
    $guides = [
        ['icon' => '🔍', 'title' => 'Property Research', 'desc' => 'How to research a property like an expert.', 'url' => '#'],
        ['icon' => '⚖️', 'title' => 'Legal Search', 'desc' => 'Understanding the paperwork and legalities.', 'url' => '#'],
        ['icon' => '💰', 'title' => 'Deposit Guide', 'desc' => 'How much you need and how to save it.', 'url' => '#'],
        ['icon' => '🏦', 'title' => 'Mortgage Guide', 'desc' => 'Navigating rates, lenders, and brokers.', 'url' => '#'],
        ['icon' => '📋', 'title' => 'Buyer\'s Guide', 'desc' => 'A step-by-step walk through the process.', 'url' => '#'],
        ['icon' => '🏗️', 'title' => 'New Build vs Period', 'desc' => 'Deciding between modern and classic.', 'url' => '#'],
    ];
}
?>

<main class="main-content" style="background: #f8fafc; padding-top: 140px; padding-bottom: 100px;">
    <div class="container" style="max-width: 1200px;">
        <!-- Hub Header -->
        <div style="margin-bottom: 60px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                <a href="<?php echo home_url('/'); ?>" style="color: #94a3b8; text-decoration: none; font-size: 0.85rem;">Home</a>
                <span style="color: #cbd5e1;">/</span>
                <span style="color: #64748b; font-size: 0.85rem; font-weight: 600;">Buying Advice</span>
            </div>
            <h1 style="font-family: var(--font-display); font-size: clamp(2.5rem, 5vw, 4rem); color: #0f172a; margin: 0;">Buying Advice & Guides</h1>
            <p style="color: #64748b; font-size: 1.2rem; max-width: 700px; margin-top: 20px; line-height: 1.6;">
                Everything you need to know about the home-buying process in the UK. From finding your dream home to signing the contracts.
            </p>
        </div>

        <!-- Guides Grid (MoveIQ Category Style) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 40px;">
            <?php foreach ($guides as $guide) : ?>
                <a href="<?php echo esc_url($guide['url']); ?>" style="text-decoration: none; display: flex; align-items: flex-start; gap: 25px; background: white; padding: 35px; border-radius: 24px; box-shadow: var(--shadow-md); border: 1px solid #f1f5f9; transition: all 0.3s ease;" class="guide-card">
                    <div style="background: #f1f5f9; width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                        <?php echo $guide['icon']; ?>
                    </div>
                    <div>
                        <h2 style="font-family: var(--font-display); font-size: 1.8rem; color: #0f172a; margin-bottom: 10px; line-height: 1.2;"><?php echo esc_html($guide['title']); ?></h2>
                        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin: 0;"><?php echo esc_html($guide['desc']); ?></p>
                        <div style="margin-top: 15px; color: var(--accent); font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                            Read Guide <span>→</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Bottom CTA (MoveIQ Style) -->
        <div style="margin-top: 80px; background: #0f172a; border-radius: 32px; padding: 60px; text-align: center; color: white;">
            <h2 style="font-family: var(--font-display); font-size: 2.5rem; margin-bottom: 20px;">Need personalized advice?</h2>
            <p style="color: #94a3b8; font-size: 1.1rem; max-width: 500px; margin: 0 auto 30px;">Our expert buyer agents are ready to help you navigate the complex UK property market.</p>
            <a href="<?php echo home_url('/contact'); ?>" class="button button--primary" style="background: var(--accent); border: none; padding: 15px 40px;">Book a Free Consultation</a>
        </div>
    </div>
</main>

<style>
.guide-card:hover {
    transform: translateY(-5px);
    border-color: var(--accent);
    box-shadow: var(--shadow-lg);
}
</style>

<?php get_footer(); ?>
