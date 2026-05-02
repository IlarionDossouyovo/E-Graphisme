<?php
/**
 * Newsletter/Subscribers Database Integration
 * Connect newsletter to JSON database
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Subscribe email to newsletter
function subscribe_to_newsletter($email, $name = '') {
    $db_file = __DIR__ . '/../db/database.json';
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    // Check if already subscribed
    if (isset($db['subscribers'])) {
        foreach ($db['subscribers'] as $sub) {
            if ($sub['email'] === $email) {
                return ['success' => false, 'message' => 'Déjà abonné'];
            }
        }
    }
    
    $subscriber = [
        'id' => 'sub_' . substr(md5(uniqid()), 0, 10),
        'email' => $email,
        'name' => $name,
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $db['subscribers'][] = $subscriber;
    
    file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return ['success' => true, 'message' => 'Abonné avec succès'];
}

// Unsubscribe from newsletter
function unsubscribe_from_newsletter($email) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['subscribers'])) {
        return false;
    }
    
    $db['subscribers'] = array_filter($db['subscribers'], function($s) use ($email) {
        return $s['email'] !== $email;
    });
    
    $db['subscribers'] = array_values($db['subscribers']);
    
    file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return true;
}

// Get subscribers
function get_subscribers_from_db($status = 'active', $limit = 100) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['subscribers'])) {
        return [];
    }
    
    $subscribers = $db['subscribers'];
    
    if ($status) {
        $subscribers = array_filter($subscribers, function($s) use ($status) {
            return $s['status'] === $status;
        });
    }
    
    return array_slice(array_values($subscribers), 0, $limit);
}

// Get subscriber by email
function get_subscriber_by_email($email) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return null;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['subscribers'])) {
        return null;
    }
    
    foreach ($db['subscribers'] as $sub) {
        if ($sub['email'] === $email) {
            return $sub;
        }
    }
    
    return null;
}

// Count subscribers
function count_subscribers($status = null) {
    $subscribers = get_subscribers_from_db($status);
    return count($subscribers);
}

// Update subscriber status
function update_subscriber_status($email, $status) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['subscribers'])) {
        return false;
    }
    
    $updated = false;
    foreach ($db['subscribers'] as &$sub) {
        if ($sub['email'] === $email) {
            $sub['status'] = $status;
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return $updated;
}

// Export emails as CSV string
function export_subscriber_emails() {
    $subscribers = get_subscribers_from_db('active');
    $emails = array_column($subscribers, 'email');
    return implode(", ", $emails);
}