<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";

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

$id = $data["id_report"] ?? null;
$title = trim($data["report_title"] ?? "");

if (
    !filter_var($id, FILTER_VALIDATE_INT) ||
    (int) $id <= 0 ||
    $title === "" ||
    strlen($title) > 180
) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Data laporan tidak valid"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    $stmt = $db->prepare("
        UPDATE reports
        SET report_title = :title
        WHERE id_report = :id
    ");

    $stmt->execute([
        "title" => $title,
        "id" => (int) $id
    ]);

    if ($stmt->rowCount() === 0) {
        $check = $db->prepare("
            SELECT id_report, report_title
            FROM reports
            WHERE id_report = :id
            LIMIT 1
        ");

        $check->execute(["id" => (int) $id]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Laporan tidak ditemukan"]);
            exit;
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Judul laporan berhasil diperbarui"
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
