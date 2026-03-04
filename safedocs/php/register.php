<?php
include 'db.php'; // Make sure this defines $conn

$success = '';
$error = '';
$name = $email = $dob = $born_city = '';
$nameErr = $bornCityErr = $passwordErr = $dobErr = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name         = trim($_POST["name"]);
    $email        = trim($_POST["email"]);
    $dob          = $_POST["dob"];
    $born_city    = trim($_POST["born_city"]);
    $password_raw = $_POST["password"];

    $isValid = true;

    // ✅ Validate name (min 4 characters, alphabets only)
    if (strlen($name) < 4 || !ctype_alpha($name)) {
        $nameErr = "Name must be at least 4 letters and contain only alphabets.";
        $isValid = false;
    }

    // ✅ Validate born_city (min 3 characters, alphabets only)
    if (strlen($born_city) < 3 || !ctype_alpha($born_city)) {
        $bornCityErr = "Born city must be at least 3 letters and contain only alphabets.";
        $isValid = false;
    }

    // ✅ Validate password (min 8 characters)
    if (strlen($password_raw) < 8) {
        $passwordErr = "Password must be at least 8 characters.";
        $isValid = false;
    }

    // ✅ Validate date of birth (user must be older than 10 years)
    $today = new DateTime();
    $birthDate = new DateTime($dob);
    $age = $today->diff($birthDate)->y;

    if ($age < 10) {
        $dobErr = "You must be older than 10 years to register.";
        $isValid = false;
    }

    if ($isValid) {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered. <a href='login.php'>Login here</a>.";
        } else {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO users (name, email, date_of_birth, born_city, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $dob, $born_city, $password);

            if ($stmt->execute()) {
                $success = "Registration successful! <a href='login.php'>Check it using login</a>.";
                $name = $email = $dob = $born_city = '';
            } else {
                $error = "Error: Could not register user.";
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - SafeDocs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(120deg, #6093c7, #3498db);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-box {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .register-box h1 {
            text-align: center;
            margin-bottom: 15px;
            color: #f61703;
        }

        .register-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1f3c88;
        }

        input[type="text"], input[type="email"], input[type="date"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .error-text {
            color: red;
            font-size: 13px;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #1f3c88;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0d2764;
        }

        .message {
            text-align: center;
            color: green;
        }

        .error {
            text-align: center;
            color: red;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h1>Welcome to SafeDocs</h1>
    <h2>Create New Account</h2>

    <?php if ($success): ?>
        <p class="message"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <input type="text" name="name" placeholder="Your Full Name (min 4 letters only)" value="<?= htmlspecialchars($name) ?>" required>
        <?php if ($nameErr): ?><div class="error-text"><?= $nameErr ?></div><?php endif; ?>

        <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email) ?>" required>

        <input type="date" name="dob" placeholder="Date of Birth" value="<?= htmlspecialchars($dob) ?>" required>
        <?php if ($dobErr): ?><div class="error-text"><?= $dobErr ?></div><?php endif; ?>

        <input type="text" name="born_city" placeholder="Born City (min 3 letters only)" value="<?= htmlspecialchars($born_city) ?>" required>
        <?php if ($bornCityErr): ?><div class="error-text"><?= $bornCityErr ?></div><?php endif; ?>

        <input type="password" name="password" placeholder="Create Password (min 8 characters)" required>
        <?php if ($passwordErr): ?><div class="error-text"><?= $passwordErr ?></div><?php endif; ?>

        <input type="submit" value="Register">
    </form>
</div>

</body>
</html>
