<?php
/**
 * Blog Index - E-Graphisme
 * Système de blog dynamique
 */
require_once 'php/config.php';

// Configuration du blog
$blog_title = 'Blog - E-Graphisme';
$blog_description = 'Actualités, conseils et tendances du design graphique et web';

// Articles du blog (simulés - à remplacer par une base de données)
$articles = [
    [
        'id' => 1,
        'title' => 'Les tendances design graphique en 2026',
        'slug' => 'tendances-design-2026',
        'excerpt' => 'Découvrez les principales tendances qui vont marquer le monde du design graphique cette année.',
        'content' => 'Le design graphique évolue constamment. En 2026, plusieurs tendances se dégagent...',
        'category' => 'Tendances',
        'date' => '2026-04-15',
        'author' => 'E-Graphisme',
        'image' => 'https://via.placeholder.com/600x400/6366f1/ffffff?text=Design+2026',
        'featured' => true
    ],
    [
        'id' => 2,
        'title' => 'Comment créer une identité visuelle forte',
        'slug' => 'creer-identite-visuelle',
        'excerpt' => 'L\'identité visuelle est essentielle pour toute entreprise. Voici nos conseils pour la créer.',
        'content' => 'Une identité visuelle forte permet de se différencier de la concurrence...',
        'category' => 'Conseil',
        'date' => '2026-04-10',
        'author' => 'E-Graphisme',
        'image' => 'https://via.placeholder.com/600x400/f472b6/ffffff?text=Identité',
        'featured' => false
    ],
    [
        'id' => 3,
        'title' => 'L\'importance du web design responsive',
        'slug' => 'web-design-responsive',
        'excerpt' => 'Avec plus de 60% du trafic web sur mobile, le design responsive est devenu indispensable.',
        'content' => 'Le web design responsive permet d\'adapter un site à tous les écrans...',
        'category' => 'Web Design',
        'date' => '2026-04-05',
        'author' => 'E-Graphisme',
        'image' => 'https://via.placeholder.com/600x400/22d3ee/ffffff?text=Responsive',
        'featured' => false
    ],
    [
        'id' => 4,
        'title' => 'Les erreurs à éviter en print design',
        'slug' => 'erreurs-print-design',
        'excerpt' => 'Le print design présente des défis uniques. Évitez ces erreurs courantes.',
        'content' => 'Le print design nécessite une attention particulière aux détails techniques...',
        'category' => 'Print',
        'date' => '2026-03-28',
        'author' => 'E-Graphisme',
        'image' => 'https://via.placeholder.com/600x400/10b981/ffffff?text=Print',
        'featured' => false
    ]
];

// Catégories uniques
$categories = array_unique(array_column($articles, 'category'));

// Filtrer par catégorie si sélectionné
$selected_category = $_GET['category'] ?? null;
if ($selected_category && in_array($selected_category, $categories)) {
    $articles = array_filter($articles, fn($a) => $a['category'] === $selected_category);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $blog_description; ?>">
    <title><?php echo $blog_title; ?></title>
    
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/extra.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Blog Styles */
        .blog-hero {
            padding: 150px 0 80px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            text-align: center;
        }
        
        .blog-hero h1 {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .blog-hero h1 span {
            background: linear-gradient(135deg, #6366f1 0%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .blog-hero p {
            color: #64748b;
            font-size: 1.2rem;
        }
        
        /* Category Filter */
        .category-filter {
            padding: 30px 0;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 70px;
            z-index: 50;
        }
        
        .category-filter .container {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .category-btn {
            padding: 10px 25px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .category-btn:hover, .category-btn.active {
            background: linear-gradient(135deg, #6366f1 0%, #f472b6 100%);
            color: white;
            border-color: transparent;
        }
        
        /* Blog Grid */
        .blog-section {
            padding: 80px 0;
            background: #f8fafc;
        }
        
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .blog-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        
        .blog-card-image {
            height: 220px;
            overflow: hidden;
        }
        
        .blog-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .blog-card:hover .blog-card-image img {
            transform: scale(1.1);
        }
        
        .blog-card-content {
            padding: 25px;
        }
        
        .blog-card-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        
        .blog-card-meta i {
            margin-right: 5px;
        }
        
        .blog-card-category {
            display: inline-block;
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 12px;
        }
        
        .blog-card h3 {
            font-size: 1.3rem;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        
        .blog-card h3 a {
            color: #1e1e2f;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .blog-card h3 a:hover {
            color: #6366f1;
        }
        
        .blog-card p {
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .blog-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .read-more {
            color: #6366f1;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: gap 0.3s;
        }
        
        .read-more:hover {
            gap: 15px;
        }
        
        .blog-card-footer span {
            font-size: 0.85rem;
            color: #94a3b8;
        }
        
        /* Featured Article */
        .featured-article {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
        }
        
        .featured-image {
            height: 100%;
            min-height: 400px;
        }
        
        .featured-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .featured-content {
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .featured-content h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        
        .featured-content h2 a {
            color: #1e1e2f;
            text-decoration: none;
        }
        
        .featured-content p {
            color: #64748b;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        
        /* Pagination */
        .blog-pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
        }
        
        .page-num {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            font-weight: 500;
            color: #64748b;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .page-num:hover, .page-num.active {
            background: linear-gradient(135deg, #6366f1 0%, #f472b6 100%);
            color: white;
            border-color: transparent;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .featured-article {
                grid-template-columns: 1fr;
            }
            
            .featured-image {
                min-height: 300px;
            }
        }
        
        @media (max-width: 768px) {
            .blog-hero h1 {
                font-size: 2rem;
            }
            
            .blog-grid {
                grid-template-columns: 1fr;
            }
            
            .featured-content {
                padding: 30px;
            }
            
            .featured-content h2 {
                font-size: 1.5rem;
            }
            
            .blog-card-content {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .blog-hero {
                padding: 120px 0 60px;
            }
            
            .blog-hero h1 {
                font-size: 1.6rem;
            }
            
            .blog-hero p {
                font-size: 1rem;
            }
            
            .category-filter {
                top: 60px;
                padding: 20px 0;
            }
            
            .category-btn {
                padding: 8px 16px;
                font-size: 0.8rem;
            }
            
            .blog-section {
                padding: 50px 0;
            }
            
            .featured-image {
                min-height: 200px;
            }
            
            .featured-content {
                padding: 20px;
            }
            
            .featured-content h2 {
                font-size: 1.3rem;
            }
            
            .featured-content p {
                font-size: 1rem;
            }
            
            .blog-card-meta {
                flex-direction: column;
                gap: 5px;
            }
            
            .blog-pagination {
                gap: 5px;
            }
            
            .page-num {
                width: 35px;
                height: 35px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.html" class="logo">E-<span>Graphisme</span></a>
            <ul class="nav-links">
                <li><a href="index.html">Accueil</a></li>
                <li><a href="services.html">Services</a></li>
                <li><a href="portfolio.html">Portfolio</a></li>
                <li><a href="about.html">À propos</a></li>
                <li><a href="blog.html" class="active">Blog</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Blog Hero -->
    <section class="blog-hero">
        <div class="container">
            <h1>Notre <span>Blog</span></h1>
            <p><?php echo $blog_description; ?></p>
        </div>
    </section>

    <!-- Category Filter -->
    <div class="category-filter">
        <div class="container">
            <a href="blog.php" class="category-btn <?php echo !$selected_category ? 'active' : ''; ?>">Tous</a>
            <?php foreach ($categories as $cat): ?>
                <a href="blog.php?category=<?php echo urlencode($cat); ?>" 
                   class="category-btn <?php echo $selected_category === $cat ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Blog Section -->
    <section class="blog-section">
        <div class="container">
            <!-- Featured Article -->
            <?php 
            $featured = array_filter($articles, fn($a) => $a['featured']);
            if (!empty($featured) && !$selected_category): 
                $featured = reset($featured);
            ?>
                <article class="featured-article">
                    <div class="featured-image">
                        <img src="<?php echo $featured['image']; ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
                    </div>
                    <div class="featured-content">
                        <span class="blog-card-category"><?php echo htmlspecialchars($featured['category']); ?></span>
                        <h2><a href="blog-post.php?slug=<?php echo $featured['slug']; ?>"><?php echo htmlspecialchars($featured['title']); ?></a></h2>
                        <div class="blog-card-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($featured['date'])); ?></span>
                            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($featured['author']); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($featured['excerpt']); ?></p>
                        <a href="blog-post.php?slug=<?php echo $featured['slug']; ?>" class="btn btn-primary">
                            Lire l'article <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            <?php endif; ?>

            <!-- Blog Grid -->
            <div class="blog-grid">
                <?php foreach ($articles as $article): ?>
                    <?php if (!$article['featured'] || $selected_category): ?>
                        <article class="blog-card">
                            <div class="blog-card-image">
                                <img src="<?php echo $article['image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                            </div>
                            <div class="blog-card-content">
                                <span class="blog-card-category"><?php echo htmlspecialchars($article['category']); ?></span>
                                <h3><a href="blog-post.php?slug=<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                                <div class="blog-card-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($article['date'])); ?></span>
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author']); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($article['excerpt']); ?></p>
                                <div class="blog-card-footer">
                                    <a href="blog-post.php?slug=<?php echo $article['slug']; ?>" class="read-more">
                                        Lire la suite <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="blog-pagination">
                <a href="#" class="page-num active">1</a>
                <a href="#" class="page-num">2</a>
                <a href="#" class="page-num">3</a>
                <a href="#" class="page-num"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <a href="#" class="logo">E-<span>Graphisme</span></a>
                    <p>Design graphique & web professionnel</p>
                </div>
                <div class="footer-links">
                    <h4>Liens Rapides</h4>
                    <ul>
                        <li><a href="index.html">Accueil</a></li>
                        <li><a href="services.html">Services</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-newsletter">
                    <h4>Newsletter</h4>
                    <p>Inscrivez-vous pour recevoir nos Actualités</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Votre email">
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 E-Graphisme. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <script src="js/chat-widget.js"></script>
</body>
</html>
