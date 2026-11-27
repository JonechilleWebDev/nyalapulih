<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";

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
        "message" => "Format JSON tidak valid"
    ]);
    exit;
}

$name = trim($data["name"] ?? "");
$region = trim($data["region"] ?? "");
$description = trim($data["description"] ?? "");
$lat = $data["center_lat"] ?? null;
$lng = $data["center_lng"] ?? null;

if (
    $name === "" ||
    !is_numeric($lat) ||
    !is_numeric($lng)
) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Nama dan koordinat lokasi wajib diisi dengan benar"
    ]);
    exit;
}

if (
    !is_finite((float) $lat) ||
    !is_finite((float) $lng) ||
    (float) $lat < -90 || (float) $lat > 90 ||
    (float) $lng < -180 || (float) $lng > 180
) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Koordinat latitude atau longitude tidak valid"
    ]);
    exit;
}

try {
    $db = (new Database())->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    $query = $db->prepare(
        "INSERT INTO plant_locations
        (name, region, description, center_lat, center_lng)
        VALUES
        (:name, :region, :description, :lat, :lng)"
    );

    $query->execute([
        "name" => $name,
        "region" => $region ?: null,
        "description" => $description ?: null,
        "lat" => $lat,
        "lng" => $lng
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Lokasi berhasil ditambahkan",
        "data" => [
            "id_p_location" => (int) $db->lastInsertId(),
            "name" => $name,
            "region" => $region ?: null,
            "description" => $description ?: null,
            "center_lat" => $lat,
            "center_lng" => $lng
        ]
    ]);

} catch (PDOException $e) {
    error_log("Plant Location Create Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
