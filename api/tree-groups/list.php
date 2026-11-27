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

try {
    $db = (new Database())->connect();
    authenticateUser($db);

    $query = $db->query(
        "SELECT
            tg.*,
            pl.name AS location_name,
            u.name AS creator_name
         FROM tree_groups tg
         INNER JOIN plant_locations pl
            ON pl.id_p_location = tg.id_p_location
         INNER JOIN users u
            ON u.id_user = tg.created_by
         ORDER BY tg.created_at DESC"
    );

    $groups = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "message" => "Data kelompok pohon berhasil diambil",
        "data" => $groups
    ]);

} catch (PDOException $e) {
    error_log("Tree Groups List Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
