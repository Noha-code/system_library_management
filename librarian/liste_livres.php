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

// Vérifier si un message de confirmation existe
$successMessage = "";
if (isset($_GET['deleted']) && $_GET['deleted'] == 'success') {
    $successMessage = "Le livre a été supprimé avec succès.";
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
$nextOrder = $order === 'asc' ? 'desc' : 'asc';

// Pagination
$livresParPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$debut = ($page - 1) * $livresParPage;

// Requête pour compter le nombre total de livres
$countSql = "SELECT COUNT(*) FROM livres $searchSql";
$countStmt = $pdo->prepare($countSql);
if (!empty($search)) {
    $searchParam = "%$search%";
    $countStmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
}
$countStmt->execute();
$totalLivres = $countStmt->fetchColumn();
$totalPages = ceil($totalLivres / $livresParPage);

// Requête SQL avec pagination
$sql = "SELECT livres.*, categorie.nom_categorie, genre.nom_genre
        FROM livres 
        LEFT JOIN categorie ON livres.id_categorie = categorie.id_categorie
        LEFT JOIN genre ON livres.id_genre = genre.id_genre
        $searchSql
        ORDER BY $sort $order
        LIMIT $debut, $livresParPage";

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

// Statistiques générales - simplifiées
$totalBooks = $pdo->query("SELECT COUNT(*) FROM livres")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des livres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF5E14;
            --secondary-color: #5F5F5F;
            --dark-color: #212529;
            --light-color: #F8F9FA;
            --danger-color: #DC3545;
            --success-color: #28A745;
            --warning-color: #FFC107;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F5F7FB;
        }
        
        #content {
            width: 100%;
            min-height: 100vh;
            transition: all 0.3s;
            padding: 30px;
        }
        
        .navbar {
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 20px;
            border-radius: 8px;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            font-weight: 500;
            padding: 15px 20px;
        }
        
        .stats-card {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            overflow: hidden;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card .icon {
            font-size: 32px;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .custom-table th {
            background-color: #f8f9fa;
            color: #333;
            border-top: none;
            font-weight: 500;
        }
        
        .custom-table th a {
            color: #333;
            text-decoration: none;
        }
        
        .custom-table th a:hover {
            color: #FF5E14;
        }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            padding: 0;
            line-height: 32px;
            text-align: center;
            margin: 0 2px;
        }
        
        .btn-primary {
            background-color: #FF5E14;
            border-color: #FF5E14;
        }
        
        .btn-primary:hover {
            background-color: #e54600;
            border-color: #e54600;
        }
        
        .img-cover {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 3px;
        }
        
        .stock-badge {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
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
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .page-link {
            color: #FF5E14;
        }
        
        .page-item.active .page-link {
            background-color: #FF5E14;
            border-color: #FF5E14;
        }
        
        .description-text {
            max-height: 80px;
            overflow-y: auto;
        }
        
        .alert-floating {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 300px;
            text-align: center;
            animation: fadeIn 0.5s, fadeOut 0.5s 3.5s forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        
        body:before, body:after, 
        #content:before, #content:after {
            display: none !important;
        }
        
        .navbar {
            display: none !important;
        }
        
        .breadcrumb {
            display: none !important;
        }
        
        /* Masquer les deux dernières cartes de statistiques */
        #stats-container .col-xl-3:nth-child(3),
        #stats-container .col-xl-3:nth-child(4) {
            display: none !important;
        }
        
        /* Style pour la modale de confirmation */
        .modal-confirm .modal-header {
            border-bottom: none;
            position: relative;
        }
        
        .modal-confirm .modal-content {
            border-radius: 10px;
        }
        
        .modal-confirm .icon-box {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            border-radius: 50%;
            z-index: 9;
            text-align: center;
            border: 3px solid #f15e5e;
        }
        
        .modal-confirm .icon-box i {
            color: #f15e5e;
            font-size: 46px;
            display: inline-block;
            margin-top: 10px;
        }
        
        .modal-confirm .btn {
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.4s;
            line-height: normal;
            min-width: 120px;
            border: none;
            margin: 0 5px;
        }
        
        .modal-confirm .btn-secondary {
            background: #c1c1c1;
        }
        
        .modal-confirm .btn-secondary:hover,
        .modal-confirm .btn-secondary:focus {
            background: #a8a8a8;
        }
        
        .modal-confirm .btn-danger {
            background: #f15e5e;
        }
        
        .modal-confirm .btn-danger:hover,
        .modal-confirm .btn-danger:focus {
            background: #ee3535;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Page Content -->
    <div id="content">
        <!-- Message de succès pour la suppression -->
        <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-floating alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= $successMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Titre principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-book me-2 text-primary"></i>Gestion des Livres</h1>
            <a href="ajouter_livre.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Ajouter un livre
            </a>
        </div>

        <!-- Statistics Cards - seulement deux cartes -->
        <div class="container-fluid mb-4" id="stats-container">
            <div class="row">
                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted text-uppercase fw-normal mb-1">Total des Livres</h6>
                                    <h2 class="mb-0 fw-bold"><?= $totalBooks ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon bg-success bg-opacity-10 text-success rounded-circle me-3">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted text-uppercase fw-normal mb-1">Livres Disponibles</h6>
                                    <h2 class="mb-0 fw-bold"><?= $totalBooks ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Box -->
        <div class="container-fluid mb-4">
            <div class="search-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Recherche</h5>
                </div>
                <form method="get" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher par titre ou auteur" value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark"><i class="fas fa-list me-2 text-primary"></i>Liste des Livres</h5>
                        <span class="badge bg-primary"><?= $totalLivres ?> livres trouvés</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th><a href="?sort=titre&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Titre <i class="fas fa-sort"></i></a></th>
                                    <th><a href="?sort=auteur&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Auteur <i class="fas fa-sort"></i></a></th>
                                    <th><a href="?sort=annee_parution&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Année <i class="fas fa-sort"></i></a></th>
                                    <th>Catégorie/Genre</th>
                                    <th><a href="?sort=quantite&order=<?= $nextOrder ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Stock <i class="fas fa-sort"></i></a></th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($result) > 0): ?>
                                    <?php foreach($result as $row): 
                                        $imagePath = $row['image'];
                                        $imageAffichee = (!empty($imagePath) && file_exists($imagePath)) 
                                                        ? $imagePath 
                                                        : 'https://media.istockphoto.com/id/847970782/fr/vectoriel/r%C3%A9servez-lic%C3%B4ne.jpg?s=612x612&w=0&k=20&c=ZoIH7qCKXunH-GUdijH1c8suGl22cw_6srt3KHxoPuE=';
                                        
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
                                        <tr>
                                            <td class="text-center"><img src="<?= $imageAffichee ?>" alt="Image" class="img-cover"></td>
                                            <td><strong><?= htmlspecialchars($row["titre"]) ?></strong></td>
                                            <td><?= htmlspecialchars($row["auteur"]) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row["annee_parution"]) ?></td>
                                            <td>
                                                <?= htmlspecialchars($row["nom_categorie"]) ?><br>
                                                <small class="text-muted"><?= htmlspecialchars($row["nom_genre"]) ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="stock-badge <?= $stockClass ?>">
                                                    <?= $quantite ?> ex.
                                                </span>
                                            </td>
                                            <td>
                                                <div class="description-text">
                                                    <?= !empty($row["description"]) ? htmlspecialchars(mb_substr($row["description"], 0, 100)) . (mb_strlen($row["description"]) > 100 ? '...' : '') : "<em class='text-muted'>Aucune description</em>" ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <a href="modifier_livre.php?id=<?= $row['id_livre'] ?>" class="btn btn-sm btn-warning btn-action" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger btn-action" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal" 
                                                            data-id="<?= $row['id_livre'] ?>"
                                                            data-titre="<?= htmlspecialchars($row["titre"]) ?>"
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i> Aucun livre trouvé.
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?sort=<?= $sort ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&page=<?= $page-1 ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?sort=<?= $sort ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?sort=<?= $sort ?>&order=<?= $order ?>&search=<?= urlencode($search) ?>&page=<?= $page+1 ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Modal de confirmation de suppression -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-confirm">
                <div class="modal-content">
                    <div class="modal-header flex-column">
                        <div class="icon-box">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h4 class="modal-title w-100 mt-3" id="deleteModalLabel">Confirmation de suppression</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>Êtes-vous sûr de vouloir supprimer le livre "<span id="livre-titre"></span>" ?</p>
                        <p class="text-muted small">Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <a href="#" id="btn-confirmer-suppression" class="btn btn-danger">Confirmer la suppression</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-5 py-3">
            <div class="container-fluid">
                <div class="text-center">
                    <p class="m-0">BiblioTech © <?= date('Y') ?> | Tous droits réservés</p>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script pour la gestion de la modale de suppression
    document.addEventListener('DOMContentLoaded', function() {
        // Récupérer la modale
        var deleteModal = document.getElementById('deleteModal');
        
        // Quand la modale est affichée, on met à jour les informations
        deleteModal.addEventListener('show.bs.modal', function(event) {
            // Bouton qui a déclenché la modale
            var button = event.relatedTarget;
            
            // Extraire les informations du livre
            var id = button.getAttribute('data-id');
            var titre = button.getAttribute('data-titre');
            
            // Mettre à jour le contenu de la modale
            document.getElementById('livre-titre').textContent = titre;
            document.getElementById('btn-confirmer-suppression').href = 'supprimer_livre.php?id=' + id;
        });
        
        // Auto-fermeture des alertes après 4 secondes
        var alertList = document.querySelectorAll('.alert-floating');
        alertList.forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 4000);
        });
    });
</script>
</body>
</html>
