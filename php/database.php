<?php
/**
 * Database Configuration & Connection
 * E-Graphisme - JSON-based Database System
 */

class Database {
    private static $dbPath = __DIR__ . '/db/';
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Read data from JSON file
     */
    public static function read($table) {
        $file = self::$dbPath . $table . '.json';
        if (!file_exists($file)) {
            return [];
        }
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    
    /**
     * Write data to JSON file
     */
    public static function write($table, $data) {
        $file = self::$dbPath . $table . '.json';
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Insert record
     */
    public static function insert($table, $record) {
        $data = self::read($table);
        $record['id'] = self::generateId($table);
        $record['created_at'] = date('Y-m-d H:i:s');
        $record['updated_at'] = date('Y-m-d H:i:s');
        $data[] = $record;
        self::write($table, $data);
        return $record['id'];
    }
    
    /**
     * Update record by ID
     */
    public static function update($table, $id, $record) {
        $data = self::read($table);
        foreach ($data as &$item) {
            if ($item['id'] === $id || $item['id'] == $id) {
                $item = array_merge($item, $record);
                $item['updated_at'] = date('Y-m-d H:i:s');
                self::write($table, $data);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Delete record by ID
     */
    public static function delete($table, $id) {
        $data = self::read($table);
        $newData = array_filter($data, function($item) use ($id) {
            return $item['id'] !== $id && $item['id'] != $id;
        });
        self::write($table, array_values($newData));
        return true;
    }
    
    /**
     * Select records with optional filters
     */
    public static function select($table, $filters = [], $limit = 100) {
        $data = self::read($table);
        
        if (!empty($filters)) {
            $data = array_filter($data, function($item) use ($filters) {
                foreach ($filters as $key => $value) {
                    if (!isset($item[$key]) || $item[$key] != $value) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        return array_slice(array_values($data), 0, $limit);
    }
    
    /**
     * Find single record by ID
     */
    public static function find($table, $id) {
        $data = self::read($table);
        foreach ($data as $item) {
            if ($item['id'] === $id || $item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }
    
    /**
     * Generate unique ID
     */
    private static function generateId($table) {
        return uniqid() . '-' . substr(md5($table), 0, 8);
    }
    
    /**
     * Count records
     */
    public static function count($table) {
        $data = self::read($table);
        return count($data);
    }
    
    /**
     * Check if table exists
     */
    public static function tableExists($table) {
        return file_exists(self::$dbPath . $table . '.json');
    }
    
    /**
     * Create new table
     */
    public static function createTable($table, $schema = []) {
        $file = self::$dbPath . $table . '.json';
        if (!file_exists($file)) {
            self::write($table, $schema);
            return true;
        }
        return false;
    }
    
    /**
     * Get all tables
     */
    public static function getTables() {
        $files = glob(self::$dbPath . '*.json');
        return array_map(function($f) {
            return basename($f, '.json');
        }, $files);
    }
}

/**
 * Helper functions
 */
function db() {
    return Database::getInstance();
}

function db_read($table) {
    return Database::read($table);
}

function db_write($table, $data) {
    return Database::write($table, $data);
}

function db_insert($table, $record) {
    return Database::insert($table, $record);
}

function db_update($table, $id, $record) {
    return Database::update($table, $id, $record);
}

function db_delete($table, $id) {
    return Database::delete($table, $id);
}

function db_select($table, $filters = [], $limit = 100) {
    return Database::select($table, $filters, $limit);
}

function db_find($table, $id) {
    return Database::find($table, $id);
}

function db_count($table) {
    return Database::count($table);
}