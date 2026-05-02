<?php
/**
 * Contact Form Database Integration
 * Connect contact.php to JSON database
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Database functions
function save_contact_to_db($data) {
    $db_file = __DIR__ . '/../db/contacts.json';
    
    // Read existing data
    $contacts = [];
    if (file_exists($db_file)) {
        $json = file_get_contents($db_file);
        $contacts = json_decode($json, true) ?: [];
    }
    
    // Generate ID
    $id = 'msg_' . substr(md5(uniqid()), 0, 10);
    $data['id'] = $id;
    $data['status'] = 'new';
    $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['updated_at'] = date('Y-m-d H:i:s');
    
    // Add to contacts array
    $contacts['contacts'][] = $data;
    
    // Save
    file_put_contents($db_file, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $id;
}

function get_contacts_from_db($status = null, $limit = 50) {
    $db_file = __DIR__ . '/../db/contacts.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $contacts = json_decode($json, true) ?: [];
    
    if (!isset($contacts['contacts'])) {
        return [];
    }
    
    $data = $contacts['contacts'];
    
    if ($status) {
        $data = array_filter($data, function($c) use ($status) {
            return $c['status'] === $status;
        });
    }
    
    // Sort by date descending
    usort($data, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return array_slice($data, 0, $limit);
}

function get_contact_by_id($id) {
    $contacts = get_contacts_from_db();
    
    foreach ($contacts as $c) {
        if ($c['id'] === $id) {
            return $c;
        }
    }
    
    return null;
}

function update_contact_status($id, $status) {
    $db_file = __DIR__ . '/../db/contacts.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $contacts = json_decode($json, true) ?: [];
    
    if (!isset($contacts['contacts'])) {
        return false;
    }
    
    $updated = false;
    foreach ($contacts['contacts'] as &$c) {
        if ($c['id'] === $id) {
            $c['status'] = $status;
            $c['updated_at'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        file_put_contents($db_file, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return $updated;
}

function delete_contact($id) {
    $db_file = __DIR__ . '/../db/contacts.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $contacts = json_decode($json, true) ?: [];
    
    if (!isset($contacts['contacts'])) {
        return false;
    }
    
    $contacts['contacts'] = array_filter($contacts['contacts'], function($c) use ($id) {
        return $c['id'] !== $id;
    });
    
    $contacts['contacts'] = array_values($contacts['contacts']);
    
    file_put_contents($db_file, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return true;
}

function count_contacts($status = null) {
    $contacts = get_contacts_from_db();
    
    if ($status) {
        $contacts = array_filter($contacts, function($c) use ($status) {
            return $c['status'] === $status;
        });
    }
    
    return count($contacts);
}