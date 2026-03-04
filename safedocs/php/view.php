<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Calculate storage usage
$stmt = $conn->prepare("SELECT SUM(size) AS total_used FROM documents WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalUsedBytes = $row['total_used'] ?? 0;

$maxStorageBytes = 100 * 1024 * 1024;
$usedMB = round($totalUsedBytes / (1024 * 1024), 2);
$usedKB = round($totalUsedBytes / 1024, 2);
$percentageUsed = min(100, round(($totalUsedBytes / $maxStorageBytes) * 100));

$storageClass = 'success';
if ($percentageUsed > 90) $storageClass = 'danger';
elseif ($percentageUsed > 70) $storageClass = 'warning';

// Handle viewer mode
$viewing = false;
$view_title = $view_type = $view_uploaded = $view_content = '';
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $docId = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $docId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $doc = $result->fetch_assoc();
    if ($doc) {
        $viewing = true;
        $view_title = $doc['title'] ?: 'Untitled Document';
        $view_type = $doc['file_type'] ?? 'Unknown';
        $view_uploaded = $doc['uploaded_at'] ?? '';
        $view_content = $doc['file_data'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - SafeDocs</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background-color: #f3f6fd; }
        header {
            background-color: #0033a0; color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center; position: relative;
        }
        .storage-container { position: absolute; left: 50%; transform: translateX(-50%); text-align: center; }
        .storage-bar { width: 250px; height: 14px; background-color: #ddd; border-radius: 7px; overflow: hidden; margin-bottom: 5px; }
        .storage-fill { height: 100%; transition: width 0.4s ease-in-out; }
        .storage-fill.success { background-color: #28a745; }
        .storage-fill.warning { background-color: #ffc107; }
        .storage-fill.danger { background-color: #dc3545; }
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
        .upload-section input, .upload-section button {
            width: 80%; padding: 8px; margin-bottom: 10px;
        }
        .upload-section button {
            background-color: #0033a0; color: white; border: none;
            border-radius: 5px; font-weight: bold; cursor: pointer;
        }
        .upload-section button:hover { background-color: #002275; }

        .content { width: <?= $viewing ? '50%' : '75%' ?>; padding: 30px; overflow-y: auto; }
        .docs-table table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .docs-table th, .docs-table td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        .btn-view, .btn-download, .btn-delete, .btn-edit {
            margin-right: 6px; padding: 6px 10px; border-radius: 4px;
            font-size: 13px; text-decoration: none; font-weight: bold;
        }
        .btn-view { background-color: #28a745; color: white; }
        .btn-download { background-color: #007bff; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-edit { background-color: #ffc107; color: black; }
        .btn-cancel { background-color: #f91515ff; color: white; }

        .message { margin-top: 15px; font-weight: bold; }

        .viewer-pane {
            width: 25%; background-color: #ffffff; border-left: 1px solid #ddd;
            padding: 20px; overflow-y: auto; position: relative;
        }
        .viewer-pane h2 { font-size: 20px; margin-bottom: 10px; }
        .viewer-pane .info { font-size: 13px; color: #333; margin-bottom: 10px; }
        .viewer-pane .doc-box {
            background: #f9f9f9; padding: 10px; border-radius: 5px;
            font-size: 13px; white-space: pre-wrap; border: 1px solid #ddd;
            max-height: 65vh; overflow-y: auto;
        }
        .close-btn {
            position: absolute; top: 10px; right: 10px;
            padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 13px;
        }
    </style>
</head>
<body>
<header>
    <h1>📂 SafeDocs</h1>

    <div class="storage-container" title="<?= $percentageUsed ?>% used">
        <div class="storage-bar">
            <div class="storage-fill <?= $storageClass ?>" style="width: <?= $percentageUsed ?>%;"></div>
        </div>
        <div class="storage-text">
            <?= $usedKB ?> KB (<?= $usedMB ?> MB) / 100 MB used
        </div>
    </div>

    <div class="logout">
        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>
</header>

<div class="main-container">
    <div class="sidebar">
        <h2>👋 Welcome</h2>
        <strong><?= htmlspecialchars($userName) ?></strong>
        <hr>
        <div class="upload-section">
            <h3>📤 Add Document</h3>
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Enter file title (optional)" />
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
                        <th>Uploaded At</th>
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
                ?>
                    <tr>
                        <td><?= $title ?></td>
                        <td><?= $uploaded_at ?></td>
                        <td>
                            <a class='btn-view' href='dashboard.php?id=<?= $id ?>'>View</a>
                            <a class='btn-edit' href='edit.php?id=<?= $id ?>'>Edit</a>
                            <a class='btn-download' href='confirm_download.php?id=<?= $id ?>'>Download</a>
                            <a class='btn-delete' href='delete.php?id=<?= $id ?>' onclick="return confirm('Do you really want to delete this file?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>

    <?php if ($viewing): ?>
    <div class="viewer-pane">
        <!-- ✅ Cancel button now goes back to dashboard -->
        <a href="dashboard.php" class="close-btn btn-cancel">&times; Cancel</a>
        <h2><?= htmlspecialchars($view_title) ?></h2>
        <div class="info">
            <strong>Type:</strong> <?= htmlspecialchars($view_type) ?><br>
            <strong>Uploaded:</strong> <?= htmlspecialchars($view_uploaded) ?>
        </div>
        <div class="doc-box">
            <?php
            $ext = strtolower(pathinfo($view_title, PATHINFO_EXTENSION));
            if ($ext === 'txt') {
                echo htmlspecialchars($view_content);
            } else {
                echo "This file cannot be previewed here. Please download it to view the content.";
            }
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
