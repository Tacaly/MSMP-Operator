<?php
session_start();

// --- Discord OAuth2 Config ---
define('DISCORD_CLIENT_ID', 'YOUR_DISCORD_CLIENT_ID');
define('DISCORD_CLIENT_SECRET', 'YOUR_DISCORD_CLIENT_SECRET');
define('DISCORD_REDIRECT_URI', 'http://localhost/callback.php'); // Your full callback URL
define('DISCORD_AUTH_URL', 'https://discord.com/api/oauth2/authorize');
define('DISCORD_TOKEN_URL', 'https://discord.com/api/oauth2/token');
define('DISCORD_API_URL', 'https://discord.com/api/users/@me');

// --- MSMP Proxy Config ---
define('MSMP_PROXY_URL', 'http://localhost:8081'); // The URL of your Node.js proxy


// Helper function to check if user is logged in
function check_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }
}