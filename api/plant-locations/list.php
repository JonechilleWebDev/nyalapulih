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
    authenticateUser($db);

    $query = $db->query(
        "SELECT * FROM plant_locations
         ORDER BY created_at DESC"
    );

    $locations = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "message" => "Data lokasi berhasil diambil",
        "data" => $locations
    ]);

} catch (PDOException $e) {
    error_log("Plant Location List Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
