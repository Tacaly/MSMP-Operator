<?php
include 'templates/header.php';
check_login(); // Make sure user is logged in
?>

<h1>Server Dashboard</h1>
<p>This is your main management page. You can add server status here.</p>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</p>

<?php include 'templates/footer.php'; ?>