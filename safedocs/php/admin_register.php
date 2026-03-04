<?php
session_start();
require_once 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $security_code = trim($_POST['security_code']);

    // Backend validation
    if (strlen($username) < 4) {
        $error = "Username must be at least 4 characters long!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif (!preg_match("/^[A-Za-z0-9]{6}$/", $security_code)) {
        $error = "Security Code must be exactly 6 alphanumeric characters!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash password (use password_hash in real apps, MD5 for demo only)
        $hashedPassword = md5($password);

        // Check if username already exists
        $check = $conn->prepare("SELECT id FROM admin WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO admin (username, password, security_code) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $security_code);

            if ($stmt->execute()) {
                $success = "Admin registered successfully! <a href='admin_login.php'>Click here to login</a>";
            } else {
                $error = "Registration failed. Try again.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Registration - SafeDocs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #7db5deff, #b29df2ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-box {
            background: #fcfafaff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 400px;
        }
        .register-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #010204ff;
        }
        .input-group {
            position: relative;
        }
        .register-box input {
            width: 90%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .toggle-password {
            position: absolute;
            right: 30px;
            top: 35%;
            cursor: pointer;
            color: #555;
        }
        .register-box button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #27ae60;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .register-box button:hover {
            background: #219150;
        }
        .message {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Admin Registration</h2>
        <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username (min 4 letters)" minlength="4" required>
            <input type="text" name="security_code" placeholder="Security Code (6 chars)" pattern="[A-Za-z0-9]{6}" title="Exactly 6 alphanumeric characters" required>
            
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password (min 8 characters)" minlength="8" required>
                <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
            </div>
            
            <div class="input-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" minlength="8" required>
                <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
            </div>
            
            <button type="submit">Register</button>
        </form>
    </div>

    <script>
        function togglePassword(id) {
            const field = document.getElementById(id);
            field.type = field.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
