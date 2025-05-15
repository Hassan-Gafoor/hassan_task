<?php
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$error = '';
$success = '';

// Handle Post Upload
if (isset($_POST['upload'])) {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $title = $_POST['title'] ?? '';
        $caption = $_POST['caption'] ?? '';
        $location = $_POST['location'] ?? $user['location'];

        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $newPost = [
                'id' => uniqid(),
                'image' => $target_file,
                'title' => $title,
                'caption' => $caption,
                'location' => $location,
                'likes' => [],
                'comments' => [],
                'timestamp' => time()
            ];

            foreach ($_SESSION['users'] as &$u) {
                if ($u['id'] === $user['id']) {
                    $u['posts'][] = $newPost;
                    $_SESSION['user'] = $u;
                    $user = $u;
                    $success = "Post uploaded successfully!";
                    break;
                }
            }
        } else {
            $error = "Error uploading file.";
        }
    } else {
        $error = "Please select an image to upload.";
    }
}

// Handle Profile Picture Update
if (isset($_POST['update_pic'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profile_pics/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = uniqid() . '_' . basename($_FILES['profile_pic']['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            foreach ($_SESSION['users'] as &$u) {
                if ($u['id'] === $user['id']) {
                    $u['profile_pic'] = $target_file;
                    $_SESSION['user'] = $u;
                    $user = $u;
                    $success = "Profile picture updated!";
                    break;
                }
            }
        } else {
            $error = "Error uploading profile picture.";
        }
    } else {
        $error = "Please select a profile picture.";
    }
}

// Handle Removing a Follower
if (isset($_POST['remove_follower_id'])) {
    $removeId = $_POST['remove_follower_id'];
    foreach ($_SESSION['users'] as &$u) {
        if ($u['id'] === $user['id']) {
            $u['followers'] = array_filter($u['followers'], fn($f) => $f !== $removeId);
            $_SESSION['user'] = $u;
            $user = $u;
            $success = "Follower removed!";
            break;
        }
    }
}

// Handle Unfollowing a User
if (isset($_POST['unfollow_user_id'])) {
    $unfollowId = $_POST['unfollow_user_id'];
    foreach ($_SESSION['users'] as &$u) {
        if ($u['id'] === $user['id']) {
            $u['following'] = array_filter($u['following'], fn($f) => $f !== $unfollowId);
            $_SESSION['user'] = $u;
            $user = $u;
            $success = "Unfollowed user!";
            break;
        }
    }
}

// Handle Post Deletion
if (isset($_POST['delete_post_id'])) {
    $postIdToDelete = $_POST['delete_post_id'];
    foreach ($_SESSION['users'] as &$u) {
        if ($u['id'] === $user['id']) {
            $u['posts'] = array_filter($u['posts'], fn($p) => $p['id'] !== $postIdToDelete);
            $_SESSION['user'] = $u;
            $user = $u;
            $success = "Post deleted successfully!";
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['username']) ?>'s Profile | InstaClone</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Roboto', sans-serif; }
        body { background: #f0f2f5; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: auto; }
        .profile-header { display: flex; align-items: center; gap: 30px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .profile-pic { width: 140px; height: 140px; border-radius: 50%; background-size: cover; background-position: center; border: 2px solid #ddd; }
        .profile-info h1 { font-size: 28px; margin-bottom: 10px; }
        .profile-stats { display: flex; gap: 20px; margin: 10px 0; }
        .stat span { font-weight: bold; }
        .section { background: #fff; padding: 20px; margin-top: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        input, textarea, button { width: 100%; padding: 10px; margin-top: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #3897f0; color: white; font-weight: bold; border: none; transition: 0.3s; }
        button:hover { background: #2d74c4; }
        .error { color: red; text-align: center; margin-top: 10px; }
        .success { color: green; text-align: center; margin-top: 10px; }
        .posts { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px; margin-top: 20px; }
        .post { position: relative; border-radius: 10px; overflow: hidden; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .post img { width: 100%; height: auto; display: block; }
        .post form { position: absolute; top: 10px; right: 10px; }
        .post button { background: rgba(255,0,0,0.7); padding: 5px 10px; font-size: 12px; }
        .post .title { padding: 10px; font-weight: bold; text-align: center; background: #fafafa; }
    </style>
</head>
<body>
<div class="container">
    <div class="profile-header">
        <div class="profile-pic" style="background-image: url('<?= !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://via.placeholder.com/150' ?>');"></div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <div class="profile-stats">
                <div class="stat"><span><?= count($user['posts']) ?></span> Posts</div>
            </div>
            <div><?= htmlspecialchars($user['fullname']) ?></div>
            <div><?= htmlspecialchars($user['bio']) ?></div>
        </div>
    </div>

    <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?= $success ?></div><?php endif; ?>

    <div class="section">
        <h3>Update Profile Picture</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="profile_pic" accept="image/*" required>
            <button type="submit" name="update_pic">Update Picture</button>
        </form>
    </div>

    <div class="section">
        <h3>Upload New Post</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="image" accept="image/*" required>
            <input type="text" name="title" placeholder="Title (optional)">
            <textarea name="caption" placeholder="Write a caption..."></textarea>
            <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($user['location']) ?>">
            <button type="submit" name="upload">Upload</button>
        </form>
    </div>

    <div class="posts">
        <?php foreach($user['posts'] as $post): ?>
            <div class="post">
                <img src="<?= $post['image'] ?>" alt="Post Image">
                <?php if (!empty($post['title'])): ?>
                    <div class="title"><?= htmlspecialchars($post['title']) ?></div>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="delete_post_id" value="<?= $post['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
