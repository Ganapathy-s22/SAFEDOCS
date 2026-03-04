<?php
session_start();
require_once 'db.php';

// ✅ Only allow access if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// ✅ Fetch total storage used
$stmt = $conn->prepare("SELECT SUM(size) AS total_used FROM documents WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalUsedBytes = $row['total_used'] ?? 0;

$maxStorageBytes = 100 * 1024 * 1024; // 100 MB
$usedMB = round($totalUsedBytes / (1024 * 1024), 2);
$usedKB = round($totalUsedBytes / 1024, 2);
$percentageUsed = min(100, round(($totalUsedBytes / $maxStorageBytes) * 100));

$storageClass = 'success';
if ($percentageUsed > 90) $storageClass = 'danger';
elseif ($percentageUsed > 70) $storageClass = 'warning';

// ✅ Format file size function
function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    if ($bytes > 1) return $bytes . ' bytes';
    if ($bytes == 1) return "1 byte";
    return "0 bytes";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SafeDocs</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f3f6fd; margin: 0; }
        header {
            background-color: #0033a0; color: white; padding: 15px 30px;
            display: flex; align-items: center; position: relative;
        }
        .hamburger { cursor: pointer; display: inline-block; padding: 8px; }
        .hamburger div {
            width: 25px; height: 3px; background-color: white;
            margin: 5px 0; transition: 0.4s;
        }
        .dropdown {
            display: none; position: absolute; top: 55px; left: 20px;
            background-color: white; color: black; min-width: 150px;
            box-shadow: 0px 4px 6px rgba(0,0,0,0.2); z-index: 10;
            border-radius: 5px;
        }
        .dropdown a {
            display: block; padding: 10px; text-decoration: none; color: black;
        }
        .dropdown a:hover { background-color: #f1f1f1; }
        .storage-container {
            position: absolute; left: 50%; transform: translateX(-50%);
            text-align: center;
        }
        .storage-bar {
            width: 250px; height: 14px; background-color: #ddd;
            border-radius: 7px; overflow: hidden; margin-bottom: 5px;
        }
        .storage-fill { height: 100%; transition: width 0.4s ease-in-out; }
        .storage-fill.success { background-color: #28a745; }
        .storage-fill.warning { background-color: #ffc107; }
        .storage-fill.danger  { background-color: #dc3545; }
        .storage-text { font-size: 13px; color: #f0f0f0; }
        .logout button {
            background-color: #ff4d4d; color: white; padding: 8px 16px;
            border: none; border-radius: 5px; font-weight: bold; cursor: pointer;
        }
        .logout button:hover { background-color: #cc0000; }
        .main-container { display: flex; height: calc(100vh - 70px); }
        .sidebar {
            width: 25%; background-color: #ffffff;
            padding: 30px 20px; box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        }
        .sidebar h1{color:green;margin-left: 20%;}
        .upload-section input, .upload-section textarea, .upload-section button {
            width: 80%; padding: 8px; margin-bottom: 10px;
        }
        .upload-section textarea {
            resize: vertical; height: 60px;
        }
        .upload-section button {
            background-color: #0033a0; color: white; border: none;
            border-radius: 5px; font-weight: bold; cursor: pointer;
        }
        .upload-section button:hover { background-color: #002275; }
        .content {
            width: 75%; padding: 30px; overflow-y: auto;
        }
        .docs-table table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
        }
        .docs-table th, .docs-table td {
            padding: 12px; border-bottom: 1px solid #ddd; text-align: left;
        }
        .btn-view, .btn-download, .btn-delete, .btn-edit, .btn-desc {
            margin-right: 6px; padding: 6px 10px; border-radius: 4px;
            font-size: 13px; text-decoration: none; font-weight: bold;
        }
        .btn-view { background-color: #28a745; color: white; }
        .btn-edit { background-color: #ffc107; color: black; }
        .btn-download { background-color: #007bff; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-desc { background-color: orange; color: white; }
        .message { margin-top: 15px; font-weight: bold; }
    </style>
</head>
<body>
<header>
    <div class="hamburger" onclick="toggleDropdown()">
        <div></div><div></div><div></div>
    </div>
    <div class="dropdown" id="menuDropdown">
        <a href="help.php">Help</a>
        <a href="feedback.php">Feedbacks</a>
        <a href="about.php">About</a>
    </div>
    <h1 style="margin-left: 20px;">📂 SafeDocs</h1>
    <div class="storage-container" title="<?= $percentageUsed ?>% used">
        <div class="storage-bar">
            <div class="storage-fill <?= $storageClass ?>" style="width: <?= $percentageUsed ?>%;"></div>
        </div>
        <div class="storage-text">
            <?= $usedKB ?> KB (<?= $usedMB ?> MB) / 100 MB used
        </div>
    </div>
    <div class="logout" style="margin-left:auto;">
        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>
</header>

<div class="main-container">
    <div class="sidebar">
        <h2>👋 Welcome</h2>
        <strong><h1><?= htmlspecialchars($userName) ?></h1></strong>
        <hr>
        <div class="upload-section">
            <h3>📤 Add Document</h3>
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Enter file title " />
                <textarea name="description" placeholder="Enter file description "></textarea>
                <input type="file" name="document" required>
                <button type="submit">Upload</button>
            </form>
            <?php if (isset($_GET['msg'])): ?>
                <p class="message" style="color: green;"> <?= htmlspecialchars($_GET['msg']) ?> </p>
            <?php elseif (isset($_GET['error'])): ?>
                <p class="message" style="color: red;"> <?= htmlspecialchars($_GET['error']) ?> </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="content">
        <h2>📑 Saved Documents</h2>
        <section class="docs-table">
            <table>
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Uploaded At  / Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = ? ORDER BY uploaded_at DESC");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($doc = $result->fetch_assoc()):
                    $id = (int)$doc['id'];
                    $title = htmlspecialchars($doc['title']);
                    $uploaded_at = htmlspecialchars($doc['uploaded_at']);
                    $sizeFormatted = formatSize($doc['size']);
                ?>
                    <tr>
                        <td><?= $title ?></td>
                        <td><?= $uploaded_at ?> / <?= $sizeFormatted ?></td>
                        <td>
                            <a class='btn-view' href='view.php?id=<?= $id ?>'>View</a>
                            <a class='btn-edit' href='edit.php?id=<?= $id ?>'>Edit</a>
                            <a class='btn-download' href='confirm_download.php?id=<?= $id ?>'>Download</a>
                            <a class='btn-delete' href='delete.php?id=<?= $id ?>' onclick="return confirm('Do you really want to delete this file?');">Delete</a>
                            <a class='btn-desc' href='description.php?id=<?= $id ?>'>Description</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>
</div>

<script>
function toggleDropdown() {
    var menu = document.getElementById("menuDropdown");
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}
</script>
</body>
</html>
