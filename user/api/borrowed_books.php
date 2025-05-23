
<?php
require_once '../config.php';
header('Content-Type: application/json');

$user_id = (int)$_GET['user_id'];
$conn = connect_db($db_config);

$stmt = $conn->prepare("
    SELECT e.date_emprunt, e.date_retour, l.titre, l.auteur, l.image 
    FROM emprunts e 
    JOIN livres l ON e.livre_id = l.id_livre 
    WHERE e.utilisateur_id = :user_id
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>