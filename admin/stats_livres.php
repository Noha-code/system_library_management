<?php
require_once 'connexion.php'; 
$pdo = getDBConnection(); // Connexion sécurisée

// 📌 Récupérer les livres les plus empruntés
$stmt = $pdo->query("
    SELECT l.titre, COUNT(e.id) AS total_emprunts 
    FROM emprunts e 
    INNER JOIN livres l ON e.livre_id = l.id_livre 
    GROUP BY l.id_livre, l.titre 
    ORDER BY total_emprunts DESC 
    LIMIT 10
");
$livres_populaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📚 Livres les plus empruntés</title>
    <link rel="stylesheet" href="style8.css">
</head>
<body>
    <div class="container">
        <h2>📊 Top 10 des livres les plus empruntés</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Titre du livre</th>
                    <th>Nombre d'emprunts</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($livres_populaires as $livre): ?>
                <tr>
                    <td><?= htmlspecialchars($livre['titre']) ?></td>
                    <td class="text-center"><?= $livre['total_emprunts'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination text-center">
            <button onclick="window.location.href='index.php'" class="btn-home">🏠 Retour à l'accueil</button>
        </div>
    </div>
</body>
</html>
