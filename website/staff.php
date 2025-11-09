<?php
include 'templates/header.php';
check_login();
// $proxy, $current_server_id, and $current_server_name are from header.php

echo "<h1>Server Staff (" . htmlspecialchars($current_server_name) . ")</h1>";

if (!$current_server_id) {
    echo '<p class="error">No server selected or available.</p>';
} else {
    $data = $proxy->getOps($current_server_id);
    
    if (isset($data['error'])) {
         echo '<div class="error"><strong>Error:</strong> ' . htmlspecialchars($data['error']) . '</div>';
    } else {
        // ... (The rest of your table HTML from before) ...
        // ... (No changes needed to the table itself) ...
?>
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
<?php
    }
}
include 'templates/footer.php'; 
?>