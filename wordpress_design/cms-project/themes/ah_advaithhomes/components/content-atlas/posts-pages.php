<?php
defined( 'ABSPATH' ) || exit;
$recent_posts = $args['recent_posts'] ?? [];
$recent_pages = $args['recent_pages'] ?? [];
?>
<section class="section" aria-label="<?php echo esc_attr( TXT_PUBLISHED_CONTENT ); ?>">
  <div class="container">
    <div class="section__header">
      <span class="section__eyebrow">Posts and Pages</span>
      <h2 class="section__title">The Main Reading Content on the Site</h2>
    </div>
    <div class="atlas-two-col">
      <div class="atlas-card" data-aos="fade-up">
        <h3>Recent Blog Posts</h3>
        <ul class="atlas-list">
          <?php if ( $recent_posts ) : foreach ( $recent_posts as $post_item ) : ?>
            <li>
              <strong><a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>"><?php echo esc_html( get_the_title( $post_item ) ); ?></a></strong>
              <div class="atlas-muted"><?php echo esc_html( get_the_date( 'j M Y', $post_item ) ); ?></div>
              <div class="atlas-muted"><?php echo esc_html( wp_trim_words( $post_item->post_excerpt ?: wp_strip_all_tags( $post_item->post_content ), 18 ) ); ?></div>
            </li>
          <?php endforeach; else : ?>
            <li class="atlas-muted">No published posts yet.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="atlas-card" data-aos="fade-up" data-delay="100">
        <h3>Recent WordPress Pages</h3>
        <ul class="atlas-list">
          <?php if ( $recent_pages ) : foreach ( $recent_pages as $page_item ) : ?>
            <li>
              <strong><a href="<?php echo esc_url( get_permalink( $page_item ) ); ?>"><?php echo esc_html( get_the_title( $page_item ) ); ?></a></strong>
              <div class="atlas-muted">Modified <?php echo esc_html( get_the_modified_date( 'j M Y', $page_item ) ); ?></div>
            </li>
          <?php endforeach; else : ?>
            <li class="atlas-muted">No published pages yet.</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</section>
