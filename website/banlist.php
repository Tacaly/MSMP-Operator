<?php
include 'templates/header.php';
check_login();
// $proxy and $current_server_id are now available from header.php

echo "<h1>Ban List (" . htmlspecialchars($current_server_name) . ")</h1>";

if (!$current_server_id) {
    echo '<p class="error">No server selected or available.</p>';
} else {
    $data = $proxy->getBans($current_server_id);
    
    if (isset($data['error'])) {
        echo '<div class="error"><strong>Error:</strong> ' . htmlspecialchars($data['error']);
        if (isset($data['tip'])) echo "<br><em>" . htmlspecialchars($data['tip']) . "</em>";
        echo '</div>';
    } else {
        // ... (The rest of your table HTML from before) ...
        // ... (No changes needed to the table itself) ...
?>
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
<?php 
    }
}
include 'templates/footer.php'; 
?>