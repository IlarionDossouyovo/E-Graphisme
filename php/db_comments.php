<?php
/**
 * Comments Database Integration
 * Connect comments to JSON database
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Save comment to database
function save_comment_to_db($data) {
    $db_file = __DIR__ . '/../db/database.json';
    
    // Read existing data
    $data_db = [];
    if (file_exists($db_file)) {
        $json = file_get_contents($db_file);
        $data_db = json_decode($json, true) ?: [];
    }
    
    // Generate ID
    $id = 'comment_' . substr(md5(uniqid()), 0, 10);
    $comment = [
        'id' => $id,
        'post_id' => $data['post_id'] ?? 'post_001',
        'parent_id' => $data['parent_id'] ?? null,
        'author' => $data['author'],
        'email' => $data['email'],
        'website' => $data['website'] ?? '',
        'content' => $data['content'],
        'status' => 'pending', // pending, approved, spam
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add to comments array
    $data_db['comments'][] = $comment;
    
    // Save
    file_put_contents($db_file, json_encode($data_db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $id;
}

// Get comments from database
function get_comments_from_db($post_id = null, $status = 'approved', $limit = 50) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $data_db = json_decode($json, true) ?: [];
    
    if (!isset($data_db['comments'])) {
        return [];
    }
    
    $comments = $data_db['comments'];
    
    // Filter by post_id
    if ($post_id) {
        $comments = array_filter($comments, function($c) use ($post_id) {
            return $c['post_id'] === $post_id;
        });
    }
    
    // Filter by status
    if ($status) {
        $comments = array_filter($comments, function($c) use ($status) {
            return $c['status'] === $status;
        });
    }
    
    // Sort by date descending
    usort($comments, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return array_slice(array_values($comments), 0, $limit);
}

// Get comment by ID
function get_comment_by_id($id) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return null;
    }
    
    $json = file_get_contents($db_file);
    $data_db = json_decode($json, true) ?: [];
    
    if (!isset($data_db['comments'])) {
        return null;
    }
    
    foreach ($data_db['comments'] as $comment) {
        if ($comment['id'] === $id) {
            return $comment;
        }
    }
    
    return null;
}

// Update comment status
function update_comment_status($id, $status) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $data_db = json_decode($json, true) ?: [];
    
    if (!isset($data_db['comments'])) {
        return false;
    }
    
    $updated = false;
    foreach ($data_db['comments'] as &$comment) {
        if ($comment['id'] === $id) {
            $comment['status'] = $status;
            $comment['updated_at'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        file_put_contents($db_file, json_encode($data_db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return $updated;
}

// Delete comment
function delete_comment($id) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $data_db = json_decode($json, true) ?: [];
    
    if (!isset($data_db['comments'])) {
        return false;
    }
    
    $data_db['comments'] = array_filter($data_db['comments'], function($c) use ($id) {
        return $c['id'] !== $id;
    });
    
    $data_db['comments'] = array_values($data_db['comments']);
    
    file_put_contents($db_file, json_encode($data_db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return true;
}

// Count comments
function count_comments($post_id = null, $status = null) {
    $comments = get_comments_from_db($post_id, $status);
    return count($comments);
}

// Get recent comments (all statuses)
function get_all_comments_db($limit = 20) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $data_db = json_decode($json, true) ?: [];
    
    if (!isset($data_db['comments'])) {
        return [];
    }
    
    $comments = $data_db['comments'];
    
    // Sort by date descending
    usort($comments, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return array_slice($comments, 0, $limit);
}

// Approve comment
function approve_comment($id) {
    return update_comment_status($id, 'approved');
}

// Mark as spam
function spam_comment($id) {
    return update_comment_status($id, 'spam');
}