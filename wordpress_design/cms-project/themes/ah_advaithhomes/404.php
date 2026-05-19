<?php get_header(); ?>
<main id="main-content">

  <section class="section" style="min-height:70vh;display:flex;align-items:center">
    <div class="container" style="text-align:center;max-width:600px">
      <div style="font-size:6rem;line-height:1;margin-bottom:24px">🏠</div>
      <div style="font-size:5rem;font-weight:800;color:var(--accent);line-height:1;margin-bottom:8px">404</div>
      <p style="color:var(--text-muted);margin-bottom:40px">
        <?php esc_html_e( "The page you're looking for doesn't exist or may have moved. Let's get you back on track.", 'ah-theme' ); ?>
      </p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
          <?php esc_html_e( 'Back to Home', 'ah-theme' ); ?>
        </a>
        <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="btn btn-outline">
          <?php esc_html_e( 'Browse Guides', 'ah-theme' ); ?>
        </a>
        <a href="<?php echo esc_url( home_url( '/free-consultation/' ) ); ?>" class="btn btn-outline">
          <?php esc_html_e( 'Contact Us', 'ah-theme' ); ?>
        </a>
      </div>
    </div>
  </section>

</main>

<?php get_template_part( 'components/cta-section', null, [
  'eyebrow'   => 'While You\'re Here',
  'title'     => 'Let\'s Find Your<br><em>Perfect Property.</em>',
  'desc'      => 'Book a free consultation with one of our buyer\'s agents — no obligation, just honest advice on how we can help you buy smarter.',
  'cta_label' => 'Book a Free Call',
  'cta_url'   => home_url( '/contact/' ),
  'sec_label' => 'Browse Our Guides',
  'sec_url'   => home_url( '/guides/' ),
] ); ?>

<?php get_footer(); ?>
