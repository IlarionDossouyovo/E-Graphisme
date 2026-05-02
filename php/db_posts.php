<?php
/**
 * Blog Posts Database Integration
 * Connect blog to JSON database
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Save post to database
function save_post_to_db($data) {
    $db_file = __DIR__ . '/../db/database.json';
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    $id = 'post_' . substr(md5(uniqid()), 0, 10);
    $post = [
        'id' => $id,
        'title' => $data['title'],
        'slug' => $data['slug'] ?? sanitize_slug($data['title']),
        'content' => $data['content'],
        'excerpt' => $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 150),
        'author' => $data['author'] ?? 'admin',
        'status' => $data['status'] ?? 'draft',
        'category' => $data['category'] ?? 'general',
        'tags' => $data['tags'] ?? [],
        'featured_image' => $data['featured_image'] ?? '',
        'views' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db['posts'][] = $post;
    
    file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $id;
}

// Get posts from database
function get_posts_from_db($status = 'published', $category = null, $limit = 20) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['posts'])) {
        return [];
    }
    
    $posts = $db['posts'];
    
    if ($status) {
        $posts = array_filter($posts, function($p) use ($status) {
            return $p['status'] === $status;
        });
    }
    
    if ($category) {
        $posts = array_filter($posts, function($p) use ($category) {
            return $p['category'] === $category;
        });
    }
    
    usort($posts, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return array_slice(array_values($posts), 0, $limit);
}

// Get post by ID
function get_post_by_id($id) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return null;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['posts'])) {
        return null;
    }
    
    foreach ($db['posts'] as $post) {
        if ($post['id'] === $id) {
            return $post;
        }
    }
    
    return null;
}

// Get post by slug
function get_post_by_slug($slug) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return null;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['posts'])) {
        return null;
    }
    
    foreach ($db['posts'] as $post) {
        if ($post['slug'] === $slug) {
            return $post;
        }
    }
    
    return null;
}

// Update post
function update_post($id, $data) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['posts'])) {
        return false;
    }
    
    $updated = false;
    foreach ($db['posts'] as &$post) {
        if ($post['id'] === $id) {
            $post = array_merge($post, $data);
            $post['updated_at'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return $updated;
}

// Delete post
function delete_post($id) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['posts'])) {
        return false;
    }
    
    $db['posts'] = array_filter($db['posts'], function($p) use ($id) {
        return $p['id'] !== $id;
    });
    
    $db['posts'] = array_values($db['posts']);
    
    file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return true;
}

// Increment views
function increment_views($id) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['posts'])) {
        return false;
    }
    
    foreach ($db['posts'] as &$post) {
        if ($post['id'] === $id) {
            $post['views'] = ($post['views'] ?? 0) + 1;
            break;
        }
    }
    
    file_put_contents($db_file, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return true;
}

// Count posts
function count_posts($status = null) {
    $posts = get_posts_from_db($status);
    return count($posts);
}

// Get all posts for admin
function get_all_posts_db($limit = 50) {
    $db_file = __DIR__ . '/../db/database.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $json = file_get_contents($db_file);
    $db = json_decode($json, true) ?: [];
    
    if (!isset($db['posts'])) {
        return [];
    }
    
    $posts = $db['posts'];
    
    usort($posts, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return array_slice($posts, 0, $limit);
}

// Helper: Create URL-friendly slug
function sanitize_slug($string) {
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/\s+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}