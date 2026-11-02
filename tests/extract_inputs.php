<?php
$content = file_get_contents(__DIR__ . '/../app/Views/office/customers/create.php');
preg_match_all('/<(input|select|textarea)[^>]+name=["\']([^"\']+)["\']/i', $content, $matches);
$names = array_unique($matches[2]);
print_r($names);
