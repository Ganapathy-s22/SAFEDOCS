<?php
session_start();
require_once 'db.php';

// ✅ Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Get total users count
$totalUsers = 0;
$countQuery = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($countQuery && $countQuery->num_rows > 0) {
    $totalUsers = $countQuery->fetch_assoc()['total'];
}

// ✅ Fetch all users with storage usage
$users = [];
$userQuery = $conn->query("SELECT id, name, email FROM users ORDER BY created_at asc");

if ($userQuery && $userQuery->num_rows > 0) {
    while ($row = $userQuery->fetch_assoc()) {
        // Calculate storage used by each user
        $sizeStmt = $conn->prepare("SELECT SUM(size) AS total_size FROM documents WHERE user_id = ?");
        $sizeStmt->bind_param("i", $row['id']);
        $sizeStmt->execute();
        $sizeResult = $sizeStmt->get_result()->fetch_assoc();
        $totalSize = $sizeResult['total_size'] ?? 0;

        $row['storage_used'] = $totalSize;
        $users[] = $row;
    }
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Manage Users</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { background: #f9fbfd; font-family: 'Segoe UI', sans-serif; }
    .navbar { background: #0d47a1; color: #fff; padding: 12px 20px; }
    .navbar h4 { margin: 0; font-weight: bold; }
    .logout-btn {
        background: #e74c3c; border: none; padding: 6px 15px;
        color: #fff; border-radius: 6px; font-weight: bold;
    }
    .sidebar {
        background: #fafafa; padding: 20px; min-height: 100vh;
        box-shadow: 2px 0px 8px rgba(0,0,0,0.1);
    }
    .sidebar .nav-link { color: #333333ff; padding: 10px; border-radius: 6px; }
    .sidebar .nav-link:hover, 
    .sidebar .nav-link.active { background: #1976d2; color: #fff; }
    .main-content {
        background: #fff; padding: 25px; border-radius: 10px;
        margin: 20px; box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
    }
    .table thead { background: #1976d2; color: #fff; }
    .user-count {
        font-size: 18px; font-weight: bold; background: #1976d2;
        color: #fff; padding: 8px 15px; border-radius: 8px;
    }
  </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar d-flex justify-content-between align-items-center">
        <h4>📂 SafeDocs Admin</h4>
        <a href="admin_logout.php"><button class="logout-btn">Logout</button></a>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h5>👨‍💼 Admin Panel</h5>
                <ul class="nav flex-column mt-3">
                    <li class="nav-item"><a class="nav-link active" href="manage_users.php">👥 Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="feedback_view.php">💬 Feedbacks</a></li>
                    <li class="nav-item"><a class="nav-link" href="help_mail.php">📧 Help Mail</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="main-content">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>👥 Manage Users</h3>
                        <span class="user-count">Total Users: <?php echo $totalUsers; ?></span>
                    </div>

                    <?php if (!empty($users)) { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Storage Used</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user) { ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo formatSize($user['storage_used']) . " / 100 MB"; ?></td>
                                            <td>
                                                <a href="stored_files_list.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                    📂 Stored Files List
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <p class="text-muted">No users registered yet.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
