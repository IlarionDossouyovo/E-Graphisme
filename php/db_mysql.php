<?php
/**
 * MySQL Database Connection
 * E-Graphisme - Switch between JSON and MySQL based on config
 */

require_once __DIR__ . '/config.php';

class MySQL {
    private static $connection = null;
    private static $isConnected = false;

    /**
     * Get PDO connection
     */
    public static function getConnection() {
        if (!DB_MYSQL_ENABLED) {
            return null;
        }

        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
                self::$isConnected = true;
            } catch (PDOException $e) {
                error_log('MySQL Connection Error: ' . $e->getMessage());
                self::$isConnected = false;
            }
        }
        
        return self::$connection;
    }

    /**
     * Check if connected
     */
    public static function isConnected() {
        return self::$isConnected;
    }

    /**
     * Execute query
     */
    public static function query($sql, $params = []) {
        $conn = self::getConnection();
        if (!$conn) {
            return null;
        }

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('MySQL Query Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch all rows
     */
    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Fetch single row
     */
    public static function fetchOne($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }

    /**
     * Insert and return ID
     */
    public static function insert($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt ? self::getConnection()->lastInsertId() : null;
    }

    /**
     * Update or delete and return affected rows
     */
    public static function execute($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction() {
        $conn = self::getConnection();
        return $conn ? $conn->beginTransaction() : false;
    }

    /**
     * Commit transaction
     */
    public static function commit() {
        $conn = self::getConnection();
        return $conn ? $conn->commit() : false;
    }

    /**
     * Rollback transaction
     */
    public static function rollback() {
        $conn = self::getConnection();
        return $conn ? $conn->rollback() : false;
    }
}

/**
 * Unified Database Interface
 * Automatically uses MySQL if enabled, otherwise falls back to JSON
 */
class DB {
    private static $useMySQL = false;

    public static function init() {
        self::$useMySQL = DB_MYSQL_ENABLED && MySQL::isConnected();
    }

    /**
     * Check which backend is active
     */
    public static function isMySQL() {
        return self::$useMySQL;
    }

    /**
     * Read from database
     */
    public static function read($table, $filters = [], $limit = 100) {
        if (self::$useMySQL) {
            $sql = "SELECT * FROM $table";
            $params = [];

            if (!empty($filters)) {
                $conditions = [];
                foreach ($filters as $key => $value) {
                    $conditions[] = "$key = ?";
                    $params[] = $value;
                }
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            $sql .= ' LIMIT ' . (int)$limit;
            return MySQL::fetchAll($sql, $params);
        }

        return Database::select($table, $filters, $limit);
    }

    /**
     * Find single record
     */
    public static function find($table, $id) {
        if (self::$useMySQL) {
            return MySQL::fetchOne("SELECT * FROM $table WHERE id = ?", [$id]);
        }

        return Database::find($table, $id);
    }

    /**
     * Insert record
     */
    public static function insert($table, $record) {
        if (self::$useMySQL) {
            $record['id'] = $record['id'] ?? uniqid() . '-' . substr(md5($table), 0, 8);
            $record['created_at'] = date('Y-m-d H:i:s');
            $record['updated_at'] = date('Y-m-d H:i:s');

            $columns = array_keys($record);
            $placeholders = array_fill(0, count($record), '?');

            $sql = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $table,
                implode(', ', $columns),
                implode(', ', $placeholders)
            );

            return MySQL::insert($sql, array_values($record));
        }

        return Database::insert($table, $record);
    }

    /**
     * Update record
     */
    public static function update($table, $id, $record) {
        $record['updated_at'] = date('Y-m-d H:i:s');

        if (self::$useMySQL) {
            $sets = [];
            $params = [];
            foreach ($record as $key => $value) {
                $sets[] = "$key = ?";
                $params[] = $value;
            }
            $params[] = $id;

            $sql = sprintf("UPDATE %s SET %s WHERE id = ?", $table, implode(', ', $sets));
            return MySQL::execute($sql, $params);
        }

        return Database::update($table, $id, $record);
    }

    /**
     * Delete record
     */
    public static function delete($table, $id) {
        if (self::$useMySQL) {
            return MySQL::execute("DELETE FROM $table WHERE id = ?", [$id]);
        }

        return Database::delete($table, $id);
    }

    /**
     * Count records
     */
    public static function count($table, $filters = []) {
        if (self::$useMySQL) {
            $sql = "SELECT COUNT(*) as cnt FROM $table";
            $params = [];

            if (!empty($filters)) {
                $conditions = [];
                foreach ($filters as $key => $value) {
                    $conditions[] = "$key = ?";
                    $params[] = $value;
                }
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            $result = MySQL::fetchOne($sql, $params);
            return $result ? (int)$result['cnt'] : 0;
        }

        return Database::count($table);
    }

    /**
     * Raw query (MySQL only)
     */
    public static function raw($sql, $params = []) {
        if (self::$useMySQL) {
            return MySQL::fetchAll($sql, $params);
        }
        return [];
    }
}

// Initialize
DB::init();