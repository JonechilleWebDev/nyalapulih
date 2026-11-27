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
    echo json_encode(["success" => false, "message" => "ID riwayat tidak valid"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    authenticateUser($db);

    $stmt = $db->prepare("
        SELECT
            tl.*,
            t.tree_code,
            tg.group_name,
            updater.name AS updater_name,
            verifier.name AS verifier_name
        FROM tree_logs tl
        JOIN trees t
            ON tl.id_tree = t.id_tree
        JOIN tree_groups tg
            ON tl.id_t_groups = tg.id_t_groups
        JOIN users updater
            ON tl.updated_by = updater.id_user
        LEFT JOIN users verifier
            ON tl.verified_by = verifier.id_user
        WHERE tl.id_t_logs = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $id]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$log) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Riwayat tidak ditemukan"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Detail riwayat berhasil diambil",
        "data" => $log
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
