<?php 
require_once 'config.php'; 
require_once 'lib/msmp_proxy.php';

// --- Server Selection Logic ---
$proxy = new MSMP_Proxy(MSMP_PROXY_URL);
$available_servers = $proxy->getServers();

// Check if the user is changing servers
if (isset($_GET['server'])) {
    $selected_id = $_GET['server'];
    // Validate that this ID actually exists
    $found = false;
    if (is_array($available_servers) && !isset($available_servers['error'])) {
        foreach ($available_servers as $server) {
            if ($server['id'] === $selected_id) {
                $found = true;
                break;
            }
        }
    }
    if ($found) {
        $_SESSION['current_server_id'] = $selected_id;
    }
    // Redirect to clean the URL (removes ?server=...)
    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Check if a server is already in the session
$current_server_id = $_SESSION['current_server_id'] ?? null;
$current_server_name = 'No Server Selected';

// If no server is in session (or it's invalid), default to the first one
if (is_array($available_servers) && !isset($available_servers['error'])) {
    if (!$current_server_id || !in_array($current_server_id, array_column($available_servers, 'id'))) {
        if (!empty($available_servers)) {
            $current_server_id = $available_servers[0]['id'];
            $_SESSION['current_server_id'] = $current_server_id;
        }
    }
    
    // Find the name of the currently selected server
    foreach ($available_servers as $server) {
        if ($server['id'] === $current_server_id) {
            $current_server_name = $server['name'];
            break;
        }
    }
}
// --- End Server Selection ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Minecraft Server</title>
    <style>
        /* Add your CSS here */
        body { font-family: sans-serif; padding: 20px; }
        nav { background: #eee; padding: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        nav .links a { margin-right: 15px; }
        nav .server-switcher { font-size: 0.9em; }
        nav .server-switcher select { padding: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .error { color: red; background: #ffe0e0; border: 1px solid red; padding: 10px; }
    </style>
</head>
<body>
    <nav>
        <div class="links">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="players.php">Players</a>
                <a href="banlist.php">Ban List</a>
                <a href="staff.php">Staff</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user']['username']); ?>)</a>
            <?php else: ?>
                <a href="login.php">Login with Discord</a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_SESSION['user']) && is_array($available_servers) && !isset($available_servers['error'])): ?>
            <div class="server-switcher">
                <strong>Manage Server:</strong>
                <select onchange="window.location.href = '?server=' + this.value">
                    <?php foreach ($available_servers as $server): ?>
                        <option 
                            value="<?php echo htmlspecialchars($server['id']); ?>"
                            <?php echo ($server['id'] === $current_server_id) ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($server['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </nav>

    <?php 
    // Display a global error if the proxy is down or no servers are found
    if (isset($_SESSION['user']) && (isset($available_servers['error']) || empty($available_servers))) {
        echo '<div class="error"><strong>Connection Error:</strong> Could not fetch server list from the MSMP Proxy. Please ensure the proxy is running and `servers.json` is configured.</div>';
    }
    ?>