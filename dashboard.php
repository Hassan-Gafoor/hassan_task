<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$current_page = $_GET['page'] ?? 'home';

// Params for search and viewing other profiles
$search_query = $_GET['search'] ?? '';
$view_user_id = $_GET['view_user_id'] ?? '';

function getUserById($id) {
    foreach ($_SESSION['users'] as $u) {
        if ($u['id'] === $id) return $u;
    }
    return null;
}

function getUsersBySearch($query, $excludeUserId) {
    $results = [];
    foreach ($_SESSION['users'] as $u) {
        if ($u['id'] === $excludeUserId) continue;
        if (stripos($u['username'], $query) !== false) {
            $results[] = $u;
        }
    }
    return $results;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | InstaClone</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            color: #333;
        }

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #dbdbdb;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .nav-container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-img {
            height: 40px;
            margin-right: 10px;
        }

        .logo-text {
            font-size: 24px;
            font-family: 'Billabong', cursive;
            color: #262626;
        }

        .nav-left,
        .nav-right {
            display: flex;
            align-items: center;
        }

        .nav-link {
            margin: 0 12px;
            text-decoration: none;
            color: #262626;
            font-size: 18px;
        }

        .nav-link.active {
            color: #0095f6;
            font-weight: bold;
        }

        .logout-btn {
            background: none;
            border: none;
            color: #262626;
            font-size: 16px;
            cursor: pointer;
            margin-left: 15px;
        }

        .search-form-inline {
            display: flex;
            align-items: center;
        }

        .search-input {
            padding: 6px 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
        }

        .search-button {
            padding: 6px 12px;
            font-size: 14px;
            background-color: #3897f0;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 6px;
        }

        .content {
            margin-top: 100px;
            padding: 30px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .user-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #efefef;
        }

        .user-item img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #ddd;
        }

        .user-info {
            flex-grow: 1;
        }

        .view-profile-link {
            background-color: #0095f6;
            color: white;
            padding: 6px 14px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .view-profile-link:hover {
            background-color: #007bd1;
        }

        .profile-pic-large {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
            margin-bottom: 15px;
        }

        .profile-info {
            text-align: center;
        }

        .profile-info h2 {
            margin: 10px 0;
        }

        .profile-info p {
            margin: 5px 0;
            color: #666;
        }

        .profile-bio {
            margin-top: 10px;
            font-style: italic;
        }

        a.back-link {
            color: #3897f0;
            text-decoration: none;
        }

        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <div class="logo-container">
                    <img src="logo.png" alt="InstaClap Logo" class="logo-img">
                    <div class="logo-text">InstaClap</div>
                </div>
                <a href="dashboard.php?page=home" class="nav-link <?= $current_page === 'home' ? 'active' : '' ?>">Home</a>
                <a href="dashboard.php?page=profile" class="nav-link <?= $current_page === 'profile' ? 'active' : '' ?>">Profile</a>
                <a href="dashboard.php?page=messages" class="nav-link <?= $current_page === 'messages' ? 'active' : '' ?>">Messages</a>
            </div>
            <div class="nav-right">
                <form class="search-form-inline" method="get" action="dashboard.php">
                    <input type="hidden" name="page" value="search">
                    <input type="text" name="search" class="search-input" placeholder="Search users..." value="<?= htmlspecialchars($search_query) ?>" required>
                    <button type="submit" class="search-button">Search</button>
                </form>
                <form action="logout.php" method="post" style="display:inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="content">
        <?php if ($current_page === 'search'): ?>
            <?php if ($search_query): ?>
                <?php
                $matchedUsers = getUsersBySearch($search_query, $user['id']);
                if (count($matchedUsers) > 0): ?>
                    <ul class="user-list">
                        <?php foreach($matchedUsers as $u): ?>
                            <li class="user-item">
                                <?php $profilePic = $u['profile_pic'] ?: 'default-profile.png'; ?>
                                <img src="<?= htmlspecialchars($profilePic) ?>" alt="<?= htmlspecialchars($u['username']) ?>'s profile picture">
                                <div class="user-info">
                                    <strong><?= htmlspecialchars($u['username']) ?></strong><br>
                                    <small><?= htmlspecialchars($u['fullname']) ?></small>
                                </div>
                                <a href="dashboard.php?page=view_profile&view_user_id=<?= urlencode($u['id']) ?>&search=<?= urlencode($search_query) ?>" class="view-profile-link">View Profile</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No users found matching <strong><?= htmlspecialchars($search_query) ?></strong>.</p>
                <?php endif; ?>
            <?php endif; ?>
        <?php elseif ($current_page === 'view_profile' && $view_user_id): ?>
            <?php
            $profileUser = getUserById($view_user_id);
            if ($profileUser): 
                $profilePic = $profileUser['profile_pic'] ?: 'default-profile.png';
            ?>
                <div class="profile-info">
                    <img src="<?= htmlspecialchars($profilePic) ?>" alt="<?= htmlspecialchars($profileUser['username']) ?>'s profile picture" class="profile-pic-large">
                    <h2><?= htmlspecialchars($profileUser['fullname']) ?> (@<?= htmlspecialchars($profileUser['username']) ?>)</h2>
                    <p><strong>Location:</strong> <?= htmlspecialchars($profileUser['location']) ?></p>
                    <p class="profile-bio"><?= nl2br(htmlspecialchars($profileUser['bio'])) ?></p>
                </div>
                <p><a class="back-link" href="dashboard.php?page=search&search=<?= urlencode($search_query) ?>">← Back to Search Results</a></p>
            <?php else: ?>
                <p>User not found.</p>
            <?php endif; ?>
        <?php else: ?>
            <?php
            switch($current_page) {
                case 'home':
                    include 'home.php';
                    break;
                case 'profile':
                    include 'profile.php';
                    break;
                case 'messages':
                    include 'messages.php';
                    break;
                default:
                    include 'home.php';
            }
            ?>
        <?php endif; ?>
    </div>
</body>
</html>
