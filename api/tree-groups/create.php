<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";

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
    !$idLocation ||
    $idLocation < 1 ||
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

// Validasi format tanggal
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

    // Ambil identitas pengguna dari token
    $user = authenticateUser($db);

    // Pastikan lokasi tersedia
    $check = $db->prepare(
        "SELECT id_p_location
         FROM plant_locations
         WHERE id_p_location = :id"
    );

    $check->execute(["id" => $idLocation]);

    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Lokasi penanaman tidak ditemukan"
        ]);
        exit;
    }

    $query = $db->prepare(
        "INSERT INTO tree_groups
        (
            id_p_location,
            created_by,
            group_name,
            species,
            total_planted,
            alive_count,
            center_lat,
            center_lng,
            planted_at
        )
        VALUES
        (
            :location,
            :created_by,
            :group_name,
            :species,
            0,
            0,
            :lat,
            :lng,
            :planted_at
        )"
    );

    $query->execute([
        "location" => $idLocation,
        "created_by" => $user["id_user"],
        "group_name" => $groupName,
        "species" => $species,
        "lat" => $lat,
        "lng" => $lng,
        "planted_at" => $plantedAt
    ]);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Kelompok pohon berhasil dibuat",
        "data" => [
            "id_t_groups" => (int) $db->lastInsertId(),
            "id_p_location" => $idLocation,
            "created_by" => (int) $user["id_user"],
            "group_name" => $groupName,
            "species" => $species,
            "total_planted" => 0,
            "alive_count" => 0,
            "center_lat" => $lat,
            "center_lng" => $lng,
            "planted_at" => $plantedAt
        ]
    ]);

} catch (PDOException $e) {
    error_log("Tree Groups Create Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
