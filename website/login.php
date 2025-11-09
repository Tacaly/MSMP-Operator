<?php
require_once 'config.php';

$params = [
    'client_id' => DISCORD_CLIENT_ID,
    'redirect_uri' => DISCORD_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'identify email' // 'identify' is all we need
];

// Redirect to Discord's authorization page
header('Location: ' . DISCORD_AUTH_URL . '?' . http_build_query($params));
exit();