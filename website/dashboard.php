<?php
require_once 'config.php';
require_once 'lib/msmp_proxy.php';

$action_message = null; // To show success/error
$proxy = new MSMP_Proxy(MSMP_PROXY_URL); // We need this early for the POST handler

// Handle "Send Message" action
if (isset($_POST['send_message']) && isset($_SESSION['current_server_id'])) {
    $message = $_POST['message'];
    $serverId = $_SESSION['current_server_id'];
    
    $result = $proxy->sendMessage($serverId, $message);
    
    if (isset($result['success']) && $result['success']) {
        $action_message = ['type' => 'success', 'text' => 'Message sent!'];
    } else {
        $action_message = ['type' => 'error', 'text' => 'Error: ' . ($result['error'] ?? 'Unknown error.')];
    }
}

// Now include the header
include 'templates/header.php';
check_login(); 
?>

<h1>Server Dashboard</h1>

<?php 
// Display the success/error message
if ($action_message): 
?>
    <div class="<?php echo $action_message['type'] === 'error' ? 'error' : 'success'; ?>" style="background: <?php echo $action_message['type'] === 'error' ? '#ffe0e0' : '#e0ffe0'; ?>; border: 1px solid <?php echo $action_message['type'] === 'error' ? 'red' : 'green'; ?>; padding: 10px; margin-bottom: 15px;">
        <?php echo htmlspecialchars($action_message['text']); ?>
    </div>
<?php endif; ?>

<p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</p>

<h3>Send Server Message</h3>
<form method="POST" action="dashboard.php">
    <input type="text" name="message" placeholder="Your message..." style="width: 300px;" required>
    <button type="submit" name="send_message">Send</button>
</form>

<?php include 'templates/footer.php'; ?>