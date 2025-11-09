<?php include 'templates/header.php'; ?>

<h1>Welcome to the Minecraft Server</h1>
<?php if (isset($_SESSION['user'])): ?>
    <p>Hello, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>! You are logged in.</p>
    <p>Go to your <a href="dashboard.php">Dashboard</a>.</p>
<?php else: ?>
    <p>Please <a href="login.php">login with Discord</a> to manage the server.</p>
<?php endif; ?>

<?php include 'templates/footer.php'; ?>