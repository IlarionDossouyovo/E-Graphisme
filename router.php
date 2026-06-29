<?php
/**
 * E-Graphisme & E-Studio Router
 * Unified routing for both platforms
 * 
 * Routes:
 * /                 -> index.html (E-Graphisme home)
 * /studio           -> studio.html (E-Graphisme AI Studio)
 * /brand-analyzer   -> brand-analyzer.html
 * /dashboard        -> dashboard.html
 * /admin           -> admin/index.php
 * /blog            -> blog.html
 * /portfolio       -> portfolio.html
 * /services        -> services.html
 * /contact         -> contact.html
 * /about           -> about.html
 * /integration     -> README-INTEGRATION.md (this file)
 * 
 * E-Studio Routes:
 * /e-studio        -> Redirects to E-Studio platform
 * /video           -> video production
 * /motion          -> motion design
 * /content         -> content generation
 */

// Get the request URI
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);
$path = trim($path, '/');

// Remove directory traversal attempts
$path = str_replace('..', '', $path);

// Route mapping
$routes = [
    '' => 'index.html',
    'home' => 'index.html',
    'index' => 'index.html',
    'studio' => 'studio.html',
    'ai-studio' => 'studio.html',
    'brand-analyzer' => 'brand-analyzer.html',
    'brand' => 'brand-analyzer.html',
    'dashboard' => 'dashboard.html',
    'client' => 'dashboard.html',
    'admin' => 'admin/index.php',
    'blog' => 'blog.html',
    'news' => 'blog.html',
    'portfolio' => 'portfolio.html',
    'work' => 'portfolio.html',
    'services' => 'services.html',
    'offers' => 'services.html',
    'contact' => 'contact.html',
    'about' => 'about.html',
    'integration' => 'README-INTEGRATION.md',
    'docs' => 'README-INTEGRATION.md',
    // E-Studio Routes
    'e-studio' => 'studio.html',
    'video' => 'studio.html',
    'motion' => 'studio.html',
    'content' => 'studio.html',
    'production' => 'studio.html',
];

// Check for direct route match or serve file
if (isset($routes[$path])) {
    $path = $routes[$path];
}

$dir = dirname(__FILE__);
$file = $dir . '/' . $path;

// Serve the file if it exists
if (file_exists($file) && is_file($file)) {
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $types = [
        'html' => 'text/html',
        'htm' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'md' => 'text/markdown',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',
    ];
    
    header('Content-Type: ' . ($types[$ext] ?? 'text/html'));
    readfile($file);
    exit;
}

// Try serving from subdirectories
$subdirs = ['en', 'es', 'pt', 'css', 'js', 'images', 'db', 'php', 'admin'];
foreach ($subdirs as $subdir) {
    $file = $dir . '/' . $subdir . '/' . $path;
    if (file_exists($file) && is_file($file)) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        header('Content-Type: ' . ($types[$ext] ?? 'text/html'));
        readfile($file);
        exit;
    }
}

// 404 Not Found
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>404 - Page Non Trouvée</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0A0A0A; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { text-align: center; }
        h1 { font-size: 72px; color: #D4AF37; margin: 0; }
        p { font-size: 18px; color: #888; }
        a { color: #00B8FF; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <p>Page non trouvée</p>
        <p><a href="/">Retour à l'accueil</a></p>
    </div>
</body>
</html>