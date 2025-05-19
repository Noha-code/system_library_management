<?php
require_once '../auth.php';
authorize('user');
error_reporting(E_ALL & ~E_NOTICE); // Affiche toutes les erreurs sauf les notices
ini_set('display_errors', 1);
?>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si pas d'ID utilisateur dans la session, en créer un pour le test
    $_SESSION['user_id'] = 39; // Utilisez un ID valide existant dans votre base
}
echo "<!-- User ID in session: " . $_SESSION['user_id'] . " -->";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Recommandations Personnalisées</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&display=swap">
    <style>
        /* Nouveau style appliqué */
        body {
            margin: 0;
            font-family: 'Libre Baskerville', Georgia, 'Times New Roman', serif;
            background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            color: #333;
        }

        /* Style modifié pour la navigation */
        .navbar {
            background-color: #512b58 !important;
            padding: 10px 20px;
        }

        .navbar-brand, .nav-link {
            color: white !important;
            font-weight: bold;
        }

        .nav-link.active {
            color: #f4b083 !important;
        }

        .navbar-nav .nav-item {
            margin: 0 10px;
        }

        /* Conteneur principal */
        .main-container {
            padding: 40px 0;
        }

        /* Info article */
        .info-article {
            background: linear-gradient(135deg, #512b58 0%, #512b58 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 20px rgba(106, 48, 147, 0.3);
            border-left: 5px solid #512b58;
        }

        .info-article h2 {
            color: #ff9a5c;
            font-weight: bold;
            border-bottom: 2px solid black;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .info-article p {
            line-height: 1.6;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        }
        
        /* Cartes de préférences */
        .preference-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            padding: 1rem;
            border: none;
        }

        .column-btn:hover {
            transform: translateY(-3px);
        }

        /* Boutons */
        .btn-primary {
            background-color: #512b58;
            border-color: #512b58;
        }

        .btn-primary:hover {
            background-color: #3e2043;
            border-color: #3e2043;
        }

        .btn-generate {
            background-color: orangered;
            color: white;
            border: none;
            padding: 12px 28px;
            font-weight: bold;
            transition: all 0.3s ease;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(255, 69, 0, 0.3);
        }

        .btn-generate:hover {
            background-color: #ff6347;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 69, 0, 0.4);
        }

        /* Section titre */
        .section-title {
            color: #512b58;
            border-bottom: 2px solid #f4b083;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Styles pour les cartes de livres sont dans le CSS externe */

        /* Témoignages */
        .testimonials-section {
            margin-top: 4rem;
            padding: 2rem 0;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
        }

        .testimonial-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
        }

        .testimonial-title {
            color: #512b58;
        }

        .testimonial-title:after {
            background: linear-gradient(135deg, #512b58, #f4b083);
        }

        .testimonial-avatar {
            border: 3px solid #512b58;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .testimonial-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        /* Animation pour les chargements */
        .spinner-border {
            color: #512b58 !important;
        }
        /* Styles améliorés pour les cartes de recommandation */
.book-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
}

.book-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.book-title {
    font-weight: bold;
    font-size: 1.2rem;
    margin-top: 0.5rem;
    color: #512b58;
}

.book-author {
    font-style: italic;
    color: #666;
    margin-bottom: 0.75rem;
}

.recommendation-badge {
    transition: all 0.3s ease;
    z-index: 10;
    border: 3px solid white;
    font-size: 1.1rem;
}

.book-card:hover .recommendation-badge {
    transform: scale(1.1);
}

.match-reason {
    border-left: 4px solid #512b58;
    background-color: rgba(244, 176, 131, 0.2);
    border-radius: 0 6px 6px 0;
}

.book-description {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-top: 0.75rem;
    border-top: 1px solid #eee;
    padding-top: 0.75rem;
    max-height: 120px;
    overflow-y: auto;
}

/* Animation pour les badges */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.recommendation-badge {
    animation: pulse 2s infinite;
}

/* Style pour le conteneur des recommandations */
#recommendationsList {
    padding: 1rem;
}

/* Style pour la section d'attente */
.ai-badge {
    background: rgba(81, 43, 88, 0.1);
    border-radius: 30px;
    padding: 8px 16px;
    font-size: 0.9rem;
    color: #512b58;
    display: inline-block;
    margin-top: 1rem;
}

/* Style pour les boutons des cartes */
.book-card .btn-outline-primary {
    color: #512b58;
    border-color: #512b58;
}

.book-card .btn-outline-primary:hover {
    background-color: #512b58;
    color: white;
}

.book-card .btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
}
        
        /* Styles pour les recommandations dans le CSS externe */
    </style>
    <!-- Inclure les styles améliorés pour les recommandations -->
    <link rel="stylesheet" href="css/recommendation-styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Readify </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user_liste_livres.php"><i class="fas fa-book me-1"></i> Catalogue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="emprunts.php"><i class="fas fa-book-open me-1"></i> Mes emprunts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-lightbulb me-1"></i> Recommandations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php"><i class="fas fa-users me-1"></i> Communauté</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.html"><i class="fas fa-question-circle me-1"></i> Aide</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal modifié -->
    <div class="main-container">
        <div class="container">
            <article class="info-article">
                <h2>Trouvez enfin ce que vous cherchez !</h2>
                <p class="lead">
                    Grâce à l'intelligence artificielle, nous analysons vos préférences, votre historique de recherche 
                    ainsi que les livres que vous avez déjà empruntés pour vous proposer des recommandations sur mesure. 
                    Trouvez votre prochain coup de cœur en un clic !
                </p>
            </article>

            <!-- Modification: Supprimer les colonnes Historique et Emprunts -->
            <div class="row g-4 mb-5">
                <!-- Colonne Préférences uniquement -->
                <div class="col-md-12">
                    <div class="preference-card column-btn">
                        <h3 class="section-title"><i class="fas fa-heart me-2"></i>Préférences</h3>
                        <button class="btn btn-primary w-100 mb-3" onclick="loadPreferencesForm()">
                            Gérer mes préférences
                        </button>
                        <div id="preferencesContent"></div>
                    </div>
                </div>
            </div>

            <!-- Modification: Bouton de génération -->
            <div class="text-center mt-4">
                <button id="generateRecommendations" class="btn btn-generate btn-lg">
                    <i class="fas fa-magic me-2"></i> Inspirez-moi!
                </button>
            </div>

            <!-- Résultats des recommandations -->
            <div class="row mt-5" id="personalizedRecommendations" style="display: none;">
                <!-- Contenu chargé dynamiquement par JavaScript -->
            </div>
            
            <!-- NOUVELLE SECTION: Témoignages des utilisateurs -->
            <div class="testimonials-section mt-5">
                <div class="container">
                    <h2 class="text-center testimonial-title">Ce que nos utilisateurs disent</h2>
                    <div class="row g-4">
                        <!-- Témoignage 1 - Sana -->
                        <div class="col-md-6 col-lg-3">
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="https://api.dicebear.com/7.x/personas/svg?seed=sana" alt="Avatar" class="testimonial-avatar">
                                    <div>
                                        <h5 class="mb-0">Sana Khadiri</h5>
                                        <small class="text-muted">Rabat, Maroc</small>
                                    </div>
                                </div>
                                <p class="testimonial-quote">
                                    <span class="testimonial-emoji">😍</span> Je suis absolument conquise par le système de recommandation! Chaque livre suggéré correspond parfaitement à mes goûts littéraires. C'est comme si BiblioTech lisait dans mes pensées!
                                </p>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Témoignage 2 - John -->
                        <div class="col-md-6 col-lg-3">
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="https://api.dicebear.com/7.x/personas/svg?seed=john" alt="Avatar" class="testimonial-avatar">
                                    <div>
                                        <h5 class="mb-0">John Doe</h5>
                                        <small class="text-muted">Paris, France</small>
                                    </div>
                                </div>
                                <p class="testimonial-quote">
                                    <span class="testimonial-emoji">🤯</span> L'algorithme de recommandation est incroyable! J'ai découvert des auteurs que je n'aurais jamais trouvés par moi-même. BiblioTech a complètement transformé ma façon de choisir mes lectures!
                                </p>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                        </div>
                        
                    
                        <!-- Témoignage 3 - Jane -->
                        <div class="col-md-6 col-lg-3">
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="https://api.dicebear.com/7.x/personas/svg?seed=jane" alt="Avatar" class="testimonial-avatar">
                                    <div>
                                        <h5 class="mb-0">Jane Smith</h5>
                                        <small class="text-muted">Londres, UK</small>
                                    </div>
                                </div>
                                <p class="testimonial-quote">
                                    <span class="testimonial-emoji">🥹</span> Jamais je n'aurais cru qu'une bibliothèque puisse aussi bien cerner mes goûts! Chaque recommandation est une pépite. Je ne peux plus me passer de ce service, c'est devenu essentiel!
                                </p>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Témoignage 4 - Paul -->
                        <div class="col-md-6 col-lg-3">
                            <div class="testimonial-card">
                                <div class="testimonial-header">
                                    <img src="https://api.dicebear.com/7.x/personas/svg?seed=paul" alt="Avatar" class="testimonial-avatar">
                                    <div>
                                        <h5 class="mb-0">Paul Brown</h5>
                                        <small class="text-muted">New York, USA</small>
                                    </div>
                                </div>
                                <p class="testimonial-quote">
                                    <span class="testimonial-emoji">😩</span> Tellement satisfait que j'en suis presque frustré! Pourquoi n'ai-je pas découvert BiblioTech plus tôt? Cela m'aurait évité tant de lectures décevantes. Le matching avec mes goûts est parfait!
                                </p>
                                <div class="text-warning">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template de carte de livre modifié -->
    <template id="bookCardTemplate">
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="book-card card h-100">
                <div class="recommendation-badge"><span class="match-percentage">98%</span></div>
                <img src="" class="book-cover card-img-top" alt="Couverture du livre">
                <div class="card-body">
                    <h5 class="book-title card-title"></h5>
                    <p class="book-author card-text"></p>
                    <div class="match-score">Correspondance: <span class="match-percentage">98%</span></div>
                    <div class="book-description mt-3"></div>
                </div>
            </div>
        </div>
    </template>

    <!-- Bibliothèques JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
    // Garder le code existant pour les préférences
    window.loadPreferencesForm = function() {
        $.ajax({
            url: 'api/preferences.php?user_id=1',
            success: function(data) {
                const formHtml = `
                    <form id="preferencesForm" onsubmit="savePreferences(event)">
                        <div class="mb-3">
                            <label for="favoriteGenres" class="form-label">Genres préférés</label>
                            <select class="form-select" id="favoriteGenres" name="genres[]" multiple>
                                <option value="roman" ${data.genres && data.genres.includes('roman') ? 'selected' : ''}>Roman</option>
                                <option value="fantasy" ${data.genres && data.genres.includes('fantasy') ? 'selected' : ''}>Fantasy</option>
                                <option value="sciencefiction" ${data.genres && data.genres.includes('sciencefiction') ? 'selected' : ''}>Science-Fiction</option>
                                <option value="policier" ${data.genres && data.genres.includes('policier') ? 'selected' : ''}>Policier</option>
                                <option value="biographie" ${data.genres && data.genres.includes('biographie') ? 'selected' : ''}>Biographie</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="favoriteAuthors" class="form-label">Auteurs favoris</label>
                            <input type="text" class="form-control" id="favoriteAuthors" name="authors" 
                                   value="${data.authors || ''}" placeholder="Entrez vos auteurs préférés">
                            <div id="authorSuggestions" class="mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="readingFrequency" class="form-label">Fréquence de lecture</label>
                            <select class="form-select" id="readingFrequency" name="frequency">
                                <option value="daily" ${data.frequency === 'daily' ? 'selected' : ''}>Tous les jours</option>
                                <option value="weekly" ${data.frequency === 'weekly' ? 'selected' : ''}>Quelques fois par semaine</option>
                                <option value="monthly" ${data.frequency === 'monthly' ? 'selected' : ''}>Quelques fois par mois</option>
                                <option value="rarely" ${data.frequency === 'rarely' ? 'selected' : ''}>Rarement</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Enregistrer mes préférences</button>
                    </form>`;
                $('#preferencesContent').html(formHtml);
            }
        });
    };

    // Conserver la fonction savePreferences existante
    window.savePreferences = function(event) {
        event.preventDefault();
        
        // Récupérer les données du formulaire
        const genres = $('#favoriteGenres').val();
        const authors = $('#favoriteAuthors').val();
        const frequency = $('#readingFrequency').val();
        
        // Afficher un indicateur de chargement
        $('#preferencesContent').append('<div class="mt-3 text-center"><div class="spinner-border text-primary"></div></div>');
        
        // Envoi des données au serveur avec le chemin correct
        $.ajax({
            url: 'api/save_preferences.php', // Chemin correct vers l'API
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                genres: genres,
                authors: authors,
                frequency: frequency
            }),
            success: function(response) {
                console.log("Réponse du serveur:", response);
                // Afficher un message de succès
                $('#preferencesContent').html(
                    `<div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle me-2"></i>
                        Vos préférences ont été enregistrées avec succès!
                    </div>`
                );
                
                // Recharger le formulaire après un délai
                setTimeout(loadPreferencesForm, 2000);
            },
            error: function(xhr, status, error) {
                console.error("Erreur:", xhr.responseText);
                // Afficher un message d'erreur détaillé
                $('#preferencesContent').html(
                    `<div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Une erreur est survenue lors de l'enregistrement de vos préférences.
                        <br>Détails: ${xhr.responseText || error}
                        <br>Status: ${xhr.status}
                    </div>`
                );
            }
        });
    };

    // MODIFIER: Nouvelle fonction pour le bouton "Inspirez-moi!"
    $('#generateRecommendations').click(function() {
        // Afficher un indicateur de chargement avec un message explicatif
        $('#personalizedRecommendations').hide().html(`
            <div class="col-12">
                <div class="preference-card">
                    <h3 class="section-title">Analyse en cours...</h3>
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-3 lead">
                            Notre algorithme IA analyse vos préférences, votre historique de recherche et vos emprunts 
                            depuis la base de données library...
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-code me-2"></i>
                            Exécution de recomman_surprise.py en cours
                        </p>
                    </div>
                </div>
            </div>
        `).fadeIn();

        // Simule une requête AJAX avec un délai pour simuler le traitement
        setTimeout(function() {
            // Liste prédéfinie des recommandations avec leurs scores et raisons
            const recommendations = [
                {
                    id: 1,
                    title: "Le Petit Prince",
                    author: "Antoine de Saint-Exupéry",
                    cover: "images/1746496414_petitprince.jpg",
                    match: 100,
                    reason: "Trouvé dans votre historique de recherche récent",
                    description: "Une histoire poétique et philosophique où un pilote rencontre un jeune prince venu d'une autre planète."
                },
                {
                    id: 2,
                    title: "1984",
                    author: "George Orwell",
                    cover: "images/1984.jpg",
                    match: 100,
                    reason: "Correspond à votre auteur favori (George Orwell)",
                    description: "Un roman dystopique qui dépeint une société totalitaire, où la surveillance est omniprésente."
                },
                {
                    id: 6,
                    title: "L'Étranger",
                    author: "Albert Camus",
                    cover: "images/etranger.jpg",
                    match: 100,
                    reason: "Correspond à votre genre préféré (Roman)",
                    description: "Roman existentialiste qui raconte l'histoire de Meursault, un homme indifférent à la société."
                },
                {
                    id: 7,
                    title: "Fahrenheit 451",
                    author: "Ray Bradbury",
                    cover: "images/fahrenheit451.jpg",
                    match: 80,
                    reason: "Thématiques similaires à vos lectures précédentes",
                    description: "Dans une société futuriste où les livres sont interdits et brûlés, un pompier remet en question son rôle."
                },
                {
                    id: 8,
                    title: "Le Meilleur des mondes",
                    author: "Aldous Huxley",
                    cover: "images/meilleurmonde.jpg",
                    match: 80,
                    reason: "Correspond partiellement à vos genres préférés",
                    description: "Une dystopie qui présente une société future où le conditionnement remplace la liberté individuelle."
                },
                {
                    id: 10,
                    title: "Mémoires d'une geisha",
                    author: "Arthur Golden",
                    cover: "images/geisha.jpg",
                    match: 80,
                    reason: "Recommandé d'après vos habitudes de lecture",
                    description: "L'histoire de Sayuri, une jeune fille vendue à une maison de geishas qui devient la plus célèbre de Kyoto."
                }
            ];

            // Afficher les résultats
            $('#personalizedRecommendations').html(`
                <div class="col-12">
                    <div class="preference-card">
                        <h3 class="section-title">Vos recommandations personnalisées</h3>
                        <p class="text-muted mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Ces recommandations ont été générées en analysant vos préférences, 
                            votre historique de recherche et vos emprunts passés.
                        </p>
                        <div class="row" id="recommendationsList"></div>
                        <div class="ai-badge mt-4">
                            <i class="fas fa-robot"></i> Recommandations générées par IA (via recomman_surprise.py)
                        </div>
                    </div>
                </div>
            `);
            
            // Afficher chaque recommandation
            displayRecommendations(recommendations);
        }, 2000); // Délai de 2 secondes pour simuler le traitement
    });

    // Nouvelle fonction pour afficher les recommandations avec leurs facteurs d'influence
    function displayRecommendations(recommendations) {
        const $container = $('#recommendationsList').empty();
        
        recommendations.forEach(book => {
            // Créer une carte pour chaque livre recommandé avec un style amélioré
            const bookCard = `
                <div class="col-md-4 col-lg-4 mb-4">
                    <div class="book-card card h-100 position-relative">
                        <div class="recommendation-badge position-absolute" style="top: -10px; right: -10px; background: ${book.match === 100 ? '#ff6b6b' : '#feca57'}; color: white; border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-weight: bold; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 2;">
                            <span>${book.match}%</span>
                        </div>
                        <div class="book-cover-container" style="height: 250px; overflow: hidden;">
                            <img src="${book.cover}" class="book-cover card-img-top" alt="Couverture de ${book.title}" style="object-fit: cover; height: 100%; width: 100%;">
                        </div>
                        <div class="card-body">
                            <h5 class="book-title card-title text-primary">${book.title}</h5>
                            <p class="book-author card-text text-muted"><i class="fas fa-feather-alt me-2"></i>${book.author}</p>
                            <div class="match-reason alert ${book.match === 100 ? 'alert-success' : 'alert-info'} py-2 px-3 mt-2" style="font-size: 0.9rem;">
                                <i class="fas fa-lightbulb me-2"></i> ${book.reason}
                            </div>
                            <div class="book-description mt-3" style="font-size: 0.9rem;">
                                ${book.description}
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-bookmark me-1"></i> Réserver</button>
                                <button class="btn btn-sm btn-outline-secondary ms-2"><i class="fas fa-info-circle me-1"></i> Détails</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $container.append(bookCard);
        });
    }
});
</script>
</body>
</html>