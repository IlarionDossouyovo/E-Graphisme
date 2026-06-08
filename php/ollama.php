<?php
/**
 * Ollama Integration
 * E-Graphisme - Local AI powered by Ollama
 */

require_once __DIR__ . '/config.php';

class Ollama {
    private static $baseUrl = '';
    private static $model = '';

    /**
     * Initialize Ollama settings
     */
    public static function init() {
        self::$baseUrl = OLLAMA_HOST;
        self::$model = defined('OLLAMA_MODEL') ? OLLAMA_MODEL : 'llama3';
    }

    /**
     * Check if Ollama is available
     */
    public static function isAvailable() {
        if (!OLLAMA_ENABLED) {
            return false;
        }

        $context = stream_context_create([
            'http' => ['timeout' => 2]
        ]);

        $response = @file_get_contents(self::$baseUrl . '/api/tags', false, $context);
        return $response !== false;
    }

    /**
     * Generate text completion
     */
    public static function generate($prompt, $options = []) {
        $data = [
            'model' => self::$model,
            'prompt' => $prompt,
            'stream' => false
        ];

        // Merge options
        $data = array_merge($data, $options);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($data),
                'timeout' => 60
            ]
        ]);

        $response = @file_get_contents(self::$baseUrl . '/api/generate', false, $context);
        
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'Failed to connect to Ollama'
            ];
        }

        $result = json_decode($response, true);
        
        return [
            'success' => true,
            'response' => $result['response'] ?? '',
            'done' => $result['done'] ?? true
        ];
    }

    /**
     * Chat completion
     */
    public static function chat($messages, $options = []) {
        $data = [
            'model' => self::$model,
            'messages' => $messages,
            'stream' => false
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($data),
                'timeout' => 60
            ]
        ]);

        $response = @file_get_contents(self::$baseUrl . '/api/chat', false, $context);
        
        if ($response === false) {
            return [
                'success' => false,
                'error' => 'Failed to connect to Ollama'
            ];
        }

        $result = json_decode($response, true);
        
        return [
            'success' => true,
            'message' => $result['message'] ?? ['content' => ''],
            'done' => $result['done'] ?? true
        ];
    }

    /**
     * Embeddings
     */
    public static function embeddings($text) {
        $data = [
            'model' => self::$model,
            'prompt' => $text
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ]);

        $response = @file_get_contents(self::$baseUrl . '/api/embeddings', false, $context);
        
        if ($response === false) {
            return null;
        }

        $result = json_decode($response, true);
        return $result['embedding'] ?? null;
    }

    /**
     * List available models
     */
    public static function listModels() {
        $context = stream_context_create([
            'http' => ['timeout' => 5]
        ]);

        $response = @file_get_contents(self::$baseUrl . '/api/tags', false, $context);
        
        if ($response === false) {
            return [];
        }

        $result = json_decode($response, true);
        return $result['models'] ?? [];
    }

    /**
     * Get current model info
     */
    public static function getModelInfo() {
        return [
            'url' => self::$baseUrl,
            'model' => self::$model,
            'available' => self::isAvailable()
        ];
    }
}

// Initialize
Ollama::init();

/**
 * Helper function for AI chat
 */
function ai_chat($prompt, $context = '') {
    $messages = [];
    
    if ($context) {
        $messages[] = [
            'role' => 'system',
            'content' => $context
        ];
    }
    
    $messages[] = [
        'role' => 'user',
        'content' => $prompt
    ];
    
    return Ollama::chat($messages);
}

/**
 * Helper function for AI generation
 */
function ai_generate($prompt) {
    return Ollama::generate($prompt);
}