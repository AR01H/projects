<?php defined( 'ABSPATH' ) || exit; ?>

<div class="news-ticker" id="ahNewsTicker" role="region" aria-label="<?php esc_attr_e( 'Property market news', 'ah-theme' ); ?>">
  <div class="news-ticker__label">
    <span class="news-ticker__pulse"></span>
    <?php esc_html_e( 'NEWS', 'ah-theme' ); ?>
  </div>
  <div class="news-ticker__track-wrap">
    <div class="news-ticker__track" id="ahNewsTrack">
      <!-- Populated by main.js from AH_THEME.newsItems -->
    </div>
  </div>
  <button class="news-ticker__close" id="ahTickerClose" aria-label="<?php esc_attr_e( 'Close news ticker', 'ah-theme' ); ?>">✕</button>
</div>
<script>document.body.classList.add('has-ticker');</script>
