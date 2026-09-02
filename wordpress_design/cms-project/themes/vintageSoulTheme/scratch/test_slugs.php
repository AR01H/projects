<?php
$data = json_decode(file_get_contents(__DIR__ . '/../data/content/history.json'), true)['uses'];

echo "=== CATEGORIES ===\n";
$catSlugs = [];
foreach ($data['categories'] as $i => $c) {
    $slug = (0 === $i) ? 'all' : strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $c), '-'));
    $catSlugs[] = $slug;
    echo "Button: [{$c}] => slug: [{$slug}]\n";
}

echo "\n=== ITEMS ===\n";
foreach ($data['items'] as $item) {
    $itemCat = $item['category'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $itemCat), '-'));
    $match = in_array($slug, $catSlugs, true) ? "MATCHES" : "MISMATCH!";
    echo "Item: [{$item['title']}] | Category: [{$itemCat}] => slug: [{$slug}] ({$match})\n";
}
