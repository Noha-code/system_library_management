<?php
require_once 'connexion.php';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emprunt_id'])) {
    $id_emprunt = intval($_POST['emprunt_id']);

    if ($id_emprunt > 0) {
        try {
            // 1️⃣ Récupérer l'ID du livre emprunté
            $stmt = $pdo->prepare("SELECT livre_id FROM emprunts WHERE id = ?");
            $stmt->execute([$id_emprunt]);
            $livre = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$livre) {
                echo "error: Livre introuvable.";
                exit;
            }

            $id_livre = $livre['livre_id'];

            // 2️⃣ Mettre à jour la quantité du livre (+1)
            $stmt = $pdo->prepare("UPDATE livres SET quantite = quantite + 1 WHERE id_livre = ?");
            $stmt->execute([$id_livre]);

            // 3️⃣ Supprimer l’emprunt après retour
            $stmt = $pdo->prepare("DELETE FROM emprunts WHERE id = ?");
            $stmt->execute([$id_emprunt]);

            echo "success"; // Réponse pour AJAX
        } catch (PDOException $e) {
            echo "error: " . $e->getMessage();
        }
    } else {
        echo "invalid_id";
    }
} else {
    echo "no_data";
}

exit;
