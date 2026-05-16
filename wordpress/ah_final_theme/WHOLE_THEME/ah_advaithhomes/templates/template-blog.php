<?php
/**
 * Template Name: Blog Listing
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$categories = ah_get_post_categories();
$active_cat = isset( $_GET['cat'] ) ? sanitize_key( $_GET['cat'] ) : '';
?>
<main id="main-content">

  <!-- Page Hero -->
  <section class="page-hero page-hero--sm">
    <div class="container">
      <div class="eyebrow reveal"><?php esc_html_e( 'Knowledge Hub', 'ah-theme' ); ?></div>
      <h1 class="reveal reveal-delay-1"><?php esc_html_e( 'Buying Guides & Articles', 'ah-theme' ); ?></h1>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( 'Expert advice on every stage of buying a home in the UK — free, practical, and written by people who do this every day.', 'ah-theme' ); ?>
      </p>
    </div>
  </section>

  <section class="section">
    <div class="container">

      <!-- Category Filter -->
      <?php if ( ! empty( $categories ) ) : ?>
        <div class="blog-filter reveal" id="ahBlogFilter" role="navigation" aria-label="<?php esc_attr_e( 'Filter by category', 'ah-theme' ); ?>">
          <a href="<?php echo esc_url( get_permalink() ); ?>"
             class="filter-btn <?php echo '' === $active_cat ? 'is-active' : ''; ?>">
            <?php esc_html_e( 'All', 'ah-theme' ); ?>
          </a>
          <?php foreach ( $categories as $cat ) :
            $slug  = is_array( $cat ) ? ( $cat['slug'] ?? '' ) : ( $cat->slug ?? '' );
            $label = is_array( $cat ) ? ( $cat['name'] ?? '' ) : ( $cat->name ?? '' );
          ?>
            <a href="<?php echo esc_url( add_query_arg( 'cat', $slug, get_permalink() ) ); ?>"
               class="filter-btn <?php echo $active_cat === $slug ? 'is-active' : ''; ?>">
              <?php echo esc_html( $label ); ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php get_template_part( 'components/blog-grid' ); ?>

    </div>
  </section>

  <?php get_template_part( 'components/cta' ); ?>

</main>
<?php get_template_part( 'parts/footer' ); ?>
