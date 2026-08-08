<?php
/**
 * Closing chrome + wp_footer. Every template ends with get_footer().
 */
defined( 'ABSPATH' ) || exit;
?>
</main>

<?php app_component( 'parts/main_footer' ); ?>

<?php get_template_part( 'components/floating-popup' ); ?>

<?php if ( app_section_visible( 'site_decor' ) ) app_component( 'parts/site-decor' ); ?>

<?php app_component( 'parts/svg_defs' ); ?>

<?php wp_footer(); ?>
</body>
</html>
