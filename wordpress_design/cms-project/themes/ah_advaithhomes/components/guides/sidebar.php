<?php
defined( 'ABSPATH' ) || exit;
$sidebar_pts    = $args['sidebar_pts']    ?? [];
$active_pt      = $args['active_pt']      ?? null;
$active_cat     = $args['active_cat']     ?? '';
$cat_pt_map     = $args['cat_pt_map']     ?? [];
if ( ! $sidebar_pts ) return;

$sidebar_active_pt_id = null;
if ( $active_pt ) {
	$sidebar_active_pt_id = $active_pt->id;
} elseif ( $active_cat && isset( $cat_pt_map[ $active_cat ] ) ) {
	$sidebar_active_pt_id = $cat_pt_map[ $active_cat ]->id ?? null;
}
?>
<div class="gc-see-more">
  <div class="gc-see-more__title">See More</div>
  <?php foreach ( $sidebar_pts as $sb ) :
    $sb_pt    = $sb['pt'];
    $sb_color = ! empty( $sb_pt->color ) ? $sb_pt->color : 'var(--accent)';
    $is_open  = $sidebar_active_pt_id && ( (int) $sb_pt->id === (int) $sidebar_active_pt_id );
  ?>
  <div class="gc-see-more__group<?php echo $is_open ? ' is-open' : ''; ?>" style="--gc-group-color:<?php echo esc_attr( $sb_color ); ?>">
    <a href="<?php echo esc_url( home_url( '/guides/?parent_term=' . urlencode( $sb_pt->slug ) ) ); ?>"
       class="gc-see-more__pt-header" style="background:<?php echo esc_attr( $sb_color ); ?>">
      <span class="gc-see-more__pt-icon" aria-hidden="true"><?php echo esc_html( ah_guide_topic_icon( $sb_pt->name ?? '', $sb_pt->slug ?? '', $sb_pt->icon_emoji ?? '' ) ); ?></span>
      <?php echo esc_html( $sb_pt->name ); ?>
    </a>
    <?php if ( $is_open && $sb['children'] ) : ?>
    <ul class="gc-see-more__children">
      <?php foreach ( $sb['children'] as $sb_child ) :
        $is_active_child = ( $sb_child->slug === $active_cat );
      ?>
      <li>
        <a href="<?php echo esc_url( home_url( '/guides/?category=' . urlencode( $sb_child->slug ) ) ); ?>"
           class="<?php echo $is_active_child ? 'is-active' : ''; ?>">
          <?php echo esc_html( $sb_child->name ); ?>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
