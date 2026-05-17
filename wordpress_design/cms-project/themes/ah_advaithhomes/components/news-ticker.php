<?php
defined( 'ABSPATH' ) || exit;

$items = ah_get_news_bar_items();
if ( empty( $items ) ) return;

// Duplicate for seamless loop
$all = array_merge( $items, $items );
?>
<div class="news-ticker" role="complementary" aria-label="News updates">
  <div class="container">
    <div class="news-ticker__inner">
      <span class="news-ticker__label">News</span>
      <div class="news-ticker__track" aria-live="off">
        <div class="news-ticker__items">
          <?php foreach ( $all as $item ) : ?>
            <span class="news-ticker__item"><?php echo esc_html( $item ); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
