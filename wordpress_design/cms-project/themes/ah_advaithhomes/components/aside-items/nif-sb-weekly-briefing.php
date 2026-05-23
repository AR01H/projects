<?php
/**
 * Component: NIF Sidebar — Weekly Market Briefing
 *
 * Self-contained newsletter signup card. No $args dependencies.
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="nif-sb-card nif-sb-card--briefing" aria-label="<?php esc_attr_e( 'Need to be a part?', 'ah-theme' ); ?>">
  <div class="nif-sb-card__header">
    <span class="nif-section-label--primary"><?php esc_html_e( 'Need to be a part?', 'ah-theme' ); ?></span>
  </div>
  <p class="nif-sb-briefing__desc">
    <?php esc_html_e( CTA_DESCRIPTION, 'ah-theme' ); ?>
  </p>
  <form class="nif-sb-briefing__form" data-form="briefing" novalidate>
    <label for="nif-briefing-email" class="screen-reader-text">
      <?php esc_html_e( 'Your email address', 'ah-theme' ); ?>
    </label>
    <a href="/contact" >
      <button type="submit" class="nif-sb-briefing__btn">
        Contact
      </button>
    </a>
  </form>
</div>