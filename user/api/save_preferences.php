<?php
// Protection contre l'accès direct
define('IN_APP', true); 

// Charger la configuration
require_once '../config.php'; 

// Démarrer la session pour l'authentification
session_start(); 

// Récupération du user_id de manière sécurisée
// Option 1: Utiliser l'ID de l'utilisateur connecté (recommandé)
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
} 
// Option 2: Utiliser l'ID fourni dans les données JSON
elseif (!empty($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
}
// Si aucun ID n'est disponible, retourner une erreur
else {
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié ou ID non spécifié']);
    exit;
}

// Préparer la réponse JSON
header('Content-Type: application/json');

try {
    // Récupérer et décoder les données JSON du formulaire
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception("Données invalides");
    }
    
    // Valider les données
    $genres = isset($data['genres']) ? $data['genres'] : [];
    $categories = isset($data['categories']) ? $data['categories'] : [];
    $authors = isset($data['authors']) ? trim($data['authors']) : '';
    $frequency = isset($data['frequency']) ? $data['frequency'] : 'rarely';
    
    // Nettoyer les données
    $genres = array_map('intval', $genres); // Assurer que les IDs sont des entiers
    $categories = array_map('intval', $categories); // Assurer que les IDs sont des entiers
    $authors = explode(',', $authors); // Diviser la chaîne en un tableau
    $authors = array_map('trim', $authors); // Enlever les espaces
    $authors = array_filter($authors); // Supprimer les entrées vides
    
    // Créer une connexion PDO
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], $db_config['options']);
    
    // Vérifier si l'utilisateur existe dans la base de données
    $check_user = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
    $check_user->execute([$user_id]);
    if ($check_user->fetchColumn() == 0) {
        throw new Exception("Utilisateur introuvable");
    }
    
    // Commencer une transaction
    $pdo->beginTransaction();
    
    try {
        // Supprimer les anciennes préférences
        $stmt = $pdo->prepare("DELETE FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Insérer les nouvelles préférences de genre
        if (!empty($genres)) {
            $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, preference_type, preference_value) VALUES (?, 'genre', ?)");
            foreach ($genres as $genre_id) {
                $stmt->execute([$user_id, $genre_id]);
            }
        }
        
        // Insérer les nouvelles préférences de catégorie
        if (!empty($categories)) {
            $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, preference_type, preference_value) VALUES (?, 'category', ?)");
            foreach ($categories as $category_id) {
                $stmt->execute([$user_id, $category_id]);
            }
        }
        
        // Insérer les nouvelles préférences d'auteur
        if (!empty($authors)) {
            $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, preference_type, preference_value) VALUES (?, 'author', ?)");
            foreach ($authors as $author) {
                if (!empty($author)) {
                    $stmt->execute([$user_id, $author]);
                }
            }
        }
        
        // Insérer la fréquence de lecture
        $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, preference_type, preference_value) VALUES (?, 'frequency', ?)");
        $stmt->execute([$user_id, $frequency]);
        
        // Valider la transaction
        $pdo->commit();
        
        // Répondre avec succès
        echo json_encode([
            'success' => true, 
            'message' => 'Préférences enregistrées avec succès',
            'user_id' => $user_id // Inclure l'ID utilisateur dans la réponse pour confirmation
        ]);
        
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // Gérer les erreurs
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur lors de l\'enregistrement des préférences: ' . $e->getMessage()
    ]);
}
?>