<?php
require_once '../auth.php';
authorize('librarian');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
   
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="liste_livres.php">Manage Books</a></li>
                <li><a href="reserve.php">Manage Reservations</a></li>
                <li><a href="Profil.php">Profile</a></li>
                <li><a href="logout.php" class="btn">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <h1>Welcome Librarian</h1>
        <p>Efficiently manage loans and reservations from your dashboard.</p>
    </section>

    

    <footer>
        <p>&copy; 2025 Library Management System</p>
    </footer>
</body>
</html>

   

   