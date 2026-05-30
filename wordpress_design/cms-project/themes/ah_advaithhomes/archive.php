<?php get_header();

$archive_title = get_the_archive_title();
$clean_title   = preg_replace( '/^[^:]+:\s*/', '', strip_tags( $archive_title ) );
$archive_desc  = get_the_archive_description();

// Current category for active-state detection
$current_cat = is_category() ? get_queried_object() : null;
$current_cat_slug = $current_cat ? $current_cat->slug : '';

// ── See More sidebar data (same as guides page) ───────────────────────────────
$sidebar_pts     = [];
$cat_pt_map      = [];
$sidebar_active_pt_id = null;

if ( class_exists( 'AH_Taxonomy_Parent_Model' ) && class_exists( 'AH_DB_Helper' ) ) {
	global $wpdb;
	$_ptm_sb = new AH_Taxonomy_Parent_Model();
	$_tax_sb = AH_DB_Helper::table( 'taxonomies' );
	foreach ( $_ptm_sb->get_all_active() as $_sb_pt ) {
		$_sb_children = $wpdb->get_results( $wpdb->prepare(
			"SELECT slug, name FROM `{$_tax_sb}` WHERE parent_term_id = %d AND status = 1 ORDER BY name ASC",
			(int) $_sb_pt->id
		) ) ?: [];
		$sidebar_pts[] = [ 'pt' => $_sb_pt, 'children' => $_sb_children ];
		foreach ( $_sb_children as $_sbc ) {
			$cat_pt_map[ $_sbc->slug ] = $_sb_pt;
			if ( $_sbc->slug === $current_cat_slug ) {
				$sidebar_active_pt_id = $_sb_pt->id;
			}
		}
	}
}

get_template_part( 'components/page-header', null, [
	'eyebrow'    => is_category() ? 'Category' : ( is_tag() ? 'Tag' : 'Archive' ),
	'title'      => $clean_title,
	'desc'       => $archive_desc ? wp_strip_all_tags( $archive_desc ) : '',
	'breadcrumb' => [
		[ 'Home', home_url( '/' ) ],
		[ $clean_title, '' ],
	],
] );
?>

<div class="gc-portal-bg">
<div class="container">
<div class="gc-portal-layout">

<main class="gc-portal-main">

  <?php if ( have_posts() ) : ?>
  <div class="post-grid">
    <?php while ( have_posts() ) : the_post();
      $cats      = get_the_category();
      $cat0      = $cats ? $cats[0] : null;
      $cat_slug  = $cat0 ? $cat0->slug : '';
      $cat_name  = $cat0 ? $cat0->name : '';
      $thumb_url = get_the_post_thumbnail_url( null, 'ah-card' ) ?: '';
      $pt        = $cat_slug ? ( $cat_pt_map[ $cat_slug ] ?? null ) : null;
    ?>
    <a href="<?php the_permalink(); ?>" class="gc" data-cat="<?php echo esc_attr( $cat_slug ); ?>" data-aos="fade-up">
      <div class="gc__img-wrap">
        <?php if ( $thumb_url ) : ?><img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="gc__img" loading="lazy">
        <?php else : ?><div class="gc__img gc__img--fallback">📖</div><?php endif; ?>
        <?php if ( $cat_name ) : ?><span class="gc__cat"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
      </div>
      <div class="gc__body">
        <?php if ( $pt ) : ?>
        <span class="gc__pt-badge" style="--ptc:<?php echo esc_attr( $pt->color ?? 'var(--accent)' ); ?>"><?php echo esc_html( $pt->name ); ?></span>
        <?php endif; ?>
        <div class="gc__meta"><span class="gc__read-time">⏱ <?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span></div>
        <h2 class="gc__title"><?php the_title(); ?></h2>
        <?php $exc = get_the_excerpt(); if ( $exc ) : ?><p class="gc__excerpt"><?php echo wp_trim_words( $exc, 18, '…' ); ?></p><?php endif; ?>
        <span class="gc__btn">Read Guide <span class="gc__arrow">→</span></span>
      </div>
    </a>
    <?php endwhile; ?>
  </div>

  <?php ah_pagination(); ?>

  <?php else : ?>
  <div class="text-center" style="padding:60px 0">
    <div style="font-size:3rem;margin-bottom:16px">📂</div>
    <h2 style="font-family:var(--font-display);font-size:1.4rem;margin-bottom:10px">No posts found</h2>
    <p style="color:var(--text-secondary)">Nothing published here yet — check back soon.</p>
  </div>
  <?php endif; ?>

</main>

<!-- ── SIDEBAR ── -->
<aside class="gc-portal-sidebar">
  <?php if ( $sidebar_pts ) : ?>
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
        <?php echo esc_html( $sb_pt->name ); ?>
      </a>
      <?php if ( $is_open && $sb['children'] ) : ?>
      <ul class="gc-see-more__children">
        <?php foreach ( $sb['children'] as $sb_child ) :
          $is_active_child = ( $sb_child->slug === $current_cat_slug );
        ?>
        <li><a href="<?php echo esc_url( get_category_link( get_term_by( 'slug', $sb_child->slug, 'category' ) ) ?: home_url( '/guides/?category=' . urlencode( $sb_child->slug ) ) ); ?>"
               class="<?php echo $is_active_child ? 'is-active' : ''; ?>"><?php echo esc_html( $sb_child->name ); ?></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</aside>

</div><!-- /.gc-portal-layout -->
</div><!-- /.container -->
</div><!-- /.gc-portal-bg -->

<?php get_template_part( 'components/cta-section' ); ?>
<?php get_footer(); ?>
