<?php
session_start();
require_once 'db.php'; // Uses your provided DB connection script

$message = '';
$login_success = false;
$show_forgot = false;
$name = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_submit'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (!empty($email) && !empty($password)) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $login_success = true;
                $message = "Login successful!";
            } else {
                $message = "Incorrect email or password.";
            }
        } else {
            $message = "Please fill in all fields.";
        }
    }

    if (isset($_POST['forgot_submit'])) {
        $email = trim($_POST['forgot_email']);
        $dob = $_POST['dob'];
        $city = trim($_POST['city']);

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $show_forgot = true;

        if ($user) {
            $name = $user['name'];

            if ($user['date_of_birth'] === $dob && strtolower($user['born_city']) === strtolower($city)) {
                $_SESSION['reset_email'] = $email;
                header("Location: reset_password.php");
                exit;
            } else {
                $message = "Verification failed. DOB or City doesn't match.";
            }
        } else {
            $message = "No user found with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - SafeDocs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #638cf1, #00b4db);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: #fff;
            padding: 35px 30px;
            border-radius: 14px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #1f3c88;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #1451ee;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #0f3ca8;
        }
        .message-box {
            text-align: center;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .message-box.success { color: green; }
        .message-box.error { color: #c0392b; }

        .btn-dashboard {
            display: block;
            margin: 0 auto;
            margin-top: 20px;
            padding: 12px 24px;
            text-align: center;
            background-color: #1451ee;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            width: 60%;
        }
        .btn-dashboard:hover {
            background-color: #0f3ca8;
        }

        .forgot-section {
            display: <?= $show_forgot ? 'block' : 'none' ?>;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }
        .toggle-link {
            text-align: right;
            color: #1a237e;
            font-size: 14px;
            cursor: pointer;
            display: block;
        }
        .bottom-text {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .bottom-text a {
            color: #0033a0;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>SafeDocs Login</h2>

    <?php if (!empty($message)): ?>
        <div class="message-box <?= $login_success ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($login_success): ?>
        <a class="btn-dashboard" href="dashboard.php">Go to Dashboard</a>
    <?php else: ?>
        <!-- Login Form -->
        <form method="post">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="Enter your email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>
            <span class="toggle-link" onclick="document.getElementById('forgot-section').style.display='block'">Forgot Password?</span>
            <br>
            <input type="submit" name="login_submit" value="Login">
        </form>

        <!-- Forgot Password Section -->
        <div id="forgot-section" class="forgot-section">
            <form method="post">
                <div class="form-group">
                    <label>Enter your Email</label>
                    <input type="email" name="forgot_email" required value="<?= htmlspecialchars($_POST['forgot_email'] ?? '') ?>">
                </div>

                <?php if ($name): ?>
                    <div class="form-group">
                        <label>Your Name (Autofilled)</label>
                        <input type="text" value="<?= htmlspecialchars($name) ?>" disabled>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" required>
                </div>

                <div class="form-group">
                    <label>City You Were Born</label>
                    <input type="text" name="city" required placeholder="e.g., Chennai">
                </div>

                <input type="submit" name="forgot_submit" value="Verify & Reset">
            </form>
        </div>

        <div class="bottom-text">
            New here? <a href="register.php">Create an account</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
