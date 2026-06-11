<?php
/**
 * components/parts/post_sidebar_toc.php
 *
 * Table of Contents sidebar box.
 * The <nav> is empty on render; single.js populates it by scanning
 * the h2 headings inside .article-body and injecting anchor links.
 * No props needed.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sidebar-box sidebar-toc" id="tocBox">
	<h3><?php esc_html_e( 'On this page', ADN_TEXT_DOMAIN ); ?></h3>
	<nav class="toc-nav" id="tocNav" aria-label="<?php esc_attr_e( 'Article contents', ADN_TEXT_DOMAIN ); ?>">
		<?php /* single.js builds the links here */ ?>
	</nav>
</div>
