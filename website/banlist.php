<?php
include 'templates/header.php';
check_login();

require_once 'lib/msmp_proxy.php';
$proxy = new MSMP_Proxy(MSMP_PROXY_URL);
$data = $proxy->getBans();
?>

<h1>Ban List</h1>

<?php if (isset($data['error'])): ?>
    <div class="error">
        <strong>Error:</strong> <?php echo htmlspecialchars($data['error']); ?>
        <?php if (isset($data['tip'])) echo "<br><em>" . htmlspecialchars($data['tip']) . "</em>"; ?>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>UUID</th>
                <th>Name</th>
                <th>Reason</th>
                <th>Banned On</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $ban): // Adjust this loop based on your API's output ?>
                <tr>
                    <td><?php echo htmlspecialchars($ban['uuid']); ?></td>
                    <td><?php echo htmlspecialchars($ban['name']); ?></td>
                    <td><?php echo htmlspecialchars($ban['reason']); ?></td>
                    <td><?php echo htmlspecialchars($ban['created']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr><td colspan="4">No banned players found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>