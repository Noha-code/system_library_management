<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Bibliothèque en Ligne</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <nav>
        <ul>
            <li><a href="index.php"><i class="fas fa-home category-icon"></i>Home</a></li>
            <li><a href="index.php#books"><i class="fas fa-book category-icon"></i>My Books</a></li>
            <li><a href="index.php#browse"><i class="fas fa-search category-icon"></i>Browse</a></li>
            <li><a href="about.php"><i class="fas fa-info-circle category-icon"></i>About us</a></li>
            <li><a href="index.php#help"><i class="fas fa-question-circle category-icon"></i>Help</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <li><a href="liste_livres.php" class="btn btn-info"><i class="fas fa-cog"></i> Admin</a></li>
                <?php endif; ?>
                <li><a href="deconnexion.php" class="btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="connexion.php" class="btn"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="inscription.php" class="btn"><i class="fas fa-user-plus"></i> Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>h