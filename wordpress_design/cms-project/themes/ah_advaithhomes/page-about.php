<?php
/**
 * Template Name: About Page
 *
 * Matches the reference About design (mockup #9):
 *   header → Mission / How We Help → Principles → What We Stand For →
 *   Meet the Team → CTA. Content from real_data/json/about.json.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/about.php';
$h    = $data['header'] ?? array();
$team = $data['team']   ?? array();
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => $h['eyebrow'] ?? 'About Us',
	'title'      => $h['title']   ?? 'About Advaith Homes',
	'desc'       => $h['sub']     ?? '',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'About', '' ] ],
] ); ?>

<?php get_template_part( 'components/about/about-intro', null, [
	'items' => $data['intro'] ?? [],
] ); ?>

<?php get_template_part( 'components/about/about-principles', null, [
	'title' => $data['principles']['title'] ?? '',
	'sub'   => $data['principles']['sub']   ?? '',
	'items' => $data['principles']['items'] ?? [],
] ); ?>

<?php get_template_part( 'components/about/about-principles', null, [
	'title'    => $data['values']['title'] ?? '',
	'sub'      => $data['values']['sub']   ?? '',
	'items'    => $data['values']['items'] ?? [],
	'modifier' => 'about-principles--alt',
] ); ?>

<!-- Meet the Team (DB-driven team members) -->
<section class="about-team">
  <div class="container">
    <div class="about-principles__head">
      <h2 class="about-principles__title"><?php echo esc_html( $team['title'] ?? 'Meet the Team' ); ?></h2>
      <?php if ( ! empty( $team['sub'] ) ) : ?><p class="about-principles__sub"><?php echo esc_html( $team['sub'] ); ?></p><?php endif; ?>
    </div>
    <?php get_template_part( 'components/team-section' ); ?>
    <?php if ( ! empty( $team['cta']['label'] ) ) : ?>
    <div class="about-team__cta">
      <a class="btn btn-primary" href="<?php echo esc_url( $team['cta']['url'] ?? '#' ); ?>"><?php echo esc_html( $team['cta']['label'] ); ?></a>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php
get_template_part( 'components/cta-section', null, [] );
get_footer();
