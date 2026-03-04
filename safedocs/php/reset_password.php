<?php
session_start();
require_once 'db.php'; // Uses your DB config

if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['reset_email'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (!empty($new_pass) && !empty($confirm_pass)) {
        if ($new_pass === $confirm_pass) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_pass, $email);

            if ($stmt->execute()) {
                $message = "Password successfully reset. You can now <a href='login.php'>login</a>.";
                unset($_SESSION['reset_email']);
            } else {
                $message = "Error updating password. Please try again.";
            }
        } else {
            $message = "Passwords do not match!";
        }
    } else {
        $message = "Please fill in both fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - SafeDocs</title>
    <style>
        body {
            background: linear-gradient(to right, #1f6feb, #66d9ef);
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .reset-container {
            background-color: white;
            padding: 35px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #1f3c88;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0e2a63;
        }
        .message {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
            color: #d63031;
        }
        .message a {
            color: #1f3c88;
        }
    </style>
</head>
<body>

<div class="reset-container">
    <h2>Reset Your Password</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required placeholder="Enter new password">
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required placeholder="Re-enter new password">
        </div>
        <input type="submit" value="Reset Password">
    </form>
</div>

</body>
</html>
