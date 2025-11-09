<?php
require_once 'config.php';

if (!isset($_GET['code'])) {
    echo 'Error: No code provided.';
    exit();
}

// Exchange the code for an access token
$data = [
    'client_id' => DISCORD_CLIENT_ID,
    'client_secret' => DISCORD_CLIENT_SECRET,
    'grant_type' => 'authorization_code',
    'code' => $_GET['code'],
    'redirect_uri' => DISCORD_REDIRECT_URI,
];

$ch = curl_init(DISCORD_TOKEN_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    echo 'Error: Failed to get access token.';
    var_dump($token_data);
    exit();
}

// Use the access token to get user info
$ch = curl_init(DISCORD_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token_data['access_token']
]);
$user_response = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($user_response, true);

if (!isset($user_data['id'])) {
    echo 'Error: Failed to get user data.';
    exit();
}

// Save user data to session and redirect
$_SESSION['user'] = [
    'id' => $user_data['id'],
    'username' => $user_data['username'],
    'avatar' => $user_data['avatar']
];

header('Location: dashboard.php');
exit();