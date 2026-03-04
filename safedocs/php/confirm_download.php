<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$doc_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Check if document exists
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doc = $result->fetch_assoc();

if (!$doc) {
    die("Document not found or access denied.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Confirm Download</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #eef2f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .confirm-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .confirm-box h2 {
            margin-bottom: 20px;
        }
        .confirm-box form {
            margin-top: 20px;
        }
        .btn {
            padding: 10px 18px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            margin: 0 10px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-confirm {
            background-color: #007bff;
            color: white;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
<div class="confirm-box">
    <h2>Download Confirmation</h2>
    <p>Are you sure ,you want to download the file:  <strong><?= htmlspecialchars($doc['title']) ?></strong>?</p>

    <form method="get" action="download.php" style="display:inline;">
        <input type="hidden" name="id" value="<?= $doc_id ?>">
        <button type="submit" class="btn btn-confirm">Yes, Download</button>
    </form>

    <form method="get" action="dashboard.php" style="display:inline;">
        <button type="submit" class="btn btn-cancel">Cancel</button>
    </form>
</div>
</body>
</html>
