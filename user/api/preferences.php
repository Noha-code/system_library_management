<?php
// Protection contre l'accès direct
define('IN_APP', true);

// Charger la configuration
require_once '../config.php';

// Démarrer la session pour l'authentification
session_start();

// Dans un système réel, vous devriez vérifier l'authentification
// Pour l'instant, on récupère l'ID utilisateur de la requête GET
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1;

// Préparer la réponse JSON
header('Content-Type: application/json');

try {
    // Créer une connexion PDO
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], $db_config['options']);
    
    // Récupérer les préférences de l'utilisateur
    $stmt = $pdo->prepare("SELECT preference_type, preference_value FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $preferences = $stmt->fetchAll();
    
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
    
    // Répondre avec les préférences
    echo json_encode($result);
    
} catch (Exception $e) {
    // Gérer les erreurs
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la récupération des préférences: ' . $e->getMessage()]);
}
?>