<?php
/**
 * Générateur de Sitemap XML Dynamique - E-Graphisme
 */
require_once __DIR__ . '/config.php';

// Configuration du sitemap
$config = [
    'base_url' => 'https://ilariondossouyovo.github.io/E-Graphisme',
    'changefreq' => 'weekly',
    'priority' => [
        'home' => 1.0,
        'page' => 0.8,
        'post' => 0.6,
        'category' => 0.5
    ]
];

// Pages du site
$pages = [
    ['loc' => '', 'changefreq' => 'daily', 'priority' => 1.0], // Accueil
    ['loc' => 'services.html', 'changefreq' => 'monthly', 'priority' => 0.8],
    ['loc' => 'portfolio.html', 'changefreq' => 'weekly', 'priority' => 0.9],
    ['loc' => 'about.html', 'changefreq' => 'monthly', 'priority' => 0.7],
    ['loc' => 'blog.php', 'changefreq' => 'daily', 'priority' => 0.8],
    ['loc' => 'contact.html', 'changefreq' => 'monthly', 'priority' => 0.7],
    ['loc' => 'privacy.html', 'changefreq' => 'yearly', 'priority' => 0.3],
    ['loc' => 'terms.html', 'changefreq' => 'yearly', 'priority' => 0.3]
];

// Articles du blog (simulés)
$articles = [
    ['loc' => 'tendances-design-2026', 'date' => '2026-04-15'],
    ['loc' => 'creer-identite-visuelle', 'date' => '2026-04-10'],
    ['loc' => 'web-design-responsive', 'date' => '2026-04-05'],
    ['loc' => 'erreurs-print-design', 'date' => '2026-03-28']
];

// Début du XML
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Ajouter les pages
foreach ($pages as $page) {
    $loc = $config['base_url'] . ($page['loc'] ? '/' . $page['loc'] : '');
    $changefreq = $page['changefreq'] ?? $config['changefreq'];
    $priority = $page['priority'] ?? 0.8;
    
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . htmlspecialchars($loc) . '</loc>' . "\n";
    $xml .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
    $xml .= '    <priority>' . $priority . '</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Ajouter les articles du blog
foreach ($articles as $article) {
    $loc = $config['base_url'] . '/blog-post.php?slug=' . $article['loc'];
    $lastmod = $article['date'];
    
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . htmlspecialchars($loc) . '</loc>' . "\n";
    $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
    $xml .= '    <changefreq>weekly</changefreq>' . "\n";
    $xml .= '    <priority>0.6</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Fin du XML
$xml .= '</urlset>';

// Headers pour XML
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: max-age=86400'); // Cache 24h

echo $xml;
