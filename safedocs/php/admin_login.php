<?php
session_start();
require_once 'db.php';

$error = "";

// If form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $security_code = trim($_POST['security_code']);

    // ✅ Backend validation
    if (strlen($username) < 4) {
        $error = "Username must be at least 4 characters!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters!";
    } elseif (strlen($security_code) !== 6) {
        $error = "Security code must be exactly 6 characters!";
    } else {
        // Hash password (⚠️ better use password_hash() in real apps)
        $hashedPassword = md5($password);

        // ✅ Check if admin exists
        $stmt = $conn->prepare("SELECT id, username FROM admin WHERE username = ? AND password = ? AND security_code = ?");
        $stmt->bind_param("sss", $username, $hashedPassword, $security_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // ✅ Admin found → login success
            $admin = $result->fetch_assoc();
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];

            header("Location: admin_dashboard.php");
            exit();
        } else {
            // ❌ Admin not found
            $error = "No admin account found! <a href='admin_register.php'>Create an admin account</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - SafeDocs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #7db5de, #b29df2);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            width: 350px;
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .login-box input {
            width: 90%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-wrapper input {
            width: 82.5%;
            padding-right: 40px;
        }
        .toggle-pass {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #666;
        }
        .login-box button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #3498db;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .login-box button:hover {
            background: #2980b9;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        .error a {
            color: blue;
            text-decoration: none;
        }
    </style>
    <!-- FontAwesome for eye icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username (min 4 char)" minlength="4" required>
            <input type="text" name="security_code" placeholder="Security Code (6 char)" minlength="6" maxlength="6" required>
            
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Password (min 8 char)" minlength="8" required>
                <i class="fa-solid fa-eye toggle-pass" id="togglePass"></i>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>

    <script>
        const toggle = document.getElementById('togglePass');
        const passField = document.getElementById('password');

        toggle.addEventListener('click', function() {
            const type = passField.type === "password" ? "text" : "password";
            passField.type = type;
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    </script>
</body>
</html>
