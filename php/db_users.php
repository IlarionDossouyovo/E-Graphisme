<?php
/**
 * Users Database Integration
 * Connect users/auth to JSON database
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Save user to database
function save_user_to_db($data) {
    $db_file = __DIR__ . '/../db/users.json';
    
    $users = [];
    if (file_exists($db_file)) {
        $users = json_decode(file_get_contents($db_file), true) ?: [];
    }
    
    $id = 'user_' . substr(md5(uniqid()), 0, 10);
    $user = [
        'id' => $id,
        'username' => $data['username'],
        'email' => $data['email'],
        'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        'role' => $data['role'] ?? 'user',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $users['users'][] = $user;
    
    file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return $id;
}

// Get users from database
function get_users_from_db($role = null, $status = null, $limit = 50) {
    $db_file = __DIR__ . '/../db/users.json';
    
    if (!file_exists($db_file)) {
        return [];
    }
    
    $users = json_decode(file_get_contents($db_file), true) ?: [];
    
    if (!isset($users['users'])) {
        return [];
    }
    
    $data = $users['users'];
    
    if ($role) {
        $data = array_filter($data, function($u) use ($role) {
            return $u['role'] === $role;
        });
    }
    
    if ($status) {
        $data = array_filter($data, function($u) use ($status) {
            return $u['status'] === $status;
        });
    }
    
    return array_slice(array_values($data), 0, $limit);
}

// Get user by ID
function get_user_by_id($id) {
    $db_file = __DIR__ . '/../db/users.json';
    
    if (!file_exists($db_file)) {
        return null;
    }
    
    $users = json_decode(file_get_contents($db_file), true) ?: [];
    
    if (!isset($users['users'])) {
        return null;
    }
    
    foreach ($users['users'] as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }
    
    return null;
}

// Get user by username
function get_user_by_username($username) {
    $db_file = __DIR__ . '/../db/users.json';
    
    if (!file_exists($db_file)) {
        return null;
    }
    
    $users = json_decode(file_get_contents($db_file), true) ?: [];
    
    if (!isset($users['users'])) {
        return null;
    }
    
    foreach ($users['users'] as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    
    return null;
}

// Get user by email
function get_user_by_email($email) {
    $db_file = __DIR__ . '/../db/users.json';
    
    if (!file_exists($db_file)) {
        return null;
    }
    
    $users = json_decode(file_get_contents($db_file), true) ?: [];
    
    if (!isset($users['users'])) {
        return null;
    }
    
    foreach ($users['users'] as $user) {
        if ($user['email'] === $email) {
            return $user;
        }
    }
    
    return null;
}

// Verify user credentials
function verify_user($username, $password) {
    $user = get_user_by_username($username);
    
    if (!$user) {
        return false;
    }
    
    if ($user['status'] !== 'active') {
        return false;
    }
    
    return password_verify($password, $user['password']);
}

// Update user
function update_user($id, $data) {
    $db_file = __DIR__ . '/../db/users.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $users = json_decode(file_get_contents($db_file), true) ?: [];
    
    if (!isset($users['users'])) {
        return false;
    }
    
    $updated = false;
    foreach ($users['users'] as &$user) {
        if ($user['id'] === $id) {
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            $user = array_merge($user, $data);
            $user['updated_at'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return $updated;
}

// Delete user
function delete_user($id) {
    $db_file = __DIR__ . '/../db/users.json';
    
    if (!file_exists($db_file)) {
        return false;
    }
    
    $users = json_decode(file_get_contents($db_file), true) ?: [];
    
    if (!isset($users['users'])) {
        return false;
    }
    
    $users['users'] = array_filter($users['users'], function($u) use ($id) {
        return $u['id'] !== $id;
    });
    
    $users['users'] = array_values($users['users']);
    
    file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return true;
}

// Count users
function count_users($role = null, $status = null) {
    $users = get_users_from_db($role, $status);
    return count($users);
}

// Check if username exists
function username_exists($username) {
    return get_user_by_username($username) !== null;
}

// Check if email exists
function email_exists($email) {
    return get_user_by_email($email) !== null;
}