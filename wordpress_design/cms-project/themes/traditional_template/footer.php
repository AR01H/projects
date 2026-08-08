<?php
/**
 * Closing chrome + wp_footer. Every template ends with get_footer().
 */
defined( 'ABSPATH' ) || exit;
?>
</main>

<?php App_Helpers::component( 'parts/main_footer' ); ?>

<?php get_template_part( 'components/floating-popup' ); ?>

<?php if ( app_section_visible( 'site_decor' ) ) App_Helpers::component( 'parts/site-decor' ); ?>

<?php App_Helpers::component( 'parts/svg_defs' ); ?>

<?php wp_footer(); ?>
</body>
</html>
