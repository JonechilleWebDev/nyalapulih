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

$id = $_GET["id"] ?? "";

if (!ctype_digit((string) $id) || (int) $id < 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "ID kelompok tidak valid"
    ]);
    exit;
}

try {
    $db = (new Database())->connect();
    authenticateUser($db);

    $query = $db->prepare(
        "SELECT
            tg.*,
            pl.name AS location_name,
            u.name AS creator_name
         FROM tree_groups tg
         INNER JOIN plant_locations pl
            ON pl.id_p_location = tg.id_p_location
         INNER JOIN users u
            ON u.id_user = tg.created_by
         WHERE tg.id_t_groups = :id
         LIMIT 1"
    );

    $query->execute(["id" => $id]);
    $group = $query->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Kelompok pohon tidak ditemukan"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Detail kelompok berhasil diambil",
        "data" => $group
    ]);

} catch (PDOException $e) {
    error_log("Tree Groups Detail Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
