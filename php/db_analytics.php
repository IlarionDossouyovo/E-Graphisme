<?php
/**
 * Analytics System
 * Track and analyze site usage
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Track page view
function track_page_view($page, $title = '') {
    $db_file = __DIR__ . '/../db/database.json';
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    $entry = [
        'id' => 'analytics_' . substr(md5(uniqid()), 0, 10),
        'type' => 'page_view',
        'page' => $page,
        'title' => $title,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $db['analytics'][] = $entry;
    
    file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $entry['id'];
}

// Track event
function track_event($category, $action, $label = '', $value = 0) {
    $db_file = __DIR__ . '/../db/database.json';
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    $entry = [
        'id' => 'event_' . substr(md5(uniqid()), 0, 10),
        'type' => 'event',
        'category' => $category,
        'action' => $action,
        'label' => $label,
        'value' => $value,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $db['analytics'][] = $entry;
    
    file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $entry['id'];
}

// Get page views
function get_page_views($days = 7, $limit = 20) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['analytics'])) {
        return [];
    }
    
    $analytics = $db['analytics'];
    
    // Filter by type and date
    $since = strtotime("-{$days} days");
    $analytics = array_filter($analytics, function($a) use ($since) {
        return $a['type'] === 'page_view' && strtotime($a['created_at']) > $since;
    });
    
    // Group by page
    $pages = [];
    foreach ($analytics as $a) {
        $page = $a['page'];
        if (!isset($pages[$page])) {
            $pages[$page] = 0;
        }
        $pages[$page]++;
    }
    
    // Sort by count
    arsort($pages);
    
    return array_slice($pages, 0, $limit, true);
}

// Get unique visitors
function get_unique_visitors($days = 7) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return 0;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['analytics'])) {
        return 0;
    }
    
    $analytics = $db['analytics'];
    
    $since = strtotime("-{$days} days");
    $analytics = array_filter($analytics, function($a) use ($since) {
        return strtotime($a['created_at']) > $since;
    });
    
    $ips = array_unique(array_column($analytics, 'ip'));
    
    return count(array_filter($ips));
}

// Get total views
function get_total_views($days = 7) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return 0;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['analytics'])) {
        return 0;
    }
    
    $analytics = $db['analytics'];
    
    $since = strtotime("-{$days} days");
    $analytics = array_filter($analytics, function($a) use ($since) {
        return $a['type'] === 'page_view' && strtotime($a['created_at']) > $since;
    });
    
    return count($analytics);
}

// Get daily views
function get_daily_views($days = 7) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['analytics'])) {
        return [];
    }
    
    $analytics = $db['analytics'];
    
    $since = strtotime("-{$days} days");
    $analytics = array_filter($analytics, function($a) use ($since) {
        return $a['type'] === 'page_view' && strtotime($a['created_at']) > $since;
    });
    
    // Group by date
    $daily = [];
    foreach ($analytics as $a) {
        $date = date('Y-m-d', strtotime($a['created_at']));
        if (!isset($daily[$date])) {
            $daily[$date] = 0;
        }
        $daily[$date]++;
    }
    
    return $daily;
}

// Get top referrers
function get_top_referrers($days = 7, $limit = 10) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['analytics'])) {
        return [];
    }
    
    $analytics = $db['analytics'];
    
    $since = strtotime("-{$days} days");
    $analytics = array_filter($analytics, function($a) use ($since) {
        return strtotime($a['created_at']) > $since && !empty($a['referrer']);
    });
    
    $referrers = [];
    foreach ($analytics as $a) {
        $ref = parse_url($a['referrer'], PHP_URL_HOST);
        if ($ref && strpos($ref, 'e-graphisme') === false) {
            if (!isset($referrers[$ref])) {
                $referrers[$ref] = 0;
            }
            $referrers[$ref]++;
        }
    }
    
    arsort($referrers);
    
    return array_slice($referrers, 0, $limit, true);
}

// Get statistics summary
function get_analytics_summary($days = 7) {
    return [
        'page_views' => get_total_views($days),
        'unique_visitors' => get_unique_visitors($days),
        'top_pages' => get_page_views($days, 5),
        'daily_views' => get_daily_views($days),
        'top_referrers' => get_top_referrers($days, 5)
    ];
}

// Clear old analytics (older than 30 days)
function clean_analytics() {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return 0;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['analytics'])) {
        return 0;
    }
    
    $before = count($db['analytics']);
    
    $cutoff = strtotime('-30 days');
    $db['analytics'] = array_filter($db['analytics'], function($a) use ($cutoff) {
        return strtotime($a['created_at']) > $cutoff;
    });
    
    $db['analytics'] = array_values($db['analytics']);
    
    file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $before - count($db['analytics']);
}