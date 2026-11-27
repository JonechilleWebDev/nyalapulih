<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Content-Type: application/json");
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method tidak diizinkan"]);
    exit;
}

$id = $_GET["id"] ?? "";

if (!ctype_digit((string) $id) || (int) $id <= 0) {
    header("Content-Type: application/json");
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID laporan tidak valid"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    authenticateUser($db);

    $stmt = $db->prepare("
        SELECT file_path, report_title
        FROM reports
        WHERE id_report = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        header("Content-Type: application/json");
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Laporan tidak ditemukan"]);
        exit;
    }

    $prefix = "uploads/reports/";

    if (strpos($report["file_path"], $prefix) !== 0) {
        throw new RuntimeException("Lokasi file tidak valid");
    }

    $filename = basename($report["file_path"]);
    $filePath = dirname(__DIR__, 2) . "/" . $prefix . $filename;

    if (!is_file($filePath) || !is_readable($filePath)) {
        header("Content-Type: application/json");
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "File PDF tidak ditemukan"]);
        exit;
    }

    $downloadName = "laporan-" . (int) $id . ".pdf";

    header("Content-Type: application/pdf");
    header("Content-Length: " . filesize($filePath));
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header("X-Content-Type-Options: nosniff");

    readfile($filePath);
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);

} catch (RuntimeException $e) {
    error_log($e->getMessage());
    header("Content-Type: application/json");
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "File laporan tidak dapat diakses"]);
}
