<?php
defined( 'ABSPATH' ) || exit;

$settings = ah_get_settings();
$consult  = $settings['consultation_url'] ?? home_url( '/free-consultation/' );
$phone    = $settings['phone'] ?? '+44 7747 223762';
$whatsapp = $settings['whatsapp'] ?? '+447747223762';
?>
<section class="cta-section">
  <div class="container">
    <div class="cta-inner reveal">
      <div class="cta-badge">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
        <?php esc_html_e( 'Free — No Obligation', 'ah-theme' ); ?>
      </div>

      <h2><?php esc_html_e( 'Ready to Find Your Dream Home Without the Stress?', 'ah-theme' ); ?></h2>

      <p>
        <?php esc_html_e( "Book a free 30-minute discovery call. We'll listen to exactly what you need, show you what's possible in your budget, and explain how we work — with zero pressure to commit.", 'ah-theme' ); ?>
      </p>

      <div class="cta-actions">
        <a href="<?php echo esc_url( $consult ); ?>" class="btn btn-gold btn-lg">
          <?php esc_html_e( 'Book Free Discovery Call', 'ah-theme' ); ?>
        </a>
        <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="btn btn-outline-white btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.7A2 2 0 012 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
          </svg>
          <?php echo esc_html( $phone ); ?>
        </a>
      </div>

      <div class="cta-trust">
        <span>✓ <?php esc_html_e( '100+ Families Helped', 'ah-theme' ); ?></span>
        <span>✓ <?php esc_html_e( 'Avg. £22k Saved', 'ah-theme' ); ?></span>
        <span>✓ <?php esc_html_e( '5-Star Rated', 'ah-theme' ); ?></span>
      </div>

      <?php if ( $whatsapp ) : ?>
        <a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>"
           class="cta-whatsapp"
           target="_blank"
           rel="noopener noreferrer">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
          </svg>
          <?php esc_html_e( 'Or message us on WhatsApp', 'ah-theme' ); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>
