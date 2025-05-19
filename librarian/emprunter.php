<?php
require_once '../auth.php';
authorize('librarian');
?>
<?php
require_once 'connexion.php'; 

$pdo = getDBConnection(); // Connexion sécurisée
$emprunt_reussi = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utilisateur_id = intval($_POST['utilisateur_id']);
    $livre_id = intval($_POST['livre_id']);

    try {
        // Vérifier si le livre est déjà emprunté
        $verif = $pdo->prepare("SELECT COUNT(*) FROM emprunts WHERE livre_id = ? AND date_retour IS NULL");
        $verif->execute([$livre_id]);
        $deja_emprunte = $verif->fetchColumn();

        if ($deja_emprunte > 0) {
            $message = "<div class='alert error'>❌ Ce livre est déjà emprunté et n'a pas encore été retourné.</div>";
        } else {
            // Enregistrer l'emprunt
            $date_emprunt = date('Y-m-d');
            $date_limite = date('Y-m-d', strtotime("+14 days"));

            $stmt = $pdo->prepare("INSERT INTO emprunts (utilisateur_id, livre_id, date_emprunt, date_limite) VALUES (?, ?, ?, ?)");
            $stmt->execute([$utilisateur_id, $livre_id, $date_emprunt, $date_limite]);

            // Diminuer la quantité du livre emprunté
            $stmt = $pdo->prepare("UPDATE livres SET quantite = quantite - 1 WHERE id_livre = ?");
            $stmt->execute([$livre_id]);

            $message = "<div class='alert success'>✅ Emprunt enregistré avec succès.</div>";
            $emprunt_reussi = true;
        }
    } catch (PDOException $e) {
        $message = "<div class='alert error'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📚 Emprunter un livre</title>
    <link rel="stylesheet" href="style1.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
    <div class="books">
        <h2>📚 Enregistrer un emprunt</h2>

        <?php if (isset($message)) echo $message; ?>

        <form method="POST" class="contact-form">
            <div>
                <label>Utilisateur :</label>
                <select name="utilisateur_id" class="select2" required>
                    <option></option>
                    <?php
                        $res = $pdo->query("SELECT id, username FROM users");
                        foreach ($res as $row) {
                            echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['username']) . "</option>";
                        }
                    ?>
                </select>
            </div>

            <div>
                <label>Livre :</label>
                <select name="livre_id" class="select2" required>
                    <option></option>
                    <?php
                        $res = $pdo->query("SELECT id_livre, titre FROM livres WHERE quantite > 0");
                        foreach ($res as $row) {
                            echo "<option value='" . htmlspecialchars($row['id_livre']) . "'>" . htmlspecialchars($row['titre']) . "</option>";
                        }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn">📖 Emprunter</button>
        </form>

        <div class="pagination">
            <a href="index.php" class="page-link">🏠 Retour à la page principale</a>
        </div>
    </div>

    <?php if ($emprunt_reussi): ?>
        <script>
            alert("✅ Le livre a été emprunté avec succès !");
        </script>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Sélectionner une option",
                width: '100%'
            });
        });
    </script>
</body>
</html>
