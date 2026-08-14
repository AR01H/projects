<?php
/**
 * Section placeholder - marks a page-section slot that the reference design
 * calls for but that doesn't have real content/JSON wired up yet.
 *
 * Structural only, on purpose: renders a labelled, semantic <section> with no
 * visual design - just enough to see the page's section rhythm while its real
 * component + admin/data/*.json content is still being built. Swap the
 * page_sections.json entry for the real component when it's ready; nothing
 * else on the page needs to change.
 *
 * Called via App_Helpers::component() - page_sections.json "args" are
 * extracted into these BARE variables:
 *   label (string, required) Section name, e.g. "Trust Ticker".
 *   note  (string, optional) One line on what will render here.
 *
 * Renders nothing if `label` is missing (fails safe - never shows a mystery
 * empty box in production once real content replaces every placeholder).
 */
defined( 'ABSPATH' ) || exit;

$ph_label = isset( $label ) ? trim( (string) $label ) : '';
if ( '' === $ph_label ) {
	return;
}
$ph_note = isset( $note ) ? (string) $note : '';
$ph_slug = sanitize_html_class( 'app-ph-' . sanitize_title( $ph_label ) );
?>
<section class="app-section-placeholder <?php echo esc_attr( $ph_slug ); ?>" data-section-placeholder="<?php echo esc_attr( $ph_label ); ?>">
	<div class="container app-section-placeholder__inner">
		<span class="app-section-placeholder__label"><?php echo esc_html( $ph_label ); ?></span>
		<?php if ( $ph_note ) : ?>
			<span class="app-section-placeholder__note"><?php echo esc_html( $ph_note ); ?></span>
		<?php endif; ?>
	</div>
</section>
