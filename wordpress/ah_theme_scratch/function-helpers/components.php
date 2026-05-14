<?php
/**
 * Component Helpers
 */

// News Ticker Shortcode
function ah_ticker_shortcode() {
    ob_start();
    // Use a specific template part for the ticker
    get_template_part('pages/components/header/news-ticker');
    return ob_get_clean();
}
add_shortcode('news_ticker', 'ah_ticker_shortcode');