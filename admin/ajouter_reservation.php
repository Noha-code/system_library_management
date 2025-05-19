<?php
include 'connexion.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utilisateur_id = intval($_POST['utilisateur_id']);
    $livre_id = intval($_POST['livre_id']);
    $date = date('Y-m-d');

    // Vérifier s'il reste des exemplaires disponibles
    $check = "
        SELECT COUNT(*) AS total FROM (
            SELECT id FROM emprunts WHERE livre_id = $livre_id AND date_retour IS NULL
            UNION ALL
            SELECT id FROM reservations WHERE livre_id = $livre_id AND statut = 'active'
        ) AS total_occupé
    ";
    $result = $conn->query($check);
    $data = $result->fetch_assoc();

    if ($data['total'] < 5) {
        $stmt = $conn->prepare("INSERT INTO reservations (utilisateur_id, livre_id, date_reservation, statut) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("iis", $utilisateur_id, $livre_id, $date);
        $stmt->execute();
        $message = "<div class='alert success'>✅ Réservation enregistrée.</div>";
    } else {
        $message = "<div class='alert'>❌ Aucune copie disponible pour ce livre.</div>";
    }
}

// Récupérer les utilisateurs et les livres
$utilisateurs = $conn->query("SELECT id, username FROM users");
$livres = $conn->query("SELECT id_livre, titre FROM livres");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📌 Ajouter une réservation</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <div class="books">
        <h2>📌 Ajouter une réservation</h2>

        <?php if ($message): ?>
            <?= $message ?>
        <?php endif; ?>

        <form method="post" class="contact-form">
            <div>
                <label>Utilisateur :</label>
                <select name="utilisateur_id" required>
                    <?php while ($u = $utilisateurs->fetch_assoc()): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div>
                <label>Livre :</label>
                <select name="livre_id" required>
                    <?php while ($l = $livres->fetch_assoc()): ?>
                        <option value="<?= $l['id_livre'] ?>"><?= htmlspecialchars($l['titre']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn">📚 Réserver</button>
        </form>
        
        <div class="pagination">
            <a href="index.php" class="page-link">🏠 Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
