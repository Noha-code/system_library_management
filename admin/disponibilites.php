<?php
require_once 'connexion.php'; 
$pdo = getDBConnection(); // Connexion sécurisée

try {
    $stmt = $pdo->query("SELECT id_livre, titre, quantite FROM livres");
    $livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📚 Disponibilité des livres</title>
    <link rel="stylesheet" href="style7.css">
</head>
<body>
    <div class="container">
        <h2>📊 Disponibilité des livres</h2>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Titre</th>
                        <th>Quantité empruntée</th>
                        <th>Quantité réservée</th>
                        <th>Quantité disponible</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livres as $livre): 
                        $id = $livre['id_livre'];
                        $titre = htmlspecialchars($livre['titre']);
                        $quantite_disponible = intval($livre['quantite']); // ✅ Correspond à la colonne `quantite` dans la BDD

                        // 📌 Emprunts en cours
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprunts WHERE livre_id = ? AND date_retour IS NULL");
                        $stmt->execute([$id]);
                        $quantite_empruntee = $stmt->fetchColumn();

                        // 📌 Réservations valides (sans celles expirées ou annulées)
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE livre_id = ? AND statut NOT IN ('expirée', 'annulée')");

                        $stmt->execute([$id]);
                        $quantite_reservee = $stmt->fetchColumn();
                    ?>
                    <tr>
                        <td><?= $titre ?></td>
                        <td class="text-center"><?= $quantite_empruntee ?></td>
                        <td class="text-center"><?= $quantite_reservee ?></td>
                        <td class="text-center font-weight-bold <?= $quantite_disponible <= 0 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $quantite_disponible ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination text-center">
            <button onclick="window.location.href='index.php'" class="btn-home">🏠 Retour à la page d'accueil</button>
        </div>
    </div>
</body>
</html>
