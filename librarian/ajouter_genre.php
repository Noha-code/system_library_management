<?php
require_once '../auth.php';
authorize('librarian');
?>
<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=library", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_genre = $_POST["nom_genre"];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO genre (nom_genre) VALUES (:nom_genre)");
        $stmt->bindParam(':nom_genre', $nom_genre, PDO::PARAM_STR);
        $stmt->execute();
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un genre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-primary mb-4">➕ Ajouter un nouveau genre</h2>
    <form method="post" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Nom du genre :</label>
            <input type="text" name="nom_genre" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Ajouter</button>
        <a href="ajouter_livre.php" class="btn btn-secondary ms-2"> ↩ Retour</a>
    </form>
</div>
</body>
</html>
