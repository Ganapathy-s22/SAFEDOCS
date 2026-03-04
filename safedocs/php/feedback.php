<?php
session_start();
require_once "db.php"; // assumes $conn is your mysqli connection

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Fetch logged-in user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$stmt->close();

// ✅ Handle Delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ✅ Handle New Feedback Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $userData['name']; // autofill from DB
    $gmail = $userData['email'];   // autofill from DB
    $rating = intval($_POST['rating']);
    $feedback = trim(htmlspecialchars($_POST['feedback']));

    if (strlen($feedback) >= 5 && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO feedback (username, gmail, rating, feedback) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $username, $gmail, $rating, $feedback);
        $stmt->execute();
        $stmt->close();
    }
}

// ✅ Fetch all feedbacks
$result = $conn->query("SELECT * FROM feedback ORDER BY id DESC");
$feedbacks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SafeDocs Feedback</title>
<style>
    body { font-family: Arial, sans-serif; background: #b6d4f1ff; margin: 0; padding: 0; }
    .container { background-color: #f3efefff; max-width: 700px; margin: 20px auto; padding: 20px; border-radius: 10px; box-shadow: 0px 4px 12px rgba(0,0,0,0.15); }
    h1 { text-align: center; color: #f60808ff; }
    form { background-color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
    label { font-weight: bold; display: block; margin-top: 10px; }
    input, textarea, select { width: 80%; padding: 10px; margin-left: 35px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
    input[readonly] { background: #e9ecef; cursor: not-allowed; }
    button { background: #27ae60; color: white; padding: 10px; border: none; border-radius: 5px; margin-top: 15px; width: 100%; font-size: 16px; cursor: pointer; }
    button:hover { background: #219150; }
    .feedback-display { background: #fdf7f7ff; padding: 15px; border-radius: 8px; box-shadow: 0px 3px 8px rgba(0,0,0,0.1); margin-top: 20px; position: relative; }
    .feedback-display h3 { margin: 0; color: #34495e; }
    .rating { font-size: 20px; color: gold; }
    .delete-btn { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; position: absolute; top: 10px; right: 10px; }
    .delete-btn:hover { background: #c0392b; }
    .back-btn {
        display: inline-block;
        background: #f39c12;
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 14px;
        margin-bottom: 15px;
    }
    .back-btn:hover { background: #d68910; }
</style>
</head>
<body>

<div class="container">
    <!-- ✅ Back Button -->
    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

    <h1>SafeDocs Feedback</h1>

    <!-- Feedback Form -->
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($userData['name']); ?>" readonly>

        <label>Gmail:</label>
        <input type="email" name="gmail" value="<?= htmlspecialchars($userData['email']); ?>" readonly>

        <label>Rating:</label>
        <select name="rating" required>
            <option value="">-- Select Rating --</option>
            <option value="1">1 ★ (Bad)</option>
            <option value="2">2 ★★ (Okay)</option>
            <option value="3">3 ★★★ (Good)</option>
            <option value="4">4 ★★★★ (Great)</option>
            <option value="5">5 ★★★★★ (Excellent)</option>
        </select>

        <label>Feedback:</label>
        <textarea name="feedback" rows="4" required minlength="5"></textarea>

        <button type="submit">Submit Feedback</button>
    </form>

    <!-- Display Feedbacks -->
    <?php if (!empty($feedbacks)) : ?>
        <?php foreach ($feedbacks as $fb) : ?>
            <div class="feedback-display">
                <h3><?= htmlspecialchars($fb['username']); ?> 
                    <small>(<?= htmlspecialchars($fb['gmail']); ?>)</small>
                </h3>
                <p class="rating">
                    <?= str_repeat("★", $fb['rating']); ?>
                    (<?= $fb['rating']; ?>/5)
                </p>
                <p><?= htmlspecialchars($fb['feedback']); ?></p>
                <a class="delete-btn" href="?delete=<?= $fb['id']; ?>" onclick="return confirm('Delete this feedback?');">Delete</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
