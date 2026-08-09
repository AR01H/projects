<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$p = $wpdb->prefix . 'tt_';
$counts = [
    'FAQs'           => $wpdb->get_var("SELECT COUNT(*) FROM {$p}faqs"),
    'Reviews'        => $wpdb->get_var("SELECT COUNT(*) FROM {$p}reviews"),
    'Team Members'   => $wpdb->get_var("SELECT COUNT(*) FROM {$p}team"),
    'Drinks'         => $wpdb->get_var("SELECT COUNT(*) FROM {$p}drinks"),
    'Gallery Images' => $wpdb->get_var("SELECT COUNT(*) FROM {$p}gallery"),
    'Nav Links'      => $wpdb->get_var("SELECT COUNT(*) FROM {$p}nav"),
    'Settings'       => $wpdb->get_var("SELECT COUNT(*) FROM {$p}settings"),
];
?>
<div class="wrap">
<h2>TT Admin Dashboard</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin-top:20px;">
<?php foreach ($counts as $label => $count): ?>
<div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div style="font-size:2em;font-weight:700;color:#2271b1;"><?php echo esc_html($count); ?></div>
    <div style="font-size:13px;color:#666;margin-top:4px;"><?php echo esc_html($label); ?></div>
</div>
<?php endforeach; ?>
</div>
<p style="margin-top:24px;color:#666;">⚡ All content is served from the database. Use the menus on the left to manage each section.</p>
</div>