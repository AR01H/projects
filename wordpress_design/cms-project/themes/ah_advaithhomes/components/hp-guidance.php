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
<section class="hp-guidance" aria-label="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_TXT_PERSONALISED_GUIDANCE ); ?>">
  <div class="container">
    <p class="hp-guidance__eyebrow"><?php echo esc_html( TXT_PERSONALISED_GUIDANCE ); ?></p>
    <h2 class="hp-guidance__title"><?php echo esc_html( TXT_I_NEED_ADVICE_ON ); ?></h2>
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
