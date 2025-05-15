<?php
session_start();

// Redirect to dashboard if already logged in
if(isset($_SESSION['user'])) {
    header("Location: dashboard2.php");
    exit();
}

// Initialize users array if not exists
if(!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

$error = '';
if(isset($_POST['signup'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $fullname = $_POST['fullname'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $location = $_POST['location'] ?? '';
    
    // Validation
    if(empty($username) || empty($email) || empty($password) || empty($fullname) || empty($bio) || empty($location)) {
        $error = "All fields are required!";
    } else {
        // Check if email or username already exists
        foreach($_SESSION['users'] as $user) {
            if($user['email'] === $email) {
                $error = "Email already registered!";
                break;
            }
            if($user['username'] === $username) {
                $error = "Username already taken!";
                break;
            }
        }
        
        if(empty($error)) {
            $newUser = [
                'id' => uniqid(),
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'fullname' => $fullname,
                'bio' => $bio,
                'location' => $location,
                'profile_pic' => '',
                'posts' => [],
                'followers' => [],
                'following' => []
            ];
            
            $_SESSION['users'][] = $newUser;
            $_SESSION['registered_email'] = $email;
            header("Location: index.php?registered=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up | InstaClap</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
           background: linear-gradient(to right, #f9f9f9, #e0eafc);
            margin: 0; padding: 0; 
            display: flex; justify-content: center; align-items: center; 
            height: 100vh; 
        }
        .container { 
            width: 350px; 
            background: #fff; 
            border: 1px solid #dbdbdb; 
            padding: 20px 40px; 
            text-align: center; 
            box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
        }
        .logo img {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
        }
        input, textarea { 
            width: 100%; 
            padding: 10px; 
            margin: 5px 0; 
            border: 1px solid #efefef; 
            background: #fafafa; 
            box-sizing: border-box; 
            font-size: 14px;
            border-radius: 8px;
        }
        textarea { 
            height: 80px; 
            resize: vertical; 
        }
        button { 
            width: 100%; 
            background: #3897f0; 
            color: #fff; 
            border: none; 
            padding: 10px; 
            margin: 10px 0; 
            font-weight: bold; 
            cursor: pointer; 
            border-radius: 8px;
            font-size: 16px;
        }
        button:hover {
            background: #3073cc;
        }
        .error { 
            color: #ed4956; 
            margin: 10px 0; 
            font-size: 14px; 
        }
        .login-link { 
            margin: 20px 0; 
            color: #262626; 
            font-size: 14px;
        }
        a { 
            color: #3897f0; 
            text-decoration: none; 
            font-weight: bold; 
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="InstaClap Logo">
        </div>
        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="fullname" placeholder="Full Name" required>
            <textarea name="bio" placeholder="Bio" required></textarea>
            <input type="text" name="location" placeholder="Location" required>
            <button type="submit" name="signup">Sign Up</button>
        </form>
        <div class="login-link">Already have an account? <a href="index.php">Log in</a></div>
    </div>
</body>
</html>
