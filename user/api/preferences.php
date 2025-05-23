<?php
// Protection contre l'accès direct
define('IN_APP', true); 

// Charger la configuration
require_once '../config.php'; 

// Démarrer la session pour l'authentification
session_start(); 

// Vérification de l'authentification
// Récupération du user_id de manière sécurisée
// Option 1: Utiliser l'ID de l'utilisateur connecté (recommandé)
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
} 
// Option 2: Utiliser l'ID fourni en paramètre GET (si nécessaire pour certains cas d'usage)
elseif (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
} 
// Si aucun ID n'est disponible, retourner une erreur
else {
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Utilisateur non authentifié ou ID non spécifié']);
    exit;
}

// Préparer la réponse JSON
header('Content-Type: application/json');

try {
    // Créer une connexion PDO
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], $db_config['options']);
    
    // Vérifier si l'utilisateur existe dans la base de données
    $check_user = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
    $check_user->execute([$user_id]);
    if ($check_user->fetchColumn() == 0) {
        throw new Exception("Utilisateur introuvable");
    }
    
    // Récupérer les préférences de l'utilisateur
    $stmt = $pdo->prepare("SELECT preference_type, preference_value FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $preferences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organiser les préférences par type
    $result = [
        'genres' => [],
        'categories' => [],
        'authors' => [],
        'frequency' => 'rarely' // Valeur par défaut
    ];
    
    foreach ($preferences as $pref) {
        switch ($pref['preference_type']) {
            case 'genre':
                $result['genres'][] = intval($pref['preference_value']);
                break;
            case 'category':
                $result['categories'][] = intval($pref['preference_value']);
                break;
            case 'author':
                $result['authors'][] = $pref['preference_value'];
                break;
            case 'frequency':
                $result['frequency'] = $pref['preference_value'];
                break;
        }
    }
    
    // Convertir les tableaux d'auteurs en chaîne CSV pour le champ de formulaire
    $result['authors'] = !empty($result['authors']) ? implode(', ', $result['authors']) : '';
    
    // Ajouter l'ID de l'utilisateur à la réponse pour information
    $result['user_id'] = $user_id;
    
    // Répondre avec les préférences
    echo json_encode($result);
    
} catch (Exception $e) {
    // Gérer les erreurs
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des préférences: ' . $e->getMessage()]);
}
?>