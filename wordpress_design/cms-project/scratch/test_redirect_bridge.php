<?php
require_once __DIR__ . '/../../../../wp-load.php';

use VintageSoul\Services\Plugins\RedirectBridgeService;

echo "--- REDIRECT BRIDGE TEST ---\n";

global $wpdb;
$table = $wpdb->prefix . 'ah_redirect_rules';
$exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
echo "Table {$table} exists: " . ($exists ? "YES" : "NO") . "\n";

// Fetch existing rules
$rules = $wpdb->get_results("SELECT * FROM `{$table}`");
echo "Total rules in DB: " . count($rules) . "\n";

foreach ($rules as $r) {
    echo "  [#{$r->id}] {$r->source_slug} -> {$r->target_url} (Type: {$r->type}, Hits: {$r->hit_count}, Active: {$r->is_active})\n";
}

// Add sample rules if empty to test all types
if (empty($rules)) {
    echo "Inserting test redirect rules...\n";
    $wpdb->insert($table, [
        'source_slug' => '/legacy-shop',
        'target_url' => '/shop',
        'type' => '301',
        'notes' => 'Old shop URL',
        'is_active' => 1,
        'hit_count' => 0
    ]);
    $wpdb->insert($table, [
        'source_slug' => 'old-catalog',
        'target_url' => '',
        'type' => '410',
        'notes' => 'Catalog removed',
        'is_active' => 1,
        'hit_count' => 0
    ]);
    $wpdb->insert($table, [
        'source_slug' => 'partner-site',
        'target_url' => 'https://example.com/partner',
        'type' => 'exit',
        'notes' => 'Our Royal Partner Guild',
        'is_active' => 1,
        'hit_count' => 0
    ]);
}

// Test get_rule lookup
echo "\n--- Testing Rule Lookup ---\n";
$rule1 = RedirectBridgeService::get_rule('legacy-shop');
echo "Lookup 'legacy-shop': " . ($rule1 ? "FOUND (ID: {$rule1->id}, Type: {$rule1->type}, Target: {$rule1->target_url})" : "NOT FOUND") . "\n";

$rule2 = RedirectBridgeService::get_rule('old-catalog');
echo "Lookup 'old-catalog': " . ($rule2 ? "FOUND (ID: {$rule2->id}, Type: {$rule2->type})" : "NOT FOUND") . "\n";

$rule3 = RedirectBridgeService::get_rule('/partner-site/');
echo "Lookup '/partner-site/': " . ($rule3 ? "FOUND (ID: {$rule3->id}, Type: {$rule3->type}, Target: {$rule3->target_url})" : "NOT FOUND") . "\n";

// Test hit count
if ($rule1) {
    $before_hits = (int)$rule1->hit_count;
    RedirectBridgeService::record_hit((int)$rule1->id);
    $after_hits = (int)$wpdb->get_var($wpdb->prepare("SELECT hit_count FROM `{$table}` WHERE id = %d", $rule1->id));
    echo "Hit increment test: Before = {$before_hits}, After = {$after_hits} -> " . ($after_hits === $before_hits + 1 ? "PASSED" : "FAILED") . "\n";
}

echo "\n--- ALL REDIRECT BRIDGE TESTS COMPLETE ---\n";
