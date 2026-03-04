<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to SafeDocs</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #7db5deff, #b29df2ff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Admin dropdown */
        .admin-dropdown {
            position: absolute;
            top: 20px;
            right: 30px;
        }

        .dropdown-btn {
            background-color: #ea6c49ff;
            color: black;
            font-weight: bold;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .dropdown-btn:hover {
            background-color: #0d2764;
            color: white;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 45px;
            right: 0;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            min-width: 180px;
            z-index: 1;
        }

        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-weight: bold;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .show {
            display: block;
        }

        /* Welcome Box */
        .welcome-box {
            text-align: center;
            background: palegoldenrod;
            padding: 60px 40px;
            box-shadow: 0px 10px 30px rgba(0,0,0,0.1);
            border-radius: 12px;
        }

        .welcome-box h1 {
            color: #1f3c88;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .welcome-box p {
            font-size: 18px;
            color: orangered;
            margin-bottom: 30px;
        }

        .buttons a {
            display: inline-block;
            padding: 12px 25px;
            margin: 10px;
            background-color: #45fa03ff;
            color: black;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            transition: background 0.3s;
        }

        .buttons a:hover {
            background-color: #0d2764;
            color: white;
        }
    </style>
</head>
<body>

    <!-- Admin Dropdown -->
    <div class="admin-dropdown">
        <button class="dropdown-btn" onclick="toggleDropdown()">☰ Admin</button>
        <div id="dropdownMenu" class="dropdown-content">
            <!-- Redirect to admin pages -->
            <a href="admin_register.php">Admin Register</a>
            <a href="admin_login.php">Admin Login</a>
        </div>
    </div>

    <!-- Welcome Box -->
    <div class="welcome-box">
        <h1>Welcome to SafeDocs</h1>
        <p>Your Personal Document Wallet</p>

        <div class="buttons">
            <a href="login.php">Login</a>
            <a href="register.php">New User</a>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            document.getElementById("dropdownMenu").classList.toggle("show");
        }

        // Close the dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown-btn')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        };
    </script>

</body>
</html>
