<?php
session_start();

// Connexion à la base de données
$host = "localhost";
$user = "root";
$password = "";
$db = "library";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: user_liste_livres.php?error=id_invalid');
    exit();
}

$id_livre = intval($_GET['id']);
$message = '';
$error = '';

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer les données du formulaire
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $nom = trim(filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING));
        $prenom = trim(filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING));
        
        // Valider les données
        if (!$email) {
            $error = "L'adresse email est invalide.";
        } elseif (empty($nom) || empty($prenom)) {
            $error = "Veuillez remplir tous les champs obligatoires.";
        } else {
            // Vérifier la disponibilité du livre
            $stmt = $pdo->prepare("SELECT quantite, titre FROM livres WHERE id_livre = :id");
            $stmt->bindParam(':id', $id_livre, PDO::PARAM_INT);
            $stmt->execute();
            $livre = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$livre || $livre['quantite'] <= 0) {
                $error = "Ce livre n'est plus disponible.";
            } else {
                // Rechercher l'utilisateur par email
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $id_utilisateur = null;
                
                if ($user) {
                    // Utilisateur trouvé
                    $id_utilisateur = $user['id'];
                } else {
                    // Vérifier si l'utilisateur existe avec le nom et prénom fournis
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE first_name = :prenom AND last_name = :nom");
                    $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
                    $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($user) {
                        $id_utilisateur = $user['id'];
                    } else {
                        $error = "Utilisateur non trouvé dans le système. Veuillez vérifier vos informations.";
                    }
                }
                
                // Si un utilisateur est trouvé, procéder à la réservation
                if ($id_utilisateur) {
                    // Début de la transaction
                    $pdo->beginTransaction();
                    
                    // Date actuelle pour la réservation
                    $date_reservation = date('Y-m-d');
                    
                    // 1. Insérer dans la table reservations avec l'ID utilisateur et la date
                    // CORRECTION: Utiliser le nom correct de la colonne selon votre structure de base de données
                    // (Exemple avec 'livre_id' au lieu de 'id_livre')
                    $stmt = $pdo->prepare("INSERT INTO reservations (livre_id, id_utilisateur, date_reservation, etat) VALUES (:id_livre, :id_utilisateur, :date_reservation, 'active')");
                    $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
                    $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
                    $stmt->bindParam(':date_reservation', $date_reservation, PDO::PARAM_STR);
                    $stmt->execute();
                    
                    // 2. Mettre à jour la quantité dans la table livres
                    $stmt = $pdo->prepare("UPDATE livres SET quantite = quantite - 1 WHERE id_livre = :id");
                    $stmt->bindParam(':id', $id_livre, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Valider la transaction
                    $pdo->commit();
                    
                    // Message de succès et redirection
                    header("Location: user_liste_livres.php?success=reserved&titre=" . urlencode($livre['titre']));
                    exit();
                }
            }
        }
    } catch(PDOException $e) {
        // Annuler la transaction en cas d'erreur
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Erreur lors de la réservation : " . $e->getMessage();
    }
}

// Rechercher les informations du livre
try {
    $stmt = $pdo->prepare("SELECT livres.*, categorie.nom_categorie, genre.nom_genre 
                          FROM livres 
                          LEFT JOIN categorie ON livres.id_categorie = categorie.id_categorie
                          LEFT JOIN genre ON livres.id_genre = genre.id_genre
                          WHERE id_livre = :id");
    $stmt->bindParam(':id', $id_livre, PDO::PARAM_INT);
    $stmt->execute();
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$livre) {
        header('Location: user_liste_livres.php?error=not_found');
        exit();
    }

    if ($livre['quantite'] <= 0) {
        header('Location: user_liste_livres.php?error=not_available');
        exit();
    }
} catch(PDOException $e) {
    header('Location: user_liste_livres.php?error=db_error');
    exit();
}

// Définir la classe de stock pour l'affichage
$quantite = intval($livre["quantite"]);
$stockClass = 'text-success';
$stockText = 'En stock';

if ($quantite <= 0) {
    $stockClass = 'text-danger';
    $stockText = 'Indisponible';
} elseif ($quantite <= 2) {
    $stockClass = 'text-warning';
    $stockText = 'Stock limité';
}

// Préparer l'image du livre
$imagePath = $livre['image'] ?? '';
$imageAffichee = (!empty($imagePath) && file_exists($imagePath)) 
    ? $imagePath 
    : 'https://media.istockphoto.com/id/847970782/fr/vectoriel/r%C3%A9servez-lic%C3%B4ne.jpg?s=612x612&w=0&k=20&c=ZoIH7qCKXunH-GUdijH1c8suGl22cw_6srt3KHxoPuE=';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - <?= htmlspecialchars($livre['titre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap">
    <style>
        :root {
            --primary-color: #4a304d;      /* Violet foncé */
            --secondary-color: #7c5295;    /* Violet moyen */
            --accent-color: #c49b66;       /* Or/Bronze */
            --dark-color: #2d2327;         /* Brun très foncé */
            --light-color: #f7f3ee;        /* Beige clair */
            --text-color: #362f35;         /* Texte principal sombre */
            --light-text: #f7f3ee;         /* Texte clair */
            --border-radius: 8px;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            --overlay-dark: rgba(45, 35, 39, 0.7); /* Superposition foncée pour la lisibilité */
        }
        
        body {
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
            background-color: var(--light-color);
            color: var(--text-color);
            line-height: 1.7;
            background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            position: relative;
            padding-top: 20px;
            min-height: 100vh;
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
        
        .reservation-wrapper {
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
            max-width: 1000px;
        }
        
        .page-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .book-image {
            max-height: 300px;
            object-fit: contain;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .book-details {
            background-color: white;
            border: 1px solid var(--accent-color);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-section {
            background-color: white;
            border: 1px solid var(--accent-color);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-section h5 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
            border-bottom: 1px solid var(--accent-color);
            padding-bottom: 10px;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .form-control {
            border-radius: var(--border-radius);
        }
        
        .btn-outline-secondary {
            color: var(--text-color);
            border-color: var(--text-color);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--text-color);
            color: white;
            border-color: var(--text-color);
        }
    </style>
</head>
<body>

<div class="container reservation-wrapper">
    <div class="row mb-4">
        <div class="col">
            <h2 class="page-title"><i class="fas fa-bookmark me-2"></i>Réservation de Livre</h2>
            <a href="user_liste_livres.php" class="btn btn-outline-secondary mb-4">
                <i class="fas fa-arrow-left me-2"></i>Retourner au catalogue
            </a>
        </div>
    </div>
    
    <?php if(!empty($error)): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    
    <?php if(!empty($message)): ?>
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Détails du livre -->
        <div class="col-md-5">
            <div class="book-details">
                <div class="text-center mb-4">
                    <img src="<?= $imageAffichee ?>" alt="<?= htmlspecialchars($livre['titre']) ?>" class="img-fluid book-image">
                </div>
                <h4 class="text-center"><?= htmlspecialchars($livre['titre']) ?></h4>
                <p class="text-center text-muted mb-4">par <?= htmlspecialchars($livre['auteur']) ?>, <?= htmlspecialchars($livre['annee_parution']) ?></p>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><i class="fas fa-bookmark me-2"></i><strong>Genre:</strong></span>
                    <span><?= htmlspecialchars($livre['nom_genre'] ?? 'Non spécifié') ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><i class="fas fa-tags me-2"></i><strong>Catégorie:</strong></span>
                    <span><?= htmlspecialchars($livre['nom_categorie'] ?? 'Non spécifiée') ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><i class="fas fa-check-circle me-2"></i><strong>Disponibilité:</strong></span>
                    <span class="<?= $stockClass ?>">
                        <?= $stockText ?> (<?= $quantite ?> exemplaire<?= $quantite > 1 ? 's' : '' ?>)
                    </span>
                </div>
                
                <?php if(!empty($livre['description'])): ?>
                <div class="mt-4">
                    <h5><i class="fas fa-info-circle me-2"></i>Description</h5>
                    <p class="text-justify">
                        <?= htmlspecialchars($livre['description']) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Formulaire de réservation avec informations utilisateur -->
        <div class="col-md-7">
            <div class="form-section">
                <h5><i class="fas fa-file-alt me-2"></i>Formulaire de Réservation</h5>
                
                <form method="post" action="reserver.php?id=<?= $id_livre ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Entrez votre adresse email">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nom" class="form-label"><i class="fas fa-user me-2"></i>Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nom" name="nom" required placeholder="Entrez votre nom">
                        </div>
                        <div class="col-md-6">
                            <label for="prenom" class="form-label"><i class="fas fa-user me-2"></i>Prénom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required placeholder="Entrez votre prénom">
                        </div>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        En réservant ce livre, vous vous engagez à venir le chercher à la bibliothèque dans les 3 jours ouvrables. Passé ce délai, la réservation sera annulée.
                    </div>
                    
                    <div class="text-end mt-4">
                        <a href="user_liste_livres.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-bookmark me-1"></i> Confirmer la réservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>