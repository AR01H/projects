<?php
/**
 * Component: Feed Info / Resource Card
 * Used inside template-news-info-feeder.php to render static tool/resource cards.
 *
 * @var array $args {
 *   @type array $card {
 *     @type string $icon   Emoji icon
 *     @type string $badge  Badge label
 *     @type string $title  Card title
 *     @type string $desc   Short description
 *     @type string $url    CTA link URL
 *     @type string $style  'light' | 'accent' | 'dark'
 *   }
 * }
 */

$card  = $args['card'] ?? [];
if ( empty( $card['title'] ) ) return;

$icon  = $card['icon']  ?? '📌';
$badge = $card['badge'] ?? '';
$title = $card['title'];
$desc  = $card['desc']  ?? '';
$url   = $card['url']   ?? '#';
$style = $card['style'] ?? 'light';

$variant_class = match ( $style ) {
	'accent' => 'nif-card--resource nif-card--accent',
	'dark'   => 'nif-card--resource nif-card--dark',
	default  => 'nif-card--resource',
};
?>
<div class="nif-card <?php echo esc_attr( $variant_class ); ?>" data-aos="fade-up">
  <div class="nif-card__body">

    <span class="nif-card__icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>

    <?php if ( $badge ) : ?>
      <span class="nif-badge"><?php echo esc_html( $badge ); ?></span>
    <?php endif; ?>

    <h3 class="nif-card__title"><?php echo esc_html( $title ); ?></h3>

    <?php if ( $desc ) : ?>
      <p class="nif-card__excerpt"><?php echo esc_html( $desc ); ?></p>
    <?php endif; ?>

    <a href="<?php echo esc_url( $url ); ?>" class="nif-card__cta">
      Go <span aria-hidden="true">→</span>
    </a>

  </div>
</div>
