<?php
/**
 * Backup System
 * Automatic database backup and restore
 */

if (!defined('ACCESS')) {
    define('ACCESS', true);
}

// Create backup directory
function create_backup_dir() {
    $dir = __DIR__ . '/../backups/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

// Create full backup
function create_backup($name = null) {
    $dir = create_backup_dir();
    $name = $name ?? date('Y-m-d_H-i-s');
    $file = $dir . 'backup_' . $name . '.json';
    
    $backup = [
        'created_at' => date('Y-m-d H:i:s'),
        'version' => '1.0',
        'database' => []
    ];
    
    // Backup all database files
    $db_files = glob(__DIR__ . '/../db/*.json');
    foreach ($db_files as $file) {
        $basename = basename($file, '.json');
        $backup['database'][$basename] = json_decode(file_get_contents($file), true);
    }
    
    // Save backup
    $backup_file = $dir . 'backup_' . $name . '.json';
    file_put_contents($backup_file, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return [
        'success' => true,
        'file' => $backup_file,
        'size' => filesize($backup_file)
    ];
}

// List backups
function list_backups() {
    $dir = create_backup_dir();
    $files = glob($dir . 'backup_*.json');
    
    $backups = [];
    foreach ($files as $file) {
        $backups[] = [
            'file' => basename($file),
            'size' => filesize($file),
            'created' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
    
    usort($backups, function($a, $b) {
        return strtotime($b['created']) - strtotime($a['created']);
    });
    
    return $backups;
}

// Restore from backup
function restore_backup($filename) {
    $dir = create_backup_dir();
    $file = $dir . $filename;
    
    if (!file_exists($file)) {
        return ['success' => false, 'message' => 'Backup not found'];
    }
    
    $backup = json_decode(file_get_contents($file), true);
    
    if (!isset($backup['database'])) {
        return ['success' => false, 'message' => 'Invalid backup format'];
    }
    
    // Restore each database
    foreach ($backup['database'] as $name => $data) {
        $db_file = __DIR__ . '/../db/' . $name . '.json';
        file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return ['success' => true, 'message' => 'Backup restored'];
}

// Delete backup
function delete_backup($filename) {
    $dir = create_backup_dir();
    $file = $dir . $filename;
    
    if (!file_exists($file)) {
        return ['success' => false, 'message' => 'Backup not found'];
    }
    
    unlink($file);
    
    return ['success' => true, 'message' => 'Backup deleted'];
}

// Auto-backup (run periodically)
function auto_backup() {
    $backup_file = __DIR__ . '/../db/.last_backup';
    $interval = 24 * 60 * 60; // 24 hours
    
    // Check last backup time
    if (file_exists($backup_file)) {
        $last = filemtime($backup_file);
        if (time() - $last < $interval) {
            return ['success' => false, 'message' => 'Backup already today'];
        }
    }
    
    $result = create_backup();
    touch($backup_file);
    
    return $result;
}

// Export database to downloadable format
function export_database() {
    $backup = [
        'exported_at' => date('Y-m-d H:i:s'),
        'data' => []
    ];
    
    $db_files = glob(__DIR__ . '/../db/*.json');
    foreach ($db_files as $file) {
        $basename = basename($file, '.json');
        $backup['data'][$basename] = json_decode(file_get_contents($file), true);
    }
    
    return $backup;
}

// Clean old backups (keep last 7)
function clean_old_backups() {
    $backups = list_backups();
    
    if (count($backups) <= 7) {
        return ['deleted' => 0];
    }
    
    $deleted = 0;
    $to_delete = array_slice($backups, 7);
    
    foreach ($to_delete as $backup) {
        delete_backup($backup['file']);
        $deleted++;
    }
    
    return ['deleted' => $deleted];
}