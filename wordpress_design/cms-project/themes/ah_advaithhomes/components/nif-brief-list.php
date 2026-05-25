<?php

defined( 'ABSPATH' ) || exit;

$posts     = $args['posts']     ?? [];
$max_pages = $args['max_pages'] ?? 1;
$paged     = $args['paged']     ?? 1;
$base_url  = $args['base_url']  ?? get_permalink();
$eyebrow   = $args['eyebrow']   ?? TXT_IN_BRIEF;

if ( empty( $posts ) ) return;
?>
<section class="nif-portal-section" aria-label="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_EYEBROW ); ?>">

  <div class="nif-portal-section-row">
    <span class="nif-section-label--primary"><?php echo esc_html( $eyebrow ); ?></span>
  </div>

  <div class="nif-brief-list">
    <?php foreach ( $posts as $p ) :
      $d       = nif_get_post_data( $p );
      $cats    = get_the_category( $p->ID );
      $cat1    = $cats[0] ?? null;
      $cat2    = $cats[1] ?? null;
      $excerpt = wp_trim_words( get_the_excerpt( $p->ID ) ?: $p->post_content, 35, '…' );
    ?>
    <article class="nif-brief-item" data-aos="fade-up">

      <!-- Thumbnail -->
      <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-brief-item__img" tabindex="-1" aria-hidden="true">
        <?php if ( $d['thumb_url'] ) : ?>
          <img src="<?php echo esc_url( $d['thumb_url'] ); ?>"
               alt="<?php echo esc_attr( TXT_PHP_ECHO_ESC_ATTR_GET_THE_TITLE_P_ID ); ?>"
               loading="lazy" decoding="async">
        <?php else : ?>
          <span class="nif-brief-item__placeholder" aria-hidden="true"><?php echo esc_html( $d['emoji'] ); ?></span>
        <?php endif; ?>
      </a>

      <!-- Content -->
      <div class="nif-brief-item__body">
        <div class="nif-brief-item__badges">
          <?php if ( $cat1 ) : ?>
            <span class="nif-tile-badge" data-slug="<?php echo esc_attr( $cat1->slug ); ?>">
              <?php echo esc_html( $cat1->name ); ?>
            </span>
          <?php endif; ?>
          <?php if ( $cat2 ) : ?>
            <span class="nif-tile-badge nif-tile-badge--secondary" data-slug="<?php echo esc_attr( $cat2->slug ); ?>">
              <?php echo esc_html( $cat2->name ); ?>
            </span>
          <?php endif; ?>
        </div>

        <h3 class="nif-brief-item__title">
          <a href="<?php echo esc_url( $d['permalink'] ); ?>">
            <?php echo esc_html( get_the_title( $p->ID ) ); ?>
          </a>
        </h3>

        <p class="nif-brief-item__excerpt"><?php echo esc_html( $excerpt ); ?></p>

        <div class="nif-brief-item__meta">
          <?php if ( $d['read_time'] ) : ?>
            <span class="nif-meta-time"><?php echo esc_html( $d['read_time'] ); ?></span>
          <?php endif; ?>
          <a href="<?php echo esc_url( $d['permalink'] ); ?>" class="nif-brief-item__cta">
            <?php echo esc_html( TXT_CONTINUE_READING ); ?> <span aria-hidden="true">→</span>
          </a>
        </div>
      </div>

    </article>
    <?php endforeach; ?>
  </div>

  <!-- Pagination - ?pg=X avoids WordPress's redirect_canonical for ?page and ?paged -->
  <?php
  $sep = strpos( $base_url, '?' ) !== false ? '&' : '?';
  if ( $max_pages > 1 ) :
    $links = paginate_links( [
      'base'      => $base_url . $sep . 'pg=%#%',
      'format'    => '',
      'current'   => $paged,
      'total'     => $max_pages,
      'prev_text' => '← Prev',
      'next_text' => 'Next →',
      'type'      => 'array',
    ] );
    if ( $links ) : ?>
  <nav class="nif-brief-pagination" aria-label="<?php echo esc_attr( TXT_PAGE_NAVIGATION ); ?>">
    <?php foreach ( $links as $link ) echo ( '<span class="nif-brief-page-link">' . $link . '</span>' ); ?>
  </nav>
  <?php endif; ?>
  
  <?php endif; ?>

</section>
