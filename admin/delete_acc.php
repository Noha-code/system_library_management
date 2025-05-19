<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Vérifie si c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profil.php');
    exit;
}

// Vérifie le token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: profil.php?error=csrf');
    exit;
}

// Invalide le token après utilisation
unset($_SESSION['csrf_token']);

// Connexion à la base de données
$host = 'localhost';
$db = 'library';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die('Erreur connexion: ' . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Supprime l'utilisateur
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    // Déconnexion et destruction de session
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    // Redirection avec message
    header('Location: register.php?message=account_deleted');
    exit;
} else {
    header('Location: profil.php?error=delete_failed');
    exit;
}
?>
