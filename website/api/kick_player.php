<?php
require_once '../config.php';
require_once '../lib/msmp_proxy.php';

check_login(); // Security check
header('Content-Type: application/json');

// Get the server ID from the session
$serverId = $_SESSION['current_server_id'] ?? null;
if (!$serverId) {
    echo json_encode(['error' => 'No server selected in session.']);
    exit();
}

// Get the POST data sent by JavaScript
$request_body = file_get_contents('php://input');
$request_data = json_decode($request_body, true);
$name = $request_data['name'] ?? null;

if (!$name) {
    echo json_encode(['error' => 'No player name provided.']);
    exit();
}

// Call the proxy
$proxy = new MSMP_Proxy(MSMP_PROXY_URL);
$result = $proxy->kickPlayer($serverId, $name);

if (isset($result['success']) && $result['success']) {
    echo json_encode(['success' => true, 'message' => "Player $name kicked."]);
} else {
    echo json_encode(['error' => $result['error'] ?? 'Failed to kick player.']);
}