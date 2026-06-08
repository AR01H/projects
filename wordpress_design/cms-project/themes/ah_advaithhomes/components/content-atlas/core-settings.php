<?php
defined( 'ABSPATH' ) || exit;
$settings = $args['settings'] ?? [];
$home     = $args['home']     ?? [];
?>
<section class="section" aria-label="<?php echo esc_attr( TXT_CORE_SETTINGS ); ?>">
  <div class="container">
    <div class="section__header">
      <span class="section__eyebrow">Site Basics</span>
      <h2 class="section__title">The Core Information Behind the Site</h2>
    </div>
    <div class="atlas-two-col">
      <div class="atlas-card" data-aos="fade-up">
        <h3>Contact and Brand Settings</h3>
        <dl class="atlas-kv">
          <dt>Business Name</dt><dd><?php echo esc_html( $settings['business_name'] ?? get_bloginfo( 'name' ) ); ?></dd>
          <dt>Phone</dt><dd><?php echo esc_html( $settings['phone'] ?? 'Not set' ); ?></dd>
          <dt>WhatsApp</dt><dd><?php echo esc_html( $settings['whatsapp'] ?? 'Not set' ); ?></dd>
          <dt>Email</dt><dd><?php echo esc_html( $settings['email'] ?? get_option( 'admin_email' ) ); ?></dd>
          <dt>Address</dt><dd><?php echo nl2br( esc_html( $settings['address'] ?? 'Not set' ) ); ?></dd>
        </dl>
      </div>
      <div class="atlas-card" data-aos="fade-up" data-delay="100">
        <h3>Hero and CTA Direction</h3>
        <dl class="atlas-kv">
          <dt>Hero Heading</dt><dd><?php echo esc_html( $home['heading'] ?? 'Not set' ); ?></dd>
          <dt>Hero Subheading</dt><dd><?php echo esc_html( $home['subheading'] ?? 'Not set' ); ?></dd>
          <dt>Primary CTA</dt><dd><?php echo esc_html( $home['cta_primary_text'] ?? 'Not set' ); ?></dd>
          <dt>Primary URL</dt><dd><?php echo esc_html( $home['cta_primary_url'] ?? 'Not set' ); ?></dd>
          <dt>Secondary CTA</dt><dd><?php echo esc_html( $home['cta_secondary_text'] ?? 'Not set' ); ?></dd>
        </dl>
      </div>
    </div>
  </div>
</section>
