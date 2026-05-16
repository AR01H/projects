(function ($) {
  'use strict';

  /**
   * Generic AJAX form handler.
   * Any <form data-ah-form="TYPE"> will be handled here.
   * Types: contact, consultation, newsletter, valuation
   */

  $(document).on('submit', '[data-ah-form]', function (e) {
    e.preventDefault();

    var $form    = $(this);
    var formType = $form.data('ah-form') || 'contact';
    var $btn     = $form.find('[type="submit"]');
    var $status  = $form.find('.ah-form__status');   // simple single-div feedback
    var $success = $form.find('.form-notice--success');
    var $error   = $form.find('.form-notice--error');
    var data     = $form.serializeArray();

    function showStatus(msg, isSuccess) {
      if ($status.length) {
        $status.text(msg)
          .removeClass('ah-form__status--success ah-form__status--error')
          .addClass(isSuccess ? 'ah-form__status--success' : 'ah-form__status--error');
      } else if (isSuccess) {
        $success.addClass('is-visible').find('.notice-text').text(msg);
      } else {
        $error.addClass('is-visible').find('.notice-text').text(msg);
      }
    }

    // Add form type and nonce
    data.push({ name: 'action', value: 'ah_form_submit' });
    data.push({ name: 'form_type', value: formType });
    data.push({ name: 'nonce', value: (window.ahTheme && ahTheme.nonce) || '' });

    // UI — loading state
    $btn.prop('disabled', true).data('orig-text', $btn.text()).text('Sending…');
    $status.text('').removeClass('ah-form__status--success ah-form__status--error');
    $success.removeClass('is-visible');
    $error.removeClass('is-visible');
    $form.find('.form-control, .form-input').removeClass('has-error');
    $form.find('.form-error').text('');

    $.ajax({
      url:      (window.ahTheme && ahTheme.ajaxUrl) || '/wp-admin/admin-ajax.php',
      type:     'POST',
      data:     data,
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          showStatus(res.data.message || 'Thank you! We\'ll be in touch shortly.', true);
          $form[0].reset();

          if (res.data.redirect) {
            setTimeout(function () { window.location.href = res.data.redirect; }, 1500);
          }
        } else {
          var msg = (res.data && res.data.message) || 'Something went wrong. Please try again.';
          showStatus(msg, false);

          if (res.data && res.data.errors) {
            $.each(res.data.errors, function (field, errMsg) {
              $form.find('[name="' + field + '"]').addClass('has-error')
                .closest('.form-group').find('.form-error').text(errMsg);
            });
          }
        }
      },
      error: function () {
        showStatus('Network error — please check your connection and try again.', false);
      },
      complete: function () {
        $btn.prop('disabled', false).text($btn.data('orig-text') || 'Send');
      }
    });
  });

  // ── Newsletter inline form ─────────────────────────────────────────────────
  $(document).on('submit', '[data-ah-newsletter]', function (e) {
    e.preventDefault();
    var $form  = $(this);
    var email  = $form.find('[type="email"]').val().trim();
    var $btn   = $form.find('[type="submit"]');

    if (!email) return;

    $btn.prop('disabled', true).text('…');

    $.ajax({
      url:  (window.ahTheme && ahTheme.ajaxUrl) || '/wp-admin/admin-ajax.php',
      type: 'POST',
      data: {
        action:    'ah_newsletter_subscribe',
        email:     email,
        nonce:     (window.ahTheme && ahTheme.nonce) || '',
      },
      dataType: 'json',
      success: function (res) {
        $form.html(
          '<p style="color:var(--accent);font-weight:600;font-size:.875rem">' +
          '✓ ' + ((res.data && res.data.message) || 'Thanks! You\'re subscribed.') +
          '</p>'
        );
      },
      error: function () {
        $btn.prop('disabled', false).text('Subscribe');
      }
    });
  });

})(jQuery);
