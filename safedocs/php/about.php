<?php
// about.php - SafeDocs About Page
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - SafeDocs</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #333;
        }
        header {
            background: #007bff;
            color: #fff;
            padding: 20px 10%;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        header h1 {
            margin: 0;
            font-size: 2rem;
        }
        /* Back to Dashboard Button */
        .back-btn {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: #ffc107;
            color: #000;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: bold;
        }
        .back-btn:hover {
            background: #e0a800;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #007bff;
            margin-top: 0;
        }
        p {
            line-height: 1.6;
            margin-bottom: 15px;
        }
        ul {
            list-style: none;
            padding-left: 0;
        }
        ul li {
            background: url('https://img.icons8.com/ios-filled/15/007bff/checkmark.png') no-repeat left center;
            padding-left: 25px;
            margin-bottom: 10px;
        }
        footer {
            background: #343a40;
            color: #ccc;
            text-align: center;
            padding: 15px 10px;
            margin-top: 40px;
            font-size: 0.9rem;
        }
        @media(max-width: 768px) {
            header, .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<header>
    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
    <h1>About SafeDocs</h1>
</header>

<div class="container">
    <h2>Our Vision</h2>
    <p>SafeDocs is a secure, user-friendly document vault system designed to store, manage, and retrieve digital documents effortlessly. 
       Our goal is to provide individuals and organizations with a reliable platform to keep their sensitive files safe while ensuring easy accessibility.</p>

    <h2>Key Features</h2>
    <ul>
        <li>Secure storage with encrypted file handling</li>
        <li>User authentication with session-based access</li>
        <li>Easy file upload, preview, and download</li>
        <li>Automatic timestamp logging for uploads and deletions</li>
        <li>Admin access to monitor storage usage and user activity</li>
        <li>Responsive and intuitive user interface</li>
    </ul>

    <h2>Why Choose SafeDocs?</h2>
    <p>With the rise in digital data breaches, SafeDocs offers a robust security layer for your important files. 
       We prioritize privacy, reliability, and user experience, making it an ideal solution for personal and professional document management.</p>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> SafeDocs. All Rights Reserved.
</footer>

</body>
</html>
