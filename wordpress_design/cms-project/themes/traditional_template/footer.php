<?php
/**
 * Closing chrome + wp_footer. Every template ends with get_footer().
 */
defined( 'ABSPATH' ) || exit;
?>
</main>

<?php nt_component( 'parts/main_footer' ); ?>

<?php get_template_part( 'components/floating-popup' ); ?>

<?php if ( nt_section_visible( 'site_decor' ) ) nt_component( 'parts/site-decor' ); ?>

<?php wp_footer(); ?>
</body>
</html>
