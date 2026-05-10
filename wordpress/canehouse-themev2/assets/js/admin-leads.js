jQuery(function($) {

    // ── Toggle meta panel
    $(document).on('click', '.ch-meta-toggle', function() {
        var id    = $(this).data('id');
        var panel = $('#meta-' + id);
        panel.toggle();
        $(this).text(panel.is(':visible') ? '🔼 Hide Meta' : '📋 View Meta');
    });

    // ── Save lead (status + comment + contacted_at)
    $(document).on('click', '.ch-save-btn', function() {
        var id      = $(this).data('id');
        var row     = $('#lead-row-' + id);
        var status  = row.find('.ch-status-select').val();
        var comment = row.find('.ch-comment-input').val();
        var contacted = row.find('.ch-contacted-input').val();
        var btn     = $(this);
        var msg     = $('#save-msg-' + id);

        btn.text('Saving...').prop('disabled', true);

        $.post(CH_ADMIN.ajax_url, {
            action:        'ch_update_lead',
            nonce:         CH_ADMIN.nonce,
            id:            id,
            status:        status,
            admin_comment: comment,
            contacted_at:  contacted
        }, function(res) {
            btn.text('💾 Save').prop('disabled', false);
            if (res.success) {
                msg.fadeIn().delay(2000).fadeOut();
                // Update status border colour
                var colors = {
                    new:'#3b82f6', contacted:'#f59e0b',
                    converted:'#10b981', rejected:'#ef4444',
                    pending:'#8b5cf6', spam:'#6b7280'
                };
                row.find('.ch-status-select').css('border-left', '3px solid ' + (colors[status] || '#888'));
            }
        }).fail(function() {
            btn.text('💾 Save').prop('disabled', false);
            alert('Save failed. Please try again.');
        });
    });

    // ── Auto-save status on change (optional quick action)
    $(document).on('change', '.ch-status-select', function() {
        var row = $(this).closest('tr');
        row.find('.ch-save-btn').css('animation', 'none').addClass('button-primary');
    });

});
