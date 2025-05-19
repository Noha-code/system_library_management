<?php
session_start();
include('config/database.php'); // ajuste le chemin selon ta structure

// Vérifie si l'utilisateur est connecté (exemple)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['cancel_error'] = "Vous devez être connecté pour annuler une réservation.";
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reservation_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Supprimer uniquement si la réservation appartient à l'utilisateur connecté
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ? AND utilisateur_id = ?");
    $stmt->bind_param("ii", $reservation_id, $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['cancel_success'] = "Réservation annulée avec succès.";
        } else {
            $_SESSION['cancel_error'] = "Réservation introuvable ou vous n'êtes pas autorisé à l'annuler.";
        }
    } else {
        $_SESSION['cancel_error'] = "Erreur lors de l'annulation. Veuillez réessayer.";
    }

    $stmt->close();
} else {
    $_SESSION['cancel_error'] = "ID de réservation invalide.";
}

$conn->close();

header("Location: profil.php");
exit();
?>
