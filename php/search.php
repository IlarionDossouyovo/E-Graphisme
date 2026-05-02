<?php
/**
 * Search System
 * Full-text search across all content
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Search across all content types
function search_all($query, $types = ['posts', 'comments'], $limit = 20) {
    $results = [];
    
    $query = strtolower(trim($query));
    if (empty($query)) {
        return $results;
    }
    
    // Search posts
    if (in_array('posts', $types)) {
        $posts = get_posts_from_db('published');
        foreach ($posts as $post) {
            $score = calculate_search_score($query, $post);
            if ($score > 0) {
                $results[] = [
                    'type' => 'post',
                    'id' => $post['id'],
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'url' => '/blog.php?post=' . $post['slug'],
                    'score' => $score,
                    'date' => $post['created_at']
                ];
            }
        }
    }
    
    // Search comments
    if (in_array('comments', $types)) {
        $comments = get_comments_from_db(null, 'approved', 50);
        foreach ($comments as $comment) {
            $score = calculate_search_score($query, $comment);
            if ($score > 0) {
                $results[] = [
                    'type' => 'comment',
                    'id' => $comment['id'],
                    'title' => 'Commentaire',
                    'excerpt' => substr($comment['content'], 0, 100),
                    'url' => '/blog.php',
                    'score' => $score,
                    'date' => $comment['created_at']
                ];
            }
        }
    }
    
    // Sort by score
    usort($results, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    return array_slice($results, 0, $limit);
}

// Calculate search relevance score
function calculate_search_score($query, $item) {
    $score = 0;
    
    // Check title (highest weight)
    if (isset($item['title'])) {
        $title = strtolower($item['title']);
        if (strpos($title, $query) !== false) {
            $score += 10;
        }
    }
    
    // Check content
    if (isset($item['content'])) {
        $content = strtolower($item['content']);
        if (strpos($content, $query) !== false) {
            $score += 5;
        }
        // Count occurrences
        $count = substr_count($content, $query);
        $score += min($count, 3);
    }
    
    // Check tags
    if (isset($item['tags']) && is_array($item['tags'])) {
        foreach ($item['tags'] as $tag) {
            if (strpos(strtolower($tag), $query) !== false) {
                $score += 3;
            }
        }
    }
    
    return $score;
}

// Get search suggestions
function get_search_suggestions($query, $limit = 5) {
    $suggestions = [];
    $query = strtolower(trim($query));
    
    if (strlen($query) < 2) {
        return $suggestions;
    }
    
    // Get popular tags
    $posts = get_posts_from_db('published', null, 50);
    $tags = [];
    
    foreach ($posts as $post) {
        if (isset($post['tags']) && is_array($post['tags'])) {
            $tags = array_merge($tags, $post['tags']);
        }
    }
    
    // Filter matching tags
    foreach ($tags as $tag) {
        if (strpos(strtolower($tag), $query) !== false) {
            $suggestions[] = $tag;
        }
    }
    
    return array_slice(array_unique($suggestions), 0, $limit);
}

// Search with filters
function search_filtered($query, $filters = [], $limit = 20) {
    $results = search_all($query, $filters['types'] ?? ['posts'], $limit);
    
    // Apply date filter
    if (!empty($filters['date_from'])) {
        $results = array_filter($results, function($r) use ($filters) {
            return strtotime($r['date']) >= strtotime($filters['date_from']);
        });
    }
    
    // Apply type filter
    if (!empty($filters['type'])) {
        $results = array_filter($results, function($r) use ($filters) {
            return $r['type'] === $filters['type'];
        });
    }
    
    return array_slice($results, 0, $limit);
}

// Get search statistics
function get_search_stats() {
    return [
        'total_posts' => count_posts('published'),
        'total_comments' => count_comments(),
        'indexed_at' => date('Y-m-d H:i:s')
    ];
}

// Save search query for analytics
function log_search_query($query, $results_count) {
    track_event('search', $query, 'results', $results_count);
}