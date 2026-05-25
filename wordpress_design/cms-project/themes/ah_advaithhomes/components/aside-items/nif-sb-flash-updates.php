<?php
/**
 * Component: NIF Sidebar - Flash Updates
 *
 * @var array $args {
 *   @type array $news_bar_items  From ah_get_news_bar_items().
 * }
 */
defined( 'ABSPATH' ) || exit;

$news_bar_items = $args['news_bar_items'] ?? [];

if ( empty( $news_bar_items ) ) {
	return;
}
?>
<div class="nif-sb-card nif-sb-card--flash" aria-label="<?php echo esc_attr( TXT_LATEST_NEWS ); ?>">
  <div class="nif-sb-card__header nif-sb-card__header--row">
    <span class="nif-section-label--primary" style="color:var(--important);font-weight:bolder;"><?php echo esc_html( TXT_LATEST_NEWS ); ?></span>
    <a href="<?php echo esc_url( home_url( '/allnews/' ) ); ?>" class="nif-sb-more-link" >
      <?php echo esc_html( TXT_SEE_ALL ); ?> <span aria-hidden="true">→</span>
    </a>
  </div>
  <ul class="nif-sb-flash">
    <?php foreach ( $news_bar_items as $item ) :
      $item   = is_object( $item ) ? (array) $item : $item;
      $text   = $item['text']        ?? '';
      $url    = $item['link_url']    ?? '';
      $target = $item['link_target'] ?? '_self';
      if ( ! $text ) continue;
    ?>
    <li class="nif-sb-flash__item">
      <?php if ( $url ) : ?>
        <a href="<?php echo esc_url( $url ); ?>"
           class="nif-sb-flash__link"
           target="<?php echo esc_attr( $target ); ?>"
           <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
          <span class="nif-sb-flash__dot" aria-hidden="true"></span>
          <?php echo esc_html( $text ); ?>
        </a>
      <?php else : ?>
        <span class="nif-sb-flash__text">
          <span class="nif-sb-flash__dot" aria-hidden="true"></span>
          <?php echo esc_html( $text ); ?>
        </span>
      <?php endif; ?>
    </li>
    <?php endforeach; ?>
  </ul>
</div>