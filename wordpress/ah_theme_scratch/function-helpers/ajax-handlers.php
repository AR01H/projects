<?php
/**
 * scratch/function-helpers/ajax-handlers.php
 */

/* ═══════════════════════════════════════════════
   1. ADMIN COLUMNS
   ═══════════════════════════════════════════════ */

function ah_scratch_add_columns($cols) {
    $pt = get_post_type();
    $new = ['cb' => $cols['cb'], 'title' => 'Title'];
    
    if ($pt === 'ah_review') $new['ah_rating'] = 'Rating';
    if ($pt === 'ah_post')   $new['ah_suggested'] = '⭐';
    if ($pt === 'ah_lead')   $new['ah_contact'] = 'Contact Details';

    $new['ah_status'] = 'Status';
    $new['ah_group']  = 'Group';
    $new['ah_tags']   = 'Tags';
    $new['date']      = 'Date';
    return $new;
}

function ah_scratch_render_columns($col, $post_id) {
    switch ($col) {
        case 'ah_rating':
            $r = intval(get_post_meta($post_id, '_ah_rating', true) ?: 5);
            echo '<span style="color:#f59e0b;">' . str_repeat('★', $r) . '</span>';
            break;
        case 'ah_suggested':
            echo (get_post_meta($post_id, '_ah_is_suggested', true) === '1') ? '✅' : '—';
            break;
        case 'ah_contact':
            echo '<strong>'.esc_html(get_post_meta($post_id, '_ah_lead_name', true)).'</strong><br>';
            echo '<small>'.esc_html(get_post_meta($post_id, '_ah_lead_email', true)).'</small>';
            break;
        case 'ah_status':
            $s = get_post_meta($post_id, '_ah_status', true) ?: 'Active';
            $c = ($s === 'Active') ? '#00a32a' : '#d63638';
            echo '<span style="background:'.$c.';color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;">'.strtoupper($s).'</span>';
            break;
        case 'ah_group':
            echo get_the_term_list($post_id, 'ah_group', '', ', ', '') ?: '—';
            break;
        case 'ah_tags':
            echo get_the_term_list($post_id, 'ah_tag', '', ', ', '') ?: '—';
            break;
    }
}

$slugs = ['ah_review', 'ah_post', 'ah_project', 'ah_guide', 'ah_lead', 'ah_log'];
foreach ($slugs as $s) {
    add_filter("manage_{$s}_posts_columns", 'ah_scratch_add_columns');
    add_action("manage_{$s}_posts_custom_column", 'ah_scratch_render_columns', 10, 2);
}

/* ═══════════════════════════════════════════════
   2. SQL RUNNER
   ═══════════════════════════════════════════════ */

function ah_ajax_run_report() {
    check_ajax_referer('ah_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

    global $wpdb;
    $post_id = absint($_POST['post_id']);
    $sql     = get_post_meta($post_id, '_ah_sql', true);

    if (empty($sql)) wp_send_json_error('No query found.');
    if (stripos(trim($sql), 'SELECT') !== 0) wp_send_json_error('SELECT only.');

    $results = $wpdb->get_results($sql, ARRAY_A);
    if ($wpdb->last_error) wp_send_json_error('SQL Error: ' . $wpdb->last_error);
    if (empty($results)) wp_send_json_success('<p>No results.</p>');

    $html = '<table class="wp-list-table widefat fixed striped"><thead><tr>';
    foreach (array_keys($results[0]) as $key) { $html .= '<th>' . esc_html($key) . '</th>'; }
    $html .= '</tr></thead><tbody>';
    foreach ($results as $row) {
        $html .= '<tr>';
        foreach ($row as $val) { $html .= '<td>' . esc_html($val) . '</td>'; }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    wp_send_json_success($html);
}
add_action('wp_ajax_ah_run_report', 'ah_ajax_run_report');

/* ═══════════════════════════════════════════════
   3. FORM SUBMISSIONS
   ═══════════════════════════════════════════════ */

function ah_scratch_contact_submit() {
    check_ajax_referer('ah_contact_nonce', 'nonce');
    $name  = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    
    $id = wp_insert_post([
        'post_type' => 'ah_lead',
        'post_title' => 'Scratch Lead: ' . $name,
        'post_status' => 'publish'
    ]);

    if ($id) {
        update_post_meta($id, '_ah_lead_name', $name);
        update_post_meta($id, '_ah_lead_email', $email);
        update_post_meta($id, '_ah_status', 'Active');
        wp_send_json_success('Saved to Scratch DB!');
    }
    wp_send_json_error('Failed.');
}
add_action('wp_ajax_ah_contact_submit', 'ah_scratch_contact_submit');
add_action('wp_ajax_nopriv_ah_contact_submit', 'ah_scratch_contact_submit');
