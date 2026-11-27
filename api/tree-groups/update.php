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

$id = filter_var(
    $data["id_t_groups"] ?? null,
    FILTER_VALIDATE_INT
);

$idLocation = filter_var(
    $data["id_p_location"] ?? null,
    FILTER_VALIDATE_INT
);

$groupName = trim($data["group_name"] ?? "");
$species = trim($data["species"] ?? "");
$lat = $data["center_lat"] ?? null;
$lng = $data["center_lng"] ?? null;
$plantedAt = $data["planted_at"] ?? "";

if (
    !$id || $id < 1 ||
    !$idLocation || $idLocation < 1 ||
    $groupName === "" ||
    $species === "" ||
    !is_numeric($lat) ||
    !is_numeric($lng) ||
    !is_string($plantedAt)
) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Data kelompok tidak lengkap atau tidak valid"
    ]);
    exit;
}

// Validasi koordinat
if (
    !is_finite((float) $lat) ||
    !is_finite((float) $lng) ||
    (float) $lat < -90 || (float) $lat > 90 ||
    (float) $lng < -180 || (float) $lng > 180
) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Koordinat tidak valid"
    ]);
    exit;
}

// Validasi tanggal
$date = DateTime::createFromFormat(
    '!Y-m-d H:i:s',
    $plantedAt
);

if (
    !$date ||
    $date->format("Y-m-d H:i:s") !== $plantedAt
) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Format planted_at harus YYYY-MM-DD HH:MM:SS"
    ]);
    exit;
}

try {
    $db = (new Database())->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    // Pastikan kelompok tersedia
    $check = $db->prepare(
        "SELECT id_t_groups
         FROM tree_groups
         WHERE id_t_groups = :id"
    );

    $check->execute(["id" => $id]);

    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Kelompok pohon tidak ditemukan"
        ]);
        exit;
    }

    // Pastikan lokasi tersedia
    $checkLocation = $db->prepare(
        "SELECT id_p_location
         FROM plant_locations
         WHERE id_p_location = :id"
    );

    $checkLocation->execute(["id" => $idLocation]);

    if (!$checkLocation->fetch()) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Lokasi penanaman tidak ditemukan"
        ]);
        exit;
    }

    $query = $db->prepare(
        "UPDATE tree_groups
         SET id_p_location = :location,
             group_name = :group_name,
             species = :species,
             center_lat = :lat,
             center_lng = :lng,
             planted_at = :planted_at
         WHERE id_t_groups = :id"
    );

    $query->execute([
        "location" => $idLocation,
        "group_name" => $groupName,
        "species" => $species,
        "lat" => $lat,
        "lng" => $lng,
        "planted_at" => $plantedAt,
        "id" => $id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Kelompok pohon berhasil diperbarui"
    ]);

} catch (PDOException $e) {
    error_log("Tree Groups Update Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
