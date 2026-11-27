<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";
require_once __DIR__ . "/../../middleware/tree_log_helpers.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
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

    $user = authenticateUser($db);
    requireAdmin($user);

    $stmt = $db->prepare("
        SELECT id_tree, photo_path
        FROM tree_logs
        WHERE id_t_logs = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $id]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$log) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Riwayat tidak ditemukan"]);
        exit;
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        DELETE FROM tree_logs
        WHERE id_t_logs = :id
    ");

    $stmt->execute(["id" => (int) $id]);

    syncTreeAfterLogChange($db, (int) $log["id_tree"]);

    $db->commit();

    deleteTreeLogPhoto($log["photo_path"]);

    echo json_encode([
        "success" => true,
        "message" => "Riwayat pemantauan berhasil dihapus"
    ]);

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log($e->getMessage());

    if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Riwayat tidak dapat dihapus karena masih memiliki data terkait"]);
        exit;
    }

    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
