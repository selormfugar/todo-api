<?php

namespace TodoApi;
use PDO;

class Database {
   private PDO $conn;
       public function __construct()
    {

        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'] ?? 3306;
        $dbname = $_ENV['DB_DATABASE'];
        $username = $_ENV['DB_USERNAME'];
        // $password = $_ENV['DB_PASSWORD'];

         $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4;port={$port}";
        
         $this->conn = new PDO($dsn, $username ?? null, $password ?? null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
