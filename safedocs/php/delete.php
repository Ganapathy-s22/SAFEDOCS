<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php?error=Invalid file ID.");
    exit();
}

$doc_id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doc = $result->fetch_assoc();

if (!$doc) {
    header("Location: dashboard.php?error=Document not found or permission denied.");
    exit();
}

// If user confirmed deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $deleteStmt = $conn->prepare("DELETE FROM documents WHERE id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $doc_id, $user_id);
    $deleteStmt->execute();

    header("Location: dashboard.php?msg=Document deleted successfully.");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Document - SafeDocs</title>
    <style>
        * {
            margin: 0; padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            padding: 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .card h2 {
            color: #e74c3c;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .card p {
            color: #555;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .file-name {
            font-weight: bold;
            color: #0033a0;
            margin-bottom: 20px;
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-secondary {
            background-color: #ccc;
            color: #333;
        }
        .btn-secondary:hover {
            background-color: #b3b3b3;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>⚠️ Delete Confirmation</h2>
        <p class="file-name">File: <?= htmlspecialchars($doc['title'] ?: 'Untitled Document') ?></p>
        <p>Are you sure you want to permanently delete this document?</p>
        <form method="POST">
            <input type="hidden" name="confirm_delete" value="yes">
            <div class="actions">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
