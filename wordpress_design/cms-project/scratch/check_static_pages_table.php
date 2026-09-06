<?php
$c = new mysqli('localhost', 'root', '', 'thecanehouse');
$r = $c->query("SHOW TABLES LIKE '%static_pages%'");
while ($row = $r->fetch_array()) {
    echo "Table: " . $row[0] . "\n";
    $d = $c->query("SELECT id, slug, title, page_id, status FROM " . $row[0]);
    while ($p = $d->fetch_assoc()) {
        print_r($p);
    }
}
