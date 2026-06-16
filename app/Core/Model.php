<?php
/**
 * Base Model Class for Custom PHP MVC
 */

namespace App\Core;

use PDO;
use PDOException;

abstract class Model {
    protected static ?PDO $dbConnection = null;

    public function getDB(): PDO {
        if (self::$dbConnection === null) {
            // Ensure config constants are loaded
            require_once dirname(__DIR__, 2) . '/config/config.php';
            
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$dbConnection = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (defined('APP_ENV') && APP_ENV === 'production') {
                    die('Database connection failed. Please contact the administrator.');
                }
                die('Critical Database Connection Error: ' . $e->getMessage());
            }
        }
        return self::$dbConnection;
    }
}
