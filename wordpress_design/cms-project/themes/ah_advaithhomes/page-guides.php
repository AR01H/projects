<?php
/**
 * Template Name: Guides Archive
 */
defined( 'ABSPATH' ) || exit;
get_header();
$data = require get_template_directory() . '/intermediate_logics/guides.php';

$active_pt      = $data['active_pt'];
$active_cat     = $data['active_cat'];
$active_cat_obj = $data['active_cat_obj'];
$is_filtered    = $data['is_filtered'];
$base_url       = $data['base_url'];

if ( $active_pt && ! $active_cat ) {
	$ph = [
		'eyebrow'    => 'Guides',
		'title'      => esc_html( $active_pt->name ),
		'title_em'   => '',
		'desc'       => ! empty( $active_pt->description ) ? esc_html( $active_pt->description ) : '',
	];
} elseif ( $active_cat_obj ) {
	$ph = [
		'eyebrow'    => ! empty( $active_pt ) ? esc_html( $active_pt->name ) : 'Guides',
		'title'      => esc_html( $active_cat_obj['title'] ?? $active_cat ),
		'title_em'   => '',
		'desc'       => ! empty( $active_cat_obj['desc'] ) ? esc_html( $active_cat_obj['desc'] ) : '',
	];
} else {
	$ph = [
		'eyebrow'    => '',
		'title'      => 'The Complete',
		'title_em'   => 'Library',
		'desc'       => 'Guides written by buyer\'s agents - not marketers. Everything you need to buy with confidence, from mortgage basics to completion day.',
	];
}
$ph['breadcrumb'] = array_filter( [
	[ 'Home',   home_url( '/' ) ],
	[ 'Guides', $is_filtered ? esc_url( $base_url ) : '' ],
	$active_cat_obj ? [ esc_html( $active_cat_obj['title'] ?? $active_cat ), '' ] : null,
	$active_pt && ! $active_cat ? [ esc_html( $active_pt->name ?? $data['active_pt_slug'] ), '' ] : null,
] );
get_template_part( 'components/page-header', null, $ph );
?>
<div class="gc-portal-bg">
<div class="container">
<div class="gc-portal-layout<?php echo ! $is_filtered ? ' gc-portal-layout--full' : ''; ?>">

<main class="gc-portal-main">
<?php if ( $is_filtered ) : ?>
  <?php get_template_part( 'components/guides/filtered-main', null, $data ); ?>
<?php else : ?>
  <?php get_template_part( 'components/guides/home-main', null, $data ); ?>
<?php endif; ?>
</main>

<aside class="gc-portal-sidebar">
  <?php if ( $is_filtered ) : ?>
    <?php get_template_part( 'components/guides/sidebar', null, $data ); ?>
  <?php endif; ?>
</aside>

</div>
</div>
</div>
<?php
get_template_part( 'components/cta-section' );
get_footer();
