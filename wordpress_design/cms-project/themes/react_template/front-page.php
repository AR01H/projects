<?php
/**
 * React Template — Front Page
 * Renders the React SPA inside WordPress's template system.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>
<main id="primary" class="site-main">
  <div id="root"></div>
</main>
<?php
get_footer();
