<?php
// Protection contre l'accès direct
define('IN_APP', true);

// Charger la configuration
require_once '../config.php';

// Récupérer l'ID du livre
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

// Vérifier que l'ID est valide
if ($book_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de livre invalide']);
    exit;
}

try {
    // Créer une connexion PDO
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], $db_config['options']);
    
    // Récupérer la description du livre
    $stmt = $pdo->prepare("
        SELECT 
            l.description, 
            g.nom_genre, 
            c.nom_categorie 
        FROM 
            livres l
            LEFT JOIN genre g ON l.id_genre = g.id_genre
            LEFT JOIN categorie c ON l.id_categorie = c.id_categorie
        WHERE 
            l.id_livre = ?
    ");
    
    $stmt->execute([$book_id]);
    $result = $stmt->fetch();
    
    if (!$result) {
        throw new Exception("Livre non trouvé");
    }
    
    // Préparer la réponse
    $response = [
        'description' => $result['description'] ?? 'Aucune description disponible.',
        'genre' => $result['nom_genre'] ?? 'Non catégorisé',
        'categorie' => $result['nom_categorie'] ?? 'Non catégorisé'
    ];
    
    // Renvoyer la description au format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    // Gérer les erreurs
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>