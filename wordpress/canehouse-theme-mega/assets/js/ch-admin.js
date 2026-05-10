/**
 * CANEHOUSE ADMIN JS — ch-admin.js
 * Handles: Add/Edit modal, Save/Delete AJAX, Status toggle, Drag-drop sort, Image upload
 */
jQuery(function ($) {
    'use strict';

    var currentTab = new URLSearchParams(window.location.search).get('tab') || 'reviews';

    // ── IMAGE UPLOAD (WordPress Media Library) ────────────────────────────────
    var mediaFrame;
    $(document).on('click', '.ch-btn-upload', function (e) {
        e.preventDefault();
        var targetInput   = '#' + $(this).data('target');
        var previewDiv    = '#' + $(this).data('preview');

        if (mediaFrame) { mediaFrame.open(); return; }
        mediaFrame = wp.media({ title: 'Select Image', button: { text: 'Use This Image' }, multiple: false });
        mediaFrame.on('select', function () {
            var att = mediaFrame.state().get('selection').first().toJSON();
            $(targetInput).val(att.url);
            $(previewDiv).html('<img src="' + att.url + '" style="max-width:180px;max-height:120px;border-radius:8px;border:1px solid #e5e7eb;display:block;">');
            mediaFrame = null;
        });
        mediaFrame.open();
    });

    $(document).on('click', '.ch-btn-remove-img', function () {
        var targetInput = '#' + $(this).data('target');
        var previewDiv  = '#' + $(this).data('preview');
        $(targetInput).val('');
        $(previewDiv).html('');
    });

    // ── OPEN ADD MODAL ────────────────────────────────────────────────────────
    $(document).on('click', '#ch-add-new', function () {
        openModal('Add New', {});
    });

    // ── OPEN EDIT MODAL ───────────────────────────────────────────────────────
    $(document).on('click', '.ch-edit-btn', function () {
        var id  = $(this).data('id');
        var tab = $(this).data('tab');
        $.get(CH.ajax, { action: 'ch_get_item', tab: tab, id: id, nonce: CH.nonce }, function (res) {
            if (res.success) openModal('Edit Item', res.data);
        });
    });

    function openModal(title, data) {
        $('#ch-modal-title').text(title);
        $('#ch-form-id').val(data.id || 0);
        $('#ch-form-tab').val(currentTab);
        $('#ch-form-status').text('').removeClass('success error');
        mediaFrame = null;

        // Populate all fields
        $('#ch-item-form [id^="ch-f-"]').each(function () {
            var fname = this.id.replace('ch-f-', '');
            var val   = data[fname] !== undefined ? data[fname] : '';
            if (this.tagName === 'SELECT') {
                $(this).val(val);
            } else {
                $(this).val(val);
            }
            // Image preview
            if ($(this).attr('type') === 'hidden' && val) {
                var previewId = 'ch-img-preview-' + fname;
                $('#' + previewId).html('<img src="' + val + '" style="max-width:180px;max-height:120px;border-radius:8px;border:1px solid #e5e7eb;display:block;">');
            } else if ($(this).attr('type') === 'hidden') {
                var previewId2 = 'ch-img-preview-' + fname;
                $('#' + previewId2).html('');
            }
        });

        $('#ch-modal-overlay').fadeIn(150);
    }

    // ── CLOSE MODAL ───────────────────────────────────────────────────────────
    $('#ch-modal-close, #ch-form-cancel').on('click', closeModal);
    $('#ch-modal-overlay').on('click', function (e) { if (e.target === this) closeModal(); });
    function closeModal() { $('#ch-modal-overlay').fadeOut(150); }

    // ── SAVE FORM ─────────────────────────────────────────────────────────────
    $('#ch-item-form').on('submit', function (e) {
        e.preventDefault();
        var btn    = $('#ch-form-submit');
        var status = $('#ch-form-status');
        btn.text('Saving...').prop('disabled', true);
        status.text('').removeClass('success error');

        var data = $(this).serializeArray();
        data.push({ name: 'action', value: 'ch_save_item' });
        data.push({ name: 'nonce',  value: CH.nonce });

        $.post(CH.ajax, data, function (res) {
            btn.text('💾 Save').prop('disabled', false);
            if (res.success) {
                status.text('✅ Saved!').addClass('success');
                setTimeout(function () {
                    closeModal();
                    location.reload();
                }, 800);
            } else {
                status.text('❌ Error: ' + (res.data || 'Try again')).addClass('error');
            }
        }).fail(function () {
            btn.text('💾 Save').prop('disabled', false);
            status.text('❌ Network error').addClass('error');
        });
    });

    // ── DELETE ────────────────────────────────────────────────────────────────
    $(document).on('click', '.ch-delete-btn', function () {
        var id   = $(this).data('id');
        var tab  = $(this).data('tab');
        var name = $(this).data('name') || 'this item';
        if (!confirm('Delete "' + name + '"?\n\nThis cannot be undone.')) return;
        var row = $(this).closest('tr');
        $.post(CH.ajax, { action: 'ch_delete_item', tab: tab, id: id, nonce: CH.nonce }, function (res) {
            if (res.success) {
                row.fadeOut(300, function () { $(this).remove(); });
            } else {
                alert('Delete failed. Please try again.');
            }
        });
    });

    // ── TOGGLE STATUS ─────────────────────────────────────────────────────────
    $(document).on('click', '.ch-status-toggle', function () {
        var btn = $(this);
        var id  = btn.data('id');
        var tab = btn.data('tab');
        btn.text('...').prop('disabled', true);
        $.post(CH.ajax, { action: 'ch_toggle_status', tab: tab, id: id, nonce: CH.nonce }, function (res) {
            btn.prop('disabled', false);
            if (res.success) {
                var s   = res.data.status;
                var row = btn.closest('tr');
                btn.text(s === 'active' ? '✅ Active' : '⏸ Inactive')
                   .removeClass('ch-status-active ch-status-inactive')
                   .addClass('ch-status-' + s);
                row.toggleClass('ch-inactive', s === 'inactive');
            }
        });
    });

    // ── DRAG & DROP SORT (using HTML5 drag, no external lib needed) ───────────
    var dragSrc = null;

    $(document).on('dragstart', '.ch-drag-handle', function (e) {
        dragSrc = $(this).closest('tr')[0];
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        e.originalEvent.dataTransfer.setData('text/plain', '');
        $(dragSrc).addClass('ch-sortable-ghost');
    });

    $(document).on('dragend', '.ch-drag-handle', function () {
        if (dragSrc) $(dragSrc).removeClass('ch-sortable-ghost');
        dragSrc = null;
        saveSortOrder();
    });

    $(document).on('dragover', '#ch-sortable-body tr', function (e) {
        e.preventDefault();
        e.originalEvent.dataTransfer.dropEffect = 'move';
        if (dragSrc && this !== dragSrc) {
            var rect   = this.getBoundingClientRect();
            var midY   = rect.top + rect.height / 2;
            var before = e.originalEvent.clientY < midY;
            var tbody  = $('#ch-sortable-body')[0];
            if (before) {
                tbody.insertBefore(dragSrc, this);
            } else {
                tbody.insertBefore(dragSrc, this.nextSibling);
            }
        }
    });

    // Make rows draggable
    $(document).on('mousedown', '.ch-drag-handle', function () {
        $(this).closest('tr').attr('draggable', 'true');
    });

    function saveSortOrder() {
        var order = [];
        $('#ch-sortable-body tr[data-id]').each(function (i) {
            order.push($(this).data('id'));
            $(this).find('.ch-order-cell').text(i + 1);
        });
        $.post(CH.ajax, {
            action: 'ch_save_order',
            tab:    currentTab,
            order:  order,
            nonce:  CH.nonce
        });
    }

    // ── LEADS PAGE: toggle meta, save, status ─────────────────────────────────
    $(document).on('click', '.ch-meta-toggle', function () {
        var id    = $(this).data('id');
        var panel = $('#meta-' + id);
        panel.toggle();
        $(this).text(panel.is(':visible') ? '🔼 Hide Meta' : '📋 View Meta');
    });

    $(document).on('click', '.ch-save-btn', function () {
        var id        = $(this).data('id');
        var row       = $('#lead-row-' + id);
        var status    = row.find('.ch-status-select').val();
        var comment   = row.find('.ch-comment-input').val();
        var contacted = row.find('.ch-contacted-input').val();
        var btn       = $(this);
        var msg       = $('#save-msg-' + id);
        btn.text('Saving...').prop('disabled', true);
        $.post(CH.ajax, {
            action: 'ch_update_lead', nonce: CH.nonce,
            id: id, status: status, admin_comment: comment, contacted_at: contacted
        }, function (res) {
            btn.text('💾 Save').prop('disabled', false);
            if (res.success) {
                msg.fadeIn().delay(2000).fadeOut();
                var colors = { new:'#3b82f6', contacted:'#f59e0b', converted:'#10b981', rejected:'#ef4444', pending:'#8b5cf6', spam:'#6b7280' };
                row.find('.ch-status-select').css('border-left', '3px solid ' + (colors[status] || '#888'));
            }
        }).fail(function () {
            btn.text('💾 Save').prop('disabled', false);
            alert('Save failed. Please try again.');
        });
    });

    $(document).on('change', '.ch-status-select', function () {
        $(this).closest('tr').find('.ch-save-btn').addClass('button-primary');
    });
});
