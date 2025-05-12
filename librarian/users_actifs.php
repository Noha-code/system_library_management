<?php
require_once '../auth.php';
authorize('librarian');
error_reporting(E_ALL & ~E_NOTICE); // Affiche toutes les erreurs sauf les notices
ini_set('display_errors', 1);
?>
<?php
require_once 'connexion.php'; 
$pdo = getDBConnection(); // Connexion sécurisée

// 📌 Récupérer les utilisateurs les plus actifs
$stmt = $pdo->query("
    SELECT u.username, COUNT(e.id) AS total_emprunts 
    FROM emprunts e 
    INNER JOIN users u ON e.utilisateur_id = u.id 
    GROUP BY u.id, u.username 
    ORDER BY total_emprunts DESC 
    LIMIT 10
");
$users_actifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📈 Utilisateurs les plus actifs</title>
    <link rel="stylesheet" href="style8.css">
</head>
<body>
    <div class="container">
        <h2>📊 Top 10 des utilisateurs les plus actifs</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Nom d'utilisateur</th>
                    <th>Nombre d'emprunts</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users_actifs as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td class="text-center"><?= $user['total_emprunts'] ?></td>
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
