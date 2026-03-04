<?php
session_start();
require_once 'db.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Count total users
$total_users_query = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_users = $total_users_query->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - SafeDocs</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { background: #f9fbfd; font-family: 'Segoe UI', sans-serif; }

    /* Top Navbar */
    .navbar {
        background: #0d47a1;
        color: #fff;
        padding: 12px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .navbar h4 { margin: 0; font-weight: bold; color: #fff; }
    .navbar .logout-btn {
        background: #e74c3c;
        border: none;
        padding: 6px 15px;
        color: #fff;
        border-radius: 5px;
        font-weight: bold;
    }

    /* Sidebar */
    .sidebar {
        background: #fafafa;
        padding: 20px 10px;
        min-height: calc(100vh - 20px);
        box-shadow: 2px 0px 8px rgba(0,0,0,0.1);
        font-size: 15px;
    }
    .sidebar h5 {
        margin-bottom: 20px;
        font-weight: 600;
    }
    .sidebar .nav-link {
        color: #333;
        padding: 10px;
        border-radius: 6px;
        transition: background 0.3s;
        font-weight: 500;
    }
    .sidebar .nav-link:hover {
        background: #1976d2;
        color: #fff;
    }
    

    /* Main content */
    .main-content {
        background: #fff;
        padding: 40px;
        border-radius: 10px;
        margin: 20px;
        box-shadow: 0px 3px 8px rgba(0,0,0,0.1);
        min-height: 400px;
        text-align: center;
        color: #ef0e0e;
        font-size: 20px;
    }
  </style>
</head>
<body>
    <!-- Top Navbar -->
    <div class="navbar">
        <h4>📂 SafeDocs Admin Dashboard</h4>
        <a href="admin_logout.php"><button class="logout-btn">Logout</button></a>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <h5>__Admin Panel__</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">👥 Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback_view.php">💬 Feedbacks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="help_mail.php">📧 Help Mail</a>
                    </li>
                </ul>

                
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="main-content">
                    <p>Welcome to the SafeDocs Admin Dashboard</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
