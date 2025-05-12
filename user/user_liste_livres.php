<?php
require_once '../auth.php';
authorize('user');
error_reporting(E_ALL & ~E_NOTICE); // Affiche toutes les erreurs sauf les notices
ini_set('display_errors', 1);
?>
<?php
// Connexion à la base de données avec PDO
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

// Recherche
$search = $_GET['search'] ?? '';
$searchSql = "";

if (!empty($search)) {
    $searchSql = "WHERE livres.titre LIKE :search OR livres.auteur LIKE :search";
}

// Tri
$allowedSort = ['titre', 'auteur', 'annee_parution', 'quantite'];
$sort = in_array($_GET['sort'] ?? '', $allowedSort) ? $_GET['sort'] : 'titre';
$order = ($_GET['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

// Requête SQL
$sql = "SELECT livres.*, categorie.nom_categorie, genre.nom_genre
        FROM livres 
        LEFT JOIN categorie ON livres.id_categorie = categorie.id_categorie
        LEFT JOIN genre ON livres.id_genre = genre.id_genre
        $searchSql
        ORDER BY $sort $order";

try {
    $stmt = $pdo->prepare($sql);
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de requête : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Gestion de Bibliothèque</title>
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
        
        .dashboard-wrapper {
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
            max-width: 1200px;
        }
        
        .page-title {
            color: var(--primary-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
            letter-spacing: 0.3px;
        }
        
        .search-section {
            background-color: white;
            border: 1px solid var(--accent-color);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .search-section h5 {
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
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .book-card {
            border: 1px solid var(--accent-color);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            background-color: white;
        }
        
        .book-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .book-header {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            font-weight: 600;
            text-align: center;
            border-bottom: 1px solid var(--dark-color);
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
        }
        
        .book-content {
            padding: 15px;
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
        }
        
        .book-meta {
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        
        .stock-badge {
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 3px;
            display: inline-block;
            font-size: 0.85rem;
        }
        
        .stock-ok {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .stock-warning {
            background-color: #fff8e1;
            color: #ff8f00;
        }
        
        .stock-danger {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .action-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        /* Table styling */
        .table thead {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Modal styling */
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
        }
        
        .modal-img {
            max-height: 350px;
            object-fit: contain;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .card-header {
            background-color: var(--light-color) !important;
            color: var(--primary-color);
            font-weight: 600;
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
        }
        
        .dropdown-menu {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .dropdown-item:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .form-control {
            border-radius: var(--border-radius);
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
        }
        
        .btn-outline-primary {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
        }
        
        .alert-info {
            background-color: rgba(255, 255, 255, 0.9);
            border-left: 4px solid var(--secondary-color);
            color: var(--text-color);
            border-radius: var(--border-radius);
        }
        
        .list-group-item {
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
        }
        
        .text-primary {
            color: var(--secondary-color) !important;
        }
        
        /* Pour le fond du modal */
        .modal-content {
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        /* Pour le scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
    </style>
</head>
<body>

<!-- Main Content -->
<div class="container dashboard-wrapper">
    <h2 class="page-title"><i class="fas fa-book me-2"></i>Catalogue de Livres</h2>
    
    <!-- Search Section -->
    <div class="search-section">
        <h5><i class="fas fa-search me-2"></i>Rechercher un livre</h5>
        <form method="get" class="row g-3">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Titre ou auteur du livre" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Rechercher</button>
            </div>
        </form>
    </div>
    
    <!-- Results Section -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Résultats (<?= count($result) ?>)</h5>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-sort me-1"></i> Trier par
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="?sort=titre&order=asc<?= !empty($search) ? "&search=$search" : "" ?>">Titre (A-Z)</a></li>
                                    <li><a class="dropdown-item" href="?sort=titre&order=desc<?= !empty($search) ? "&search=$search" : "" ?>">Titre (Z-A)</a></li>
                                    <li><a class="dropdown-item" href="?sort=auteur&order=asc<?= !empty($search) ? "&search=$search" : "" ?>">Auteur (A-Z)</a></li>
                                    <li><a class="dropdown-item" href="?sort=annee_parution&order=desc<?= !empty($search) ? "&search=$search" : "" ?>">Plus récent</a></li>
                                    <li><a class="dropdown-item" href="?sort=annee_parution&order=asc<?= !empty($search) ? "&search=$search" : "" ?>">Plus ancien</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($result) > 0): ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                            <?php foreach($result as $row): 
                                $quantite = isset($row["quantite"]) ? intval($row["quantite"]) : 0;
                                $stockClass = 'stock-ok';
                                $stockText = 'En stock';
                                
                                if ($quantite <= 0) {
                                    $stockClass = 'stock-danger';
                                    $stockText = 'Indisponible';
                                } elseif ($quantite <= 2) {
                                    $stockClass = 'stock-warning';
                                    $stockText = 'Stock limité';
                                }
                            ?>
                                <div class="col">
                                    <div class="book-card">
                                        <div class="book-header">
                                            <?= htmlspecialchars($row["titre"]) ?>
                                        </div>
                                        <div class="book-content">
                                            <div class="book-meta">
                                                <p class="mb-1"><i class="fas fa-user me-2"></i><strong>Auteur:</strong> <?= htmlspecialchars($row["auteur"]) ?></p>
                                                <p class="mb-1"><i class="fas fa-calendar me-2"></i><strong>Année:</strong> <?= htmlspecialchars($row["annee_parution"]) ?></p>
                                                <p class="mb-1"><i class="fas fa-bookmark me-2"></i><strong>Genre:</strong> <?= htmlspecialchars($row["nom_genre"]) ?></p>
                                                <p class="mb-1"><i class="fas fa-tags me-2"></i><strong>Catégorie:</strong> <?= htmlspecialchars($row["nom_categorie"]) ?></p>
                                            </div>
                                            
                                            <div class="mt-3 mb-2">
                                                <span class="stock-badge <?= $stockClass ?>">
                                                    <?php if ($quantite > 0): ?>
                                                        <i class="fas fa-check-circle me-1"></i> <?= $stockText ?> (<?= $quantite ?>)
                                                    <?php else: ?>
                                                        <i class="fas fa-times-circle me-1"></i> <?= $stockText ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bookModal<?= $row['id_livre'] ?>">
                                                    <i class="fas fa-eye me-1"></i> Détails
                                                </button>
                                                <?php if ($quantite > 0): ?>
                                                <a href="reserver.php?id=<?= $row['id_livre'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-bookmark me-1"></i> Réserver
                                                </a>
                                                <?php else: ?>
                                                <button class="btn btn-sm btn-success" disabled>
                                                    <i class="fas fa-bookmark me-1"></i> Réserver
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal pour chaque livre -->
                                <div class="modal fade" id="bookModal<?= $row['id_livre'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?= htmlspecialchars($row["titre"]) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-4 text-center">
                                                        <?php
                                                            $imagePath = $row['image'] ?? '';
                                                            $imageAffichee = (!empty($imagePath) && file_exists($imagePath)) 
                                                                ? $imagePath 
                                                                : 'https://media.istockphoto.com/id/847970782/fr/vectoriel/r%C3%A9servez-lic%C3%B4ne.jpg?s=612x612&w=0&k=20&c=ZoIH7qCKXunH-GUdijH1c8suGl22cw_6srt3KHxoPuE=';
                                                        ?>
                                                        <img src="<?= $imageAffichee ?>" alt="<?= htmlspecialchars($row["titre"]) ?>" class="img-fluid rounded modal-img">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="card mb-3">
                                                            <div class="card-header">
                                                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Description</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <p class="text-justify">
                                                                    <?= !empty($row["description"]) ? htmlspecialchars($row["description"]) : "<em>Aucune description disponible pour ce livre.</em>" ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="card">
                                                                    <div class="card-header">
                                                                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Détails</h5>
                                                                    </div>
                                                                    <ul class="list-group list-group-flush">
                                                                        <li class="list-group-item"><i class="fas fa-user me-2 text-primary"></i><strong>Auteur:</strong> <?= htmlspecialchars($row["auteur"]) ?></li>
                                                                        <li class="list-group-item"><i class="fas fa-calendar me-2 text-primary"></i><strong>Année:</strong> <?= htmlspecialchars($row["annee_parution"]) ?></li>
                                                                        <li class="list-group-item"><i class="fas fa-bookmark me-2 text-primary"></i><strong>Genre:</strong> <?= htmlspecialchars($row["nom_genre"]) ?></li>
                                                                        <li class="list-group-item"><i class="fas fa-tags me-2 text-primary"></i><strong>Catégorie:</strong> <?= htmlspecialchars($row["nom_categorie"]) ?></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="card">
                                                                    <div class="card-header">
                                                                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Disponibilité</h5>
                                                                    </div>
                                                                    <div class="card-body text-center <?= $stockClass ?>">
                                                                        <h3 class="mb-2"><?= $quantite ?></h3>
                                                                        <p class="mb-0">exemplaire<?= $quantite > 1 ? 's' : '' ?> disponible<?= $quantite > 1 ? 's' : '' ?></p>
                                                                        <p class="mt-2 mb-0"><i class="fas fa-<?= $quantite > 0 ? 'check' : 'times' ?>-circle me-1"></i> <?= $stockText ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                <?php if ($quantite > 0): ?>
                                                <a href="reserver.php?id=<?= $row['id_livre'] ?>" class="btn btn-success">
                                                    <i class="fas fa-bookmark me-1"></i> Réserver
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Aucun livre ne correspond à votre recherche.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>