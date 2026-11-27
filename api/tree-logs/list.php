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
        ORDER BY tl.captured_at DESC
    ");

    echo json_encode([
        "success" => true,
        "message" => "Data riwayat pemantauan berhasil diambil",
        "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
