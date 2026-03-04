<?php
session_start();
require_once 'db.php';

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Fetch all feedback from DB
$feedbacks = [];
$sql = "SELECT username, gmail, rating, feedback, created_at 
        FROM feedback 
        ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - User Feedbacks</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { background: #f9fbfd; font-family: 'Segoe UI', sans-serif; }
    .navbar { background: #0d47a1; color: #fff; padding: 12px 20px; }
    .navbar h4 { margin: 0; font-weight: bold; color: #fff; }
    .logout-btn {
        background: #e74c3c; border: none; padding: 6px 15px;
        color: #fff; border-radius: 6px; font-weight: bold;
    }
    .sidebar {
        background: #fafafa;
        padding: 20px;
        min-height: 100vh;
        box-shadow: 2px 0px 8px rgba(0,0,0,0.1);
    }
    .sidebar .nav-link { color: #333; padding: 10px; border-radius: 6px; }
    .sidebar .nav-link:hover, 
    .sidebar .nav-link.active { background: #1976d2; color: #fff; }
    .main-content {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        margin: 20px;
        box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
    }
    .table thead { background: #1976d2; color: #fff; }
  </style>
</head>
<body>
    <div class="navbar d-flex justify-content-between align-items-center">
        <h4>📂 SafeDocs Admin</h4>
        <a href="admin_logout.php"><button class="logout-btn">Logout</button></a>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <h5>👨‍💼 Admin Panel</h5>
                <ul class="nav flex-column mt-3">
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">👥 Users</a></li>
                    <li class="nav-item"><a class="nav-link active" href="feedback_view.php">💬 Feedbacks</a></li>
                    <li class="nav-item"><a class="nav-link" href="help_mail.php">📧 Help Mail</a></li>
                </ul>
            </div>

            <div class="col-md-10">
                <div class="main-content">
                    <h3 class="mb-4">💬 User Feedbacks</h3>
                    <?php if (count($feedbacks) > 0) { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Rating</th>
                                        <th>Feedback</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feedbacks as $fb) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($fb['username']); ?></td>
                                            <td><?php echo htmlspecialchars($fb['gmail']); ?></td>
                                            <td><?php echo str_repeat("⭐", (int)$fb['rating']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($fb['feedback'])); ?></td>
                                            <td><?php echo date("d M Y, h:i A", strtotime($fb['created_at'])); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <p class="text-muted">No feedback available yet.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
