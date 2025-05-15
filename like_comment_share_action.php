<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !isset($_SESSION['users'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = $_SESSION['user'];
$users = &$_SESSION['users'];
$action = $_POST['action'] ?? '';
$post_id = $_POST['post_id'] ?? '';

foreach ($users as &$u) {
    foreach ($u['posts'] as &$p) {
        if ($p['id'] === $post_id) {
            if ($action === 'like') {
                if (in_array($user['id'], $p['likes'])) {
                    $p['likes'] = array_diff($p['likes'], [$user['id']]);
                    echo json_encode(['liked' => false, 'likes' => count($p['likes'])]);
                } else {
                    $p['likes'][] = $user['id'];
                    echo json_encode(['liked' => true, 'likes' => count($p['likes'])]);
                }
                exit;
            }

            if ($action === 'comment') {
                $text = $_POST['comment_text'] ?? '';
                if (!empty($text)) {
                    $comment = [
                        'user_id' => $user['id'],
                        'username' => $user['username'],
                        'text' => $text,
                        'timestamp' => time()
                    ];
                    $p['comments'][] = $comment;
                    echo json_encode(['username' => $comment['username'], 'text' => $comment['text']]);
                    exit;
                }
            }
        }
    }
}

echo json_encode(['error' => 'Post not found']);
exit;
