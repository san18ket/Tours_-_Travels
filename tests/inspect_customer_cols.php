<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=tours_travels_db', 'root', '');
$cols = $pdo->query('DESCRIBE customers')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}
