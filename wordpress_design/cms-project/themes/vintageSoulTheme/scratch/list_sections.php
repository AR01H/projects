<?php
$files = glob(__DIR__ . '/../components/sections/*.php');
foreach ($files as $f) {
    echo basename($f) . "\n";
}
