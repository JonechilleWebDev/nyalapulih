<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method tidak diizinkan"]);
    exit;
}

$id = $_GET["id"] ?? "";

if (!ctype_digit((string) $id) || (int) $id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID pohon tidak valid"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    authenticateUser($db);

    $stmt = $db->prepare("
        SELECT
            t.*,
            pl.name AS location_name,
            tg.group_name,
            u.name AS planter_name
        FROM trees t
        JOIN plant_locations pl
            ON t.id_p_location = pl.id_p_location
        JOIN tree_groups tg
            ON t.id_t_groups = tg.id_t_groups
        JOIN users u
            ON t.planted_by = u.id_user
        WHERE t.id_tree = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $id]);
    $tree = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tree) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Pohon tidak ditemukan"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Detail pohon berhasil diambil",
        "data" => $tree
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
