<?php
/**
 * Generic parent-term (main group) filter tabs - reusable on any listing.
 *
 * Args:
 *   terms     (array)  Parent terms. Default: ah_get_parent_terms().
 *   active    (string) Active group slug. Default: ''.
 *   base_url  (string) Listing URL. Default: current permalink.
 *   param     (string) Query var to set. Default: 'group'.
 *   all_label (string) Label for the "show everything" tab. Default: 'All'.
 */
defined( 'ABSPATH' ) || exit;

$terms     = $args['terms']     ?? ( function_exists( 'ah_get_parent_terms' ) ? ah_get_parent_terms() : array() );
$active    = sanitize_title( $args['active'] ?? '' );
$base      = $args['base_url']  ?? get_permalink();
$param     = sanitize_key( $args['param'] ?? 'group' );
$all_label = $args['all_label'] ?? 'All';

if ( ! $terms ) {
	return;
}
?>
<nav class="pt-tabs" aria-label="Filter by topic">
  <a class="pt-tab<?php echo '' === $active ? ' is-active' : ''; ?>"
     href="<?php echo esc_url( remove_query_arg( array( $param, 'pg' ), $base ) ); ?>"><?php echo esc_html( $all_label ); ?></a>
  <?php foreach ( $terms as $pt ) :
    $slug = sanitize_title( $pt->slug ?? '' );
    if ( ! $slug ) continue;
  ?>
    <a class="pt-tab<?php echo $active === $slug ? ' is-active' : ''; ?>"
       href="<?php echo esc_url( add_query_arg( $param, $slug, remove_query_arg( 'pg', $base ) ) ); ?>"><?php echo esc_html( $pt->name ?? '' ); ?></a>
  <?php endforeach; ?>
</nav>
