<?php
require_once '../config.php';
require_once '../lib/msmp_proxy.php';

check_login(); // Security check
header('Content-Type: application/json');

// Get the current server from the session
$serverId = $_SESSION['current_server_id'] ?? null;

if (!$serverId) {
    echo json_encode(['error' => 'No server selected in session.']);
    exit();
}

$proxy = new MSMP_Proxy(MSMP_PROXY_URL);
$data = $proxy->getPlayers($serverId);

if (isset($data['error'])) {
    echo json_encode(['error' => $data['error']]);
    exit();
}

// Return only the 'connected' players
echo json_encode($data['connected']);