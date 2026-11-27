<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";

if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
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

$id = $data["id_p_location"] ?? null;
$name = trim($data["name"] ?? "");
$region = trim($data["region"] ?? "");
$description = trim($data["description"] ?? "");
$lat = $data["center_lat"] ?? null;
$lng = $data["center_lng"] ?? null;

if (
    !filter_var($id, FILTER_VALIDATE_INT) ||
    (int) $id < 1 ||
    $name === "" ||
    !is_numeric($lat) ||
    !is_numeric($lng)
) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Data lokasi tidak lengkap atau tidak valid"
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

    // Pastikan lokasi tersedia
    $check = $db->prepare(
        "SELECT id_p_location
         FROM plant_locations
         WHERE id_p_location = :id"
    );

    $check->execute(["id" => $id]);

    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Lokasi tidak ditemukan"
        ]);
        exit;
    }

    $query = $db->prepare(
        "UPDATE plant_locations
         SET name = :name,
             region = :region,
             description = :description,
             center_lat = :lat,
             center_lng = :lng
         WHERE id_p_location = :id"
    );

    $query->execute([
        "name" => $name,
        "region" => $region ?: null,
        "description" => $description ?: null,
        "lat" => $lat,
        "lng" => $lng,
        "id" => $id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Lokasi berhasil diperbarui"
    ]);

} catch (PDOException $e) {
    error_log("Plant Location Update Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
