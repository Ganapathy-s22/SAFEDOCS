<?php
session_start();
require_once 'db.php'; // Use your existing db.php connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle GET request: Show form with current document title
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $docId = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $docId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doc = $result->fetch_assoc();

    if (!$doc) {
        echo "Document not found or access denied.";
        exit();
    }
}

// Handle POST request: Update title
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['title'])) {
    $docId = intval($_POST['id']);
    $newTitle = trim($_POST['title']);

    $stmt = $conn->prepare("UPDATE documents SET title = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $newTitle, $docId, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?msg=Title updated successfully");
        exit();
    } else {
        echo "Error updating document title.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Document Title</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .edit-form {
            max-width: 400px;
            margin: 60px auto;
            background: #73c4e1ff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .edit-form h2 {
            margin-bottom: 20px;
            color: #0033a0;
        }

        .edit-form input[type="text"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .button-group {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }

        .edit-form button {
            background-color: #0033a0;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .edit-form button:hover {
            background-color: #002275;
        }

        .back-btn {
            background-color: #f31818ff;
        }

        
    </style>
</head>
<body>
    <div class="edit-form">
        <h2>Edit File Title:</h2>
        <form method="POST" action="edit.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($doc['id']) ?>">
            <input type="text" name="title" value="<?= htmlspecialchars($doc['title']) ?>" required>
            
            <div class="button-group">
                <button type="submit">Update</button>
                <a href="dashboard.php" class="back-btn" style="display: inline-block; text-decoration: none; padding: 10px 16px; border-radius: 5px; color: #fff;">Back</a>
            </div>
        </form>
    </div>
</body>
</html>
