<?php
$conn = new mysqli('localhost', 'root', '', 'wp_advaithhomes_project');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error . "\n");
}

$prefix = 'tig7_';
$result = $conn->query("SELECT option_value FROM {$prefix}options WHERE option_name = 'active_plugins'");
$row = $result->fetch_assoc();
$plugins = unserialize($row['option_value']);

$filtered = array_values(array_filter($plugins, function($p) {
    return strpos($p, 'coming-soon') === false;
}));

$serialized = $conn->real_escape_string(serialize($filtered));
$conn->query("UPDATE {$prefix}options SET option_value = '$serialized' WHERE option_name = 'active_plugins'");

echo "Done! Removed coming-soon. " . count($filtered) . " plugins remaining.\n";
$conn->close();
