<?php
/**
 * Template Name: Coming Soon
 *
 * pages/PageComing.php - Coming soon / maintenance page.
 */
defined( 'ABSPATH' ) || exit;

$_cs_title = esc_html( adn_term( 'coming_soon.title', 'Coming Soon' ) );
$_cs_desc  = esc_html( adn_term( 'coming_soon.description', 'We\'re working on something exciting. Stay tuned!' ) );

get_header();

adn_seo_register( array(
	'title'       => $_cs_title,
	'description' => $_cs_desc,
) );
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
