<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

try {
    $db = (new Database())->connect();

    $user = authenticateUser($db);

    $query = $db->prepare(
        "SELECT
            id_user,
            name,
            email,
            role,
            organization,
            created_at
         FROM users
         WHERE id_user = :id_user"
    );

    $query->execute([
        "id_user" => $user["id_user"]
    ]);

    $profile = $query->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Profil tidak ditemukan"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $profile
    ]);

} catch (PDOException $e) {
    error_log("Profile Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
