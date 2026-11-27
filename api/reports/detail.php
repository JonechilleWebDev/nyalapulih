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
    echo json_encode(["success" => false, "message" => "ID laporan tidak valid"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    authenticateUser($db);

    $stmt = $db->prepare("
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
        WHERE r.id_report = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Laporan tidak ditemukan"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Detail laporan berhasil diambil",
        "data" => $report
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
