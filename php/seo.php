<?php
/**
 * SEO Optimizer
 * Meta tags, Open Graph, Twitter Cards management
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Generate meta tags
function generate_meta_tags($page) {
    $defaults = [
        'title' => 'E-Graphisme - Design Graphique & Web Professionnel',
        'description' => 'E-Graphisme - Votre partenaire pour le design graphique et le développement web professionnel. Création de sites web, identité visuelle, et solutions digitales.',
        'keywords' => 'design graphique, web design, création site web, identité visuelle, logo, branding',
        'author' => 'E-Graphisme',
        'robots' => 'index, follow'
    ];
    
    $pages = [
        'index' => [
            'title' => 'E-Graphisme - Design Graphique & Web Professionnel',
            'description' => 'Votre partenaire pour le design graphique et le développement web professionnel.'
        ],
        'studio' => [
            'title' => 'E-Studio - Production Vidéo IA',
            'description' => 'Plateforme de production vidéo propulsée par l intelligence artificielle.'
        ],
        'services' => [
            'title' => 'Nos Services - E-Graphisme',
            'description' => 'Découvrez nos services : création web, design graphique, branding.'
        ],
        'portfolio' => [
            'title' => 'Portfolio - E-Graphisme',
            'description' => 'Découvrez nos réalisations et projets.'
        ],
        'about' => [
            'title' => 'À propos - E-Graphisme',
            'description' => 'En savoir plus sur E-Graphisme.'
        ],
        'blog' => [
            'title' => 'Blog - E-Graphisme',
            'description' => 'actualités, tutoriels et conseils.'
        ],
        'contact' => [
            'title' => 'Contact - E-Graphisme',
            'description' => 'Contactez-nous pour votre projet web.'
        ]
    ];
    
    $data = isset($pages[$page]) ? array_merge($defaults, $pages[$page]) : $defaults;
    
    return [
        'title' => $data['title'] . ' | E-Graphisme',
        'description' => $data['description'],
        'keywords' => $data['keywords']
    ];
}

// Generate Open Graph tags
function generate_og_tags($page) {
    $og = [
        'title' => 'E-Graphisme',
        'description' => 'Design Graphique & Web Professionnel',
        'image' => '/images/og-image.png',
        'url' => 'https://ilariondossouyovo.github.io/E-Graphisme/',
        'type' => 'website'
    ];
    
    return $og;
}

// Generate Twitter Card tags
function generate_twitter_tags($page) {
    return [
        'card' => 'summary_large_image',
        'site' => '@egraphisme',
        'creator' => '@egraphisme',
        'title' => 'E-Graphisme',
        'description' => 'Design Graphique & Web Professionnel'
    ];
}

// Generate canonical URL
function generate_canonical($path = '') {
    $base = 'https://ilariondossouyovo.github.io/E-Graphisme';
    return $base . ($path ? '/' . ltrim($path, '/') : '/');
}

// Generate structured data (JSON-LD)
function generate_structured_data($type = 'organization') {
    $data = [
        'organization' => [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'E-Graphisme',
            'url' => 'https://ilariondossouyovo.github.io/E-Graphisme/',
            'logo' => 'https://ilariondossouyovo.github.io/E-Graphisme/images/logo.svg',
            'description' => 'Design Graphique & Web Professionnel'
        ],
        'localBusiness' => [
            '@context' => 'https://schema.org',
            '@type' => 'ProfessionalService',
            'name' => 'E-Graphisme',
            'image' => 'https://ilariondossouyovo.github.io/E-Graphisme/images/logo.svg',
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => 'FR'
            ],
            'priceRange' => '€€'
        ],
        'webSite' => [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'E-Graphisme',
            'url' => 'https://ilariondossouyovo.github.io/E-Graphisme/',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => 'https://ilariondossouyovo.github.io/E-Graphisme/search?q={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ]
        ]
    ];
    
    return isset($data[$type]) ? $data[$type] : $data['organization'];
}

// Generate breadcrumb JSON-LD
function generate_breadcrumb($items) {
    $list = [];
    $i = 1;
    
    foreach ($items as $name => $url) {
        $list[] = [
            '@type' => 'ListItem',
            'position' => $i++,
            'name' => $name,
            'item' => $url
        ];
    }
    
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list
    ];
}

// SEO audit function
function seo_audit($page_data) {
    $issues = [];
    
    // Check title
    if (empty($page_data['title'])) {
        $issues[] = ['severity' => 'error', 'issue' => 'Missing title tag'];
    } elseif (strlen($page_data['title']) < 30) {
        $issues[] = ['severity' => 'warning', 'issue' => 'Title too short'];
    }
    
    // Check description
    if (empty($page_data['description'])) {
        $issues[] = ['severity' => 'error', 'issue' => 'Missing meta description'];
    } elseif (strlen($page_data['description']) < 50) {
        $issues[] = ['severity' => 'warning', 'issue' => 'Description too short'];
    }
    
    return [
        'score' => count($issues) === 0 ? 100 : 100 - (count($issues) * 20),
        'issues' => $issues
    ];
}

// Generate sitemap XML
function generate_sitemap_xml() {
    $pages = [
        ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => '/studio.html', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['loc' => '/services.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => '/portfolio.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => '/about.html', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => '/blog.html', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => '/blog.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
        ['loc' => '/contact.html', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => '/privacy.html', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ['loc' => '/terms.html', 'priority' => '0.3', 'changefreq' => 'yearly']
    ];
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    $base_url = 'https://ilariondossouyovo.github.io/E-Graphisme';
    
    foreach ($pages as $page) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . $base_url . $page['loc'] . '</loc>' . "\n";
        $xml .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
        $xml .= '    <priority>' . $page['priority'] . '</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }
    
    $xml .= '</urlset>';
    
    return $xml;
}

// Redirect with SEO preservation
function seo_redirect($url) {
    header('Location: ' . $url);
    exit;
}