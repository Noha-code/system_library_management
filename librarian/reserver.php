<?php
require_once 'connexion.php';
$pdo = getDBConnection(); // Connexion sécurisée

session_start();

if (!isset($_GET['id'])) {
    die("ID de livre manquant.");
}

$id_livre = intval($_GET['id']);
$id_utilisateur = $_SESSION['id'] ?? 1; // Temporairement 1 si session non définie

try {
    // Vérifie si le livre existe et a du stock
    $stmt = $pdo->prepare("SELECT quantite FROM livres WHERE id_livre = ?");
    $stmt->execute([$id_livre]);
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livre) {
        die("Livre introuvable.");
    }

    if ($livre['quantite'] <= 0) {
        echo "<script>alert('Ce livre est actuellement indisponible.'); window.location.href = '../librarian/liste_livres.php';</script>";
        exit;
    }

    // Vérifier s’il a déjà réservé ce livre
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE utilisateur_id = ? AND livre_id = ?");
    $stmt->execute([$id_utilisateur, $id_livre]);
    $dejaReserve = $stmt->fetchColumn();

    if ($dejaReserve > 0) {
        header("Location: ../librarian/liste_livres.php?error=deja_reserve");
        exit;
    }

    // Vérifier s’il a déjà 2 réservations
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE utilisateur_id = ?");
    $stmt->execute([$id_utilisateur]);
    $reservationsEnCours = $stmt->fetchColumn();

    if ($reservationsEnCours >= 2) {
        header("Location: ../librarian/liste_livres.php?error=max_reservations");
        exit;
    }

    // **Sécurisation avec une transaction**
    $pdo->beginTransaction();

    // Insère la réservation
    $stmt = $pdo->prepare("INSERT INTO reservations (utilisateur_id, livre_id, date_reservation, statut) VALUES (?, ?, NOW(), 'en attente')");
    $stmt->execute([$id_utilisateur, $id_livre]);

    // **Assurer que la mise à jour du stock se fait une seule fois**
    $stmt = $pdo->prepare("UPDATE livres SET quantite = quantite - 1 WHERE id_livre = ? AND quantite > 0");
    $stmt->execute([$id_livre]);

    // Commit la transaction si tout est bien passé
    $pdo->commit();

    // Redirection avec message
    echo "<script>alert('✅ Réservation effectuée avec succès !'); window.location.href = 'user_liste_livres.php';</script>";
} catch (PDOException $e) {
    // Vérifie si une transaction est active avant de tenter un rollBack
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("❌ Erreur : " . htmlspecialchars($e->getMessage()));
}

exit;
