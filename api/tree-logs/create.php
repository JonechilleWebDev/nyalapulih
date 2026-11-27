<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/tree_log_helpers.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method tidak diizinkan"]);
    exit;
}

$idTree = $_POST["id_tree"] ?? null;
$heightCm = $_POST["height_cm"] ?? null;
$healthStatus = $_POST["health_status"] ?? "";
$notes = trim($_POST["notes"] ?? "");
$latitude = $_POST["captured_lat"] ?? null;
$longitude = $_POST["captured_lng"] ?? null;
$capturedAt = $_POST["captured_at"] ?? "";

if (
    !filter_var($idTree, FILTER_VALIDATE_INT) ||
    (int) $idTree <= 0 ||
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

$photoPath = null;

try {
    $database = new Database();
    $db = $database->connect();

    $user = authenticateUser($db);

    $stmt = $db->prepare("
        SELECT id_t_groups
        FROM trees
        WHERE id_tree = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $idTree]);
    $tree = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tree) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Pohon tidak ditemukan"]);
        exit;
    }

    $photoPath = saveTreeLogPhoto($_FILES["photo"] ?? []);

    $stmt = $db->prepare("
        INSERT INTO tree_logs (
            id_tree,
            id_t_groups,
            updated_by,
            photo_path,
            height_cm,
            health_status,
            notes,
            captured_lat,
            captured_lng,
            captured_at,
            verification_status
        ) VALUES (
            :tree,
            :group_id,
            :user_id,
            :photo,
            :height,
            :health,
            :notes,
            :latitude,
            :longitude,
            :captured_at,
            'pending'
        )
    ");

    $stmt->execute([
        "tree" => (int) $idTree,
        "group_id" => $tree["id_t_groups"],
        "user_id" => $user["id_user"],
        "photo" => $photoPath,
        "height" => $heightCm === "" ? null : $heightCm,
        "health" => $healthStatus,
        "notes" => $notes === "" ? null : $notes,
        "latitude" => $latitude,
        "longitude" => $longitude,
        "captured_at" => $capturedAt
    ]);

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Laporan pemantauan berhasil dikirim dan menunggu verifikasi",
        "data" => [
            "id_t_logs" => (int) $db->lastInsertId(),
            "id_tree" => (int) $idTree,
            "photo_path" => $photoPath,
            "verification_status" => "pending"
        ]
    ]);

} catch (RuntimeException $e) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);

} catch (PDOException $e) {
    if ($photoPath !== null) {
        deleteTreeLogPhoto($photoPath);
    }

    error_log($e->getMessage());

    if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Data pohon tidak valid"]);
        exit;
    }

    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
