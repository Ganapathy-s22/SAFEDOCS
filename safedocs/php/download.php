<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$docId = (int)$_GET['id'];
$userId = $_SESSION['user_id'];

// Fetch the file from database
$stmt = $conn->prepare("SELECT title, file_data FROM documents WHERE id = ? AND user_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ii", $docId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($doc = $result->fetch_assoc()) {
    $filename = !empty($doc['title']) ? $doc['title'] : "document.txt";
    $fileData = $doc['file_data'];

    // Optional: Log the download count (if 'downloads' column exists)
    // $logStmt = $conn->prepare("UPDATE document SET downloads = downloads + 1 WHERE id = ? AND user_id = ?");
    // if ($logStmt) {
    //     $logStmt->bind_param("ii", $docId, $userId);
    //     $logStmt->execute();
    // }

    // Send file to browser
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\"");
    header("Content-Length: " . strlen($fileData));
    echo $fileData;
    exit();
} else {
    echo "File not found or permission denied.";
}
?>
