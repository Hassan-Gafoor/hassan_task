<?php
// Suppress notice-level errors
error_reporting(E_ALL & ~E_NOTICE);

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user not set
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$selected_user = null;
$messages = [];

// Initialize messages in session if not exists
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

// Get all users except current user
$other_users = [];
if (!empty($_SESSION['users'])) {
    foreach ($_SESSION['users'] as $u) {
        if ($u['id'] !== $user['id']) {
            $other_users[] = $u;
        }
    }
}

// Select a user to chat with
if (isset($_GET['user_id'])) {
    $selected_user_id = $_GET['user_id'];

    foreach ($other_users as $u) {
        if ($u['id'] === $selected_user_id) {
            $selected_user = $u;
            break;
        }
    }

    // Get conversation ID and messages
    $conversation_id = $user['id'] < $selected_user_id ?
        $user['id'] . '_' . $selected_user_id :
        $selected_user_id . '_' . $user['id'];

    $messages = $_SESSION['messages'][$conversation_id] ?? [];
}

// Send a new message
if (isset($_POST['send_message']) && $selected_user) {
    $message_text = $_POST['message_text'] ?? '';

    if (!empty($message_text)) {
        $conversation_id = $user['id'] < $selected_user['id'] ?
            $user['id'] . '_' . $selected_user['id'] :
            $selected_user['id'] . '_' . $user['id'];

        if (!isset($_SESSION['messages'][$conversation_id])) {
            $_SESSION['messages'][$conversation_id] = [];
        }

        $_SESSION['messages'][$conversation_id][] = [
            'sender_id' => $user['id'],
            'text' => $message_text,
            'timestamp' => time()
        ];

        // Redirect to avoid resubmission
        header("Location: messages.php?user_id=" . $selected_user['id']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages | InstaClone</title>
    <style>
        .messages-container { display: flex; max-width: 935px; margin: 0 auto; height: calc(100vh - 60px); }
        .users-list { width: 350px; border-right: 1px solid #dbdbdb; overflow-y: auto; }
        .user-item { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #efefef; cursor: pointer; }
        .user-item:hover { background: #fafafa; }
        .user-item.active { background: #efefef; }
        .user-pic { width: 50px; height: 50px; border-radius: 50%; background: #ddd; margin-right: 10px; }
        .chat-container { flex-grow: 1; display: flex; flex-direction: column; }
        .chat-header { padding: 15px; border-bottom: 1px solid #dbdbdb; font-weight: bold; }
        .chat-messages { flex-grow: 1; overflow-y: auto; padding: 15px; }
        .message { margin-bottom: 15px; }
        .message.sent { text-align: right; }
        .message.received { text-align: left; }
        .message-text { display: inline-block; padding: 10px 15px; border-radius: 18px; max-width: 70%; }
        .sent .message-text { background: #3897f0; color: white; }
        .received .message-text { background: #efefef; }
        .message-form { display: flex; padding: 15px; border-top: 1px solid #dbdbdb; }
        .message-input { flex-grow: 1; border: 1px solid #dbdbdb; border-radius: 18px; padding: 10px 15px; outline: none; }
        .message-submit { background: #3897f0; color: white; border: none; border-radius: 18px; padding: 10px 20px; margin-left: 10px; cursor: pointer; }
        .no-chat-selected { display: flex; justify-content: center; align-items: center; height: 100%; color: #999; }
    </style>
</head>
<body>
    <div class="messages-container">
        <div class="users-list">
            <?php foreach ($other_users as $u): ?>
                <div class="user-item <?= $selected_user && $selected_user['id'] === $u['id'] ? 'active' : '' ?>"
                     onclick="window.location.href='messages.php?user_id=<?= $u['id'] ?>'">
                    <div class="user-pic" style="background-image: url('<?= !empty($u['profile_pic']) ? $u['profile_pic'] : 'https://via.placeholder.com/50'; ?>'); background-size: cover;"></div>
                    <div>
                        <div><?= htmlspecialchars($u['username']) ?></div>
                        <div style="font-size: 12px; color: #999;"><?= htmlspecialchars($u['fullname']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="chat-container">
            <?php if ($selected_user): ?>
                <div class="chat-header">
                    <?= htmlspecialchars($selected_user['username']) ?>
                </div>

                <div class="chat-messages">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?= $msg['sender_id'] === $user['id'] ? 'sent' : 'received' ?>">
                            <div class="message-text">
                                <?= htmlspecialchars($msg['text']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form method="post" class="message-form">
                    <input type="text" name="message_text" placeholder="Message..." class="message-input" required>
                    <button type="submit" name="send_message" class="message-submit">Send</button>
                </form>
            <?php else: ?>
                <div class="no-chat-selected">
                    Select a user to start chatting
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
