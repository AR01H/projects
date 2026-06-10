<?php
/**
 * About principles / values - a centred icon-card grid (reusable).
 * Args: title, sub, items => [ { icon, title, desc }, ... ], modifier (css class)
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$title = $args['title'] ?? '';
$sub   = $args['sub']   ?? '';
$items = $args['items'] ?? array();
$mod   = $args['modifier'] ?? '';
if ( ! $items ) {
	return;
}
?>
<section class="about-principles <?php echo esc_attr( $mod ); ?>">
  <div class="container">
    <?php if ( $title ) : ?>
    <div class="about-principles__head">
      <h2 class="about-principles__title"><?php echo esc_html( $title ); ?></h2>
      <?php if ( $sub ) : ?><p class="about-principles__sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="about-principles__grid">
      <?php foreach ( $items as $it ) : ?>
        <div class="about-principle">
          <span class="about-principle__ico"><?php echo ah_khub_icon( $it['icon'] ?? 'shield', 26 ); ?></span>
          <h3 class="about-principle__title"><?php echo esc_html( $it['title'] ?? '' ); ?></h3>
          <p class="about-principle__desc"><?php echo esc_html( $it['desc'] ?? '' ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
