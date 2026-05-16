<?php
defined( 'ABSPATH' ) || exit;

$slug      = get_query_var( 'ah_post_slug' );
$post_data = null;

if ( class_exists( 'AH_Posts_Model' ) ) {
	$post_data = ( new AH_Posts_Model() )->find_by( 'slug', $slug );
}

// 404 if post not found
if ( ! $post_data ) {
	status_header( 404 );
	get_template_part( 'parts/header' );
	echo '<main id="main-content">';
	get_template_part( '404' );
	echo '</main>';
	get_template_part( 'parts/footer' );
	exit;
}

$title      = ah_val( $post_data, 'title',    'Untitled' );
$content    = ah_raw( $post_data, 'content',  '' );
$excerpt    = ah_raw( $post_data, 'excerpt',  '' );
$img_id     = ah_raw( $post_data, 'featured_image_id', 0 );
$img        = $img_id ? ah_media_url( (int) $img_id ) : ah_unsplash( '1560518883-ce09059eeffa', 1200, 600 );
$created    = ah_raw( $post_data, 'created_at', '' );
$author     = ah_val( $post_data, 'author',   'Advaith Homes' );
$read_time  = max( 1, (int) ceil( str_word_count( strip_tags( $content ) ) / 200 ) );
$date_fmt   = $created ? date_i18n( 'j F Y', strtotime( $created ) ) : '';
$settings   = ah_get_settings();
$consult    = $settings['consultation_url'] ?? home_url( '/free-consultation/' );

get_template_part( 'parts/header' );
?>
<main id="main-content">

  <!-- Article Hero -->
  <section class="article-hero reveal">
    <div class="container">
      <div class="article-hero__meta">
        <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="article-hero__back">
          ← <?php esc_html_e( 'Back to Blog', 'ah-theme' ); ?>
        </a>
        <?php if ( $date_fmt ) : ?>
          <span class="article-hero__date"><?php echo esc_html( $date_fmt ); ?></span>
        <?php endif; ?>
        <span class="article-hero__read"><?php echo esc_html( $read_time ); ?> min read</span>
      </div>
      <h1 class="article-hero__title reveal reveal-delay-1"><?php echo esc_html( $title ); ?></h1>
      <?php if ( $excerpt ) : ?>
        <p class="article-hero__excerpt reveal reveal-delay-2"><?php echo esc_html( $excerpt ); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Featured Image -->
  <?php if ( $img ) : ?>
  <div class="article-featured-img reveal">
    <div class="container">
      <img src="<?php echo esc_url( $img ); ?>"
           alt="<?php echo esc_attr( $title ); ?>"
           class="article-featured-img__img"
           loading="eager">
    </div>
  </div>
  <?php endif; ?>

  <!-- Article Body -->
  <section class="section">
    <div class="container">
      <div class="article-layout">

        <!-- Main content -->
        <div class="article-content reveal">
          <?php if ( $content ) : ?>
            <?php echo wp_kses_post( $content ); ?>
          <?php else : ?>
            <p><?php esc_html_e( 'Content coming soon.', 'ah-theme' ); ?></p>
          <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="article-sidebar reveal reveal-delay-2">
          <div class="article-sidebar__cta">
            <div class="article-sidebar__icon">🏡</div>
            <h3><?php esc_html_e( 'Ready to Buy Smarter?', 'ah-theme' ); ?></h3>
            <p><?php esc_html_e( 'Get expert guidance from a dedicated buyer\'s agent. Free initial consultation.', 'ah-theme' ); ?></p>
            <a href="<?php echo esc_url( $consult ); ?>" class="btn btn-primary btn-block" style="justify-content:center;margin-top:16px">
              <?php esc_html_e( 'Book Free Consultation', 'ah-theme' ); ?>
            </a>
            <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>" class="btn btn-secondary btn-block" style="justify-content:center;margin-top:10px">
              <?php esc_html_e( 'Our Services', 'ah-theme' ); ?>
            </a>
          </div>
          <div class="article-sidebar__share" style="margin-top:24px">
            <p style="font-weight:600;color:var(--slate-700);margin-bottom:10px"><?php esc_html_e( 'Share this article', 'ah-theme' ); ?></p>
            <?php $current_url = esc_url( home_url( '/blog/' . $slug . '/' ) ); ?>
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( $current_url ); ?>&text=<?php echo urlencode( $title ); ?>"
               target="_blank" rel="noopener" class="btn btn-sm btn-secondary" style="margin-right:8px;margin-bottom:8px">
              𝕏 Twitter
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode( $current_url ); ?>"
               target="_blank" rel="noopener" class="btn btn-sm btn-secondary" style="margin-bottom:8px">
              LinkedIn
            </a>
          </div>
        </aside>

      </div>
    </div>
  </section>

  <!-- Back to Blog -->
  <section class="section" style="padding-top:0">
    <div class="container" style="text-align:center">
      <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="btn btn-secondary">
        ← <?php esc_html_e( 'All Articles', 'ah-theme' ); ?>
      </a>
    </div>
  </section>

</main>
<?php get_template_part( 'parts/footer' ); ?>
