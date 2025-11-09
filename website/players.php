<?php
include 'templates/header.php';
check_login();

require_once 'lib/msmp_proxy.php';
$proxy = new MSMP_Proxy(MSMP_PROXY_URL);
$data = $proxy->getPlayers();
?>

<h1>Player List</h1>

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
            <?php foreach ($data['offline'] as $player): // Note: 'offline' array from proxy ?>
                <tr>
                    <td><?php echo htmlspecialchars($player['uuid']); ?></td>
                    <td><?php echo htmlspecialchars($player['name']); ?></td>
                    <td><?php echo 'N/A'; // Playtime is hard to get from MSMP ?></td>
                </tr>
            <?php endforeach; ?>
             <?php if (empty($data['offline'])): ?>
                <tr><td colspan="3">No offline players found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>