<?php
require_once '../auth.php';
authorize('librarian');
?>
<?php
require_once 'connexion.php'; 

// Connexion sécurisée
$pdo = getDBConnection();

// Récupérer les emprunts avec infos utilisateur et livre
$sql = "
    SELECT e.id, u.username AS utilisateur, l.titre AS livre,
           e.date_emprunt, e.date_limite, e.date_retour
    FROM emprunts e
    JOIN users u ON e.utilisateur_id = u.id
    JOIN livres l ON e.livre_id = l.id_livre
    ORDER BY e.date_emprunt DESC
";

$emprunts = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📊 Liste des emprunts</title>
    <link rel="stylesheet" href="style4.css">
    <script>
        function retournerLivre(btn, id) {
            if (!confirm("❗ Confirmer le retour de ce livre ?")) return;

            fetch('retourner_livre.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'emprunt_id=' + encodeURIComponent(id)
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === 'success') {
                    btn.closest("tr").remove(); // Supprime l’emprunt de l’affichage
                    alert("✅ Livre retourné et supprimé de la base.");
                } else {
                    alert("❌ Échec du retour. Détails : " + result);
                }
            })
            .catch(error => {
                alert("Erreur réseau : " + error);
            });
        }
    </script>
</head>
<body>
    <div class="books">
        <h2>📋 Liste des emprunts</h2>
        
        <div class="book-list">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Livre</th>
                        <th>Date d'emprunt</th>
                        <th>Date limite</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emprunts as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['utilisateur']) ?></td>
                        <td><?= htmlspecialchars($row['livre']) ?></td>
                        <td><?= htmlspecialchars($row['date_emprunt']) ?></td>
                        <td><?= htmlspecialchars($row['date_limite']) ?></td>
                        <td>
                            <button onclick="retournerLivre(this, <?= $row['id'] ?>)">🔁 Retourner</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <a href="index.php" class="page-link">🏠 Retour à la page principale</a>
        </div>
    </div>
</body>
</html>
