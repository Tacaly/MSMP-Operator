<?php
// This is a dedicated API endpoint. It only outputs JSON.
require_once '../config.php';
require_once '../lib/msmp_proxy.php';

// You might want to remove this check if you want the live
// list to be public, but it's good for security.
check_login();

header('Content-Type: application/json');

$proxy = new MSMP_Proxy(MSMP_PROXY_URL);
$data = $proxy->getPlayers();

if (isset($data['error'])) {
    echo json_encode(['error' => $data['error']]);
    exit();
}

// Return only the 'connected' players
echo json_encode($data['connected']);