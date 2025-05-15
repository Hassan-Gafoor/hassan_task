<?php
if(!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$posts = [];

if(!empty($_SESSION['users'])) {
    foreach($_SESSION['users'] as $u) {
        foreach($u['posts'] as $post) {
            $posts[] = [
                'user' => $u,
                'post' => $post
            ];
        }
    }
}

usort($posts, function($a, $b) {
    return $b['post']['timestamp'] - $a['post']['timestamp'];
});
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home | InstaClone</title>
    <style>
        body { font-family: Arial; background: #fafafa; }
        .posts-container { max-width: 614px; margin: 20px auto; }
        .post { background: #fff; border: 1px solid #dbdbdb; margin-bottom: 30px; }
        .post-header { display: flex; align-items: center; padding: 14px; }
        .post-user-pic { width: 32px; height: 32px; border-radius: 50%; background: #ddd; margin-right: 10px; background-size: cover; }
        .post-username { font-weight: bold; }
        .post-image { width: 100%; }
        .post-actions { padding: 10px; display: flex; align-items: center; }
        .post-action { font-size: 24px; margin-right: 15px; cursor: pointer; }
        .post-likes, .post-caption, .post-location, .post-comments, .post-time { padding: 0 10px; }
        .post-caption-username, .comment-username { font-weight: bold; margin-right: 5px; }
        .post-comment { margin: 8px 0; }
        .post-time { color: #999; font-size: 12px; }
        .comment-form { display: flex; padding: 10px; border-top: 1px solid #efefef; }
        .comment-input { flex-grow: 1; border: none; outline: none; }
        .comment-submit { color: #3897f0; font-weight: bold; background: none; border: none; cursor: pointer; }
    </style>
</head>
<body>
<div class="posts-container">
    <?php foreach($posts as $item): 
        $post_user = $item['user'];
        $post = $item['post'];
        ?>
        <div class="post" id="post-<?= $post['id'] ?>">
            <div class="post-header">
                <div class="post-user-pic" style="background-image: url('<?= !empty($post_user['profile_pic']) ? $post_user['profile_pic'] : 'https://via.placeholder.com/32' ?>');"></div>
                <div class="post-username"><?= htmlspecialchars($post_user['username']) ?></div>
            </div>

            <img src="<?= $post['image'] ?>" class="post-image">

            <div class="post-actions">
                <span class="post-action" onclick="likePost('<?= $post['id'] ?>')"><?= in_array($user['id'], $post['likes']) ? '❤️' : '♡' ?></span>
                <span class="post-action">💬</span>
                
            </div>

            <div class="post-likes" id="likes-<?= $post['id'] ?>"><?= count($post['likes']) ?> likes</div>

            <div class="post-caption">
                <span class="post-caption-username"><?= htmlspecialchars($post_user['username']) ?></span>
                <?= htmlspecialchars($post['caption']) ?>
            </div>

            <?php if(!empty($post['location'])): ?>
                <div class="post-location">📍 <?= htmlspecialchars($post['location']) ?></div>
            <?php endif; ?>

            <div class="post-comments" id="comments-<?= $post['id'] ?>">
                <?php foreach($post['comments'] as $comment): ?>
                    <div class="post-comment">
                        <span class="comment-username"><?= htmlspecialchars($comment['username']) ?></span>
                        <?= htmlspecialchars($comment['text']) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="post-time"><?= date('F j, Y', $post['timestamp']) ?></div>

            <form class="comment-form" onsubmit="submitComment(event, '<?= $post['id'] ?>')">
                <input type="text" name="comment_text" placeholder="Add a comment..." class="comment-input" id="input-<?= $post['id'] ?>">
                <button type="submit" class="comment-submit">Post</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<script>
function likePost(postId) {
    fetch('like_comment_share_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=like&post_id=' + postId
    })
    .then(res => res.json())
    .then(data => {
        document.querySelector(`#likes-${postId}`).innerText = data.likes + ' likes';
        document.querySelector(`#post-${postId} .post-action`).innerText = data.liked ? '❤️' : '♡';
    });
}

function submitComment(event, postId) {
    event.preventDefault();
    const input = document.querySelector(`#input-${postId}`);
    const text = input.value.trim();
    if(text.length === 0) return;

    fetch('like_comment_share_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=comment&post_id=' + postId + '&comment_text=' + encodeURIComponent(text)
    })
    .then(res => res.json())
    .then(data => {
        const commentsDiv = document.querySelector(`#comments-${postId}`);
        const commentHTML = `<div class="post-comment"><span class="comment-username">${data.username}</span>${data.text}</div>`;
        commentsDiv.innerHTML += commentHTML;
        input.value = '';
    });
}

function sharePost(postId) {
    const dummy = document.createElement('input');
    const url = window.location.href + `#post-${postId}`;
    dummy.value = url;
    document.body.appendChild(dummy);
    dummy.select();
    document.execCommand('copy');
    document.body.removeChild(dummy);
    alert("Post link copied to clipboard!");
}
</script>
</body>
</html>
