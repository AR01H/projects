<?php
defined( 'ABSPATH' ) || exit;

$items = ah_get_news_bar_items();
if ( empty( $items ) ) return;

$all = array_merge( $items, $items );
?>
<div class="news-ticker" role="complementary" aria-label="<?php echo esc_attr( TXT_NEWS_UPDATES ); ?>">
  <div class="">
    <div class="news-ticker__inner">
      <a class="news-ticker__label" href="<?php echo esc_url( home_url( '/news/' ) ); ?>">News</a>
      <div class="news-ticker__track" aria-live="off">
        <div class="news-ticker__items">
          <?php foreach ( $all as $item ) :
            $text   = is_object( $item ) ? ( $item->text ?? '' ) : (string) $item;
            $url    = is_object( $item ) ? ( $item->link_url ?? '' ) : '';
            $target = is_object( $item ) ? ( $item->link_target ?? '_self' ) : '_self';
          ?>
            <?php if ( $url ) : ?>
              <a class="news-ticker__item news-ticker__item--link"
                 href="<?php echo esc_url( $url ); ?>"
                 target="<?php echo esc_attr( $target ); ?>"
                 <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
                <?php echo esc_html( $text ); ?>
              </a>
            <?php else : ?>
              <span class="news-ticker__item"><?php echo esc_html( $text ); ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
