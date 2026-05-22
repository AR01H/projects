<?php
/**
 * Component: NIF Resource Strip
 * Row of tool/resource cards. Reusable across any page.
 *
 * @var array $args {
 *   @type array  $cards    Resource card definitions (icon, badge, title, desc, url, style).
 *   @type string $eyebrow  Section eyebrow. Default 'Free Tools & Guides'.
 *   @type string $heading  Heading HTML. Default 'Buyer <em>Resources</em>'.
 * }
 */
defined( 'ABSPATH' ) || exit;

$cards   = $args['cards']   ?? [];
$eyebrow = $args['eyebrow'] ?? __( 'Free Tools & Guides', 'ah-theme' );
$heading = $args['heading'] ?? 'Buyer <em>Resources</em>';

if ( empty( $cards ) ) return;
?>
<section class="section section--alt nif-section-resources" aria-label="<?php esc_attr_e( 'Tools and resources', 'ah-theme' ); ?>">
  <div class="container">

    <div class="nif-section-label" data-aos="fade-up">
      <span class="section__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
      <h2 class="section__title" style="font-size:1.4rem;margin:6px 0 0"><?php echo wp_kses_post( $heading ); ?></h2>
    </div>

    <div class="nif-resource-strip">
      <?php foreach ( $cards as $rc ) :
        get_template_part( 'components/feed-info-card', null, [ 'card' => $rc ] );
      endforeach; ?>
    </div>

  </div>
</section>
