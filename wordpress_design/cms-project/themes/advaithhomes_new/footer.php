<?php
/**
 * footer.php - HTML document closer.
 *
 * Called via get_footer() from every page template.
 * Contains ONLY wp_footer() and the closing </body></html> tags.
 * Site footer markup lives in components/parts/main_footer.php and is
 * loaded by each page template directly so it can receive data props.
 */

defined( 'ABSPATH' ) || exit;

wp_footer();
?>
</body>
</html>
