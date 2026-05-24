<?php get_header(); ?>
<main id="main-content">

  <section class="section" style="min-height:70vh;display:flex;align-items:center">
    <div class="container" style="text-align:center;max-width:600px">
      <div style="font-size:6rem;line-height:1;margin-bottom:24px">🏠</div>
      <p style="color:var(--text-muted);margin-bottom:40px">
        <?php echo  DESCRIPTION_404 ; ?>
      </p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        <a href="<?php echo esc_url( home_url( AH_LINK_HOME ) ); ?>" class="btn btn-primary">
          <?php echo esc_html( TXT_BACK_TO_HOME ); ?>
        </a>
        <a href="<?php echo esc_url( home_url( AH_LINK_GUIDES ) ); ?>" class="btn btn-outline">
          <?php echo esc_html( TXT_BROWSE_GUIDES ); ?>
        </a>
        <a href="<?php echo esc_url( home_url( AH_LINK_CONTACT_US ) ); ?>" class="btn btn-outline">
          <?php echo esc_html( AH_LABEL_CONTACT_US ); ?>
        </a>
      </div>
    </div>
  </section>

</main>

<?php get_template_part( 'components/cta-section', null, [] ); ?>

<?php get_footer(); ?>
