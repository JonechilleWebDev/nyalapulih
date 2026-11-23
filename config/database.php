<?php

class Database {
    private $host = "127.0.0.1";
    private $dbname = "nyalapulih_db";
    private $username = "root";
    private $password = "";

    public function connect() {
        try {
            $conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $conn->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

            return $conn;

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                "success" => false,
                "message" => "Database connection failed"
            ]);

            exit;
        }
    }
}
