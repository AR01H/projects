<?php
/**
 * Template Name: Stories
 * Client stories and case study listing page.
 *
 * Data flow: intermediate_pages/stories.php → components (stories/)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$data = require get_template_directory() . '/intermediate_pages/stories.php';

get_header();
?>

<main class="pt-stories-page">

	<?php
	/* ── 1. Page Hero ─────────────────────────────────────────── */
	get_template_part( 'stories/components/story-hero', null, $data['hero'] );

	/* ── 2. Featured Story ────────────────────────────────────── */
	if ( ! empty( $data['featured'] ) ) :
		get_template_part( 'stories/components/story-card', null, array_merge(
			$data['featured'],
			[ 'variant' => 'featured' ]
		) );
	endif;

	/* ── 3. Story Grid ────────────────────────────────────────── */
	if ( ! empty( $data['stories'] ) ) :
		get_template_part( 'stories/components/story-grid', null, [
			'stories'  => $data['stories'],
			'tag'      => $data['grid_heading']['tag']     ?? '',
			'heading'  => $data['grid_heading']['heading'] ?? '',
		] );
	endif;

	/* ── 4. CTA ───────────────────────────────────────────────── */
	get_template_part( 'stories/components/story-cta', null, $data['cta'] );
	?>

</main>

<?php get_footer(); ?>
