<?php
/**
 * Template Name: Areas
 *
 * Explore Areas in the UK (mockup #8): header + map, search, popular areas grid,
 * per-area feature row. Content from real_data/json/areas.json.
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/areas.php';
$h    = $data['header'] ?? array();
$cta  = $data['cta']    ?? array();
?>

<?php get_template_part( 'components/page-header', null, [
	'eyebrow'    => $h['eyebrow'] ?? 'Areas',
	'title'      => $h['title']   ?? 'Explore Areas in the UK',
	'desc'       => $h['sub']     ?? '',
	'breadcrumb' => [ [ 'Home', home_url( '/' ) ], [ 'Areas', '' ] ],
] ); ?>

<?php get_template_part( 'components/areas/areas-popular', null, [
	'title' => $data['popular']['title'] ?? 'Popular Areas',
	'sub'   => $data['popular']['sub']   ?? '',
	'items' => $data['popular']['items'] ?? [],
] ); ?>

<?php get_template_part( 'components/about/about-principles', null, [
	'title'    => $data['features']['title'] ?? '',
	'sub'      => $data['features']['sub']   ?? '',
	'items'    => $data['features']['items'] ?? [],
	'modifier' => 'about-principles--alt',
] ); ?>

<?php if ( ! empty( $cta['label'] ) ) : ?>
<div class="areas-cta"><div class="container">
  <a class="btn btn-primary" href="<?php echo esc_url( $cta['url'] ?? '#' ); ?>"><?php echo esc_html( $cta['label'] ); ?></a>
</div></div>
<?php endif; ?>

<?php
get_template_part( 'components/cta-section', null, [] );
get_footer();
