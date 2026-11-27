<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method tidak diizinkan"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    authenticateUser($db);

    $stmt = $db->query("
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
        ORDER BY t.planted_at DESC
    ");

    echo json_encode([
        "success" => true,
        "message" => "Data pohon berhasil diambil",
        "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
