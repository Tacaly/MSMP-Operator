<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Minecraft Server</title>
    <style>
        /* Add your CSS here */
        body { font-family: sans-serif; padding: 20px; }
        nav { background: #eee; padding: 10px; margin-bottom: 20px; }
        nav a { margin-right: 15px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .error { color: red; background: #ffe0e0; border: 1px solid red; padding: 10px; }
    </style>
</head>
<body>
    <nav>
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
    </nav>