<?php
defined( 'ABSPATH' ) || exit;
$base_url     = $args['base_url']     ?? get_permalink();
$news_items   = $args['news_items']   ?? [];
$item_terms   = $args['item_terms']   ?? [];
$unique_terms = $args['unique_terms'] ?? [];
$active_cat   = $args['active_cat']   ?? '';
$paged        = $args['paged']        ?? 1;
$max_pages    = $args['max_pages']    ?? 1;
?>
<?php if ( ! empty( $unique_terms ) ) : ?>
<div style="border-bottom:1px solid var(--border);background:var(--bg-alt)">
  <div class="container" style="padding-top:4px;padding-bottom:4px">
    <div class="filter-tabs" role="tablist" aria-label="<?php echo esc_attr( TXT_FILTER_BY_CATEGORY ); ?>">
      <a href="<?php echo esc_url( $base_url ); ?>"
         class="filter-tab<?php echo ! $active_cat ? ' filter-tab--active' : ''; ?>"
         role="tab" aria-selected="<?php echo ! $active_cat ? 'true' : 'false'; ?>">
        <?php echo esc_html( TXT_ALL ); ?>
      </a>
      <?php foreach ( $unique_terms as $term ) :
        $is_active = ( $active_cat === $term->slug );
      ?>
      <a href="<?php echo esc_url( add_query_arg( 'category', $term->slug, $base_url ) ); ?>"
         class="filter-tab<?php echo $is_active ? ' filter-tab--active' : ''; ?>"
         role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>">
        <?php echo esc_html( $term->name ); ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<section class="section" aria-label="<?php echo esc_attr( TXT_NEWS_ARTICLE ); ?>">
  <div class="container">
    <?php if ( ! empty( $news_items ) ) : ?>
    <div class="post-grid">
      <?php foreach ( $news_items as $idx => $item ) :
        $terms      = $item_terms[ $item->id ] ?? [];
        $cat_label  = ! empty( $terms ) ? $terms[0]->name : 'News';
        $item_link  = ( ! empty( $item->link_url ) && filter_var( $item->link_url, FILTER_VALIDATE_URL ) )
          ? $item->link_url
          : esc_url( add_query_arg( 'item', $item->id, $base_url ) );
        $ext_target = ( ! empty( $item->link_url ) && filter_var( $item->link_url, FILTER_VALIDATE_URL ) && ! empty( $item->link_target ) )
          ? 'target="' . esc_attr( $item->link_target ) . '"' : '';
        $item_title = $item->text ?? '';
        $excerpt    = ! empty( $item->content ) ? wp_trim_words( wp_strip_all_tags( $item->content ), 18, '…' ) : '';
        $thumb_url  = ! empty( $item->image_id )
          ? ( wp_get_attachment_image_url( (int) $item->image_id, 'ah-card' ) ?: wp_get_attachment_image_url( (int) $item->image_id, 'medium_large' ) )
          : '';
        $date_str   = ! empty( $item->start_date ) ? date_i18n( 'd M Y', strtotime( $item->start_date ) ) : '';
        $delay      = min( $idx % 3, 2 ) * 80;
      ?>
      <article class="blog-card" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
        <div class="blog-card__img-wrap">
          <a href="<?php echo esc_url( $item_link ); ?>" <?php echo $ext_target; ?> class="blog-card__img-link" tabindex="-1" aria-hidden="true">
            <?php if ( $thumb_url ) : ?>
              <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $item_title ); ?>" class="blog-card__img" loading="lazy" decoding="async">
            <?php else : ?>
              <div class="blog-card__img-placeholder">📰</div>
            <?php endif; ?>
          </a>
          <div class="blog-card__overlay">
            <div class="blog-card__badges">
              <span class="blog-card__cat"><?php echo esc_html( $cat_label ); ?></span>
              <?php if ( $date_str ) : ?><span class="blog-card__read-time"><?php echo esc_html( $date_str ); ?></span><?php endif; ?>
            </div>
            <h2 class="blog-card__title">
              <a href="<?php echo esc_url( $item_link ); ?>" <?php echo $ext_target; ?>><?php echo esc_html( $item_title ); ?></a>
            </h2>
            <?php if ( $excerpt ) : ?>
            <div class="blog-card__desc-wrap">
              <div>
                <p class="blog-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
                <a href="<?php echo esc_url( $item_link ); ?>" <?php echo $ext_target; ?> class="blog-card__read-btn">
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

    <?php if ( $max_pages > 1 ) :
      $pg_base = $active_cat ? add_query_arg( 'category', $active_cat, $base_url ) : $base_url;
      $sep     = strpos( $pg_base, '?' ) !== false ? '&' : '?';
      $links   = paginate_links( [
        'base'      => $pg_base . $sep . 'pg=%#%',
        'format'    => '',
        'current'   => $paged,
        'total'     => $max_pages,
        'prev_text' => '← Prev',
        'next_text' => 'Next →',
        'type'      => 'array',
      ] );
      if ( $links ) :
    ?>
    <nav class="pagination" aria-label="<?php echo esc_attr( TXT_NEWS_NAVIGATION ); ?>" style="margin-top:48px">
      <ul class="pagination__list">
        <?php foreach ( $links as $link ) echo '<li class="pagination__item">' . $link . '</li>'; ?>
      </ul>
    </nav>
    <?php endif; endif; ?>

    <?php else : ?>
    <div class="text-center" style="padding:80px 24px">
      <div style="font-size:3rem;margin-bottom:12px">📰</div>
      <h2 style="font-size:1.25rem;margin-bottom:8px"><?php echo esc_html( TXT_NO_NEWS_YET ); ?></h2>
      <p style="color:var(--text-secondary)">
        <?php echo $active_cat ? esc_html( TXT_NOTHING_IN_THIS_CATEGORY_YET ) : esc_html( TXT_CHECK_BACK_SOON_FOR_UPDATES ); ?>
      </p>
      <?php if ( $active_cat ) : ?>
      <a href="<?php echo esc_url( $base_url ); ?>" class="btn btn-outline" style="margin-top:16px"><?php echo esc_html( TXT_VIEW_ALL ); ?></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
