<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";
require_once __DIR__ . "/../../middleware/report_helpers.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
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

    $user = authenticateUser($db);
    requireAdmin($user);

    $stmt = $db->prepare("
        SELECT file_path
        FROM reports
        WHERE id_report = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Laporan tidak ditemukan"]);
        exit;
    }

    $stmt = $db->prepare("
        DELETE FROM reports
        WHERE id_report = :id
    ");

    $stmt->execute(["id" => (int) $id]);

    deleteReportFile($report["file_path"]);

    echo json_encode([
        "success" => true,
        "message" => "Laporan berhasil dihapus"
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
