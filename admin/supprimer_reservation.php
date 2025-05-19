
<?php
require_once 'connexion.php';

// Vérifier que la requête est en POST et que l'ID est présent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $id = intval($_POST['reservation_id']); // Assure que l'ID est bien un entier

    if ($id > 0) {
        // Récupérer la connexion
        $conn = getDBConnection();

        // Supprimer la réservation de la base de données
        $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$id]);

        echo "success"; // Réponse pour AJAX
        exit;
    }
}

echo "error"; // Réponse en cas d'échec
exit;
