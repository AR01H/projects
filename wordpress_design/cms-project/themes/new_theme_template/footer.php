<?php
/**
 * Closing chrome + wp_footer. Every template ends with get_footer().
 */
defined( 'ABSPATH' ) || exit;
?>
</main>

<?php nt_component( 'parts/main_footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
