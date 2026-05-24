<?php
/**
 * Reusable "I need advice on…" personalised guidance section.
 * Drop anywhere with get_template_part('components/hp-guidance').
 *
 * Reads parent terms from the CMS DB; shows them as clickable chips
 * linking to /multiinfo/<slug>/.
 */
defined( 'ABSPATH' ) || exit;

$parent_terms = [];
if ( class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$pt_table     = AH_DB_Helper::table( 'taxonomy_parent_terms' );
	$parent_terms = $wpdb->get_results(
		"SELECT id, name, slug, color, icon_emoji FROM `{$pt_table}` WHERE status = 1 ORDER BY name ASC"
	) ?: [];
}

if ( empty( $parent_terms ) ) return;
?>
<section class="hp-guidance" aria-label="<?php esc_attr_e( 'Personalised Guidance', 'ah-theme' ); ?>">
  <div class="container">
    <p class="hp-guidance__eyebrow"><?php esc_html_e( 'Personalised Guidance', 'ah-theme' ); ?></p>
    <h2 class="hp-guidance__title"><?php esc_html_e( 'I need advice on…', 'ah-theme' ); ?></h2>
    <div class="hp-guidance__chips" role="list">
      <?php foreach ( $parent_terms as $pt ) :
        $color = ! empty( $pt->color ) ? $pt->color : '#f59e0b';
        $label = ( ! empty( $pt->icon_emoji ) ? $pt->icon_emoji . ' ' : '' ) . $pt->name;
      ?>
      <a href="<?php echo esc_url( home_url( '/multiinfo/' . $pt->slug . '/' ) ); ?>"
         class="hp-guidance__chip"
         style="--ptc:<?php echo esc_attr( $color ); ?>"
         role="listitem">
        <?php echo esc_html( $label ); ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
