<?php
namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database {

    private static $instance = null;

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Non è possibile deserializzare un singleton.");
    }

    public static function getInstance() {

        if(self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            try{
                self::$instance = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            } catch(PDOException $e) {
                throw new Exception("Errore di connessione al database: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}