<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

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

// Fonction pour vérifier et annuler les réservations expirées
function verifierReservationsExpirees($pdo) {
    try {
        // Calculer la date d'il y a 3 jours ouvrables (approximation simple)
        // Pour une implémentation précise, il faudrait considérer les jours fériés et weekends
        $date_limite = date('Y-m-d', strtotime('-1 days'));
        
        // Récupérer toutes les réservations actives qui ont dépassé le délai
        $stmt = $pdo->prepare("SELECT r.id_reservation, r.livre_id 
                              FROM reservations r 
                              WHERE r.statut = 'active' 
                              AND r.date_reservation < :date_limite");
        $stmt->bindParam(':date_limite', $date_limite, PDO::PARAM_STR);
        $stmt->execute();
        $reservations_expirees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($reservations_expirees) > 0) {
            // Début de la transaction
            $pdo->beginTransaction();
            
            foreach ($reservations_expirees as $reservation) {
                // Annuler la réservation
                $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulée', 
                                      date_annulation = CURRENT_DATE() 
                                      WHERE id_reservation = :id_reservation");
                $stmt->bindParam(':id_reservation', $reservation['id_reservation'], PDO::PARAM_INT);
                $stmt->execute();
                
                // Remettre le livre en stock
                $stmt = $pdo->prepare("UPDATE livres SET quantite = quantite + 1 
                                      WHERE id_livre = :id_livre");
                $stmt->bindParam(':id_livre', $reservation['livre_id'], PDO::PARAM_INT);
                $stmt->execute();
            }
            
            // Valider la transaction
            $pdo->commit();
        }
    } catch(PDOException $e) {
        // Annuler la transaction en cas d'erreur
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Log l'erreur silencieusement pour ne pas perturber l'expérience utilisateur
        error_log("Erreur lors de la vérification des réservations expirées : " . $e->getMessage());
    }
}

// Vérifier les réservations expirées à chaque chargement de la page
verifierReservationsExpirees($pdo);

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: user_liste_livres.php?error=id_invalid');
    exit();
}

$id_livre = intval($_GET['id']);
$message = '';
$error = '';

// Récupérer les informations de l'utilisateur connecté
$id_utilisateur = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id_utilisateur, PDO::PARAM_INT);
    $stmt->execute();
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_info) {
        // Si les informations utilisateur ne sont pas trouvées, déconnecter et rediriger
        session_destroy();
        header('Location: login.php?error=session_expired');
        exit();
    }
} catch(PDOException $e) {
    $error = "Erreur lors de la récupération des informations utilisateur : " . $e->getMessage();
}

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier la disponibilité du livre
        $stmt = $pdo->prepare("SELECT quantite, titre FROM livres WHERE id_livre = :id");
        $stmt->bindParam(':id', $id_livre, PDO::PARAM_INT);
        $stmt->execute();
        $livre = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$livre || $livre['quantite'] <= 0) {
            $error = "Ce livre n'est plus disponible.";
        } else {
            // Vérifier si l'utilisateur a déjà réservé ce livre
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations 
                                  WHERE livre_id = :id_livre 
                                  AND utilisateur_id = :id_utilisateur 
                                  AND statut = 'active'");
            $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
            $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                $error = "Vous avez déjà réservé ce livre.";
            } else {
                // Début de la transaction
                $pdo->beginTransaction();
                
                // Date actuelle pour la réservation
                $date_reservation = date('Y-m-d');
                
                // Insérer dans la table reservations avec l'ID utilisateur et la date
                $stmt = $pdo->prepare("INSERT INTO reservations (livre_id, utilisateur_id, date_reservation, statut) 
                                      VALUES (:id_livre, :id_utilisateur, :date_reservation, 'active')");
                $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
                $stmt->bindParam(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
                $stmt->bindParam(':date_reservation', $date_reservation, PDO::PARAM_STR);
                $stmt->execute();
                
                // Mettre à jour la quantité dans la table livres
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
        
        .user-info {
            background-color: rgba(124, 82, 149, 0.1);
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .user-info p {
            margin-bottom: 5px;
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
                
                <!-- Affichage des informations de l'utilisateur connecté -->
                <div class="user-info">
                    <h6><i class="fas fa-user-circle me-2"></i>Réservation au nom de :</h6>
                    <p><strong>Nom:</strong> <?= htmlspecialchars($user_info['last_name']) ?></p>
                    <p><strong>Prénom:</strong> <?= htmlspecialchars($user_info['first_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
                </div>
                
                <form method="post" action="reserver.php?id=<?= $id_livre ?>">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        En réservant ce livre, vous vous engagez à venir le chercher à la bibliothèque dans les 3 jours ouvrables. Passé ce délai, la réservation sera <strong>automatiquement annulée</strong> et le livre sera remis en circulation.
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