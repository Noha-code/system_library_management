<?php
// Protection contre l'accès direct
define('IN_APP', true);

// Charger la configuration
require_once '../config.php';

// Démarrer la session pour l'authentification
session_start();

// Dans un système réel, vous devriez vérifier l'authentification
// Pour l'instant, utilisons un ID utilisateur fixe à des fins de démonstration
$user_id = 1; // À remplacer par l'ID de session réel dans un système complet

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
    $authors = isset($data['authors']) ? trim($data['authors']) : '';
    $frequency = isset($data['frequency']) ? $data['frequency'] : 'rarely';
    
    // Nettoyer les données
    $genres = array_map('intval', $genres); // Assurer que les IDs sont des entiers
    $authors = explode(',', $authors); // Diviser la chaîne en un tableau
    $authors = array_map('trim', $authors); // Enlever les espaces
    $authors = array_filter($authors); // Supprimer les entrées vides
    
    // Créer une connexion PDO
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], $db_config['options']);
    
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
        
        // Insérer les nouvelles préférences d'auteur
        if (!empty($authors)) {
            $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, preference_type, preference_value) VALUES (?, 'author', ?)");
            foreach ($authors as $author) {
                $stmt->execute([$user_id, $author]);
            }
        }
        
        // Insérer la fréquence de lecture
        $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, preference_type, preference_value) VALUES (?, 'frequency', ?)");
        $stmt->execute([$user_id, $frequency]);
        
        // Valider la transaction
        $pdo->commit();
        
        // Répondre avec succès
        echo json_encode(['success' => true, 'message' => 'Préférences enregistrées avec succès']);
        
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // Gérer les erreurs
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement des préférences: ' . $e->getMessage()]);
}
?>