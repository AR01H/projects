<?php
/**
 * Template Name: Coming Soon
 *
 * pages/PageComing.php - Coming soon / maintenance page.
 */
defined( 'ABSPATH' ) || exit;

$__cs_page = \Adn\Theme\Service\SeoService::pageConfig( 'coming_soon', array(
	'type'        => 'website',
) );
$_cs_title = esc_html( (string) ( $__cs_page['title'] ?? '' ) );
$_cs_desc  = esc_html( (string) ( $__cs_page['description'] ?? '' ) );

// Register SEO before get_header() - adn_seo_head_output() runs on wp_head priority 1.
adn_seo_register( $__cs_page );

get_header();
?>

<section class="coming-soon">
	<div class="container">
		<div class="coming-soon-content">
			<h1><?php echo $_cs_title; ?></h1>
			<p><?php echo $_cs_desc; ?></p>
		</div>
	</div>
</section>

<?php get_footer();
