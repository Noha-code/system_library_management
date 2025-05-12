<?php
require_once '../auth.php';
authorize('librarian');
error_reporting(E_ALL & ~E_NOTICE); // Affiche toutes les erreurs sauf les notices
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des emprunts</title>
    <link rel="stylesheet" href="style3.css">
</head>
<body>
    <div class="container">
        <h1>📚 Gestion des emprunts et réservations</h1>
        <div class="button-container">
            <a class="btn" href="emprunter.php">📖 Enregistrer un emprunt</a>
            <a class="btn" href="disponibilites.php">📚 Voir les livres disponibles</a>
            <a class="btn" href="liste_emprunts.php">📜 Liste des emprunts</a>
            <a class="btn" href="liste_reservations.php">📜 Liste des réservations</a>

            <!-- 📊 Ajout des statistiques -->
            <a class="btn btn-stats" href="stats_emprunts.php">📊 Statistiques des emprunts</a>
            <a class="btn btn-stats" href="stats_livres.php">📚 Livres les plus empruntés</a>
            <a class="btn btn-stats" href="users_actifs.php">👤 Utilisateurs les plus actifs</a>
        </div>
    </div>
</body>
</html>
