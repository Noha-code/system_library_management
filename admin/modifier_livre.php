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
    <title>Modifier un livre - Bibliothèque en ligne</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a304d;
            --secondary-color: #7c5295;
            --accent-color: #c49b66;
            --dark-color: #2d2327;
            --light-color: #f7f3ee;
            --text-color: #362f35;
            --light-text: #f7f3ee;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s ease;
            --border-radius: 8px;
            --main-font: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
            --overlay-dark: rgba(45, 35, 39, 0.7);
        }
        
        body {
            font-family: var(--main-font);
            line-height: 1.7;
            color: var(--text-color);
            background-color: var(--light-color);
            background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--overlay-dark);
            z-index: -1;
        }
        
        .container {
            margin-top: 40px;
            margin-bottom: 40px;
        }
        
        h2 {
            color: var(--primary-color);
            font-family: var(--main-font);
            letter-spacing: 0.3px;
            margin-bottom: 1.5rem;
            position: relative;
            display: inline-block;
            font-weight: 700;
        }
        
        h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--accent-color);
        }
        
        .form-container {
            background-color: rgba(255, 255, 255, 0.94);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
            position: relative;
            border-top: 5px solid var(--accent-color);
        }
        
        .form-label {
            color: var(--dark-color);
            font-weight: 600;
            font-family: var(--main-font);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 12px;
            font-family: var(--main-font);
            transition: var(--transition);
            background-color: white;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(196, 155, 102, 0.25);
            border-color: var(--accent-color);
        }
        
        textarea.form-control {
            min-height: 120px;
        }
        
        .btn {
            font-family: var(--main-font);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .form-text {
            color: #666;
            font-style: italic;
            font-size: 0.9rem;
        }
        
        .form-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 2rem;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .img-thumbnail {
            border: 2px solid var(--secondary-color);
            padding: 4px;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: var(--transition);
        }
        
        .img-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
                margin-bottom: 20px;
            }
            
            .form-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <h2 class="mb-4 text-light"><i class="fas fa-edit me-2"></i> Modifier le livre</h2>
            
            <div class="form-container">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label">Titre du livre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" class="form-control" value="<?= htmlspecialchars($livre['titre']) ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Auteur</label>
                        <input type="text" name="auteur" class="form-control" value="<?= htmlspecialchars($livre['auteur']) ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Année de parution</label>
                        <input type="number" name="annee" class="form-control" value="<?= $livre['annee_parution'] ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Entrez une description du livre..."><?= htmlspecialchars($livre['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Quantité disponible</label>
                        <input type="number" name="quantite" class="form-control" value="<?= $livre['quantite'] ?? 5 ?>" min="0">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select name="categorie" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <?php $selected = ($cat['id_categorie'] == $livre['id_categorie']) ? "selected" : ""; ?>
                                    <option value="<?= $cat['id_categorie'] ?>" <?= $selected ?>><?= $cat['nom_categorie'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="add-button-container mt-2">
                                <a href="ajouter_categorie.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Nouvelle catégorie
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Genre <span class="text-danger">*</span></label>
                            <select name="genre" class="form-select" required>
                                <?php foreach ($genres as $gen): ?>
                                    <?php $selected = ($gen['id_genre'] == $livre['id_genre']) ? "selected" : ""; ?>
                                    <option value="<?= $gen['id_genre'] ?>" <?= $selected ?>><?= $gen['nom_genre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="add-button-container mt-2">
                                <a href="ajouter_genre.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Nouveau genre
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Image actuelle :</label><br>
                        <img src="<?= file_exists($livre['image']) ? $livre['image'] : 'https://media.istockphoto.com/id/847970782/fr/vectoriel/r%C3%A9servez-lic%C3%B4ne.jpg?s=612x612&w=0&k=20&c=ZoIH7qCKXunH-GUdijH1c8suGl22cw_6srt3KHxoPuE=' ?>" 
                             width="100" class="img-thumbnail mb-3">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nouvelle image (facultatif)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Laissez vide pour conserver l'image actuelle</div>
                    </div>
                    
                    <div class="form-footer">
                        <a href="liste_livres.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>