<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";
require_once __DIR__ . "/../../middleware/report_helpers.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method tidak diizinkan"]);
    exit;
}

$idLocation = $_POST["id_p_location"] ?? null;
$title = trim($_POST["report_title"] ?? "");

if (
    !filter_var($idLocation, FILTER_VALIDATE_INT) ||
    (int) $idLocation <= 0 ||
    $title === "" ||
    strlen($title) > 180
) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Data laporan tidak valid"]);
    exit;
}

$filePath = null;

try {
    $database = new Database();
    $db = $database->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    $stmt = $db->prepare("
        SELECT id_p_location
        FROM plant_locations
        WHERE id_p_location = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $idLocation]);

    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Lokasi penanaman tidak ditemukan"]);
        exit;
    }

    $stats = calculateLocationReportStats($db, (int) $idLocation);

    $filePath = saveReportPdf($_FILES["report_file"] ?? []);

    $stmt = $db->prepare("
        INSERT INTO reports (
            id_p_location,
            generated_by,
            report_title,
            total_trees,
            survival_rate,
            file_path
        ) VALUES (
            :location,
            :user,
            :title,
            :total,
            :survival,
            :file
        )
    ");

    $stmt->execute([
        "location" => (int) $idLocation,
        "user" => $user["id_user"],
        "title" => $title,
        "total" => $stats["total_trees"],
        "survival" => $stats["survival_rate"],
        "file" => $filePath
    ]);

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Laporan berhasil dibuat",
        "data" => [
            "id_report" => (int) $db->lastInsertId(),
            "report_title" => $title,
            "total_trees" => $stats["total_trees"],
            "survival_rate" => $stats["survival_rate"]
        ]
    ]);

} catch (RuntimeException $e) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);

} catch (PDOException $e) {
    if ($filePath !== null) {
        deleteReportFile($filePath);
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
