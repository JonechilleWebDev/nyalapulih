<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method tidak diizinkan"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Format JSON tidak valid"]);
    exit;
}

$id = $data["id_t_logs"] ?? null;
$heightCm = $data["height_cm"] ?? null;
$healthStatus = $data["health_status"] ?? "";
$notes = trim($data["notes"] ?? "");
$latitude = $data["captured_lat"] ?? null;
$longitude = $data["captured_lng"] ?? null;
$capturedAt = $data["captured_at"] ?? "";

if (
    !filter_var($id, FILTER_VALIDATE_INT) ||
    (int) $id <= 0 ||
    !in_array($healthStatus, ["sehat", "butuh_perawatan", "mati"], true) ||
    !is_numeric($latitude) ||
    !is_numeric($longitude) ||
    !is_finite((float) $latitude) ||
    !is_finite((float) $longitude) ||
    (float) $latitude < -90 ||
    (float) $latitude > 90 ||
    (float) $longitude < -180 ||
    (float) $longitude > 180
) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Data pemantauan tidak valid"]);
    exit;
}

if ($heightCm !== null && $heightCm !== "" &&
    (!ctype_digit((string) $heightCm) || (int) $heightCm < 0)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "height_cm harus berupa angka positif atau kosong"]);
    exit;
}

$date = DateTime::createFromFormat("!Y-m-d H:i:s", $capturedAt);

if (!$date || $date->format("Y-m-d H:i:s") !== $capturedAt) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Format captured_at harus Y-m-d H:i:s"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    $stmt = $db->prepare("
        SELECT id_t_logs
        FROM tree_logs
        WHERE id_t_logs = :id
          AND verification_status = 'pending'
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $id]);

    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Laporan tidak ditemukan atau sudah diverifikasi"
        ]);
        exit;
    }

    $stmt = $db->prepare("
        UPDATE tree_logs
        SET
            height_cm = :height,
            health_status = :health,
            notes = :notes,
            captured_lat = :latitude,
            captured_lng = :longitude,
            captured_at = :captured_at
        WHERE id_t_logs = :id
          AND verification_status = 'pending'
    ");

    $stmt->execute([
        "height" => $heightCm === "" ? null : $heightCm,
        "health" => $healthStatus,
        "notes" => $notes === "" ? null : $notes,
        "latitude" => $latitude,
        "longitude" => $longitude,
        "captured_at" => $capturedAt,
        "id" => (int) $id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Laporan pemantauan berhasil diperbarui"
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
