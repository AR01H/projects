<?php
/**
 * Template Name: Contact Page
 */
defined( 'ABSPATH' ) || exit;

$contact_model = new AH_Contact_Model();
$pages_model   = new AH_Pages_Model();
$contact_page  = $pages_model->get_by_type( 'contact' );
$page_id       = $contact_page ? (int) $contact_page->id : 0;
$config        = $page_id ? $contact_model->get_page_config( $page_id ) : null;

$heading    = $config->heading        ?? 'Contact Us';
$basic_info = $config->basic_info     ?? '';
$email      = $config->email          ?? '';
$whatsapp   = $config->whatsapp_number ?? '';
$phone      = $config->phone_number   ?? '';
$maps_embed = $config->maps_embed_url ?? '';

get_header();
?>

<style>
.ah-contact-wrap{max-width:1200px;margin:0 auto;padding:60px 24px}
.ah-contact-hero{text-align:center;margin-bottom:56px}
.ah-contact-hero h1{font-size:2.4rem;font-weight:700;color:#1a1a2e;margin:0 0 14px;line-height:1.2}
.ah-contact-hero p{font-size:1.05rem;color:#6b7280;max-width:580px;margin:0 auto;line-height:1.75}
.ah-contact-grid{display:grid;grid-template-columns:1fr 400px;gap:48px;align-items:start}

/* Form card */
.ah-cf-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:36px;box-shadow:0 2px 16px rgba(0,0,0,.07)}
.ah-cf-card h2{font-size:1.3rem;font-weight:600;color:#1a1a2e;margin:0 0 24px}
.ah-cf-row{margin-bottom:18px}
.ah-cf-row label{display:block;font-size:13.5px;font-weight:500;color:#374151;margin-bottom:6px}
.req{color:#ef4444;margin-left:2px}
.ah-cf-row input,.ah-cf-row textarea{width:100%;padding:11px 14px;border:1.5px solid #d1d5db;border-radius:8px;font-size:15px;color:#1f2937;background:#fff;box-sizing:border-box;font-family:inherit;transition:border-color .18s,box-shadow .18s}
.ah-cf-row input:focus,.ah-cf-row textarea:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}
.ah-cf-row textarea{resize:vertical;min-height:130px}
.ah-cf-two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.ah-cf-btn{width:100%;padding:13px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .18s,transform .1s;margin-top:4px}
.ah-cf-btn:hover{background:#1d4ed8}
.ah-cf-btn:active{transform:scale(.98)}
.ah-cf-btn:disabled{background:#93c5fd;cursor:not-allowed}
.ah-hp{display:none!important;visibility:hidden}

/* Messages */
.ah-cf-msg{display:none;padding:13px 16px;border-radius:8px;margin-bottom:18px;font-size:14.5px;font-weight:500;align-items:center;gap:10px}
.ah-cf-msg.success{display:flex;background:#f0fdf4;border:1px solid #86efac;color:#166534}
.ah-cf-msg.error{display:flex;background:#fef2f2;border:1px solid #fca5a5;color:#991b1b}

/* Sidebar */
.ah-contact-info{display:flex;flex-direction:column;gap:20px}
.ah-info-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:28px;box-shadow:0 2px 12px rgba(0,0,0,.05)}
.ah-info-card h3{font-size:1rem;font-weight:600;color:#1a1a2e;margin:0 0 18px;padding-bottom:12px;border-bottom:2px solid #f3f4f6}
.ah-info-item{display:flex;align-items:flex-start;gap:12px;margin-bottom:16px;font-size:14.5px;color:#374151;line-height:1.5}
.ah-info-item:last-child{margin-bottom:0}
.ah-info-ico{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ah-info-ico.bl{background:#eff6ff}
.ah-info-ico.pu{background:#faf5ff}
.ah-info-ico svg{width:18px;height:18px}
.ah-info-lbl{font-size:11px;color:#9ca3af;font-weight:600;letter-spacing:.5px;margin-bottom:3px}
.ah-info-link{color:#2563eb;text-decoration:none;font-weight:500}
.ah-info-link:hover{text-decoration:underline}
.ah-wa-btn{display:inline-flex;align-items:center;gap:9px;background:#25d366;color:#fff;text-decoration:none;padding:11px 18px;border-radius:8px;font-weight:600;font-size:14.5px;transition:background .18s;margin-top:2px}
.ah-wa-btn:hover{background:#1db954;color:#fff}
.ah-maps-wrap{border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 2px 12px rgba(0,0,0,.05)}
.ah-maps-wrap iframe{display:block;width:100%;height:260px;border:none}

@media(max-width:900px){
  .ah-contact-grid{grid-template-columns:1fr}
  .ah-contact-hero h1{font-size:1.85rem}
}
@media(max-width:480px){
  .ah-cf-two-col{grid-template-columns:1fr}
  .ah-cf-card{padding:24px}
  .ah-contact-wrap{padding:40px 16px}
}
</style>

<main class="ah-contact-wrap">

  <div class="ah-contact-hero">
    <h1><?php echo esc_html( $heading ); ?></h1>
    <?php if ( $basic_info ) : ?>
      <p><?php echo nl2br( esc_html( $basic_info ) ); ?></p>
    <?php endif; ?>
  </div>

  <div class="ah-contact-grid">

    <!-- ── Contact Form ── -->
    <div class="ah-cf-card">
      <h2>Send Us a Message</h2>

      <div class="ah-cf-msg" id="ah-cf-success" role="alert">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="ah-success-text"></span>
      </div>
      <div class="ah-cf-msg" id="ah-cf-error" role="alert">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span id="ah-error-text"></span>
      </div>

      <form id="ah-contact-form" novalidate>
        <?php wp_nonce_field( 'ah_frontend_nonce', 'nonce' ); ?>

        <!-- Honeypot: hidden from humans, filled by bots -->
        <div class="ah-hp" aria-hidden="true">
          <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="ah-cf-two-col">
          <div class="ah-cf-row">
            <label for="ah-cf-name">Full Name <span class="req">*</span></label>
            <input type="text" id="ah-cf-name" name="full_name" placeholder="John Doe" required autocomplete="name">
          </div>
          <div class="ah-cf-row">
            <label for="ah-cf-email">Email Address <span class="req">*</span></label>
            <input type="email" id="ah-cf-email" name="email" placeholder="john@example.com" required autocomplete="email">
          </div>
        </div>

        <div class="ah-cf-two-col">
          <div class="ah-cf-row">
            <label for="ah-cf-phone">Phone Number</label>
            <input type="tel" id="ah-cf-phone" name="phone" placeholder="+91 98765 43210" autocomplete="tel">
          </div>
          <div class="ah-cf-row">
            <label for="ah-cf-subject">Subject</label>
            <input type="text" id="ah-cf-subject" name="subject" placeholder="How can we help?">
          </div>
        </div>

        <div class="ah-cf-row">
          <label for="ah-cf-message">Message <span class="req">*</span></label>
          <textarea id="ah-cf-message" name="message" placeholder="Tell us about your requirements…" required></textarea>
        </div>

        <button type="submit" class="ah-cf-btn" id="ah-cf-btn">
          <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          <span id="ah-btn-txt">Send Message</span>
        </button>
      </form>
    </div>

    <!-- ── Sidebar ── -->
    <div class="ah-contact-info">

      <?php if ( $email || $phone || $whatsapp ) : ?>
      <div class="ah-info-card">
        <h3>Get In Touch</h3>

        <?php if ( $email ) : ?>
        <div class="ah-info-item">
          <div class="ah-info-ico bl">
            <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <div>
            <div class="ah-info-lbl">EMAIL</div>
            <a href="mailto:<?php echo esc_attr( $email ); ?>" class="ah-info-link"><?php echo esc_html( $email ); ?></a>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $phone ) : ?>
        <div class="ah-info-item">
          <div class="ah-info-ico pu">
            <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          </div>
          <div>
            <div class="ah-info-lbl">PHONE</div>
            <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="ah-info-link"><?php echo esc_html( $phone ); ?></a>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $whatsapp ) : ?>
        <div class="ah-info-item">
          <div style="width:100%">
            <div class="ah-info-lbl" style="margin-bottom:8px">WHATSAPP</div>
            <a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>" target="_blank" rel="noopener noreferrer" class="ah-wa-btn">
              <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
              Chat on WhatsApp
            </a>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if ( $maps_embed ) : ?>
      <div class="ah-maps-wrap">
        <?php echo wp_kses( $maps_embed, array(
          'iframe' => array(
            'src' => true, 'width' => true, 'height' => true,
            'frameborder' => true, 'allowfullscreen' => true,
            'loading' => true, 'style' => true,
            'referrerpolicy' => true, 'title' => true,
          ),
        ) ); ?>
      </div>
      <?php endif; ?>

    </div><!-- .ah-contact-info -->
  </div><!-- .ah-contact-grid -->
</main>

<script>
(function ($) {
  var $form = $('#ah-contact-form');
  var $btn  = $('#ah-cf-btn');
  var $txt  = $('#ah-btn-txt');
  var $succ = $('#ah-cf-success');
  var $err  = $('#ah-cf-error');

  function showMsg($el, msg) {
    $succ.removeClass('success').hide();
    $err.removeClass('error').hide();
    $el.find('span').last().text(msg);
    $el.addClass($el.is($succ) ? 'success' : 'error').show();
    $el[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  $form.on('submit', function (e) {
    e.preventDefault();

    var name = $('#ah-cf-name').val().trim();
    var eml  = $('#ah-cf-email').val().trim();
    var msg  = $('#ah-cf-message').val().trim();

    if (!name)              { showMsg($err, 'Please enter your full name.');       $('#ah-cf-name').focus();    return; }
    if (!emailRe.test(eml)) { showMsg($err, 'Please enter a valid email address.'); $('#ah-cf-email').focus();   return; }
    if (!msg)               { showMsg($err, 'Please enter your message.');          $('#ah-cf-message').focus(); return; }

    $btn.prop('disabled', true);
    $txt.text('Sending…');

    var ajaxUrl = (typeof ahTheme !== 'undefined') ? ahTheme.ajaxUrl : '<?php echo esc_js( admin_url( "admin-ajax.php" ) ); ?>';

    $.post(ajaxUrl, $form.serialize() + '&action=ah_contact_submit', function (res) {
      if (res.success) {
        showMsg($succ, res.data.message);
        $form[0].reset();
      } else {
        showMsg($err, (res.data && res.data.message) ? res.data.message : 'Something went wrong. Please try again.');
      }
    }).fail(function () {
      showMsg($err, 'Network error. Please check your connection and try again.');
    }).always(function () {
      $btn.prop('disabled', false);
      $txt.text('Send Message');
    });
  });

  // Clear error highlight when user types
  $form.find('input, textarea').on('input', function () {
    if ($err.is(':visible')) { $err.hide(); }
  });
}(jQuery));
</script>

<?php get_footer(); ?>
