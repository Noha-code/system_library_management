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
    <title>Ajouter un genre - Bibliothèque en ligne</title>
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
            --max-width: 600px;
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
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 20px;
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
            max-width: var(--max-width);
            width: 100%;
            margin: 0 auto;
            padding: 0;
        }
        
        .header {
            background-color: var(--primary-color);
            color: var(--light-text);
            padding: 1.5rem;
            text-align: center;
            border-top-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
            font-family: var(--main-font);
        }
        
        .header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .form-container {
            background-color: white;
            padding: 2rem;
            border-bottom-left-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-family: var(--main-font);
            color: var(--text-color);
            font-weight: 500;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: var(--main-font);
            font-size: 1rem;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(124, 82, 149, 0.2);
        }
        
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-family: var(--main-font);
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            font-family: var(--main-font);
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #047857;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 0 15px;
            }
            
            .btn-container {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Formulaire d'ajout</h2>
        </div>
        <div class="form-container">
            <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Le genre a été ajouté avec succès.
            </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                Erreur : <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="nom_genre">Nom du genre :</label>
                    <input type="text" id="nom_genre" name="nom_genre" required>
                </div>
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                    <a href="ajouter_livre.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>