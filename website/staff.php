<?php
include 'templates/header.php';
check_login();

require_once 'lib/msmp_proxy.php';
$proxy = new MSMP_Proxy(MSMP_PROXY_URL);
$data = $proxy->getOps();
?>

<h1>Server Staff (Operators)</h1>

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
                <th>Level</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $op): ?>
                <tr>
                    <td><?php echo htmlspecialchars($op['uuid']); ?></td>
                    <td><?php echo htmlspecialchars($op['name']); ?></td>
                    <td><?php echo htmlspecialchars($op['level']); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data)): ?>
                <tr><td colspan="3">No operators found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>