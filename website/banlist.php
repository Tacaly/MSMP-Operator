<?php
require_once 'config.php';
require_once 'lib/msmp_proxy.php';

$action_message = null;
$proxy = new MSMP_Proxy(MSMP_PROXY_URL);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['current_server_id'])) {
    $serverId = $_SESSION['current_server_id'];
    
    // Handle BAN action
    if (isset($_POST['ban_player'])) {
        $name = $_POST['name'];
        $reason = $_POST['reason'];
        $result = $proxy->banPlayer($serverId, $name, $reason);
        if (isset($result['success']) && $result['success']) {
            $action_message = ['type' => 'success', 'text' => "Player $name has been banned."];
        } else {
            $action_message = ['type' => 'error', 'text' => 'Error banning: ' . ($result['error'] ?? 'Unknown error.')];
        }
    }
    
    // Handle PARDON action
    if (isset($_POST['pardon_player'])) {
        $name = $_POST['name'];
        $result = $proxy->pardonPlayer($serverId, $name);
         if (isset($result['success']) && $result['success']) {
            $action_message = ['type' => 'success', 'text' => "Player $name has been pardoned."];
        } else {
            $action_message = ['type' => 'error', 'text' => 'Error pardoning: ' . ($result['error'] ?? 'Unknown error.')];
        }
    }
}

// Now include the header
include 'templates/header.php';
check_login();
// $proxy and $current_server_id are available from header.php
?>
    <?php
echo "<h1>Ban List (" . htmlspecialchars($current_server_name) . ")</h1>";

// Display the success/error message
if ($action_message): 
?>
    <div class="<?php echo $action_message['type'] === 'error' ? 'error' : 'success'; ?>" style="background: <?php echo $action_message['type'] === 'error' ? '#ffe0e0' : '#e0ffe0'; ?>; border: 1px solid <?php echo $action_message['type'] === 'error' ? 'red' : 'green'; ?>; padding: 10px; margin-bottom: 15px;">
        <?php echo htmlspecialchars($action_message['text']); ?>
    </div>
<?php endif; ?>

<h3>Ban a Player</h3>
<form method="POST" action="banlist.php" style="margin-bottom: 20px;">
    <input type="text" name="name" placeholder="Player Name" required>
    <input type="text" name="reason" placeholder="Reason (optional)" style="width: 300px;">
    <button type="submit" name="ban_player">Ban Player</button>
</form>

<h3>Current Bans</h3>

<?php
if (!$current_server_id) {
    echo '<p class="error">No server selected or available.</p>';
} else {
    $data = $proxy->getBans($current_server_id);
    
    if (isset($data['error'])) {
        // ... (error handling as before) ...
    } else {
?>
    <table>
        <thead>
            <tr>
                <th>UUID</th>
                <th>Name</th>
                <th>Reason</th>
                <th>Banned On</th>
                <th>Action</th> </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $ban): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ban['uuid'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ban['name']); ?></td>
                    <td><?php echo htmlspecialchars($ban['reason'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ban['created'] ?? 'N/A'); ?></td>
                    <td>
                        <form method="POST" action="banlist.php" style="margin: 0;">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($ban['name']); ?>">
                            <button type="submit" name="pardon_player" style="background: #28a745; color: white; border: none; padding: 5px; cursor: pointer;">Pardon</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr><td colspan="5">No banned players found.</td></tr> <?php endif; ?>
        </tbody>
    </table>
<?php 
    }
}
include 'templates/footer.php'; 
?>