<?php
// Connexion à la base de données avec PDO
$host = "localhost";
$user = "root";
$password = "";
$db = "library";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

// Vérifier si l'ID est fourni
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // Préparer la requête de suppression
        $stmt = $pdo->prepare("DELETE FROM livres WHERE id_livre = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Rediriger vers la page de liste avec un message de succès
        header("Location: liste_livres.php?deleted=success");
        exit();
    } catch(PDOException $e) {
        // En cas d'erreur, rediriger avec un message d'erreur
        header("Location: liste_livres.php?error=delete");
        exit();
    }
} else {
    // Si aucun ID n'est fourni, rediriger vers la liste
    header("Location: liste_livres.php");
    exit();
}
?>
