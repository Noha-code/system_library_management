<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    // Activer l'affichage des erreurs (À désactiver en production)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $conn = connect_db($db_config);
    
    // Journalisation des paramètres entrants
    error_log("Tentative de récupération de l'historique pour user_id: " . $_GET['user_id']);
    
    if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
        throw new Exception("ID utilisateur invalide ou manquant");
    }

    $user_id = (int)$_GET['user_id'];
    
    // Journalisation de la requête SQL
    error_log("Exécution de la requête SQL pour user_id: $user_id");
    
    $stmt = $conn->prepare("
        SELECT search_query, search_date 
        FROM search_history 
        WHERE user_id = :user_id 
        ORDER BY search_date DESC
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // AJOUT ICI - Début de la modification
    if (empty($results)) {
        echo json_encode([
            'message' => 'Aucun historique de recherche trouvé', 
            'user_id' => $user_id // Optionnel: utile pour le débogage
        ]);
        exit;
    }
    // AJOUT ICI - Fin de la modification
    
    // Journalisation des résultats bruts
    error_log("Résultats de la requête: " . print_r($results, true));
    
    echo json_encode($results);
    
} catch (Exception $e) {
    // Journalisation détaillée de l'erreur
    error_log("ERREUR CRITIQUE dans search_history.php:");
    error_log("Message: " . $e->getMessage());
    error_log("Fichier: " . $e->getFile());
    error_log("Ligne: " . $e->getLine());
    error_log("Trace: " . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        'error' => 'Une erreur est survenue',
        'debug' => $e->getMessage() // À désactiver en production
    ]);
}