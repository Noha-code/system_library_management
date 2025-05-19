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
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
/* Variables CSS inspirées de style1.css */
:root {
  --primary-color: #4a304d;      /* Violet foncé */
  --secondary-color: #7c5295;    /* Violet moyen */
  --accent-color: #c49b66;       /* Or/Bronze */
  --dark-color: #2d2327;         /* Brun très foncé */
  --light-color: #f7f3ee;        /* Beige clair */
  --text-color: #362f35;         /* Texte principal sombre */
  --light-text: #f7f3ee;         /* Texte clair */
  --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  --transition: all 0.3s ease;
  --border-radius: 8px;
  --max-width: 1200px;
  --overlay-dark: rgba(45, 35, 39, 0.7);
  
  /* Police unifiée pour tout le site */
  --main-font: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
}

/* Reset et base */
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

/* Application de la police principale à tous les éléments typographiques */
h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
  font-family: var(--main-font);
  letter-spacing: 0.3px;
  color: var(--primary-color);
}

/* Liens */
a {
  color: var(--secondary-color);
  transition: var(--transition);
  text-decoration: none;
}

a:hover {
  color: var(--primary-color);
}

/* Conteneur principal */
#content {
  width: 100%;
  min-height: 100vh;
  transition: all 0.3s ease;
  padding: 30px;
  max-width: var(--max-width);
  margin: 0 auto;
}

/* Cards */
.card {
  background-color: rgba(255, 255, 255, 0.95);
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  border: none;
  margin-bottom: 20px;
  transition: transform 0.3s;
  overflow: hidden;
  border: 1px solid rgba(196, 155, 102, 0.1);
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
  border-color: var(--accent-color);
}

.card-header {
  background-color: white;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  padding: 1rem;
}

.card-header h5 {
  margin-bottom: 0;
  color: var(--primary-color);
}

/* Statistiques */
.stats-card {
  background-color: white;
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 1px solid rgba(196, 155, 102, 0.1);
}

.stats-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
  border-color: var(--accent-color);
}

.stats-card .icon {
  color: var(--accent-color);
  background-color: rgba(196, 155, 102, 0.1);
}

.stats-card h6 {
  color: var(--secondary-color);
  font-weight: 500;
}

.stats-card h2 {
  color: var(--primary-color);
  font-weight: 700;
}

/* Search Box */
.search-box {
  background-color: rgba(255, 255, 255, 0.95);
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  padding: 20px;
  margin-bottom: 20px;
  border: 1px solid rgba(196, 155, 102, 0.1);
}

.search-box h5 {
  color: var(--primary-color);
}

.search-box input {
  border: 1px solid rgba(196, 155, 102, 0.3);
  padding: 0.75rem 1rem;
  border-radius: var(--border-radius);
  font-family: var(--main-font);
}

.search-box input:focus {
  border-color: var(--accent-color);
  box-shadow: 0 0 0 0.2rem rgba(196, 155, 102, 0.25);
}

/* Boutons */
.btn-primary {
  background-color: var(--secondary-color);
  border-color: var(--secondary-color);
  color: var(--light-text);
  font-family: var(--main-font);
  transition: var(--transition);
  border-radius: var(--border-radius);
  padding: 0.5rem 1rem;
}

.btn-primary:hover {
  background-color: var(--primary-color);
  border-color: var(--primary-color);
  transform: translateY(-2px);
}

.btn-warning {
  background-color: var(--accent-color);
  border-color: var(--accent-color);
  color: var(--dark-color);
  font-weight: 600;
}

.btn-warning:hover {
  background-color: #d1ae7a;
  border-color: #d1ae7a;
  color: var(--dark-color);
}

.btn-danger {
  background-color: #a83240;
  border-color: #a83240;
}

.btn-danger:hover {
  background-color: #8a2a35;
  border-color: #8a2a35;
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

/* Tableau */
.table-container {
  background-color: white;
  border-radius: var(--border-radius);
  overflow: hidden;
}

.custom-table {
  margin-bottom: 0;
  font-family: var(--main-font);
}

.custom-table th {
  background-color: rgba(124, 82, 149, 0.05);
  color: var(--primary-color);
  border-top: none;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}

.custom-table th a {
  color: var(--primary-color);
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.custom-table th a:hover {
  color: var(--accent-color);
}

.custom-table td {
  vertical-align: middle;
  border-color: rgba(196, 155, 102, 0.1);
  padding: 0.75rem;
}

.custom-table tbody tr {
  transition: var(--transition);
}

.custom-table tbody tr:hover {
  background-color: rgba(196, 155, 102, 0.05);
}

/* Badges */
.badge {
  font-family: var(--main-font);
  padding: 0.5rem 0.75rem;
  font-weight: 500;
  border-radius: 30px;
}

.bg-primary {
  background-color: var(--secondary-color) !important;
}

.stock-badge {
  font-weight: bold;
  padding: 5px 10px;
  border-radius: var(--border-radius);
  font-size: 0.85rem;
  display: inline-block;
}

.stock-ok {
  background-color: rgba(46, 125, 50, 0.1);
  color: #2e7d32;
}

.stock-warning {
  background-color: rgba(255, 143, 0, 0.1);
  color: #ff8f00;
}

.stock-danger {
  background-color: rgba(198, 40, 40, 0.1);
  color: #c62828;
}

/* Images */
.img-cover {
  width: 60px;
  height: 80px;
  object-fit: cover;
  border-radius: var(--border-radius);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  border: 2px solid rgba(196, 155, 102, 0.2);
  transition: var(--transition);
}

tr:hover .img-cover {
  transform: scale(1.05);
  border-color: var(--accent-color);
}

/* Pagination */
.pagination {
  justify-content: center;
  margin-top: 20px;
}

.page-link {
  color: var(--secondary-color);
  border-color: rgba(196, 155, 102, 0.2);
  margin: 0 2px;
  border-radius: 4px;
  transition: var(--transition);
}

.page-link:hover {
  background-color: rgba(196, 155, 102, 0.1);
  color: var(--primary-color);
  border-color: var(--accent-color);
}

.page-item.active .page-link {
  background-color: var(--secondary-color);
  border-color: var(--secondary-color);
}

.page-item.disabled .page-link {
  color: #ccc;
}

/* Description */
.description-text {
  max-height: 80px;
  overflow-y: auto;
  font-size: 0.9rem;
  color: var(--text-color);
  font-style: italic;
  padding-right: 5px;
}

.description-text::-webkit-scrollbar {
  width: 4px;
}

.description-text::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.description-text::-webkit-scrollbar-thumb {
  background: var(--accent-color);
  border-radius: 10px;
}

/* Alert Floating */
.alert-floating {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1050;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 300px;
  text-align: center;
  animation: fadeIn 0.5s, fadeOut 0.5s 3.5s forwards;
  font-family: var(--main-font);
  border-radius: var(--border-radius);
  border: none;
}

.alert-success {
  background-color: #d1fae5;
  color: #047857;
  border-left: 4px solid #047857;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translate(-50%, -20px); }
  to { opacity: 1; transform: translate(-50%, 0); }
}

@keyframes fadeOut {
  from { opacity: 1; transform: translate(-50%, 0); }
  to { opacity: 0; transform: translate(-50%, -20px); }
}

/* Modal de confirmation */
.modal-confirm .modal-content {
  border-radius: var(--border-radius);
  border: none;
  font-family: var(--main-font);
}

.modal-confirm .icon-box {
  width: 80px;
  height: 80px;
  margin: 0 auto;
  border-radius: 50%;
  z-index: 9;
  text-align: center;
  border: 3px solid #a83240;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-confirm .icon-box i {
  color: #a83240;
  font-size: 46px;
}

.modal-confirm .btn {
  border-radius: var(--border-radius);
  font-family: var(--main-font);
  transition: var(--transition);
  padding: 8px 20px;
}

.modal-confirm .btn-secondary {
  background-color: #c1c1c1;
  border-color: #c1c1c1;
}

.modal-confirm .btn-secondary:hover {
  background-color: #a8a8a8;
  border-color: #a8a8a8;
}

.modal-confirm .modal-header {
  padding: 2rem 1rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  border: none;
}

.modal-confirm .modal-title {
  color: var(--primary-color);
  font-weight: 600;
  margin-top: 1rem;
}

.modal-confirm .modal-body {
  padding: 1rem 2rem;
}

.modal-confirm .modal-footer {
  border: none;
  padding: 1rem 2rem 2rem;
}

/* Footer */
footer {
  margin-top: auto;
  background-color: transparent;
  color: var(--light-text);
  text-align: center;
  font-family: var(--main-font);
  padding: 2rem 0 1rem;
}

footer p {
  opacity: 0.8;
  font-size: 0.9rem;
}

/* Retirer les barres de défilement latérales dans les petits éléments */
.description-text::-webkit-scrollbar {
  width: 4px;
}

.description-text::-webkit-scrollbar-thumb {
  background: var(--accent-color);
  border-radius: 10px;
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
            <h1 class="h3 mb-0 text-white"><i class="fas fa-book me-2 text-primary"></i>Gestion des Livres</h1>
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
                    <p class="m-0">Biblio © <?= date('Y') ?> | Tous droits réservés</p>
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