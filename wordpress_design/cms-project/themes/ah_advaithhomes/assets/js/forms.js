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
    var $status  = $form.find('.ah-form__status');
    var $success = $form.find('.form-notice--success');
    var $error   = $form.find('.form-notice--error');
    var hasFiles = $form.find('[type="file"]').length > 0;

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

    // UI - loading state
    $btn.prop('disabled', true).data('orig-text', $btn.text()).text('Sending…');
    $status.text('').removeClass('ah-form__status--success ah-form__status--error');
    $success.removeClass('is-visible');
    $error.removeClass('is-visible');
    $form.find('.form-control, .form-input').removeClass('has-error');
    $form.find('.form-error').text('');

    var ajaxUrl = '/wp-admin/admin-ajax.php';
    var nonce   = (window.ahTheme && ahTheme.nonce) || '';

    var ajaxOpts = {
      url:      ajaxUrl,
      type:     'POST',
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
        showStatus('Network error - please check your connection and try again.', false);
      },
      complete: function () {
        $btn.prop('disabled', false).text($btn.data('orig-text') || 'Send');
      }
    };

    if (hasFiles) {
      // Use FormData so file inputs are included
      var fd = new FormData($form[0]);
      fd.append('action',    'ah_theme_form_submit');
      fd.append('form_type', formType);
      fd.append('nonce',     nonce);
      fd.append('page_url',  window.location.href);
      ajaxOpts.data        = fd;
      ajaxOpts.processData = false;
      ajaxOpts.contentType = false;
    } else {
      var data = $form.serializeArray();
      data.push({ name: 'action',    value: 'ah_theme_form_submit' });
      data.push({ name: 'form_type', value: formType });
      data.push({ name: 'nonce',     value: nonce });
      data.push({ name: 'page_url',  value: window.location.href });
      ajaxOpts.data = data;
    }

    $.ajax(ajaxOpts);
  });

  // ── Newsletter inline form ─────────────────────────────────────────────────
  $(document).on('submit', '[data-ah-newsletter]', function (e) {
    e.preventDefault();
    var $form = $(this);
    var email = $form.find('[type="email"]').val().trim();
    var $btn  = $form.find('[type="submit"]');

    if (!email) return;

    $btn.prop('disabled', true).text('…');

    $.ajax({
      url:  '/wp-admin/admin-ajax.php',
      type: 'POST',
      data: {
        action: 'ah_newsletter_subscribe',
        email:  email,
        nonce:  (window.ahTheme && ahTheme.nonce) || '',
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
