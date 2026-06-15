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
<button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top" hidden>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="18 15 12 9 6 15"/></svg>
</button>
</body>
</html>
