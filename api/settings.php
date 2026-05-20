<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=300');

require_once __DIR__ . '/../admin/includes/config.php';
require_once __DIR__ . '/../admin/includes/db.php';

$rows = db_all('SELECT `key`, value FROM settings');
$out  = [];
foreach ($rows as $r) {
    $out[$r['key']] = $r['value'];
}
echo json_encode($out);
