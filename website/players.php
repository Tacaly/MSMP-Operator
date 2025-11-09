<?php
include 'templates/header.php';
check_login();
// $proxy, $current_server_id, and $current_server_name are from header.php

echo "<h1>Player List (" . htmlspecialchars($current_server_name) . ")</h1>";

if (!$current_server_id) {
    echo '<p class="error">No server selected or available.</p>';
} else {
    $data = $proxy->getPlayers($current_server_id);
?>

    <h2>Live Players (<span id="player-count">...</span>)</h2>
    <div id="live-player-list">
        <p>Loading live players...</p>
    </div>

    <h2>Offline Players</h2>
    <?php if (isset($data['error'])): ?>
        <div class="error">
            <strong>Error:</strong> <?php echo htmlspecialchars($data['error']); ?>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>UUID</th>
                    <th>Name</th>
                    <th>Playtime</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['offline'] as $player): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($player['uuid']); ?></td>
                        <td><?php echo htmlspecialchars($player['name']); ?></td>
                        <td><?php echo 'N/A'; // Playtime requires another API call ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($data['offline'])): ?>
                    <tr><td colspan="3">No offline players found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php
}
include 'templates/footer.php'; 
?>