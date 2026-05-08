<?php
/**
 * IN LOVING MEMORY — Database Configuration
 * Secure PDO connection with prepared statements
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'u149904157_memorial_db');
define('DB_USER', 'u149904157_h3rcio');         // Change to your MySQL username
define('DB_PASS', 'H3rcio#53125');             // Change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', 'https://herciocampos.com');   // Change to your domain
define('SITE_ROOT', dirname(__DIR__));
define('UPLOAD_DIR', SITE_ROOT . '/uploads/');

/**
 * Returns a singleton PDO instance
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error and show friendly message in production
            error_log('Database connection failed: ' . $e->getMessage());
            die('<div style="font-family:serif;text-align:center;padding:3rem;color:#666;">
                <h2>Service Temporarily Unavailable</h2>
                <p>Please try again in a moment.</p>
            </div>');
        }
    }
    return $pdo;
}

/**
 * Fetch site settings from DB (cached)
 */
function getSetting(string $key, string $default = ''): string {
    static $settings = null;
    if ($settings === null) {
        try {
            $pdo = getDB();
            $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}