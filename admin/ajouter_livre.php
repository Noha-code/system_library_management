<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=library", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Récupérer les catégories
try {
    $categories = $pdo->query("SELECT * FROM categorie")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur lors de la récupération des catégories : " . $e->getMessage());
}

// Récupérer les genres
try {
    $genres = $pdo->query("SELECT * FROM genre")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur lors de la récupération des genres : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titre = $_POST["titre"];
    $auteur = $_POST["auteur"];
    $annee = $_POST["annee"];
    $description = $_POST["description"];
    $categorie = $_POST["categorie"];
    $genre = $_POST["genre"];
    $quantite = $_POST["quantite"]; // Nouveau champ quantité

    // Gestion de l'image
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $timestamp = time(); // Ajouter un timestamp pour éviter les conflits de noms
        $imageName = $timestamp . '_' . basename($_FILES['image']['name']);
        $uploadDir = "images/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName)) {
            $imagePath = $uploadDir . $imageName;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO livres (titre, auteur, annee_parution, description, id_categorie, id_genre, image, quantite) 
                              VALUES (:titre, :auteur, :annee, :description, :categorie, :genre, :image, :quantite)");
        
        $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
        $stmt->bindParam(':auteur', $auteur, PDO::PARAM_STR);
        $stmt->bindParam(':annee', $annee, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':categorie', $categorie, PDO::PARAM_INT);
        $stmt->bindParam(':genre', $genre, PDO::PARAM_INT);
        $stmt->bindParam(':image', $imagePath, PDO::PARAM_STR);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT); // Ajout du paramètre quantité
        
        $stmt->execute();
        header("Location: liste_livres.php");
        exit;
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un livre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4 text-primary">➕ Ajouter un nouveau livre</h2>
    <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Titre :</label>
            <input type="text" name="titre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Auteur :</label>
            <input type="text" name="auteur" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Année de parution :</label>
            <input type="number" name="annee" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Description :</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Entrez une description du livre..."></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Quantité disponible :</label>
            <input type="number" name="quantite" class="form-control" value="5" min="0">
        </div>
        <div class="mb-3">
            <label class="form-label">Catégorie :</label>
            <select name="categorie" class="form-select" required>
                <option value="">--Choisir--</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id_categorie'] ?>"><?= $cat['nom_categorie'] ?></option>
                <?php endforeach; ?>
            </select>
            <div class="mt-1">
                <a href="ajouter_categorie.php" class="btn btn-sm btn-outline-primary">+ Nouvelle catégorie</a>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Genre :</label>
            <select name="genre" class="form-select" required>
                <option value="">--Choisir--</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= $g['id_genre'] ?>"><?= $g['nom_genre'] ?></option>
                <?php endforeach; ?>
            </select>
            <div class="mt-1">
                <a href="ajouter_genre.php" class="btn btn-sm btn-outline-primary">+ Nouveau genre</a>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Image de couverture :</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">L'image sera sauvegardée dans le dossier "images/"</small>
        </div>
        <button type="submit" class="btn btn-success">Ajouter le livre</button>
        <a href="liste_livres.php" class="btn btn-secondary ms-2"> ↩ Retour</a>
    </form>
</div>
</body>
</html>
