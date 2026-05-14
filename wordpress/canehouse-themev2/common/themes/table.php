<?php

$tabular_header = [];
$tabular_rows = [];


?>

<table>
    <thead>
        <tr>
            <? foreach ($tabular_header as $header) { ?>
                <th><?= ($header) ?></th>
            <? } ?>
        </tr>
    </thead>
    <tbody>
        <? foreach ($tabular_rows as $row) { ?>
            <tr>
                <? foreach ($row as $cell) { ?>
                    <td><?= ($cell) ?></td>
                <? } ?>
            </tr>
        <? } ?>
    </tbody>
</table>


