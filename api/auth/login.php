<?php

header("Content-Type: application/json; charset=UTF-8");

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

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($email === "" || $password === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Email dan password wajib diisi"
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $query = $db->prepare(
        "SELECT id, name, email, password
         FROM users
         WHERE email = :email
         LIMIT 1"
    );

    $query->execute(["email" => $email]);

    $user = $query->fetch();

    if (!$user || !password_verify($password, $user["password"])) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Email atau password salah"
        ]);

        exit;
    }

    // Generate token
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $token);

    // Masa berlaku token: 7 hari
    $expiresAt = date(
        "Y-m-d H:i:s",
        time() + (7 * 24 * 60 * 60)
    );

    $saveToken = $db->prepare(
        "INSERT INTO auth_tokens
         (user_id, token_hash, expires_at)
         VALUES (:user_id, :token_hash, :expires_at)"
    );

    $saveToken->execute([
        "user_id" => $user["id"],
        "token_hash" => $tokenHash,
        "expires_at" => $expiresAt
    ]);

    unset($user["password"]);

    echo json_encode([
        "success" => true,
        "message" => "Login berhasil",
        "token" => $token,
        "expires_at" => $expiresAt,
        "user" => $user
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
