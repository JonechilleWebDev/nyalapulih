<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . "/../../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON"
    ]);
    exit;
}

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($name === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Semua field wajib diisi"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Format email tidak valid"
    ]);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Password minimal 8 karakter"
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $check = $db->prepare(
        "SELECT id FROM users WHERE email = :email"
    );

    $check->execute(["email" => $email]);

    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Email sudah terdaftar"
        ]);
        exit;
    }

    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $query = $db->prepare(
        "INSERT INTO users (name, email, password, role)
        VALUES (:name, :email, :password, 'volunteer')"
    );

    $query->execute([
        "name" => $name,
        "email" => $email,
        "password" => $hashedPassword
    ]);

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Registrasi berhasil",
        "data" => [
            "id" => $db->lastInsertId(),
            "name" => $name,
            "email" => $email,
            "role" => "volunteer"
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
