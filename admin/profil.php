<?php
require_once '../auth.php';
authorize('admin');
error_reporting(E_ALL & ~E_NOTICE); // Affiche toutes les erreurs sauf les notices
ini_set('display_errors', 1);
?>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$host = 'localhost';
$db = 'library';
$user = 'root';
$password = ''; // Default for XAMPP

$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Get user information
$sql_user = "SELECT username, email, first_name, last_name, address, phone, created_at, last_login, status 
             FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile - Library</title>
    <link rel="stylesheet" href="style1.css">
    <style>
        .profile-container {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .profile-info {
            flex: 1;
            background-color: #f5f5f5;
            padding: 1.5rem;
            border-radius: 8px;
        }
        .borrow-history {
            flex: 2;
        }
        .book-item {
            border-bottom: 1px solid #eee;
            padding: 0.8rem 0;
        }
        .book-status {
            font-size: 0.9rem;
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-left: 0.5rem;
        }
        .active {
            background-color: #d4edda;
            color: #155724;
        }
        .returned {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
        .reserved {
            background-color: #fff3cd;
            color: #856404;
        }
        .tab-container {
            margin: 1rem 0;
        }
        .tab-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .tab-button {
            padding: 0.5rem 1rem;
            background-color: #e9ecef;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .tab-button.active {
            background-color: #007bff;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php" class="btn">Home</a></li>
                <li><a href="contact.php" class="btn">Messages</a></li>
                <li><a href="logout.php" class="btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <h1>Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
    </section>

    <div class="container">
        <div class="profile-container">
            <div class="profile-info">
                <h2>Profile Information</h2>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                <?php if ($user['address']): ?>
                    <p><strong>Address:</strong> <?= htmlspecialchars($user['address']) ?></p>
                <?php endif; ?>
                <?php if ($user['phone']): ?>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                <?php endif; ?>
                <p><strong>Member since:</strong> <?= date('F d, Y', strtotime($user['created_at'])) ?></p>
                <p><strong>Account status:</strong> 
                    <span class="book-status <?= strtolower($user['status']) ?>">
                        <?= ucfirst(htmlspecialchars($user['status'])) ?>
                    </span>
                </p>
                <?php if ($user['last_login']): ?>
                    <p><strong>Last login:</strong> <?= date('F d, Y H:i', strtotime($user['last_login'])) ?></p>
                <?php endif; ?>
                <p><a href="edit_profile.php" class="btn">Edit Profile</a></p>
                <form method="POST" action="delete_acc.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
                     <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                     <button type="submit" class="btn btn-danger">Delete your account</button>
                </form>
                
            
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Online Library System</p>
    </footer>

    <script>
        function openTab(evt, tabName) {
            // Hide all tab content
            var tabcontent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }

            // Remove "active" class from all tab buttons
            var tabbuttons = document.getElementsByClassName("tab-button");
            for (var i = 0; i < tabbuttons.length; i++) {
                tabbuttons[i].classList.remove("active");
            }

            // Show the specific tab content and add "active" class to the button
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>