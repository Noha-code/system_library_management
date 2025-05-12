<?php
session_start();
include('db.php');

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])) {
    // Récupérer les données du formulaire
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validation de base
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom est requis";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }
    
    if (empty($message)) {
        $errors[] = "Le message est requis";
    }
    
    // Si pas d'erreurs, enregistrer le message
    if (empty($errors)) {
        // Préparer la requête SQL
        $query = "INSERT INTO messages (nom, email, message, date_envoi) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $nom, $email, $message);
        
        // Exécuter la requête
        if ($stmt->execute()) {
            // Message de succès
            $_SESSION['contact_success'] = true;
        } else {
            // Erreur lors de l'enregistrement
            $_SESSION['contact_error'] = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
    } else {
        // Stocker les erreurs dans la session
        $_SESSION['contact_errors'] = $errors;
    }
}

// Rediriger vers la page d'accueil
header("Location: index.php#aide");
exit();
?>