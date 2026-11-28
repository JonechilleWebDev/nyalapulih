<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../api/cors.php";
require_once __DIR__ . "/../../config/database.php";

// Logout hanya menerima POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);

    exit;
}

// Ambil Authorization header
$headers = function_exists("getallheaders")
    ? getallheaders()
    : [];

$authorization = $_SERVER["HTTP_AUTHORIZATION"]
    ?? $headers["Authorization"]
    ?? $headers["authorization"]
    ?? "";

// Periksa format Bearer Token
if (!preg_match("/^Bearer\s+(\S+)$/i", $authorization, $matches)) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Token autentikasi tidak ditemukan"
    ]);

    exit;
}

$token = $matches[1];

// Hash token agar sesuai dengan yang tersimpan di database
$tokenHash = hash("sha256", $token);

try {
    $database = new Database();
    $db = $database->connect();

    $query = $db->prepare(
        "DELETE FROM auth_tokens
         WHERE token_hash = :token_hash"
    );

    $query->execute([
        "token_hash" => $tokenHash
    ]);

    if ($query->rowCount() === 0) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Token tidak valid atau sesi sudah berakhir"
        ]);

        exit;
    }

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "Logout berhasil"
    ]);

} catch (PDOException $e) {
    error_log("Logout Error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
