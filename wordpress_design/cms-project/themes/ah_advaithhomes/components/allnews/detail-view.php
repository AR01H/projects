<?php
defined( 'ABSPATH' ) || exit;
$base_url  = $args['base_url']  ?? get_permalink();
$single    = $args['single']    ?? null;
$s_title   = $args['s_title']   ?? '';
$s_content = $args['s_content'] ?? '';
$s_thumb   = $args['s_thumb']   ?? '';
$related   = $args['related']   ?? [];
$rel_terms = $args['rel_terms'] ?? [];
$sidebar   = $args['sidebar']   ?? [];
if ( ! $single ) return;
?>
<div class="nif-portal-bg">
<div class="container">
<div class="nif-portal-wrap">

<main class="nif-portal-main">
<article aria-label="<?php echo esc_attr( TXT_NEWS_ARTICLE ); ?>">
  <?php if ( $s_thumb ) : ?>
  <div class="news-single__hero" style="margin-bottom:32px;border-radius:var(--r-lg);overflow:hidden;aspect-ratio:16/9;box-shadow:var(--shadow-md)">
    <img src="<?php echo esc_url( $s_thumb ); ?>" alt="<?php echo esc_attr( $s_title ); ?>"
         style="width:100%;height:100%;object-fit:cover" loading="eager" decoding="async">
  </div>
  <?php endif; ?>

  <?php if ( $s_content ) : ?>
  <div class="news-single__body prose" style="margin-inline:auto;color:var(--text-secondary);font-size:1.05rem;line-height:1.85;">
    <?php echo wp_kses_post( wpautop( $s_content ) ); ?>
  </div>
  <?php else : ?>
  <p style="max-width:var(--max-w-text);margin-inline:auto;color:var(--text-muted);font-style:italic;">No further details for this update.</p>
  <?php endif; ?>

  <?php if ( class_exists( 'AH_Theme_Content_Taxonomy' ) && AH_Theme_Content_Taxonomy::has_terms_of_type( (int) $single->id, 'news_bar_item', 'useful-links' ) ) : ?>
  <div class="sidebar-card" style="max-width:var(--max-w-text);margin:32px auto 0">
    <div class="sidebar-card__title"><?php echo esc_html( TXT_USEFUL_LINKS ); ?></div>
    <div class="toc">
      <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>"         class="toc__item">📚 <?php echo esc_html( TXT_ALL_BUYING_GUIDES ); ?></a>
      <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"       class="toc__item">✦ <?php echo esc_html( TXT_OUR_SERVICES ); ?></a>
      <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="toc__item">⭐ <?php echo esc_html( TXT_CLIENT_STORIES ); ?></a>
    </div>
  </div>
  <?php endif; ?>
</article>

<?php if ( ! empty( $related ) ) : ?>
<section class="news-single__related" aria-label="<?php echo esc_attr( TXT_NEWS_ARTICLE ); ?>" style="margin-top:56px;padding-top:40px;border-top:1px solid var(--border)">
  <h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:24px">Related News</h2>
  <div class="post-grid">
    <?php foreach ( $related as $r_item ) :
      $r_terms   = $rel_terms[ $r_item->id ] ?? [];
      $r_cat     = ! empty( $r_terms ) ? $r_terms[0]->name : 'News';
      $r_link    = ( ! empty( $r_item->link_url ) && filter_var( $r_item->link_url, FILTER_VALIDATE_URL ) )
        ? $r_item->link_url
        : esc_url( add_query_arg( 'item', $r_item->id, $base_url ) );
      $r_target  = ( ! empty( $r_item->link_url ) && filter_var( $r_item->link_url, FILTER_VALIDATE_URL ) && ! empty( $r_item->link_target ) )
        ? 'target="' . esc_attr( $r_item->link_target ) . '"' : '';
      $r_title   = $r_item->text ?? '';
      $r_excerpt = ! empty( $r_item->content ) ? wp_trim_words( wp_strip_all_tags( $r_item->content ), 18, '…' ) : '';
      $r_thumb   = ! empty( $r_item->image_id )
        ? ( wp_get_attachment_image_url( (int) $r_item->image_id, 'ah-card' ) ?: wp_get_attachment_image_url( (int) $r_item->image_id, 'medium_large' ) )
        : '';
      $r_date    = ! empty( $r_item->start_date ) ? date_i18n( 'd M Y', strtotime( $r_item->start_date ) ) : '';
    ?>
    <article class="blog-card">
      <div class="blog-card__img-wrap">
        <a href="<?php echo esc_url( $r_link ); ?>" <?php echo $r_target; ?> class="blog-card__img-link" tabindex="-1" aria-hidden="true">
          <?php if ( $r_thumb ) : ?>
            <img src="<?php echo esc_url( $r_thumb ); ?>" alt="<?php echo esc_attr( $r_title ); ?>" class="blog-card__img" loading="lazy" decoding="async">
          <?php else : ?>
            <div class="blog-card__img-placeholder">📰</div>
          <?php endif; ?>
        </a>
        <div class="blog-card__overlay">
          <div class="blog-card__badges">
            <span class="blog-card__cat"><?php echo esc_html( $r_cat ); ?></span>
            <?php if ( $r_date ) : ?><span class="blog-card__read-time"><?php echo esc_html( $r_date ); ?></span><?php endif; ?>
          </div>
          <h2 class="blog-card__title">
            <a href="<?php echo esc_url( $r_link ); ?>" <?php echo $r_target; ?>><?php echo esc_html( $r_title ); ?></a>
          </h2>
          <?php if ( $r_excerpt ) : ?>
          <div class="blog-card__desc-wrap">
            <div>
              <p class="blog-card__excerpt"><?php echo esc_html( $r_excerpt ); ?></p>
              <a href="<?php echo esc_url( $r_link ); ?>" <?php echo $r_target; ?> class="blog-card__read-btn">
                <?php echo esc_html( TXT_READ_MORE ); ?> <span aria-hidden="true">→</span>
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
</main>

<aside class="nif-portal-sidebar" aria-label="<?php echo esc_attr( TXT_MARKET_INFORMATION_AND_TOOLS ); ?>">
  <?php get_template_part( 'components/nif-sidebar', null, [
    'site_stats'     => $sidebar['site_stats']     ?? [],
    'news_bar_items' => $sidebar['news_bar_items'] ?? [],
    'popular_posts'  => $sidebar['popular_posts']  ?? [],
    'cats'           => $sidebar['cats']           ?? [],
    'active_cat'     => '',
    'permalink'      => $sidebar['permalink']      ?? '',
    'parent_terms'   => $sidebar['parent_terms']   ?? [],
  ] ); ?>
</aside>

</div>
</div>
</div>
