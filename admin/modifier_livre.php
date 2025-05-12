<?php
// Connexion à la base de données avec PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=library", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de livre non spécifié.");
}

// Récupérer les données actuelles
try {
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE id_livre = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$livre) {
        die("Livre non trouvé.");
    }
} catch(PDOException $e) {
    die("Erreur de requête : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = $_POST["titre"];
    $auteur = $_POST["auteur"];
    $annee = $_POST["annee"];
    $description = $_POST["description"];
    $categorie = $_POST["categorie"];
    $genre = $_POST["genre"];
    $quantite = $_POST["quantite"]; // Nouveau champ quantité

    // Gérer l'image
    $image = $livre['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $timestamp = time(); // Ajouter un timestamp pour éviter les conflits de noms
        $image_name = $timestamp . '_' . basename($_FILES['image']['name']);
        $target_path = "images/" . $image_name;
        if (!file_exists("images/")) {
            mkdir("images/", 0777, true);
        }
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image = $target_path;
        }
    }

    try {
        $sql = "UPDATE livres 
                SET titre = :titre, auteur = :auteur, annee_parution = :annee, 
                description = :description, id_categorie = :categorie, id_genre = :genre, 
                image = :image, quantite = :quantite
                WHERE id_livre = :id";
                
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titre', $titre, PDO::PARAM_STR);
        $stmt->bindParam(':auteur', $auteur, PDO::PARAM_STR);
        $stmt->bindParam(':annee', $annee, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':categorie', $categorie, PDO::PARAM_INT);
        $stmt->bindParam(':genre', $genre, PDO::PARAM_INT);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT); // Ajout du paramètre quantité
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            header("Location: liste_livres.php?success=modification");
            exit;
        }
    } catch(PDOException $e) {
        echo "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
    }
}

// Récupérer les catégories
try {
    $resCat = $pdo->query("SELECT * FROM categorie");
    $categories = $resCat->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur lors de la récupération des catégories : " . $e->getMessage());
}

// Récupérer les genres
try {
    $resGen = $pdo->query("SELECT * FROM genre");
    $genres = $resGen->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur lors de la récupération des genres : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un livre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-primary mb-4">✏️ Modifier le livre</h2>
        <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow-sm">
            <div class="mb-3">
                <label class="form-label">Titre :</label>
                <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($livre['titre']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Auteur :</label>
                <input type="text" name="auteur" class="form-control" value="<?= htmlspecialchars($livre['auteur']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Année de parution :</label>
                <input type="number" name="annee" class="form-control" value="<?= $livre['annee_parution'] ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Description :</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($livre['description'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantité disponible :</label>
                <input type="number" name="quantite" class="form-control" value="<?= $livre['quantite'] ?? 5 ?>" min="0">
            </div>

            <div class="mb-3">
                <label class="form-label">Catégorie :</label>
                <select name="categorie" class="form-select" required>
                    <?php foreach ($categories as $cat): ?>
                        <?php $selected = ($cat['id_categorie'] == $livre['id_categorie']) ? "selected" : ""; ?>
                        <option value="<?= $cat['id_categorie'] ?>" <?= $selected ?>><?= $cat['nom_categorie'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Genre :</label>
                <select name="genre" class="form-select" required>
                    <?php foreach ($genres as $gen): ?>
                        <?php $selected = ($gen['id_genre'] == $livre['id_genre']) ? "selected" : ""; ?>
                        <option value="<?= $gen['id_genre'] ?>" <?= $selected ?>><?= $gen['nom_genre'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Image actuelle :</label><br>
                <img src="<?= file_exists($livre['image']) ? $livre['image'] : 'https://media.istockphoto.com/id/847970782/fr/vectoriel/r%C3%A9servez-lic%C3%B4ne.jpg?s=612x612&w=0&k=20&c=ZoIH7qCKXunH-GUdijH1c8suGl22cw_6srt3KHxoPuE=' ?>" 
                     width="100" class="img-thumbnail">
            </div>

            <div class="mb-3">
                <label class="form-label">Nouvelle image (facultatif) :</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="liste_livres.php" class="btn btn-secondary ms-2"> ↩ Retour</a>
        </form>
    </div>
</body>
</html>
