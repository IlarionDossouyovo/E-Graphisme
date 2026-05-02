<?php
/**
 * Admin Dashboard
 * Real-time statistics and management
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_contact.php';
require_once __DIR__ . '/db_comments.php';
require_once __DIR__ . '/db_posts.php';
require_once __DIR__ . '/db_newsletter.php';
require_once __DIR__ . '/db_analytics.php';

// Check if user is logged in
function require_admin_login() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        header('Location: login.php');
        exit;
    }
}

// Get dashboard statistics
function get_dashboard_stats() {
    $stats = [
        'messages' => [
            'total' => count_contacts(),
            'new' => count_contacts('new'),
            'read' => count_contacts('read'),
            'archived' => count_contacts('archived')
        ],
        'comments' => [
            'total' => count_comments(),
            'pending' => count_comments(null, 'pending'),
            'approved' => count_comments(null, 'approved'),
            'spam' => count_comments(null, 'spam')
        ],
        'posts' => [
            'total' => count_posts(),
            'published' => count_posts('published'),
            'draft' => count_posts('draft')
        ],
        'subscribers' => [
            'total' => count_subscribers(),
            'active' => count_subscribers('active'),
            'unsubscribed' => count_subscribers('unsubscribed')
        ],
        'analytics' => get_analytics_summary(30)
    ];
    
    return $stats;
}

// Get recent messages
function get_recent_messages($limit = 10) {
    return get_contacts_from_db(null, $limit);
}

// Get recent comments
function get_recent_comments($limit = 10) {
    return get_all_comments_db($limit);
}

// Get recent activity
function get_recent_activity($limit = 20) {
    $activities = [];
    
    // Get recent messages
    $messages = get_recent_messages($limit);
    foreach ($messages as $m) {
        $activities[] = [
            'type' => 'message',
            'title' => 'Nouveau message de ' . $m['name'],
            'date' => $m['created_at'],
            'url' => '?page=messages&id=' . $m['id']
        ];
    }
    
    // Get recent comments
    $comments = get_recent_comments($limit);
    foreach ($comments as $c) {
        $activities[] = [
            'type' => 'comment',
            'title' => 'Nouveau commentaire de ' . $c['author'],
            'date' => $c['created_at'],
            'url' => '?page=comments&id=' . $c['id']
        ];
    }
    
    // Sort by date
    usort($activities, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return array_slice($activities, 0, $limit);
}

// Quick action: Mark message as read
function quick_mark_read($id) {
    return update_contact_status($id, 'read');
}

// Quick action: Archive message
function quick_archive($id) {
    return update_contact_status($id, 'archived');
}

// Quick action: Delete message
function quick_delete_message($id) {
    return delete_contact($id);
}

// Quick action: Approve comment
function quick_approve_comment($id) {
    return approve_comment($id);
}

// Quick action: Delete comment
function quick_delete_comment($id) {
    return delete_comment($id);
}

// Quick action: Publish post
function quick_publish_post($id) {
    return update_post($id, ['status' => 'published']);
}

// Quick action: Unpublish post
function quick_unpublish_post($id) {
    return update_post($id, ['status' => 'draft']);
}

// Export data as JSON
function export_data($type) {
    $data = [];
    
    switch ($type) {
        case 'messages':
            $data = get_contacts_from_db();
            break;
        case 'comments':
            $data = get_all_comments_db();
            break;
        case 'posts':
            $data = get_all_posts_db();
            break;
        case 'subscribers':
            $data = get_subscribers_from_db();
            break;
        default:
            return null;
    }
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $type . '_export.json"');
    
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// Clean old data
function clean_old_data() {
    $cleaned = [
        'analytics' => clean_analytics()
    ];
    
    return $cleaned;
}

// Format bytes
function format_bytes($bytes) {
    if ($bytes >= 1073741824) {
        return round($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}

// Get disk usage
function get_disk_usage() {
    $dir = __DIR__ . '/../db/';
    $size = 0;
    
    if (is_dir($dir)) {
        $files = glob($dir . '*.json');
        foreach ($files as $file) {
            $size += filesize($file);
        }
    }
    
    return format_bytes($size);
}

// Get server info
function get_server_info() {
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'disk_usage' => get_disk_usage(),
        'json_version' => json_encode(['test' => true])
    ];
}