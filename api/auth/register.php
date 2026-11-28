<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../api/cors.php";
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
$organization = trim($data["organization"] ?? "");

if ($name === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Nama, email, dan password wajib diisi"
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
    $db = (new Database())->connect();

    $check = $db->prepare(
        "SELECT id_user FROM users WHERE email = :email"
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
        "INSERT INTO users
        (name, email, password, role, organization)
        VALUES
        (:name, :email, :password, 'relawan', :organization)"
    );

    $query->execute([
        "name" => $name,
        "email" => $email,
        "password" => $hashedPassword,
        "organization" => $organization !== ""
            ? $organization
            : null
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Registrasi berhasil",
        "data" => [
            "id_user" => $db->lastInsertId(),
            "name" => $name,
            "email" => $email,
            "role" => "relawan",
            "organization" => $organization ?: null
        ]
    ]);

} catch (PDOException $e) {
    error_log("Register Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
