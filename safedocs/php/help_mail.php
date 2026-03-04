<?php
session_start();
require_once "db.php";

// ✅ Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php"); // moved to admin_login instead of dashboard
    exit();
}

// ✅ Handle reply form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reply'], $_POST['parent_id'])) {
    $reply = trim($_POST['reply']);
    $parent_id = intval($_POST['parent_id']);

    if (!empty($reply)) {
        // Get user_id and email of the parent query
        $stmt = $conn->prepare("SELECT user_id, email FROM help_queries WHERE id = ?");
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $stmt->bind_result($user_id, $email);
        $stmt->fetch();
        $stmt->close();

        // Insert reply as new row (username = "Admin")
        $adminUsername = "Admin";
        $stmt = $conn->prepare("INSERT INTO help_queries (user_id, username, email, question, parent_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $adminUsername, $email, $reply, $parent_id);
        $stmt->execute();
        $stmt->close();
    }
}

// ✅ Handle delete query (with all replies)
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM help_queries WHERE id = ? OR parent_id = ?");
    $stmt->bind_param("ii", $delete_id, $delete_id);
    $stmt->execute();
    $stmt->close();
}

// ✅ Fetch all parent queries
$stmt = $conn->prepare("SELECT * FROM help_queries WHERE parent_id = 0 ORDER BY created_at DESC");
$stmt->execute();
$queries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ✅ Fetch replies for all queries
$allReplies = [];
$stmt = $conn->prepare("SELECT * FROM help_queries WHERE parent_id != 0 ORDER BY created_at ASC");
$stmt->execute();
$replies = $stmt->get_result();
while ($row = $replies->fetch_assoc()) {
    $allReplies[$row['parent_id']][] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Help Mails</title>
<style>
body { font-family: Arial, sans-serif; background: #81bcf6ff; margin: 0; padding: 20px; color: #fff; }
.container { max-width: 900px; margin: auto;background-color: #ccc; }
h2 { text-align: center; margin-bottom: 20px; color: #030303ff; }
.card { background: #fff; color: #000; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0px 2px 6px rgba(0,0,0,0.1); }
.card h3 { margin: 0 0 10px; color: #0477c4ff; }
.delete-btn { float: right; background: #e74c3c; color: white; padding: 6px 10px; border-radius: 5px; text-decoration: none; }
.delete-btn:hover { background: #c0392b; }
.info { font-size: 14px; margin-bottom: 10px; color: #555; }

.msg-container { display: flex;margin-left: 2%; margin-bottom: 10px; }

/* ✅ User messages (left side - orange) */
.user-msg { 
    background: #e2dbd0ff; 
    border: 1px solid #e67e22;
    padding: 10px; 
    border-radius: 6px; 
    font-size: 15px; 
    color: #e67e22; 
    max-width: 70%;
    text-align: left; 
    font-weight: bold;
}

/* ✅ Admin replies (right side - green) */
.reply { 
    background: #dff0d8; 
    border: 1px solid #2ecc71;
    padding: 10px; 
    border-radius: 6px; 
    font-size: 14px; 
    color: #2c662d; 
    max-width: 70%;
    text-align: left;
    margin-left: auto; /* Pushes to right */
}

.reply-box { margin-left: 10px;  margin-top: 10px; }
textarea { width: 92.5%; min-height: 60px; padding: 15px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; }
button {margin-left: 80%; margin-top: 8px; background: #1acc2fff; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 14px; }
button:hover { background: #05f569; }
.top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.back-btn, .refresh-btn { background: #2724f0ff; color: white; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: bold; }
.back-btn:hover, .refresh-btn:hover { background: #2980b9; }
small { font-size: 12px; display: block; margin-top: 4px; color: #555; }

.no-mails { text-align: center; font-size: 18px; margin-top: 50px;color: #f82039ff;  font-weight: bold; }
</style>
</head>
<body>

<div class="container">
<div class="top-bar">
    <a href="admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
    <a href="" class="refresh-btn">🔄 Refresh</a>
</div>

<h2>User Help Mails</h2>

<?php if(count($queries) === 0): ?>
    <div class="no-mails">----- No Mails From Users -----</div>
<?php else: ?>
    <?php foreach ($queries as $query): ?>
    <div class="card">
        <a class="delete-btn" href="?delete=<?= $query['id']; ?>" onclick="return confirm('Delete this mail and all its replies?');">Delete</a>
        <h3><b>Help Mail</b></h3>
        <p class="info">
            <b>User:</b> <?= htmlspecialchars($query['username']); ?> | 
            <b>Email:</b> <?= htmlspecialchars($query['email']); ?> | 
            <b>Date:</b> <?= date("M d, Y h:i A", strtotime($query['created_at'])); ?>
        </p>
        
        <!-- ✅ User Message -->
        <div class="msg-container">
            <div class="user-msg">
                <?= htmlspecialchars($query['username']); ?>:<br>
                <?= nl2br(htmlspecialchars($query['question'])); ?>
                <small>(<?= date("M d, Y h:i A", strtotime($query['created_at'])); ?>)</small>
            </div>
        </div>

        <!-- ✅ Show replies -->
        <?php if(isset($allReplies[$query['id']])): ?>
            <?php foreach($allReplies[$query['id']] as $reply): ?>
                <div class="msg-container">
                <?php if ($reply['username'] === 'Admin'): ?>
                    <div class="reply">
                        <b><?= htmlspecialchars($reply['username']); ?>:</b> 
                        <?= nl2br(htmlspecialchars($reply['question'])); ?>
                        <small>(<?= date("M d, Y h:i A", strtotime($reply['created_at'])); ?>)</small>
                    </div>
                <?php else: ?>
                    <div class="user-msg">
                        <?= htmlspecialchars($reply['username']); ?>:<br>
                        <?= nl2br(htmlspecialchars($reply['question'])); ?>
                        <small>(<?= date("M d, Y h:i A", strtotime($reply['created_at'])); ?>)</small>
                    </div>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ✅ Reply form -->
        <form method="POST" class="reply-box">
            <input type="hidden" name="parent_id" value="<?= $query['id']; ?>">
            <textarea name="reply" placeholder="Type your reply..." required></textarea>
            <button type="submit">Send Reply</button>
        </form>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

</div>
</body>
</html>
