<?php

namespace general;

use Config;
use PDO;
use PDOException;

class Database {
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . Config::DATABASE['DB_HOST'] . ";dbname=" . Config::DATABASE['DB_NAME'] . ";charset=" . Config::DATABASE['DB_CHARSET'];
            $this->conn = new PDO($dsn, Config::DATABASE['DB_USER'], Config::DATABASE['DB_PASSWORD']);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Ошибка подключения: " . $e->getMessage();
            return null;
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection(): ?PDO {
        return $this->conn;
    }

    private function __clone() {
        // Ограничивает клонирование объекта
    }

    private function __wakeup() {
        // Ограничивает десериализацию объекта
    }
}