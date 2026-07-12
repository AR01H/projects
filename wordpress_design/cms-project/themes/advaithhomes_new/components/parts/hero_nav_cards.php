<?php
/**
 * components/parts/hero_nav_cards.php
 *
 * Renders the block of chunky navigation buttons (Category, Contact, All Guides).
 * Props:
 *   $cms_terms array
 */

defined( 'ABSPATH' ) || exit;

// Get the parent term from the breadcrumb for the first button
// Breadcrumb format: Home > Parent Term > Category Term > Post Title
$category_name = 'Category';
$category_link = '#';

if ( ! empty( $breadcrumb ) && is_array( $breadcrumb ) && count( $breadcrumb ) >= 2 ) {
	// Index 1 is usually the top-level parent after 'Home' (Index 0)
	$parent_crumb = $breadcrumb[1];
	if ( ! empty( $parent_crumb['label'] ) ) {
		$category_name = $parent_crumb['label'];
		$category_link = isset( $parent_crumb['url'] ) ? $parent_crumb['url'] : '#';
	}
} elseif ( ! empty( $cms_terms ) && is_array( $cms_terms ) ) {
	$_pt = reset( $cms_terms );
	$category_name = $_pt['name'];
	$category_link = $_pt['url'];
}

// Fallback links for Contact and All Guides (could be dynamic later)
$contact_link = home_url( '/contact/' );
$all_guides_link = home_url( '/guides/' );

// Check for calculators in the sidebar context
$calc_url = '';
$calc_label = 'Calculators';
if ( ! empty( $sidebar['calculators']['view_all_url'] ) ) {
	$calc_url = $sidebar['calculators']['view_all_url'];
} elseif ( ! empty( $sidebar['calculators']['items'] ) && is_array( $sidebar['calculators']['items'] ) ) {
	$first_calc = reset( $sidebar['calculators']['items'] );
	if ( ! empty( $first_calc['url'] ) ) {
		$calc_url = $first_calc['url'];
	}
}

?>
<div class="sb-nav-buttons">
	<a href="<?php echo esc_url( $category_link ); ?>" class="sb-nav-btn sb-nav-btn--primary">
		<span class="sb-nav-btn-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
		</span>
		<span class="sb-nav-btn-text"><?php echo esc_html( $category_name ); ?></span>
		<span class="sb-nav-btn-chevron">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
		</span>
	</a>

	<?php if ( ! empty( $calc_url ) ) : ?>
		<a href="<?php echo esc_url( $calc_url ); ?>" class="sb-nav-btn sb-nav-btn--primarylight">
			<span class="sb-nav-btn-icon">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="14" x2="16" y2="14.01"/><line x1="12" y1="14" x2="12" y2="14.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="16" y1="18" x2="16" y2="18.01"/><line x1="12" y1="18" x2="12" y2="18.01"/><line x1="8" y1="18" x2="8" y2="18.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="12" y1="10" x2="12" y2="10.01"/><line x1="8" y1="10" x2="8" y2="10.01"/></svg>
			</span>
			<span class="sb-nav-btn-text"><?php echo esc_html( $calc_label ); ?></span>
			<span class="sb-nav-btn-chevron">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
			</span>
		</a>
	<?php endif; ?>

	<!-- <a href="<?php echo esc_url( $contact_link ); ?>" class="sb-nav-btn sb-nav-btn--primary">
		<span class="sb-nav-btn-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
		</span>
		<span class="sb-nav-btn-text">Contact</span>
		<span class="sb-nav-btn-chevron">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
		</span>
	</a> -->

	<a href="<?php echo esc_url( $all_guides_link ); ?>" class="sb-nav-btn sb-nav-btn--secondary">
		<span class="sb-nav-btn-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
		</span>
		<span class="sb-nav-btn-text">All Guides</span>
		<span class="sb-nav-btn-chevron">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
		</span>
	</a>
</div>
