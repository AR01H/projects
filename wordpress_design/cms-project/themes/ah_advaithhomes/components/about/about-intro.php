<?php
/**
 * About intro - two-column "Our Mission" / "How We Help" cards.
 * Args: items => [ { icon, title, body }, ... ]
 */
defined( 'ABSPATH' ) || exit;
require_once get_template_directory() . '/components/khub/khub-icons.php';

$items = $args['items'] ?? array();
if ( ! $items ) {
	return;
}
?>
<section class="about-intro">
  <div class="container">
    <div class="about-intro__grid">
      <?php foreach ( $items as $it ) : ?>
        <div class="about-intro__card">
          <span class="about-intro__ico"><?php echo ah_khub_icon( $it['icon'] ?? 'steps', 26 ); ?></span>
          <h2 class="about-intro__title"><?php echo esc_html( $it['title'] ?? '' ); ?></h2>
          <p class="about-intro__body"><?php echo esc_html( $it['body'] ?? '' ); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
