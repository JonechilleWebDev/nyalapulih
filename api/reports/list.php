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
            r.id_report,
            r.id_p_location,
            pl.name AS location_name,
            r.generated_by,
            u.name AS generator_name,
            r.report_title,
            r.total_trees,
            r.survival_rate,
            r.created_at
        FROM reports r
        JOIN plant_locations pl
            ON r.id_p_location = pl.id_p_location
        JOIN users u
            ON r.generated_by = u.id_user
        ORDER BY r.created_at DESC
    ");

    echo json_encode([
        "success" => true,
        "message" => "Data laporan berhasil diambil",
        "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
