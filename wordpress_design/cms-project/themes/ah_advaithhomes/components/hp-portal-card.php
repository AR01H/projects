<?php
/**
 * Reusable homepage portal card.
 * Drop anywhere with get_template_part('components/hp-portal-card', null, $args).
 *
 * @var array $args {
 *   @type string   $icon     Emoji or <img> HTML for the icon area.
 *   @type string   $eyebrow  Small uppercase label above title.
 *   @type string   $title    Card heading.
 *   @type string   $desc     Short description paragraph.
 *   @type string   $url      Link destination.
 *   @type string   $cta      Call-to-action link text. Default 'Learn more'.
 *   @type string   $color    CSS color value for accent. Default var(--accent).
 *   @type string   $variant  'default' | 'dark' | 'accent'. Default 'default'.
 *   @type array    $chips    Optional: array of ['label'=>'', 'url'=>''] chip items.
 *   @type int      $delay    AOS delay in ms. Default 0.
 * }
 */
defined( 'ABSPATH' ) || exit;

$icon    = $args['icon']    ?? '📋';
$eyebrow = $args['eyebrow'] ?? '';
$title   = $args['title']   ?? '';
$desc    = $args['desc']    ?? '';
$url     = $args['url']     ?? '#';
$cta     = $args['cta']     ?? TXT_LEARN_MORE;
$color   = $args['color']   ?? 'var(--accent)';
$variant = $args['variant'] ?? 'default';
$chips   = $args['chips']   ?? [];
$delay   = (int) ( $args['delay'] ?? 0 );
?>
<a href="<?php echo esc_url( $url ); ?>"
   class="hp-pcard hp-pcard--<?php echo esc_attr( $variant ); ?>"
   style="--card-accent:<?php echo esc_attr( $color ); ?>"
   data-aos="fade-up"
   <?php echo $delay ? 'data-aos-delay="' . $delay . '"' : ''; ?>>

  <div class="hp-pcard__icon" aria-hidden="true">
    <?php echo $icon; /* already-escaped HTML or emoji */ ?>
  </div>

  <div class="hp-pcard__body">
    <?php if ( $eyebrow ) : ?>
    <span class="hp-pcard__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
    <?php endif; ?>
    <h3 class="hp-pcard__title"><?php echo esc_html( $title ); ?></h3>
    <?php if ( $desc ) : ?>
    <p class="hp-pcard__desc"><?php echo esc_html( $desc ); ?></p>
    <?php endif; ?>

    <?php if ( ! empty( $chips ) ) : ?>
    <div class="hp-pcard__chips">
      <?php foreach ( array_slice( $chips, 0, 5 ) as $chip ) : ?>
      <span class="hp-pcard__chip"><?php echo esc_html( $chip['label'] ); ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <span class="hp-pcard__cta"><?php echo esc_html( $cta ); ?> <span aria-hidden="true">→</span></span>

</a>
