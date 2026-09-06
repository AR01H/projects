<?php
$c = new mysqli('localhost', 'root', '', 'thecanehouse');

echo "=== REAL-TIME VISITOR STATS IN CMS DATABASE ===\n\n";

$total = $c->query("SELECT COUNT(*) FROM wp_ch_ah_visitor_logs")->fetch_row()[0];
$unique = $c->query("SELECT COUNT(DISTINCT ip_address) FROM wp_ch_ah_visitor_logs")->fetch_row()[0];
$today = $c->query("SELECT COUNT(*) FROM wp_ch_ah_visitor_logs WHERE DATE(visited_at) = CURDATE()")->fetch_row()[0];

echo "Total Visits: {$total}\n";
echo "Unique IPs: {$unique}\n";
echo "Visits Today: {$today}\n\n";

echo "Top Pages:\n";
$pages = $c->query("SELECT page_slug, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_v FROM wp_ch_ah_visitor_logs GROUP BY page_slug ORDER BY visits DESC");
while ($row = $pages->fetch_assoc()) {
    echo "  - /{$row['page_slug']}: {$row['visits']} visits ({$row['unique_v']} unique)\n";
}

echo "\nRecent Logs:\n";
$logs = $c->query("SELECT ip_address, page_slug, page_url, visited_at FROM wp_ch_ah_visitor_logs ORDER BY visited_at DESC LIMIT 5");
while ($row = $logs->fetch_assoc()) {
    echo "  [{$row['visited_at']}] IP: {$row['ip_address']} -> {$row['page_slug']} ({$row['page_url']})\n";
}
