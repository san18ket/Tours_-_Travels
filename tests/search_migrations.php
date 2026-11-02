<?php
foreach (glob(__DIR__ . '/../app/Migrations/*.php') as $f) {
    $c = file_get_contents($f);
    if (strpos($c, 'profile_photo') !== false || strpos($c, 'id_document_file') !== false || strpos($c, 'license_document_file') !== false) {
        echo basename($f) . "\n";
    }
}
