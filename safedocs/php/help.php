<?php
session_start();
require_once "db.php"; // DB connection

// ✅ Check if user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in user info
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Handle delete reply
if (isset($_GET['delete_reply'])) {
    $delete_id = intval($_GET['delete_reply']);
    $stmt = $conn->prepare("DELETE FROM help_queries WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = "✅ Message deleted successfully.";
    } else {
        $error = "⚠️ You cannot delete this message.";
    }
    $stmt->close();
}

// ✅ Handle delete entire mail thread
if (isset($_GET['delete_mail'])) {
    $delete_id = intval($_GET['delete_mail']);

    // Delete replies first
    $stmt = $conn->prepare("DELETE FROM help_queries WHERE parent_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Delete main mail
    $stmt = $conn->prepare("DELETE FROM help_queries WHERE id = ? AND user_id = ? AND parent_id = 0");
    $stmt->bind_param("ii", $delete_id, $user_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = "✅ Conversation deleted successfully.";
    } else {
        $error = "⚠️ You cannot delete this conversation.";
    }
    $stmt->close();
}

// ✅ Insert new query or reply
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question'])) {
    $question = trim(htmlspecialchars($_POST['question']));
    if (!empty($question)) {
        $stmt = $conn->prepare("INSERT INTO help_queries (user_id, username, email, question, parent_id) VALUES (?, ?, ?, ?, ?)");
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
        $stmt->bind_param("isssi", $user_id, $user['name'], $user['email'], $question, $parent_id);
        $stmt->execute();
        $stmt->close();
        $success = "✅ Your message has been sent.";
    } else {
        $error = "⚠️ Please enter your message.";
    }
}

// ✅ Fetch all queries + replies for this user
$stmt = $conn->prepare("SELECT * FROM help_queries WHERE user_id = ? OR parent_id IN (SELECT id FROM help_queries WHERE user_id = ?) ORDER BY created_at ASC");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$queries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Help - SafeDocs</title>
<style>
    body { font-family: Arial, sans-serif; background: #002b6e; margin: 0; padding: 0; }
    .container { width: 80%; max-width: 1000px; margin: 30px auto; background: #fff; padding: 25px; border-radius: 10px; }
    h2 { text-align: center; margin-bottom: 20px; color: #002b6e; }

    /* Back Button */
    .btn-dashboard { background: #9b59b6; color: #fff; padding: 8px 14px; border-radius: 6px; border: none; cursor: pointer; margin-bottom: 15px; }
    .btn-dashboard:hover { background: #8e44ad; }

    /* Form */
    label { font-weight: bold; display: block; margin-top: 10px; }
    input, textarea { width: 92.5%; padding: 10px; margin-left:3px; margin-top: 6px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    textarea { resize: vertical; }
    button { background: #3498db; color: white; border: none; padding: 8px 14px; margin-top: 12px; border-radius: 6px; cursor: pointer; }
    button:hover { background: #2980b9; }

    .btn-refresh { background: #2ecc71; margin-left: 10px; }
    .btn-refresh:hover { background: #27ae60; }
    .btn-delete-mail { background: #e74c3c; float: right; }
    .btn-delete-mail:hover { background: #c0392b; }

    .message { text-align: center; margin-top: 10px; }
    .success { color: green; }
    .error { color: red; }

    /* Chat bubbles */
    .chat-bubble {
        max-width: 70%;
        padding: 12px;
        border-radius: 8px;
        margin: 10px 0;
        font-size: 14px;
        line-height: 1.4;
        clear: both;
    }
    .user-msg {
        background: #fff4e6;
        border: 1px solid #e67e22;
        color: #8e4400;
        float: left;
    }
    .admin-reply {
        background: #d4f9d6;
        border: 1px solid #27ae60;
        color: #1e6630;
        float: right;
        text-align: right;
    }
    .chat-bubble small { display: block; margin-top: 5px; color: gray; font-size: 11px; }

    .delete-btn { background: #e74c3c; margin-top: 6px; font-size: 12px; padding: 5px 10px; border-radius: 5px; }
    .delete-btn:hover { background: #c0392b; }

    .reply-form { margin-top: 15px; clear: both; }
    .reply-form textarea { width: 92.5%; border-radius: 5px; }
</style>
<script>
function confirmDeleteReply(id) {
    if (confirm("Are you sure you want to delete this message?")) {
        window.location.href = "?delete_reply=" + id;
    }
}
function confirmDeleteMail(id) {
    if (confirm("Are you sure you want to delete this entire conversation?")) {
        window.location.href = "?delete_mail=" + id;
    }
}
</script>
</head>
<body>
<div class="container">
    <!-- ✅ Back to Dashboard -->
    <a href="dashboard.php"><button class="btn-dashboard">⬅ Back to Dashboard</button></a>

    <h2>User Help Mails</h2>

    <!-- ✅ Show messages -->
    <?php if(isset($success)): ?><p class="message success"><?= $success; ?></p><?php endif; ?>
    <?php if(isset($error)): ?><p class="message error"><?= $error; ?></p><?php endif; ?>

    <!-- ✅ Form -->
    <form method="POST">
        <label>Your Question for Admin:</label>
        <textarea name="question" rows="3" placeholder="Type your message..." required></textarea>
        <button type="submit">Send</button>
        <button type="button" class="btn-refresh" onclick="window.location.reload()">Refresh</button>
    </form>

    <!-- ✅ Conversations -->
    <?php if (!empty($queries)): ?>
        <?php foreach ($queries as $q): ?>
            <?php if ($q['parent_id'] == 0): ?>
                <div style="margin-top:30px; padding:15px; border:1px solid #ddd; border-radius:8px;">
                    <h3 style="color:#002b6e;">Help Mails</h3>
                    <p><strong>User:</strong> <?= htmlspecialchars($q['username']); ?> | 
                       <strong>Email:</strong> <?= htmlspecialchars($q['email']); ?> | 
                       <strong>Date:</strong> <?= $q['created_at']; ?>
                       <button class="btn-delete-mail" onclick="confirmDeleteMail(<?= $q['id']; ?>)">Delete</button>
                    </p>

                    <!-- Main user message -->
                    <div class="chat-bubble user-msg">
                        <strong><?= htmlspecialchars($q['username']); ?>:</strong> <?= nl2br(htmlspecialchars($q['question'])); ?>
                        <small>(<?= $q['created_at']; ?>)</small>
                        <button type="button" class="delete-btn" onclick="confirmDeleteReply(<?= $q['id']; ?>)">🗑 Delete</button>
                    </div>

                    <!-- Replies -->
                    <?php foreach ($queries as $reply): ?>
                        <?php if ($reply['parent_id'] == $q['id']): ?>
                            <?php if ($reply['username'] == "Admin"): ?>
                                <div class="chat-bubble admin-reply">
                                    <strong>Admin:</strong> <?= nl2br(htmlspecialchars($reply['question'])); ?>
                                    <small>(<?= $reply['created_at']; ?>)</small>
                                </div>
                            <?php else: ?>
                                <div class="chat-bubble user-msg">
                                    <strong><?= htmlspecialchars($reply['username']); ?>:</strong> <?= nl2br(htmlspecialchars($reply['question'])); ?>
                                    <small>(<?= $reply['created_at']; ?>)</small>
                                    <button type="button" class="delete-btn" onclick="confirmDeleteReply(<?= $reply['id']; ?>)">🗑 Delete</button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Reply box -->
                    <form method="POST" class="reply-form">
                        <input type="hidden" name="parent_id" value="<?= $q['id']; ?>">
                        <textarea name="question" rows="2" placeholder="Type your reply..." required></textarea>
                        <button type="submit">Send Reply</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
