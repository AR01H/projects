<?php
/**
 * components/sections/faqs_footer.php - FAQ section shown above the footer.
 *
 * Reuses the same markup/classes as pages/page-faqs.php (.section-faqs,
 * .faqs-main, parts/faq_list) so it renders identically to the real /faqs/
 * page - just without its sidebar column.
 *
 * Groups items by their "section" field (from adn_get_page_faqs_grouped());
 * each group prints under its section name, ungrouped items ('' key) print
 * with no heading.
 *
 * Props:
 *   $groups array - section label => array of faq rows
 */

defined( 'ABSPATH' ) || exit;

$groups = isset( $groups ) && is_array( $groups ) ? $groups : array();
if ( empty( $groups ) ) { return; }
?>
<div class="section-faqs">
	<div class="faqs-main">
		<?php foreach ( $groups as $section_label => $items ) : ?>
			<?php adn_component( 'parts/faq_list', array(
				'faqs'    => $items,
				'heading' => (string) $section_label,
			) ); ?>
		<?php endforeach; ?>
	</div>
</div>
