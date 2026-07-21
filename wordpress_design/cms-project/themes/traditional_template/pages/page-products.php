<?php
/**
 * Products page - vintage drinks & flavours.
 * Additive, JSON-driven, each block gated by nt_section_visible().
 */
defined( 'ABSPATH' ) || exit;
get_header();

$hdr = nt_data( 'content' )['products_page'] ?? array();
?>
<main class="site-main">

	<?php if ( nt_section_visible( 'products_hero' ) ) {
		nt_component( 'parts/page_header', array(
			'title'    => $hdr['title'] ?? __( 'Our Drinks', NT_TEXT_DOMAIN ),
			'subtitle' => $hdr['subtitle'] ?? '',
		) );
	} ?>

	<?php if ( nt_section_visible( 'products_bottles' ) )  get_template_part( 'components/signature-flavours' ); ?>
	<?php if ( nt_section_visible( 'product_menu' ) )      get_template_part( 'components/product-menu' ); ?>
	<?php if ( nt_section_visible( 'products_list' ) )     get_template_part( 'components/products-list' ); ?>
	<?php if ( nt_section_visible( 'product_benefits' ) )  get_template_part( 'components/product-benefits' ); ?>

</main>
<?php get_footer(); ?>
