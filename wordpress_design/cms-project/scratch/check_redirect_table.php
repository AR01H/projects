<?php
$c = new mysqli('localhost', 'root', '', 'thecanehouse');
$r = $c->query("SHOW TABLES LIKE '%redirect%'");
while ($row = $r->fetch_array()) {
    echo "Table: " . $row[0] . "\n";
    $cols = $c->query("DESCRIBE " . $row[0]);
    while ($col = $cols->fetch_assoc()) {
        echo "  " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    $cnt = $c->query("SELECT COUNT(*) FROM " . $row[0])->fetch_row()[0];
    echo "  Total rows: " . $cnt . "\n\n";
}
