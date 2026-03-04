<?php
session_start();
require_once 'db.php'; // Assumes $conn = new mysqli(...)

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Sanitize and prepare title
$title = trim($_POST['title'] ?? '');
$title = $title !== '' ? htmlspecialchars($title, ENT_QUOTES) : 'Untitled';

// ✅ Sanitize and prepare description
$description = trim($_POST['description'] ?? '');
$description = $description !== '' ? htmlspecialchars($description, ENT_QUOTES) : null;

// ✅ Allowed file types and size limits
$allowed_extensions = ['pdf', 'docx', 'txt', 'xlsx', 'pptx'];
$max_file_size = 15 * 1024 * 1024;          // 15MB
$max_total_storage = 100 * 1024 * 1024;     // 100MB

// ✅ Check if file is uploaded
if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    header("Location: dashboard.php?error=No file uploaded or an error occurred.");
    exit();
}

$file = $_FILES['document'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// ✅ Validate extension
if (!in_array($file_ext, $allowed_extensions)) {
    header("Location: dashboard.php?error=Unsupported file type. Allowed: pdf, docx, txt, xlsx, pptx.");
    exit();
}

// ✅ Validate file size
if ($file_size > $max_file_size) {
    header("Location: dashboard.php?error=File too large. Max size: 15MB.");
    exit();
}

// ✅ Check user's current total storage usage
$stmt = $conn->prepare("SELECT SUM(size) AS total_used FROM documents WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_used = (int)($row['total_used'] ?? 0);
$stmt->close();

// ✅ Prevent exceeding total storage limit
if (($total_used + $file_size) > $max_total_storage) {
    header("Location: dashboard.php?error=You’ve reached the 100MB limit. Please delete unwanted files and try again.");
    exit();
}

// ✅ Read file contents
$file_data = file_get_contents($file_tmp);
if ($file_data === false) {
    header("Location: dashboard.php?error=Failed to read the file.");
    exit();
}

// ✅ Insert file & description into DB
$stmt = $conn->prepare("INSERT INTO documents (user_id, title, description, file_data, size, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");
$null = null;
$stmt->bind_param("issbi", $user_id, $title, $description, $null, $file_size);
$stmt->send_long_data(3, $file_data);

if ($stmt->execute()) {
    header("Location: dashboard.php?msg=File uploaded successfully.");
} else {
    header("Location: dashboard.php?error=Upload failed: " . urlencode($stmt->error));
}

$stmt->close();
exit();
?>
