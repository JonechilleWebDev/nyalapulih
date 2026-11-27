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

$id = $_GET["id"] ?? "";

if (!ctype_digit((string) $id) || (int) $id < 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "ID lokasi tidak valid"
    ]);
    exit;
}

try {
    $db = (new Database())->connect();
    authenticateUser($db);

    $query = $db->prepare(
        "SELECT * FROM plant_locations
         WHERE id_p_location = :id
         LIMIT 1"
    );

    $query->execute(["id" => $id]);
    $location = $query->fetch(PDO::FETCH_ASSOC);

    if (!$location) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Lokasi tidak ditemukan"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Detail lokasi berhasil diambil",
        "data" => $location
    ]);

} catch (PDOException $e) {
    error_log("Plant Location Detail Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
