<?php
/**
 * components/cards/spotlight_card.php
 * Spotlight card — left typographic area + right metric panel.
 * Props via $card array: icon, title, tag, meta (value), thumb_label, desc, url
 */
defined( 'ABSPATH' ) || exit;

$card  = isset( $card ) && is_array( $card ) ? $card : array();
$title = isset( $card['title'] ) ? (string) $card['title'] : '';
$meta  = isset( $card['meta'] )  ? (string) $card['meta']  : '';
$tag   = isset( $card['tag'] )   ? (string) $card['tag']   : '';
$icon  = isset( $card['icon'] )  ? (string) $card['icon']  : '';
$thumb_label = isset( $card['thumb_label'] ) ? (string) $card['thumb_label'] : '';
$desc  = isset( $card['desc'] ) ? (string) $card['desc'] : '';
$url   = ! empty( $card['url'] ) && '#' !== (string) $card['url'] ? esc_url( adn_link( (string) $card['url'] ) ) : '';

$el = $url ? 'a' : 'div';
$el_attr = $url ? ' href="' . $url . '"' : '';
?>
<<?php echo $el . $el_attr; ?> class="spotlight-card">
<div class="spotlight-card__left">
    <?php if ( '' !== $icon ) : ?>
      <div class="spotlight-card__icon" aria-hidden="true"><?php echo adn_icon( $icon ); ?></div>
    <?php endif; ?>
    <div class="spotlight-card__text">
      <div class="spotlight-card__title"><?php echo esc_html( $title ); ?></div>
      <?php if ( '' !== $desc ) : ?>
        <div class="spotlight-card__desc"><?php echo esc_html( $desc ); ?></div>
      <?php endif; ?>
    </div>
</div>
  <div class="spotlight-card__right">
    <div class="spotlight-card__meta">
      <div class="spotlight-card__value"><?php echo esc_html( $meta ); ?></div>
      <div class="spotlight-card__label"><?php echo esc_html( $tag ); ?></div>
    </div>
    <?php if ( '' !== $thumb_label ) : ?>
      <div class="spotlight-card__count"><?php echo esc_html( $thumb_label ); ?> <?php print adn_icon('info'); ?></div>
    <?php endif; ?>
  </div>
</<?php echo $el; ?>>
