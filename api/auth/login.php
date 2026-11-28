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
    $db = (new Database())->connect();

    $query = $db->prepare(
        "SELECT id_user, name, email, password,
                role, organization
         FROM users
         WHERE email = :email
         LIMIT 1"
    );

    $query->execute(["email" => $email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Email atau password salah"
        ]);
        exit;
    }

    // Buat token
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $token);

    $expiresAt = date(
        "Y-m-d H:i:s",
        strtotime("+1 month")
    );

    // Simpan hash token
    $insert = $db->prepare(
        "INSERT INTO auth_tokens
        (user_id, token_hash, expires_at)
        VALUES (:user_id, :token_hash, :expires_at)"
    );

    $insert->execute([
        "user_id" => $user["id_user"],
        "token_hash" => $tokenHash,
        "expires_at" => $expiresAt
    ]);

    unset($user["password"]);

    echo json_encode([
        "success" => true,
        "message" => "Login berhasil",
        "token" => $token,
        "expires_at" => $expiresAt,
        "data" => $user
    ]);

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
