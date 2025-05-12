<?php
// Protection contre l'accès direct
define('IN_APP', true);

// Charger la configuration
require_once '../config.php';

// Vérifier si l'utilisateur est connecté
session_start();
// Dans un système réel, vous devriez vérifier authentification
// Si vous n'avez pas de système d'authentification, on utilise l'ID fourni
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 1;

// Initialiser la réponse
header('Content-Type: application/json');

try {
    // Créer une connexion PDO
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['password'], $db_config['options']);
    
    // Exécuter le script Python pour des recommandations avancées
    if (isset($_POST['run_python']) && $_POST['run_python'] == 'recomman_surprise.py') {
        // Configuration pour le script Python
        $python_config = json_encode([
            'host' => $db_config['host'],
            'user' => $db_config['user'],
            'password' => $db_config['password'],
            'db' => $db_config['dbname'],
            'charset' => $db_config['charset']
        ]);
        
        // Écrire la configuration dans un fichier temporaire
        $temp_config = tempnam(sys_get_temp_dir(), 'py_config_');
        file_put_contents($temp_config, $python_config);
        
        // Commande pour exécuter le script Python
        $command = PYTHON_PATH . ' ' . RECOMMENDATION_SCRIPT . ' ' . $user_id . ' ' . $temp_config;
        
        // Exécuter la commande Python
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        
        // Supprimer le fichier temporaire
        unlink($temp_config);
        
        if ($return_var !== 0) {
            // Erreur dans l'exécution du script Python
            error_log('Erreur lors de l\'exécution du script Python: ' . implode("\n", $output));
            echo json_encode(['error' => 'Erreur lors de la génération des recommandations']);
            exit;
        }
        
        // Récupérer les recommandations générées par Python
        $stmt = $pdo->prepare("
            SELECT l.id_livre as id, l.titre as title, l.auteur as author, 
                   CONCAT('/images/covers/', l.id_livre, '.jpg') as cover,
                   ROUND((r.score * 100) / 5) as match
            FROM recommandations r
            JOIN livres l ON r.livre_id = l.id_livre
            WHERE r.utilisateur_id = ?
            ORDER BY r.score DESC
            LIMIT 10
        ");
        
        $stmt->execute([$user_id]);
        $recommendations = $stmt->fetchAll();
        
        // Envoyer les recommandations au client
        echo json_encode($recommendations);
    } else {
        // Si le script Python n'est pas demandé, utiliser une méthode plus simple
        echo json_encode(['error' => 'Méthode de recommandation non spécifiée']);
    }
} catch (Exception $e) {
    error_log('Erreur dans generate_recommendations.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Erreur lors de la récupération des recommandations']);
}
?>