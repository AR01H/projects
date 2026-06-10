<?php
/**
 * front-page.php - Advaith Homes "Knowledge Hub" home page.
 *
 * Data flow:  intermediate_logics/knowledge-hub.php → components/khub/*
 *
 * Sections (match the approved design):
 *   1. Hero + Property Journey card        (khub-hero)
 *   2. Audience cards (Buying/Selling/…)   (khub-audience)
 *   3. Guides · Timeline · Calculators · Red Flags  (khub-quad)
 *   4. Areas · Latest Articles · Experts   (khub-discover)
 *   5. Newsletter band (rendered globally in the footer)
 *
 * All content is provisioned in the intermediate layer and overridable via the
 * `ah_khub_data` filter - the components stay generic and reusable.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$khub = require get_template_directory() . '/intermediate_logics/knowledge-hub.php';
?>

<main id="main-content" class="khub">

	<?php get_template_part( 'components/khub/khub-hero', null, $khub['hero'] ?? array() ); ?>

	<?php get_template_part( 'components/khub/khub-audience', null, array(
		'cards' => $khub['audience'] ?? array(),
	) ); ?>

	<?php get_template_part( 'components/khub/khub-quad', null, array(
		'guides'      => $khub['popular_guides'] ?? array(),
		'timeline'    => $khub['timeline']       ?? array(),
		'calculators' => $khub['calculators']    ?? array(),
		'red_flags'   => $khub['red_flags']      ?? array(),
	) ); ?>

	<?php get_template_part( 'components/khub/khub-discover', null, array(
		'areas'    => $khub['areas']    ?? array(),
		'articles' => $khub['articles'] ?? array(),
		'experts'  => $khub['experts']  ?? array(),
	) ); ?>

</main>

<?php get_template_part( 'components/scroll-to-top' ); ?>
<?php get_footer();
