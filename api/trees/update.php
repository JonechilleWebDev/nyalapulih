<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";
require_once __DIR__ . "/../../middleware/tree_group_counter.php";

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

$id = $data["id_tree"] ?? null;
$idGroup = $data["id_t_groups"] ?? null;
$treeCode = trim($data["tree_code"] ?? "");
$latitude = $data["latitude"] ?? null;
$longitude = $data["longitude"] ?? null;
$plantedAt = $data["planted_at"] ?? "";

if (
    !filter_var($id, FILTER_VALIDATE_INT) || (int) $id <= 0 ||
    !filter_var($idGroup, FILTER_VALIDATE_INT) || (int) $idGroup <= 0 ||
    $treeCode === "" || strlen($treeCode) > 50 ||
    !is_numeric($latitude) || !is_numeric($longitude) ||
    !is_finite((float) $latitude) || !is_finite((float) $longitude) ||
    (float) $latitude < -90 || (float) $latitude > 90 ||
    (float) $longitude < -180 || (float) $longitude > 180
) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Data pohon tidak valid"]);
    exit;
}

$date = DateTime::createFromFormat("!Y-m-d H:i:s", $plantedAt);

if (!$date || $date->format("Y-m-d H:i:s") !== $plantedAt) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Format planted_at harus Y-m-d H:i:s"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    $stmt = $db->prepare("
        SELECT id_tree, id_t_groups
        FROM trees
        WHERE id_tree = :id
        LIMIT 1
    ");
    $stmt->execute(["id" => (int) $id]);

    $tree = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tree) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Pohon tidak ditemukan"]);
        exit;
    }
    $oldGroupId = (int) $tree["id_t_groups"];

    $stmt = $db->prepare("
        SELECT id_p_location, species
        FROM tree_groups
        WHERE id_t_groups = :id
        LIMIT 1
    ");
    $stmt->execute(["id" => (int) $idGroup]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Kelompok pohon tidak ditemukan"]);
        exit;
    }

    $stmt = $db->prepare("
        UPDATE trees
        SET
            tree_code = :tree_code,
            id_p_location = :location,
            id_t_groups = :group_id,
            species = :species,
            latitude = :latitude,
            longitude = :longitude,
            planted_at = :planted_at
        WHERE id_tree = :id
    ");

    $stmt->execute([
        "tree_code" => $treeCode,
        "location" => $group["id_p_location"],
        "group_id" => (int) $idGroup,
        "species" => $group["species"],
        "latitude" => $latitude,
        "longitude" => $longitude,
        "planted_at" => $plantedAt,
        "id" => (int) $id
    ]);

    if ($oldGroupId !== (int) $idGroup) {
        recalculateTreeGroupCounters($db, $oldGroupId);
    }
    recalculateTreeGroupCounters($db, (int) $idGroup);

    echo json_encode([
        "success" => true,
        "message" => "Data pohon berhasil diperbarui"
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Kode pohon sudah digunakan"]);
        exit;
    }

    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
