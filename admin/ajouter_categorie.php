<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=library", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom_categorie = $_POST["nom_categorie"];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categorie (nom_categorie) VALUES (:nom_categorie)");
        $stmt->bindParam(':nom_categorie', $nom_categorie, PDO::PARAM_STR);
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
    <title>Ajouter une catégorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-primary mb-4">➕ Ajouter une nouvelle catégorie</h2>
    <form method="post" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Nom de la catégorie :</label>
            <input type="text" name="nom_categorie" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Ajouter</button>
        <a href="ajouter_livre.php" class="btn btn-secondary ms-2"> ↩ Retour</a>
    </form>
</div>
</body>
</html>
