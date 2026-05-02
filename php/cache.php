<?php
/**
 * Système de Cache - E-Graphisme
 * Gestion du cache pour optimiser les performances
 */
require_once __DIR__ . '/config.php';

class Cache {
    private $cache_dir;
    private $default_ttl;
    
    public function __construct($ttl = 3600) {
        $this->cache_dir = __DIR__ . '/../cache/';
        $this->default_ttl = $ttl;
        
        // Créer le répertoire cache si inexistant
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Générer une clé de cache depuis des données
     */
    public function makeKey($data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        return md5($data);
    }
    
    /**
     * Vérifier si un fichier cache existe et est valide
     */
    public function has($key, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        // Vérifier l'expiration
        $mtime = filemtime($file);
        if ((time() - $mtime) > $ttl) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * Récupérer des données depuis le cache
     */
    public function get($key, $default = null) {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $content = file_get_contents($file);
        $data = unserialize($content);
        
        return $data !== false ? $data : $default;
    }
    
    /**
     * Sauvegarder des données dans le cache
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        $file = $this->getCacheFile($key);
        
        $data = serialize($value);
        
        // Écrire avec verrouillage
        if (file_put_contents($file, $data, LOCK_EX) !== false) {
            // Définir le temps d'expiration du fichier
            touch($file, time() + $ttl);
            return true;
        }
        
        return false;
    }
    
    /**
     * Supprimer une entrée du cache
     */
    public function delete($key) {
        $file = $this->getCacheFile($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    /**
     * Vider tout le cache
     */
    public function flush() {
        $files = glob($this->cache_dir . '*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Nettoyer le cache expiré
     */
    public function clean() {
        $files = glob($this->cache_dir . '*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < time()) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Obtenir le chemin du fichier cache
     */
    private function getCacheFile($key) {
        // Nettoyer la clé
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        return $this->cache_dir . $key . '.cache';
    }
    
    /**
     * Obtenir des statistiques du cache
     */
    public function stats() {
        $files = glob($this->cache_dir . '*.cache');
        $total_size = 0;
        $expired = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $total_size += filesize($file);
                if (filemtime($file) < time()) {
                    $expired++;
                }
            }
        }
        
        return [
            'files' => count($files),
            'size' => $total_size,
            'expired' => $expired,
            'size_formatted' => $this->formatSize($total_size)
        ];
    }
    
    /**
     * Formater une taille de fichier
     */
    private function function formatSize($bytes) {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// Fonction helper pour mettre en cache le résultat d'une fonction
function cache_remember($key, $ttl, $callback) {
    $cache = new Cache($ttl);
    
    if ($cache->has($key)) {
        return $cache->get($key);
    }
    
    $value = $callback();
    $cache->set($key, $value);
    
    return $value;
}

// Nettoyer le cache automatiquement si trop de fichiers
if (rand(1, 100) === 1) {
    $cache = new Cache();
    $stats = $cache->stats();
    
    if ($stats['files'] > 100) {
        $cache->clean();
    }
}
