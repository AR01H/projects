/* AH Theme – Admin JS */
(function ($) {
  'use strict';

  var nonce  = ahAdmin.nonce;
  var ajax   = ahAdmin.ajaxUrl;
  var confirm_msg = ahAdmin.confirm;

  // ----------------------------------------------------------------
  // AJAX helper
  // ----------------------------------------------------------------
  function ahAjax(data, cb) {
    data.nonce = nonce;
    $.post(ajax, data, cb, 'json');
  }

  // ----------------------------------------------------------------
  // Status toggle
  // ----------------------------------------------------------------
  $(document).on('click', '.ah-toggle-status', function (e) {
    e.preventDefault();
    var $btn   = $(this);
    var id     = $btn.data('id');
    var action = $btn.data('action');
    var table  = $btn.data('table');

    ahAjax({ action: 'ah_toggle_status', id: id, table: table, toggle_action: action }, function (res) {
      if (res.success) {
        location.reload();
      } else {
        alert(res.data || 'Error');
      }
    });
  });

  // ----------------------------------------------------------------
  // Delete record
  // ----------------------------------------------------------------
  $(document).on('click', '.ah-delete-item', function (e) {
    e.preventDefault();
    if (!confirm(confirm_msg)) return;
    var $btn  = $(this);
    var id    = $btn.data('id');
    var model = $btn.data('model');

    ahAjax({ action: 'ah_delete_item', id: id, model: model }, function (res) {
      if (res.success) {
        $btn.closest('tr').fadeOut(300, function () { $(this).remove(); });
      } else {
        alert(res.data || 'Error');
      }
    });
  });

  // ----------------------------------------------------------------
  // Sortable rows (tables with drag handles)
  // ----------------------------------------------------------------
  if ($('.ah-sortable-list').length) {
    $('.ah-sortable-list').sortable({
      handle: '.ah-sort-handle',
      axis: 'y',
      update: function (e, ui) {
        var order = [];
        $(this).find('tr[data-id]').each(function (i) {
          order.push({ id: $(this).data('id'), order: i });
        });
        ahAjax({ action: 'ah_update_sort_order', model: $(this).data('model'), order: JSON.stringify(order) });
      }
    });
  }

  // ----------------------------------------------------------------
  // Repeater fields (dynamic add/remove rows)
  // ----------------------------------------------------------------
  $(document).on('click', '.ah-add-repeater', function (e) {
    e.preventDefault();
    var $container = $(this).prev('.ah-repeater-container');
    var $tmpl      = $container.find('.ah-repeater-item:first').clone();
    $tmpl.find('input, textarea, select').val('');
    $tmpl.find('input[type="hidden"]').val(0);
    // Update names to use next index
    var count = $container.find('.ah-repeater-item').length;
    $tmpl.find('[name]').each(function () {
      var name = $(this).attr('name').replace(/\[\d+\]/, '[' + count + ']');
      $(this).attr('name', name);
    });
    $container.append($tmpl);
  });

  $(document).on('click', '.ah-repeater-remove', function (e) {
    e.preventDefault();
    var $container = $(this).closest('.ah-repeater-container');
    if ($container.find('.ah-repeater-item').length > 1) {
      $(this).closest('.ah-repeater-item').remove();
    } else {
      alert('At least one item is required.');
    }
  });

  // ----------------------------------------------------------------
  // Image / Media picker (WP Media Library)
  // ----------------------------------------------------------------
  var mediaFrame;
  var currentPicker;

  var VIDEO_EXTS = ['mp4','webm','ogv','ogg','mov','avi'];

  function ahIsVideoUrl(url) {
    if (!url) return false;
    var ext = url.split('?')[0].split('.').pop().toLowerCase();
    return VIDEO_EXTS.indexOf(ext) !== -1;
  }

  function ahPickerSetMedia($picker, id, url, isVideo) {
    $picker.find('.ah-image-id').val(id);
    $picker.find('.ah-image-placeholder').hide();
    $picker.addClass('has-image');

    // Remove any existing preview
    $picker.find('.ah-image-preview-wrap').remove();

    if (isVideo) {
      $picker.prepend(
        '<div class="ah-image-preview-wrap visible">' +
          '<video class="ah-video-preview" src="' + url + '" controls muted style="width:100%;max-height:200px;object-fit:cover;border-radius:8px;"></video>' +
        '</div>'
      );
      $picker.find('.ah-pick-image').text('Change Video');
    } else {
      $picker.prepend(
        '<div class="ah-image-preview-wrap visible">' +
          '<img class="ah-image-preview" src="' + url + '" alt="">' +
        '</div>'
      );
      $picker.find('.ah-pick-image').text('Change Image');
    }
  }

  function ahPickerClearImage($picker) {
    $picker.find('.ah-image-id').val(0);
    $picker.find('.ah-image-preview-wrap').remove();
    $picker.find('.ah-image-preview').removeClass('visible').css('display', '').attr('src', '');
    $picker.find('.ah-image-placeholder').show();
    $picker.removeClass('has-image');
    var type = $picker.data('picker-type') || 'image';
    var labels = { image: 'Choose Image', video: 'Choose Video', media: 'Choose Media' };
    $picker.find('.ah-pick-image').text(labels[type] || 'Choose Image');
  }

  // Initialise state on page load for all pickers
  $(function () {
    $('.ah-image-picker').each(function () {
      var $picker  = $(this);
      var $preview = $picker.find('.ah-image-preview');
      var $video   = $picker.find('.ah-video-preview');

      // Inject placeholder if not already present
      if ( ! $picker.find('.ah-image-placeholder').length ) {
        var type = $picker.data('picker-type') || 'image';
        var icons = { image: 'format-image', video: 'video-alt3', media: 'format-image' };
        var labels = { image: 'Click to choose image', video: 'Click to choose video', media: 'Click to choose image or video' };
        $picker.prepend(
          '<div class="ah-image-placeholder">' +
            '<span class="dashicons dashicons-' + (icons[type] || 'format-image') + '"></span>' +
            '<span>' + (labels[type] || 'Click to choose') + '</span>' +
          '</div>'
        );
      }

      if ($video.length && $video.attr('src')) {
        $video.css('display', 'block');
        $picker.addClass('has-image');
        $picker.find('.ah-image-placeholder').hide();
        $picker.find('.ah-pick-image').text('Change Video');
      } else if ($preview.hasClass('visible') && $preview.attr('src')) {
        $preview.css('display', 'block');
        $picker.addClass('has-image');
        $picker.find('.ah-image-placeholder').hide();
        $picker.find('.ah-pick-image').text('Change Image');
      }
    });
  });

  $(document).on('click', '.ah-pick-image, .ah-image-placeholder', function (e) {
    e.preventDefault();
    currentPicker = $(this).closest('.ah-image-picker');

    var pickerType = currentPicker.data('picker-type') || 'image';

    // Reuse frame only if same picker type, otherwise destroy and recreate
    if (mediaFrame && mediaFrame._ahPickerType === pickerType) {
      mediaFrame.open();
      return;
    }
    if (mediaFrame) {
      mediaFrame.destroy();
      mediaFrame = null;
    }

    var libraryType, frameTitle, buttonText;

    if (pickerType === 'video') {
      libraryType = 'video';
      frameTitle  = 'Select Video';
      buttonText  = 'Use this video';
    } else if (pickerType === 'media') {
      libraryType = '';
      frameTitle  = 'Select Image or Video';
      buttonText  = 'Use this media';
    } else {
      libraryType = 'image';
      frameTitle  = 'Select Image';
      buttonText  = 'Use this image';
    }

    var frameOpts = {
      title: frameTitle,
      button: { text: buttonText },
      multiple: false
    };
    if (libraryType) {
      frameOpts.library = { type: libraryType };
    }

    // Capture in a local variable so the 'select' handler always has a valid
    // frame reference.
    var frame = wp.media(frameOpts);
    frame._ahPickerType = pickerType;
    mediaFrame = frame;

    frame.on('select', function () {
      var selection = frame.state().get('selection');
      var model     = selection ? selection.first() : null;
      if ( ! model ) return;
      var attach = model.toJSON();
      var mime   = attach.mime || '';
      var isVideo = mime.indexOf('video/') === 0 || ahIsVideoUrl(attach.url);

      var url;
      if (isVideo) {
        url = attach.url || '';
      } else {
        url = ( attach.sizes && attach.sizes.large  && attach.sizes.large.url  )
           || ( attach.sizes && attach.sizes.medium && attach.sizes.medium.url )
           || attach.url || '';
      }
      if ( ! url ) return;
      ahPickerSetMedia(currentPicker, attach.id, url, isVideo);
    });

    frame.open();

    frame.on('close', function () { mediaFrame = null; });
  });

  $(document).on('click', '.ah-remove-image', function (e) {
    e.preventDefault();
    ahPickerClearImage($(this).closest('.ah-image-picker'));
  });

  // ----------------------------------------------------------------
  // Slug generator from title
  // ----------------------------------------------------------------
  $(document).on('input', '.ah-generate-slug-source', function () {
    var $target = $($(this).data('slug-target') || '#ah-slug');
    if ($target.data('manual')) return;
    var slug = $(this).val().toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '') // strip special chars
      .trim()
      .replace(/\s+/g, '-')         // spaces → hyphens
      .replace(/-{2,}/g, '-')       // collapse consecutive hyphens
      .replace(/^-+|-+$/g, '');     // strip leading/trailing hyphens
    $target.val(slug);
  });

  // When the user manually edits the slug field, lock it so title changes don't overwrite it.
  // Use the "Unlock to regenerate" link in the form to clear this lock.
  $(document).on('input', '.ah-slug-field', function () {
    $(this).data('manual', true);
  });

  // ----------------------------------------------------------------
  // Tab switching (client-side, no page reload)
  // ----------------------------------------------------------------
  $(document).on('click', '.ah-tab-link', function (e) {
    e.preventDefault();
    var target = $(this).data('tab');
    $('.ah-tab-link').removeClass('active');
    $(this).addClass('active');
    $('.ah-tab-panel').hide();
    $('#' + target).show();
    history.replaceState(null, '', '?page=' + $(this).data('page') + '&tab=' + target);
  });

  // Activate correct tab on load
  var urlTab = new URLSearchParams(location.search).get('tab');
  if (urlTab) {
    var $link = $('.ah-tab-link[data-tab="' + urlTab + '"]');
    if ($link.length) $link.trigger('click');
    else $('.ah-tab-link:first').trigger('click');
  } else if ($('.ah-tab-link').length) {
    $('.ah-tab-link:first').trigger('click');
  }

  // ----------------------------------------------------------------
  // Bulk actions
  // ----------------------------------------------------------------
  $('#ah-select-all').on('change', function () {
    $('input.ah-row-check').prop('checked', this.checked);
  });

  // ----------------------------------------------------------------
  // Confirm delete (form-based)
  // ----------------------------------------------------------------
  $(document).on('submit', '.ah-confirm-form', function (e) {
    if (!confirm(confirm_msg)) {
      e.preventDefault();
    }
  });

  // ----------------------------------------------------------------
  // AJAX inline save for sort orders (footer links, social, etc.)
  // ----------------------------------------------------------------
  $(document).on('click', '.ah-save-order', function (e) {
    e.preventDefault();
    var $btn   = $(this);
    var model  = $btn.data('model');
    var order  = [];
    $('[data-id]').each(function (i) {
      order.push({ id: $(this).data('id'), order: i });
    });
    ahAjax({ action: 'ah_update_sort_order', model: model, order: JSON.stringify(order) }, function (res) {
      if (res.success) {
        $btn.text('Saved!').delay(1500).queue(function (n) { $(this).text('Save Order'); n(); });
      }
    });
  });

})(jQuery);
