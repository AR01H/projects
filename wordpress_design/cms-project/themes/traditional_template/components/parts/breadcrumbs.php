<?php
/**
 * components/parts/breadcrumbs.php - the "you are here" trail.
 *
 * GENERIC and automatic: it builds itself from the router's active page key
 * plus config/pages.php titles, so a page added to the registry gets a
 * correct trail with no extra work. Labels can still be overridden per page
 * from admin/data/breadcrumbs.json when the nav wording should differ from
 * the page title (e.g. registry title "News & Updates", crumb "Journal").
 *
 * admin/data/breadcrumbs.json:
 *   {
 *     "home_label": "Home",
 *     "separator": "chevron-right",          // NT_Icons name, or any text
 *     "labels":  { "news": "Journal" },      // page key => crumb label
 *     "parents": { "order": "products" }     // page key => parent page key
 *   }
 *
 * Args:
 *   items  array   Override the whole trail: [ { label, url } … ].
 *   page   string  Force the page key (defaults to the router's).
 *   class  string  Extra class on the <nav>.
 */

defined( 'ABSPATH' ) || exit;

$nt_cfg   = App_Helpers::data( 'breadcrumbs' );
$nt_pages = App_Theme::config( 'pages' );

$nt_home_label = (string) ( $nt_cfg['home_label'] ?? '' );
if ( '' === $nt_home_label ) {
	$nt_home_label = NT_Ui::aria( 'home', 'Home' );
}
$nt_separator = (string) ( $nt_cfg['separator'] ?? 'chevron-right' );
$nt_labels    = is_array( $nt_cfg['labels'] ?? null ) ? $nt_cfg['labels'] : array();
$nt_parents   = is_array( $nt_cfg['parents'] ?? null ) ? $nt_cfg['parents'] : array();

/**
 * The crumb label for a page key: JSON override first, then the registry
 * title, then a humanised slug so nothing is ever blank.
 */
$nt_label_for = static function ( $key ) use ( $nt_labels, $nt_pages ) {
	if ( isset( $nt_labels[ $key ] ) && '' !== trim( (string) $nt_labels[ $key ] ) ) {
		return (string) $nt_labels[ $key ];
	}
	if ( isset( $nt_pages[ $key ]['title'] ) && '' !== trim( (string) $nt_pages[ $key ]['title'] ) ) {
		return (string) $nt_pages[ $key ]['title'];
	}
	return ucwords( str_replace( array( '-', '_' ), ' ', (string) $key ) );
};

// ── Build the trail ────────────────────────────────────────────────────────
$nt_trail = array();

if ( isset( $items ) && is_array( $items ) && ! empty( $items ) ) {
	// Caller supplied the whole trail (used by single.php for a post title).
	foreach ( $items as $nt_item ) {
		$nt_item  = (array) $nt_item;
		$nt_label = trim( (string) ( $nt_item['label'] ?? '' ) );
		if ( '' === $nt_label ) {
			continue;
		}
		$nt_trail[] = array( 'label' => $nt_label, 'url' => (string) ( $nt_item['url'] ?? '' ) );
	}
} else {
	$nt_key = isset( $page ) ? (string) $page : (string) get_query_var( 'app_active_page' );
	if ( '' === $nt_key || ! empty( $nt_pages[ $nt_key ]['front'] ) ) {
		return; // The front page has nowhere to go back to.
	}

	// Walk up the optional parent chain (guarded against a JSON loop).
	$nt_chain = array( $nt_key );
	$nt_up    = $nt_key;
	$nt_guard = 0;
	while ( isset( $nt_parents[ $nt_up ] ) && $nt_guard < 5 ) {
		$nt_up = (string) $nt_parents[ $nt_up ];
		if ( '' === $nt_up || in_array( $nt_up, $nt_chain, true ) ) {
			break;
		}
		array_unshift( $nt_chain, $nt_up );
		$nt_guard++;
	}

	foreach ( $nt_chain as $nt_crumb_key ) {
		$nt_trail[] = array(
			'label' => $nt_label_for( $nt_crumb_key ),
			'url'   => ( $nt_crumb_key === $nt_key ) ? '' : App_Helpers::page_url( $nt_crumb_key ),
		);
	}
}

if ( empty( $nt_trail ) ) {
	return;
}

array_unshift( $nt_trail, array( 'label' => $nt_home_label, 'url' => home_url( '/' ) ) );
$nt_last = count( $nt_trail ) - 1;
?>
<nav class="app-crumbs <?php echo esc_attr( $class ?? '' ); ?>" aria-label="<?php echo esc_attr( NT_Ui::aria( 'breadcrumb', 'Breadcrumb' ) ); ?>">
	<ol class="app-crumbs__list">
		<?php foreach ( $nt_trail as $nt_i => $nt_crumb ) : ?>
			<li class="app-crumbs__item">
				<?php if ( '' !== $nt_crumb['url'] && $nt_i !== $nt_last ) : ?>
					<a class="app-crumbs__link" href="<?php echo esc_url( $nt_crumb['url'] ); ?>"><?php echo esc_html( $nt_crumb['label'] ); ?></a>
				<?php else : ?>
					<span class="app-crumbs__current" aria-current="page"><?php echo esc_html( $nt_crumb['label'] ); ?></span>
				<?php endif; ?>

				<?php if ( $nt_i !== $nt_last ) : ?>
					<span class="app-crumbs__sep" aria-hidden="true">
						<?php echo NT_Icons::get_or_text( $nt_separator ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
					</span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
