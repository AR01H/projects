<?php
defined( 'ABSPATH' ) || exit;
$sidebar_pts    = $args['sidebar_pts']    ?? [];
$display_cats   = $args['display_cats']   ?? [];
$latest_guides  = $args['latest_guides']  ?? [];
$popular_guides = $args['popular_guides'] ?? [];
$cat_pt_map     = $args['cat_pt_map']     ?? [];

$_gc_card = function( WP_Post $p, string $fallback_icon, array $cat_pt_map ) : void {
	$cats  = get_the_category( $p->ID );
	$cat   = $cats ? $cats[0] : null;
	$slug  = $cat ? $cat->slug : '';
	$name  = $cat ? $cat->name : '';
	$thumb = get_the_post_thumbnail_url( $p->ID, 'ah-card' ) ?: '';
	$pt    = $slug ? ( $cat_pt_map[ $slug ] ?? null ) : null;
	?>
	<a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" class="gc" data-cat="<?php echo esc_attr( $slug ); ?>" data-aos="fade-up">
	  <div class="gc__img-wrap">
	    <?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $p->ID ) ); ?>" class="gc__img" loading="lazy">
	    <?php else : ?><div class="gc__img gc__img--fallback"><?php echo esc_html( $fallback_icon ); ?></div><?php endif; ?>
	    <?php if ( $name ) : ?><span class="gc__cat"><?php echo esc_html( $name ); ?></span><?php endif; ?>
	  </div>
	  <div class="gc__body">
	    <?php if ( $pt ) : ?>
	    <span class="gc__pt-badge" style="--ptc:<?php echo esc_attr( $pt->color ?? 'var(--accent)' ); ?>"><?php echo esc_html( $pt->name ); ?></span>
	    <?php endif; ?>
	    <div class="gc__meta"><span class="gc__read-time">⏱ <?php echo esc_html( ah_reading_time( $p->ID ) ); ?></span></div>
	    <h2 class="gc__title"><?php echo esc_html( get_the_title( $p->ID ) ); ?></h2>
	    <?php $exc = get_the_excerpt( $p->ID ); if ( $exc ) : ?><p class="gc__excerpt"><?php echo wp_trim_words( $exc, 15, '…' ); ?></p><?php endif; ?>
	    <span class="gc__btn">Read Guide <span class="gc__arrow">→</span></span>
	  </div>
	</a>
	<?php
};
?>

<?php if ( $sidebar_pts ) : ?>
<div class="gc-home-section">
  <div class="gc-home-section__head">
    <span class="gc-home-section__eyebrow">Explore by Topic</span>
    <h2 class="gc-home-section__title">What Are You Looking For?</h2>
  </div>
  <div class="gc-pt-grid">
    <?php foreach ( $sidebar_pts as $sb ) :
      $sb_pt    = $sb['pt'];
      $sb_count = count( $sb['children'] );
      $sb_color = ! empty( $sb_pt->color ) ? $sb_pt->color : 'var(--accent)';
    ?>
    <a href="<?php echo esc_url( home_url( '/guides/?parent_term=' . urlencode( $sb_pt->slug ) ) ); ?>"
       class="gc-pt-card" style="--ptc:<?php echo esc_attr( $sb_color ); ?>">
      <span class="gc-pt-card__icon"><?php echo esc_html( ah_guide_topic_icon( $sb_pt->name ?? '', $sb_pt->slug ?? '', $sb_pt->icon_emoji ?? '' ) ); ?></span>
      <span class="gc-pt-card__name"><?php echo esc_html( $sb_pt->name ); ?></span>
      <?php if ( $sb_count ) : ?><span class="gc-pt-card__meta"><?php echo $sb_count; ?> topics</span><?php endif; ?>
      <span class="gc-pt-card__arrow">Browse →</span>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if ( $display_cats ) : ?>
<div class="gc-home-section">
  <div class="gc-home-section__head">
    <span class="gc-home-section__eyebrow">All Categories</span>
    <h2 class="gc-home-section__title">Find Exactly What You Need</h2>
  </div>
  <div class="gcat-grid">
    <?php foreach ( $display_cats as $i => $cat ) :
      $cat = is_object( $cat ) ? (array) $cat : $cat;
      get_template_part( 'components/cards/guide-category-card', null, [ 'cat' => $cat, 'index' => $i ] );
    endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if ( $latest_guides ) : ?>
<div class="gc-home-section">
  <div class="gc-home-section__head">
    <span class="gc-home-section__eyebrow">Fresh Content</span>
    <h2 class="gc-home-section__title">Latest Guides</h2>
  </div>
  <div class="post-grid">
    <?php foreach ( $latest_guides as $p ) : $_gc_card( $p, '📖', $cat_pt_map ); endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if ( $popular_guides ) : ?>
<div class="gc-home-section">
  <div class="gc-home-section__head">
    <span class="gc-home-section__eyebrow">Readers' Favourites</span>
    <h2 class="gc-home-section__title">Popular Right Now</h2>
  </div>
  <div class="post-grid">
    <?php foreach ( $popular_guides as $p ) : $_gc_card( $p, '⭐', $cat_pt_map ); endforeach; ?>
  </div>
</div>
<?php endif; ?>
