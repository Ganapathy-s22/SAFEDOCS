<?php
session_start();
require_once 'db.php';

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    die("Invalid request.");
}

$user_id = intval($_GET['user_id']);

// ✅ Fetch user info
$userResult = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$userResult->bind_param("i", $user_id);
$userResult->execute();
$user = $userResult->get_result()->fetch_assoc();

// ✅ Fetch user files (with timestamps)
$files = [];
$sql = $conn->prepare("SELECT title, description, size, uploaded_at, deleted_at 
                       FROM documents 
                       WHERE user_id = ? 
                       ORDER BY uploaded_at DESC");
$sql->bind_param("i", $user_id);
$sql->execute();
$result = $sql->get_result();
$totalSize = 0;
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
    $totalSize += $row['size']; // Sum total size for progress
}

// ✅ Function to format size
function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    if ($bytes > 1) return $bytes . ' bytes';
    if ($bytes == 1) return "1 byte";
    return "0 bytes";
}

// ✅ Calculate storage percentage (max 100 MB)
$maxStorage = 100 * 1024 * 1024; // 100 MB in bytes
$storagePercent = min(100, ($totalSize / $maxStorage) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Files - <?php echo htmlspecialchars($user['name']); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
      body {
          background-color: #f4f6f9;
      }
      .sidebar {
          min-height: 100vh;
          background: #0d47a1;
          color: white;
          padding-top: 20px;
      }
      .sidebar a {
          color: #ddd;
          text-decoration: none;
          display: block;
          padding: 10px 15px;
          margin: 5px 0;
          border-radius: 5px;
      }
      .sidebar a:hover {
          background:  #0d47a1;
          color: #fff;
      }
      .content {
          padding: 20px;
      }
      .navbar {
          background:  #0d47a1 !important;
      }
      .navbar .navbar-brand, .navbar .nav-link {
          color: #fff !important;
      }
      .back-btn {
          display: block;
          margin: 15px;
          padding: 8px 12px;
          background: #6c757d;
          color: white;
          border-radius: 5px;
          text-decoration: none;
          text-align: center;
      }
      .back-btn:hover {
          background: #5a6268;
      }
      .progress-container {
          display: flex;
          align-items: center;
          gap: 15px;
          margin-top: 15px;
      }
      .progress {
          flex: 1;
          height: 20px;
      }
      .progress-text {
          width: 100px;
          font-size: 0.9rem;
          font-weight: bold;
      }
  </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
            <span class="navbar-text">
                Logged in as Admin
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div>
                    <h5 class="px-3">📌 Menu</h5>
                    <a href="admin_dashboard.php">🏠 Dashboard</a>
                    <a href="manage_users.php">👥 Manage Users</a>
                    <a href="logout.php" class="text-danger">🚪 Logout</a>
                    <hr class="bg-light">
                    <!-- Back Button -->
                    <a href="manage_users.php" class="back-btn">⬅ Back to Users</a>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto content">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4 class="card-title">👤 User Info</h4>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <!-- Storage Progress -->
                            <div class="progress-container">
                                <div class="progress-text"><?php echo formatSize($totalSize); ?> / 100 MB</div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $storagePercent; ?>%;" aria-valuenow="<?php echo $storagePercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">📂 File History</h4>
                        <?php if (count($files) > 0) { ?>
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Title</th>
                                            <th>Size</th>
                                            <th>Description</th>
                                            <th>Uploaded At</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($files as $file) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($file['title']); ?></td>
                                                <td><?php echo formatSize($file['size']); ?></td>
                                                <td><?php echo htmlspecialchars($file['description']); ?></td>
                                                <td><?php echo htmlspecialchars($file['uploaded_at']); ?></td>
                                                <td>
                                                    <?php 
                                                        echo $file['deleted_at'] 
                                                            ? '<span class="badge bg-danger">Deleted: ' . htmlspecialchars($file['deleted_at']) . '</span>' 
                                                            : '<span class="badge bg-success">Active</span>'; 
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <p class="text-muted">No file activity found for this user.</p>
                        <?php } ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
