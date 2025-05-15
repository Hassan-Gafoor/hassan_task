<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user'])) {
    header("Location: dashboard2.php");
    exit();
}

// Initialize users array if not exists
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [];
}

$error = '';
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    foreach ($_SESSION['users'] as $user) {
        if ($user['email'] === $email && $user['password'] === $password) {
            $_SESSION['user'] = $user;
            header("Location: dashboard2.php");
            exit();
        }
    }
    $error = "Invalid email or password!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | InstaClap</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #f9f9f9, #e0eafc);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
        }
        .logo-img {
            width: 100px;
            margin: 0 auto 20px;
        }
        .logo-text {
            font-size: 36px;
            color: #262626;
            margin-bottom: 20px;
            font-family: 'Billabong', cursive;
        }
        input {
            width: 100%;
            padding: 12px 14px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: #f7f7f7;
            font-size: 15px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #3897f0;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background-color: #2c82dd;
        }
        .error {
            color: #ed4956;
            margin: 15px 0;
            font-size: 14px;
        }
        .signup-link {
            margin-top: 20px;
            font-size: 14px;
            color: #262626;
        }
        .signup-link a {
            color: #3897f0;
            text-decoration: none;
            font-weight: 600;
        }
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo Image -->
        <img src="logo.png" alt="InstaClone Logo" class="logo-img">
        <!-- Optional: logo text -->
        <div class="logo-text">InstaClap</div>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Log In</button>
        </form>

        <div class="signup-link">
            Don't have an account? <a href="signup2.php">Sign up</a>
        </div>
    </div>
</body>
</html>
