<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Library</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    <script>
        function checkLogin() {
            <?php if (!isset($_SESSION['user_id'])): ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        }
    </script>
</head>
<body>

<header>
   
    <nav>
        <ul>
            <li><a href="index.php"><i class="fas fa-home category-icon"></i>Home</a></li>
            <li><a href="#books" onclick="checkLogin()"><i class="fas fa-book category-icon"></i>My Books</a></li>
            <li><a href="#browse"><i class="fas fa-search category-icon"></i>Browse</a></li>
            <li><a href="about.php"><i class="fas fa-info-circle category-icon"></i>About us</a></li>
            <li><a href="#help"><i class="fas fa-question-circle category-icon"></i>Help</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php" class="btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="register.php" class="btn"><i class="fas fa-user-plus"></i> Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<section class="hero">
    <h1>Welcome to Readify</h1>
    <p>Search, borrow, and discover thousands of books!</p>
    <form action="index.php" method="GET" class="search-form">
        <div class="search-container">
            <input type="text" name="search" placeholder="Search for a book or author...">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </div>
        
    </form>
</section>

<!-- Section à ajouter après la section hero et avant la section books -->
<section class="statistics">
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <span class="stat-count" data-count="1000">0</span>
                <span class="stat-label">Available Books</span>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-count" data-count="1200">0</span>
                <span class="stat-label">Users number</span>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-download"></i>
            </div>
            <div class="stat-info">
                <span class="stat-count" data-count="15000">0</span>
                <span class="stat-label">Borrowed books</span>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <span class="stat-count" data-count="7.8">0</span>
                <span class="stat-label">Average rating</span>
            </div>
        </div>
    </div>
</section>

<section class="quote-section">
    <div class="quote-container">
        <i class="fas fa-book-open quote-icon"></i>
        <blockquote>
            <p>"Reading is dreaming with open eyes."</p>
            <cite>— Ralph Waldo Emerson</cite>
        </blockquote>
    </div>
</section>

<!-- Update to the books section in index.php -->
<section class="books" id="books">
    <div class="books-content">
        <h2><i class="fas fa-book-open"></i> Our Collection</h2>
        <div class="book-list">
            <?php
            include('db.php');

            $results_per_page = 12;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $start_from = ($page-1) * $results_per_page;

            if (isset($_GET['search'])) {
                $search = $_GET['search'];
                $genre = isset($_GET['genre']) ? $_GET['genre'] : '';
                $annee = isset($_GET['annee']) ? $_GET['annee'] : '';
                
                $query = "SELECT * FROM livres WHERE titre LIKE ? OR auteur LIKE ?";
                $params = ["%$search%", "%$search%"];
                
                if (!empty($genre)) {
                    $query .= " AND id_genre = ?";
                    $params[] = $genre;
                }
                
                if (!empty($annee)) {
                    $query .= " AND annee_parution >= ?";
                    $params[] = $annee;
                }
                
                $query .= " LIMIT ?, ?";
                $params[] = $start_from;
                $params[] = $results_per_page;
                
                $stmt = $conn->prepare($query);
                $types = str_repeat('s', count($params) - 2) . 'ii';
                $stmt->bind_param($types, ...$params);
            } else {
                $query = "SELECT * FROM livres ORDER BY RAND() LIMIT ?, ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $start_from, $results_per_page);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='book'>
                          <img src='" . htmlspecialchars($row['image']) . "' alt='Book cover' loading='lazy'>
                          <div class='book-content'>
                              <h3>" . htmlspecialchars($row['titre']) . "</h3>
                              <p>Author: " . htmlspecialchars($row['auteur']) . "</p>
                              <div class='book-buttons'>
                                  <a href='login.php?id=" . $row['id_livre'] . "' class='btn'><i class='fas fa-book-reader'></i> Details</a>
                                  <a href='#' class='btn' onclick='checkLogin()'><i class='fas fa-bookmark'></i> Borrow</a>
                              </div>
                          </div>
                      </div>";
                }
            } else {
                echo "<div class='no-results'>No books found. Try different search terms.</div>";
            }

            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM livres");
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $total_pages = ceil($row['total'] / $results_per_page);
            
            echo "<div class='pagination'>";
            for ($i = 1; $i <= $total_pages; $i++) {
                echo "<a href='?page=$i' class='page-link " . ($page == $i ? "active" : "") . "'>$i</a>";
            }
            echo "</div>";
            ?>
        </div>
    </div>
</section>
<section id="browse" class="browse">
    <h2><i class="fas fa-compass"></i> Explore Categories</h2>
    <div class="categories">
        <a href="login.php" class="category-card">
            <i class="fas fa-book category-icon"></i>
            <span>Fantastique</span>
        </a>
        <a href="login.php" class="category-card">
            <i class="fas fa-rocket category-icon"></i>
            <span>Science Fiction</span>
        </a>
        <a href="login.php" class="category-card">
            <i class="fas fa-user category-icon"></i>
            <span>Biographies</span>
        </a>
        <a href="login.php" class="category-card">
            <i class="fas fa-graduation-cap category-icon"></i>
            <span>Essai</span>
        </a>
    </div>
</section>

<section id="help" class="help">
    <h2><i class="fas fa-life-ring"></i> Help & Support</h2>
    <p>Need help? Check out our <a href="faq.html">FAQ</a> or contact us below.</p>
    
    <?php if (isset($_SESSION['contact_success'])): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i> Your message has been sent successfully! We'll get back to you shortly.
        </div>
        <?php unset($_SESSION['contact_success']); ?>
    <?php endif; ?>
    
    <form action="contact.php" method="POST" class="contact-form">
        <input type="text" name="nom" placeholder="Your Name" required aria-label="Your name">
        <input type="email" name="email" placeholder="Your Email" required aria-label="Your email">
        <textarea name="message" placeholder="Your Message" rows="5" required aria-label="Your message"></textarea>
        <button type="submit" name="contact_submit"><i class="fas fa-paper-plane"></i> Send</button>
    </form>
</section>

<?php if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <div class="admin-panel">
        <h3><i class="fas fa-cog"></i> Administration</h3>
        <ul>
            <li><a href="liste_livres.php"><i class="fas fa-list"></i> Gérer les livres</a></li>
            <li><a href="ajouter_livre.php"><i class="fas fa-plus"></i> Ajouter un livre</a></li>
            <li><a href="gestion_utilisateurs.php"><i class="fas fa-users"></i> Gérer les utilisateurs</a></li>
        </ul>
    </div>
<?php endif; ?>

<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h3>My Library</h3>
            <p>Your online library accessible anytime.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="#books">My Books</a></li>
                <li><a href="#browse">Browse</a></li>
                <li><a href="about.php">About us</a></li>
                <li><a href="#help">Help</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact</h3>
            <p><i class="fas fa-envelope"></i> madiliikenza@gmail.com</p>
            <p><i class="fas fa-phone"></i> +212617916252</p>
            <p><i class="fas fa-home"></i> Avenue Abdelkrim Alkhatabi , Gueliz, Marrakech</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> My Library - All rights reserved</p>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 1000);
            }, 5000);
        });
    });
</script>
<!-- Script pour l'animation des compteurs à ajouter avant la fermeture du body -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour animer les compteurs
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-count');
            const speed = 200; // Vitesse de l'animation (plus petit = plus rapide)
            
            counters.forEach(counter => {
                const target = parseFloat(counter.getAttribute('data-count'));
                const isDecimal = target % 1 !== 0;
                const increment = target / speed;
                let count = 0;
                
                const updateCount = () => {
                    if (count < target) {
                        count += increment;
                        if (count > target) count = target;
                        
                        if (isDecimal) {
                            counter.innerText = count.toFixed(1);
                        } else {
                            counter.innerText = Math.floor(count);
                        }
                        
                        setTimeout(updateCount, 1);
                    }
                };
                
                updateCount();
            });
        }
        
        // Fonction pour vérifier si un élément est visible dans le viewport
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
        
        // Lancer l'animation quand la section est visible
        const statsSection = document.querySelector('.statistics');
        let animationStarted = false;
        
        function checkScroll() {
            if (!animationStarted && isElementInViewport(statsSection)) {
                animateCounters();
                animationStarted = true;
                window.removeEventListener('scroll', checkScroll);
            }
        }
        
        window.addEventListener('scroll', checkScroll);
        checkScroll(); // Vérifier immédiatement au chargement
    });
</script>

</body>
</html>
