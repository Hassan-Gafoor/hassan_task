<!DOCTYPE html>
<html>
<head>
    <title>Select Role | InstaClap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #e0eafc, #cfdef3);
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .container {
            text-align: center;
        }
        .role-boxes {
            display: flex;
            gap: 40px;
            justify-content: center;
            margin-top: 30px;
        }
        .role {
            background: white;
            border: 1px solid #ccc;
            border-radius: 12px;
            padding: 30px;
            width: 200px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }
        .role:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .role img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        h2 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Select Your Role</h2>
        <div class="role-boxes">
            <a class="role" href="login1.php">
                                <h3>Creator</h3>
            </a>
            <a class="role" href="login.php">
                                <h3>Consumer</h3>
            </a>
        </div>
    </div>
</body>
</html>
