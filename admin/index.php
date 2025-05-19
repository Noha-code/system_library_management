<?php
require_once '../auth.php';
authorize('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                
                <li><a href="admin_accounts.php">Manage accounts</a></li>
                <li><a href="liste_livres.php">Manage books</a></li>
                <li><a href="reserve.php">Manage reservations</a></li>
                <li><a href="profil.php">Settings</a></li>
                <li><a href="logout.php" class="btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <h1>Welcome Admin</h1>
        <p>Manage the library system efficiently from your dashboard.</p>
    </section>

    

    <footer>
        <p>&copy; 2025 Online Library System</p>
    </footer>
</body>
</html>
