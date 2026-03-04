<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$doc_id = intval($_GET['id'] ?? 0);
if ($doc_id <= 0) {
    echo "Invalid file ID.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch document info
$stmt = $conn->prepare("SELECT title, description FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Document not found.";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Description</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #9cc4feff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 650px;
            background: #fff;
            margin: 60px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 15px;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 8px 0 5px;
        }
        textarea {
            width: 90%;
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: vertical;
            min-height: 120px;
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            background: #007bff;
            color: white;
            font-size: 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 12px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #555;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Description for: <span style="color:#007bff;"><?= htmlspecialchars($row['title']) ?></span></h2>
    
    <form action="update_description.php" method="post">
        <input type="hidden" name="doc_id" value="<?= $doc_id ?>">
        
        <label for="description">Description:</label>
        <textarea name="description" id="description"><?= htmlspecialchars($row['description']) ?></textarea>
        
        <br>
        <button type="submit" class="btn">Update Description</button>
    </form>
    
    <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>

</body>
</html>
