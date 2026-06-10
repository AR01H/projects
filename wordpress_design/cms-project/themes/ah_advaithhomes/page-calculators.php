<?php
/**
 * Template Name: Calculators
 *
 * Calculators Hub (mockup #5): inline title + sidebar (categories + help) +
 * popular grid + filterable list. Content from real_data/json/calculators.json.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/calculators.php';
$h    = $data['header'] ?? array();
?>
<section class="ghub calc-hub">
  <div class="container">

    <header class="ghub-head">
      <?php if ( ! empty( $h['eyebrow'] ) ) : ?><span class="section__eyebrow"><?php echo esc_html( $h['eyebrow'] ); ?></span><?php endif; ?>
      <h1 class="ghub-head__title"><?php echo esc_html( $h['title'] ?? 'All Calculators' ); ?></h1>
      <?php if ( ! empty( $h['sub'] ) ) : ?><p class="ghub-head__sub"><?php echo esc_html( $h['sub'] ); ?></p><?php endif; ?>
    </header>

    <div class="ghub-layout">
      <aside class="ghub-aside">
        <?php get_template_part( 'components/calculators/calc-sidebar', null, [
          'categories' => $data['categories'],
          'counts'     => $data['counts'],
          'active'     => $data['active'],
          'base_url'   => $data['base_url'],
          'help'       => [
            'title' => $h['help_title'] ?? '',
            'desc'  => $h['help_desc']  ?? '',
            'cta'   => $h['help_cta']   ?? [],
          ],
        ] ); ?>
      </aside>

      <?php get_template_part( 'components/calculators/calc-main', null, $data ); ?>
    </div>

  </div>
</section>
<?php
get_template_part( 'components/cta-section' );
get_footer();
