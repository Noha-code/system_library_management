<?php
require_once 'connexion.php';

// Connexion sécurisée
$pdo = getDBConnection();

// Requête avec noms de colonnes corrects
$sql = "SELECT 
            r.id, 
            r.date_reservation, 
            r.statut,
            u.username, 
            l.titre 
        FROM reservations r
        JOIN users u ON r.utilisateur_id = u.id
        JOIN livres l ON r.livre_id = l.id_livre
        ORDER BY r.date_reservation DESC";

$stmt = $pdo->query($sql);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📑 Liste des Réservations</title>
    <link rel="stylesheet" href="style2.css">
    <script>
        function supprimerReservation(btn, id) {
            if (!confirm("❗ Confirmer la suppression de cette réservation ?")) return;

            fetch('supprimer_reservation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'reservation_id=' + encodeURIComponent(id)
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === 'success') {
                    btn.closest("tr").remove();
                    alert("✅ Réservation supprimée avec succès.");
                } else {
                    alert("❌ Échec de la suppression.");
                }
            })
            .catch(error => {
                alert("Erreur réseau : " + error);
            });
        }
    </script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-primary">📑 Liste des Réservations</h2>

        <?php if (!empty($reservations)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Livre</th>
                            <th>Date de réservation</th>
                            <th>État</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td><?= htmlspecialchars($res['id']) ?></td>
                                <td><?= htmlspecialchars($res['username']) ?></td>
                                <td><?= htmlspecialchars($res['titre']) ?></td>
                                <td><?= (new DateTime($res['date_reservation']))->format('d/m/Y H:i') ?></td>
                                <td>
                                    <span class="badge <?= strtolower($res['statut']) === 'expirée' ? 'bg-danger' : 'bg-success' ?>">
                                        <?= htmlspecialchars($res['statut']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="supprimerReservation(this, <?= $res['id'] ?>)">🗑️ Supprimer</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune réservation trouvée.</p>
        <?php endif; ?>
    </div>

    <div class="pagination text-center mt-4">
        <button onclick="window.location.href='index.php'" class="btn-home">🏠 Retour à la page d'accueil</button>
    </div>
</body>
</html>
