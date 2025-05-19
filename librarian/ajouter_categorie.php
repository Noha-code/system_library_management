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
        $success = true;
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une catégorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --overlay-dark: rgba(45, 35, 39, 0.7);
            --main-font: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            max-width: 800px;
            padding: 0 20px;
        }
        
        
        h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            letter-spacing: 0.3px;
            text-align: center;
            font-weight: 700;
            position: relative;
            display: inline-block;
        }
        
        h2::after {
            content: '';
            display: block;
            width: 70px;
            height: 3px;
            background-color: var(--accent-color);
            margin: 10px auto 0;
        }
        
        .card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(196, 155, 102, 0.3);
            transition: var(--transition);
            margin-bottom: 2rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: var(--light-text);
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            border-bottom: 3px solid var(--accent-color);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control {
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 12px;
            font-family: var(--main-font);
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(124, 82, 149, 0.25);
        }
        
        .btn {
            font-family: var(--main-font);
            border-radius: var(--border-radius);
            padding: 10px 20px;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .btn-success {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-success:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #047857;
            border-left: 4px solid #047857;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border-left: 4px solid #b91c1c;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    

    <div class="container mt-5">
        <div class="d-flex justify-content-center">
            <h2><i class="fas fa-folder-plus me-2"></i> Ajouter une nouvelle catégorie</h2>
        </div>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success" role="alert">
            <i class="fas fa-check-circle"></i> La catégorie a été ajoutée avec succès!
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle"></i> Erreur : <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                Formulaire d'ajout
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="nom_categorie" class="form-label">Nom de la catégorie :</label>
                        <input type="text" id="nom_categorie" name="nom_categorie" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus-circle me-2"></i> Ajouter
                        </button>
                        <a href="ajouter_livre.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</body>
</html>