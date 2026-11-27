<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";
require_once __DIR__ . "/../../middleware/tree_log_helpers.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "PATCH") {
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

$id = $data["id_t_logs"] ?? null;
$status = $data["verification_status"] ?? "";

if (
    !filter_var($id, FILTER_VALIDATE_INT) ||
    (int) $id <= 0 ||
    !in_array($status, ["approved", "rejected"], true)
) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Data verifikasi tidak valid"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    $db->beginTransaction();

    $stmt = $db->prepare("
        SELECT id_tree, verification_status
        FROM tree_logs
        WHERE id_t_logs = :id
        FOR UPDATE
    ");

    $stmt->execute(["id" => (int) $id]);
    $log = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$log) {
        $db->rollBack();

        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Riwayat tidak ditemukan"]);
        exit;
    }

    if ($log["verification_status"] !== "pending") {
        $db->rollBack();

        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Laporan sudah diverifikasi sebelumnya"]);
        exit;
    }

    $stmt = $db->prepare("
        UPDATE tree_logs
        SET
            verification_status = :status,
            verified_by = :admin
        WHERE id_t_logs = :id
          AND verification_status = 'pending'
    ");

    $stmt->execute([
        "status" => $status,
        "admin" => $user["id_user"],
        "id" => (int) $id
    ]);

    syncTreeAfterLogChange($db, (int) $log["id_tree"]);

    $db->commit();

    echo json_encode([
        "success" => true,
        "message" => $status === "approved"
            ? "Laporan pemantauan berhasil disetujui"
            : "Laporan pemantauan berhasil ditolak",
        "data" => [
            "id_t_logs" => (int) $id,
            "verification_status" => $status,
            "verified_by" => $user["id_user"]
        ]
    ]);

} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
