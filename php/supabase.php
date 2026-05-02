<?php
/**
 * Supabase Configuration
 * Database connection and ORM
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Supabase Configuration - Use Environment Variables
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://lmjasjoyqqanphrkbjop.supabase.co');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'sb_publishable_Pjezr_MJ00_gWVJKWJPLNA_IXI9_feA');
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY') ?: 'your_service_key_here');

// Database Tables
define('TABLE_CONTACTS', 'contacts');
define('TABLE_PRODUCTS', 'products');
define('TABLE_ORDERS', 'orders');
define('TABLE_COMMENTS', 'comments');
define('TABLE_ANALYTICS', 'analytics');

// Supabase API helper
function supabase_request($endpoint, $method = 'GET', $data = null, $use_service_key = false) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    $headers = [
        'apikey: ' . ($use_service_key ? SUPABASE_SERVICE_KEY : SUPABASE_KEY),
        'Authorization: Bearer ' . ($use_service_key ? SUPABASE_SERVICE_KEY : SUPABASE_KEY),
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    switch ($method) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOM, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOM, 'DELETE');
            break;
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $http_code,
        'data' => json_decode($response, true)
    ];
}

// Get all records
function get_records($table, $filters = [], $limit = 50) {
    $query = $table . '?limit=' . $limit;
    
    foreach ($filters as $key => $value) {
        $query .= '&' . $key . '=' . urlencode($value);
    }
    
    return supabase_request($query);
}

// Insert record
function insert_record($table, $data) {
    return supabase_request($table, 'POST', $data);
}

// Update record
function update_record($table, $id, $data) {
    return supabase_request($table . '?id=eq.' . $id, 'PATCH', $data, true);
}

// Delete record
function delete_record($table, $id) {
    return supabase_request($table . '?id=eq.' . $id, 'DELETE', [], true);
}

// Contact functions
function add_contact($name, $email, $subject, $message) {
    return insert_record(TABLE_CONTACTS, [
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'status' => 'pending'
    ]);
}

function get_contacts($status = null) {
    $filters = $status ? ['status' => 'eq.' . $status] : [];
    return get_records(TABLE_CONTACTS, $filters);
}

function update_contact_status($id, $status) {
    return update_record(TABLE_CONTACTS, $id, ['status' => $status]);
}

// Products functions
function get_products($category = null, $limit = 50) {
    $filters = $category ? ['category' => 'eq.' . $category] : [];
    return get_records(TABLE_PRODUCTS, $filters, $limit);
}

function get_product($id) {
    return supabase_request(TABLE_PRODUCTS . '?id=eq.' . $id);
}

function add_product($data) {
    return insert_record(TABLE_PRODUCTS, $data);
}

function update_product($id, $data) {
    return update_record(TABLE_PRODUCTS, $id, $data);
}

// Orders functions
function add_order($user_id, $items, $total, $status = 'pending') {
    return insert_record(TABLE_ORDERS, [
        'id' => uniqid('ORD_'),
        'user_id' => $user_id,
        'items' => json_encode($items),
        'total' => $total,
        'status' => $status
    ]);
}

function get_orders($user_id = null) {
    $filters = $user_id ? ['user_id' => 'eq.' . $user_id] : [];
    return get_records(TABLE_ORDERS, $filters);
}

function update_order_status($id, $status) {
    return update_record(TABLE_ORDERS, $id, ['status' => $status]);
}

// Analytics
function track_event($event_name, $event_data = []) {
    return insert_record(TABLE_ANALYTICS, [
        'event' => $event_name,
        'data' => json_encode($event_data),
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

function get_analytics($event = null) {
    $filters = $event ? ['event' => 'eq.' . $event] : [];
    return get_records(TABLE_ANALYTICS, $filters, 100);
}

// Database connection test
function test_database() {
    $result = supabase_request(TABLE_PRODUCTS . '?limit=1');
    return $result['code'] === 200;
}