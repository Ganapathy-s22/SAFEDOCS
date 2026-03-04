<?php
session_start();
require_once 'db.php';

$message = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
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
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

// Forgot password form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['forgot'])) {
    $email = trim($_POST['email']);
    $dob = $_POST['dob'];
    $born_city = trim($_POST['born_city']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND date_of_birth = ? AND born_city = ?");
    $stmt->bind_param("sss", $email, $dob, $born_city);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit;
    } else {
        $message = "Details did not match our records.";
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
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #638cf1, #00b4db);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            width: 350px;
        }
        h2 {
            text-align: center;
            color: #1f3c88;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #1451ee;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            margin-top: 10px;
        }
        input[type="submit"]:hover {
            background: #0f3ca8;
        }
        .bottom-text {
            text-align: center;
            margin-top: 10px;
        }
        .bottom-text a {
            color: #1a237e;
            text-decoration: none;
            font-weight: bold;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(2px);
            background: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background: white;
            padding: 20px;
            width: 300px;
            margin: 100px auto;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        .close {
            float: right;
            cursor: pointer;
            font-size: 20px;
        }
        .message {
            text-align: center;
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>SafeDocs Login</h2>

    <?php if (!empty($message)) : ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <?php if ($login_success): ?>
        <a href="dashboard.php"><input type="submit" value="Go to Dashboard"></a>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="login" value="1">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" id="login_email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <input type="submit" value="Login">
        </form>

        <div class="bottom-text">
            <a href="#" onclick="openModal()">Forgot Password?</a> | 
            <a href="register.php">Create an account</a>
        </div>
    <?php endif; ?>
</div>

<!-- Forgot Password Modal -->
<div class="modal" id="forgotModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Verify Identity</h3>
        <form method="post">
            <input type="hidden" name="forgot" value="1">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" id="modal_email" readonly>
            </div>
            <div class="form-group">
                <label>Date of Birth:</label>
                <input type="date" name="dob" required>
            </div>
            <div class="form-group">
                <label>Born City:</label>
                <input type="text" name="born_city" required>
            </div>
            <input type="submit" value="Verify">
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById("modal_email").value = document.getElementById("login_email").value;
    document.getElementById("forgotModal").style.display = "block";
}
function closeModal() {
    document.getElementById("forgotModal").style.display = "none";
}
</script>

</body>
</html>
