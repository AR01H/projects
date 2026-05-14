<?php
/**
 * News Ticker Component
 * Dynamic news ticker fetching data from Theme Settings.
 */

$raw_ticker_items = get_option('ah_ticker_items', "MARKET: UK House Prices Rise 3.2% Year-on-Year | RATES: Bank of England Holds Base Rate at 4.5% | POLICY: Stamp Duty Changes 2025");

// Parse the items
$ticker_parts = explode('|', $raw_ticker_items);
$loop_items = [];

foreach ($ticker_parts as $part) {
    $part = trim($part);
    if (empty($part)) continue;
    
    $subparts = explode(':', $part, 2);
    $tag = count($subparts) > 1 ? trim($subparts[0]) : 'NEWS';
    $text = count($subparts) > 1 ? trim($subparts[1]) : trim($subparts[0]);
    
    $loop_items[] = [
        'tag' => $tag,
        'tag_class' => strtolower($tag),
        'text' => $text
    ];
}

// Duplicate items for seamless loop if needed
if (count($loop_items) < 6) {
    $loop_items = array_merge($loop_items, $loop_items);
}
?>

<div class="news-ticker" id="newsTicker">
    <div class="news-ticker__label">
        <span class="news-ticker__pulse"></span>
        LATEST UPDATES
    </div>
    <div class="news-ticker__track-wrap">
        <div class="news-ticker__track" id="newsTickerTrack">
            <?php foreach ($loop_items as $item) : ?>
                <a href="<?php echo esc_url(home_url('/blog')); ?>" class="news-ticker__item" style="display: flex; align-items: center; gap: 10px; color: white; font-size: 0.8rem; white-space: nowrap; text-decoration: none;">
                    <mark class="news-ticker__tag news-ticker__tag--<?php echo esc_attr($item['tag_class']); ?>">
                        <?php echo esc_html($item['tag']); ?>
                    </mark>
                    <?php echo esc_html($item['text']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>