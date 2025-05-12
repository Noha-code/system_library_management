<?php
require_once 'connexion.php'; 
$pdo = getDBConnection(); // Connexion sécurisée

// 📌 Récupérer le nombre total d'emprunts
$stmt = $pdo->query("SELECT COUNT(*) FROM emprunts");
$total_emprunts = $stmt->fetchColumn();

// 📌 Récupérer le nombre total de retours (livres rendus)
$stmt = $pdo->query("SELECT COUNT(*) FROM emprunts WHERE date_retour IS NOT NULL");
$total_retours = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📚 Statistiques des emprunts</title>
    <link rel="stylesheet" href="style8.css">
</head>
<body>
    <div class="container">
        <h2>📊 Statistiques des emprunts</h2>

        <div class="stats-box">
            <p>📖 **Total d'emprunts :** <?= $total_emprunts ?></p>
            <p>🔄 **Total de retours :** <?= $total_retours ?></p>
        </div>

        <div class="pagination text-center">
            <button onclick="window.location.href='index.php'" class="btn-home">🏠 Retour à l'accueil</button>
        </div>
    </div>
</body>
</html>
