<?php
/**
 * React Template — Index fallback
 * Renders the React SPA for any unmatched routes.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="site-main">
  <div id="root"></div>
</main>
<?php
get_footer();
