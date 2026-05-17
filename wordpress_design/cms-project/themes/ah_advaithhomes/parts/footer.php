<?php
defined( 'ABSPATH' ) || exit;

$settings = ah_get_settings();
$guides   = ah_buying_guides_nav();
$phone    = $settings['phone']    ?? '+447747223762';
$email    = $settings['email']    ?? 'contact@advaithhomes.co.uk';
$address  = $settings['address']  ?? 'London & Nationwide';
$consult  = $settings['consultation_url'] ?? home_url( '/free-consultation/' );
$fb       = $settings['facebook_url']  ?? '';
$ig       = $settings['instagram_url'] ?? '';
$tw       = $settings['twitter_url']   ?? '';
$li       = $settings['linkedin_url']  ?? '';
$yt       = $settings['youtube_url']   ?? '';
$year     = gmdate( 'Y' );
?>
</div><!-- #page-content -->

<?php $above_footer = ah_get_html_block( 'above_footer' ); if ( $above_footer ) echo $above_footer; ?>

<footer class="footer" role="contentinfo">
  <div class="container">
    <div class="footer__grid">

      <!-- Brand Column -->
      <div>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__logo" style="margin-bottom:0">
          <?php
          $logo = get_template_directory() . '/assets/images/logo.png';
          if ( file_exists( $logo ) ) :
          ?>
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="<?php bloginfo('name'); ?>" style="height:36px;filter:brightness(0) invert(1)">
          <?php else : ?>
            <div class="nav__logo-mark">AH</div>
            <span style="color:white;font-size:1.4rem">Advaith <em style="font-style:italic;font-family:var(--font-accent)">Homes</em></span>
          <?php endif; ?>
        </a>
        <p class="footer__brand-desc">
          <?php esc_html_e( "The UK's dedicated buyer's agent — we work exclusively for you, not the seller. Saving you time, stress, and thousands of pounds on your most important purchase.", 'ah-theme' ); ?>
        </p>
        <div class="footer__badge">🇬🇧 <?php esc_html_e( 'Proudly serving UK home buyers', 'ah-theme' ); ?></div>
        <div class="footer__socials" style="margin-top:16px">
          <?php if ( $fb ) : ?><a href="<?php echo esc_url( $fb ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="Facebook">📘</a><?php endif; ?>
          <?php if ( $tw ) : ?><a href="<?php echo esc_url( $tw ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="Twitter/X">🐦</a><?php endif; ?>
          <?php if ( $ig ) : ?><a href="<?php echo esc_url( $ig ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="Instagram">📸</a><?php endif; ?>
          <?php if ( $yt ) : ?><a href="<?php echo esc_url( $yt ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="YouTube">▶️</a><?php endif; ?>
          <?php if ( $li ) : ?><a href="<?php echo esc_url( $li ); ?>" target="_blank" rel="noopener" class="footer__social" aria-label="LinkedIn">💼</a><?php endif; ?>
          <?php if ( ! $fb && ! $tw && ! $ig && ! $yt && ! $li ) : ?>
            <div class="footer__social">📘</div>
            <div class="footer__social">📸</div>
            <div class="footer__social">💼</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Buying Guides Column -->
      <div>
        <div class="footer__col-title"><?php esc_html_e( 'Buying Guides', 'ah-theme' ); ?></div>
        <div class="footer__links">
          <?php foreach ( $guides as $g ) :
            $url = home_url( '/guides/' . $g['slug'] . '/' );
            ?>
            <a href="<?php echo esc_url( $url ); ?>"
               class="footer__link"
               <?php if ( ! empty( $g['highlight'] ) ) echo 'style="color:var(--client-color-100);font-weight:700"'; ?>>
              <?php echo esc_html( $g['title'] ); ?>
            </a>
          <?php endforeach; ?>
          <a href="<?php echo esc_url( $consult ); ?>" class="footer__link">
            <?php esc_html_e( 'Free Consultation Guide', 'ah-theme' ); ?>
          </a>
        </div>
      </div>

      <!-- Company Column -->
      <div>
        <div class="footer__col-title"><?php esc_html_e( 'Company', 'ah-theme' ); ?></div>
        <div class="footer__links">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>"               class="footer__link"><?php esc_html_e( 'Home', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"      class="footer__link"><?php esc_html_e( 'Services', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"         class="footer__link"><?php esc_html_e( 'About Us', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/client-stories/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Client Stories', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"          class="footer__link"><?php esc_html_e( 'Blog', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"       class="footer__link"><?php esc_html_e( 'Contact', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Privacy Policy', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"         class="footer__link"><?php esc_html_e( 'Terms & Conditions', 'ah-theme' ); ?></a>
          <a href="<?php echo esc_url( home_url( '/refund-policy/' ) ); ?>" class="footer__link"><?php esc_html_e( 'Refund Policy', 'ah-theme' ); ?></a>
        </div>
      </div>

      <!-- Contact Column -->
      <div>
        <div class="footer__col-title"><?php esc_html_e( 'Get In Touch', 'ah-theme' ); ?></div>

        <?php if ( $phone ) : ?>
        <div class="footer__contact-item">
          <span class="footer__contact-icon">📞</span>
          <div>
            <a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" style="color:white;font-weight:600">
              <?php echo esc_html( $phone ); ?>
            </a>
            <div style="font-size:.78rem;margin-top:2px"><?php esc_html_e( 'Mon–Sat, 9am–6pm', 'ah-theme' ); ?></div>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $email ) : ?>
        <div class="footer__contact-item">
          <span class="footer__contact-icon">✉️</span>
          <div>
            <a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:white;font-weight:600">
              <?php echo esc_html( $email ); ?>
            </a>
            <div style="font-size:.78rem;margin-top:2px"><?php esc_html_e( 'We reply within 2 hours', 'ah-theme' ); ?></div>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $address ) : ?>
        <div class="footer__contact-item">
          <span class="footer__contact-icon">📍</span>
          <div>
            <div style="color:white;font-weight:600"><?php echo esc_html( $address ); ?></div>
            <div style="font-size:.78rem;margin-top:2px"><?php esc_html_e( 'Covering all of England & Wales', 'ah-theme' ); ?></div>
          </div>
        </div>
        <?php endif; ?>

        <div style="margin-top:20px">
          <a href="<?php echo esc_url( $consult ); ?>" class="btn btn-gold btn-sm" style="width:100%;justify-content:center">
            <?php esc_html_e( 'Book Free Consultation →', 'ah-theme' ); ?>
          </a>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer__bottom">
      <div>© <?php echo esc_html( $year ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'ah-theme' ); ?></div>
      <div class="footer__legal">
        <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'ah-theme' ); ?></a>
        <a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'ah-theme' ); ?></a>
        <a href="<?php echo esc_url( home_url( '/refund-policy/' ) ); ?>"><?php esc_html_e( 'Refund Policy', 'ah-theme' ); ?></a>
      </div>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
