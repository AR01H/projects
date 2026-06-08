<?php
defined( 'ABSPATH' ) || exit;
$base_url       = $args['base_url']       ?? get_permalink();
$active_cat     = $args['active_cat']     ?? '';
$active_cat_obj = $args['active_cat_obj'] ?? null;
$active_pt      = $args['active_pt']      ?? null;
$display_cats   = $args['display_cats']   ?? [];
$cat_pt_map     = $args['cat_pt_map']     ?? [];
$guides_query   = $args['guides_query']   ?? null;
$paged          = $args['paged']          ?? 1;

if ( ! $guides_query ) return;

$cat_img_url  = ! empty( $active_cat_obj['image_id'] ) ? wp_get_attachment_image_url( $active_cat_obj['image_id'], 'medium_large' ) : '';
$banner_title = $active_cat_obj['title'] ?? ( $active_pt ? $active_pt->name : $active_cat );
$banner_icon  = ! empty( $active_cat_obj['icon_emoji'] )
	? $active_cat_obj['icon_emoji']
	: ( $active_pt
		? ah_guide_topic_icon( $active_pt->name ?? '', $active_pt->slug ?? '', $active_pt->icon_emoji ?? '' )
		: ah_guide_topic_icon( $banner_title, $active_cat, '' ) );
$banner_desc  = $active_cat_obj['desc'] ?? ( $active_pt ? ( $active_pt->description ?? '' ) : '' );
$banner_count = $active_cat_obj['count'] ?? ( $guides_query ? $guides_query->found_posts : 0 );
?>
<div class="gc-cat-banner" style="<?php if ( $cat_img_url ) echo '--gc-cat-img:url(' . esc_url( $cat_img_url ) . ')'; ?>">
  <div class="gc-cat-banner__left">
    <a href="<?php echo esc_url( $base_url ); ?>" class="gc-cat-banner__back">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      <?php echo esc_html( TXT_ALL_TOPICS ); ?>
    </a>
    <div class="gc-cat-banner__icon"><?php echo esc_html( $banner_icon ); ?></div>
    <?php if ( $banner_desc ) : ?><p class="gc-cat-banner__desc"><?php echo esc_html( $banner_desc ); ?></p><?php endif; ?>
    <?php if ( $banner_count ) : ?><span class="gc-cat-banner__count"><?php echo (int) $banner_count; ?> <?php echo esc_html( TXT_GUIDES ); ?></span><?php endif; ?>
  </div>
  <?php if ( $cat_img_url ) : ?>
  <div class="gc-cat-banner__img-wrap"><img src="<?php echo esc_url( $cat_img_url ); ?>" alt="<?php echo esc_attr( $active_cat_obj['title'] ?? '' ); ?>" class="gc-cat-banner__img"></div>
  <?php endif; ?>
</div>

<?php if ( $guides_query->have_posts() ) : ?>
<div class="post-grid" style="margin-top:28px">
  <?php while ( $guides_query->have_posts() ) : $guides_query->the_post();
    $cats     = get_the_category(); $cat0 = $cats ? $cats[0] : null;
    $cat_name = $cat0 ? $cat0->name : ''; $cat_slug = $cat0 ? $cat0->slug : '';
    $gc_pt    = $cat_slug ? ( $cat_pt_map[ $cat_slug ] ?? null ) : null;
  ?>
  <a href="<?php the_permalink(); ?>" class="gc" data-cat="<?php echo esc_attr( $cat_slug ); ?>" data-aos="fade-up">
    <div class="gc__img-wrap">
      <?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'ah-card', [ 'class' => 'gc__img' ] );
      else : ?><div class="gc__img gc__img--fallback">📖</div><?php endif; ?>
      <?php if ( $cat_name ) : ?><span class="gc__cat"><?php echo esc_html( $cat_name ); ?></span><?php endif; ?>
    </div>
    <div class="gc__body">
      <?php if ( $gc_pt ) : ?>
      <span class="gc__pt-badge" style="--ptc:<?php echo esc_attr( $gc_pt->color ?? 'var(--accent)' ); ?>"><?php echo esc_html( $gc_pt->name ); ?></span>
      <?php endif; ?>
      <div class="gc__meta"><span class="gc__read-time">⏱ <?php echo esc_html( ah_reading_time( get_the_ID() ) ); ?></span></div>
      <h2 class="gc__title"><?php the_title(); ?></h2>
      <?php $excerpt = get_the_excerpt(); if ( $excerpt ) : ?><p class="gc__excerpt"><?php echo wp_trim_words( $excerpt, 18, '…' ); ?></p><?php endif; ?>
      <span class="gc__btn">Read Guide <span class="gc__arrow">→</span></span>
    </div>
  </a>
  <?php endwhile; wp_reset_postdata(); ?>
</div>

<?php if ( $guides_query->max_num_pages > 1 ) :
  $links = paginate_links( [
    'base'      => add_query_arg( 'category', $active_cat, $base_url ) . '&pg=%#%',
    'format'    => '', 'current' => $paged, 'total' => $guides_query->max_num_pages,
    'prev_text' => '← Prev', 'next_text' => 'Next →', 'type' => 'array',
  ] );
  if ( $links ) :
?>
<nav class="pagination" style="margin-top:40px">
  <ul class="pagination__list"><?php foreach ( $links as $l ) echo '<li class="pagination__item">' . $l . '</li>'; ?></ul>
</nav>
<?php endif; endif; ?>

<?php else : ?>
<div class="text-center" style="padding:48px 0">
  <div style="font-size:3rem;margin-bottom:16px">📚</div>
  <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:12px">No guides in this topic yet</h2>
  <p style="color:var(--text-secondary);margin-bottom:24px">Check back soon.</p>
  <a href="<?php echo esc_url( $base_url ); ?>" class="btn btn-outline">← All Topics</a>
</div>
<?php endif; ?>

<?php if ( $display_cats ) : ?>
<div style="margin-top:48px">
  <div class="section__header">
    <span class="section__eyebrow">Browse by Topic</span>
    <h2 class="section__title"><?php echo $args['active_pt_slug'] ?? '' ? esc_html( $active_pt->name ?? 'Topics' ) . ' Topics' : 'Explore More Topics'; ?></h2>
  </div>
  <div class="gcat-grid">
    <?php foreach ( $display_cats as $i => $cat ) :
      $cat = is_object( $cat ) ? (array) $cat : $cat;
      get_template_part( 'components/cards/guide-category-card', null, [ 'cat' => $cat, 'index' => $i ] );
    endforeach; ?>
  </div>
</div>
<?php endif; ?>
