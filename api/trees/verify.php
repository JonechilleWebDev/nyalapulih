<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";
require_once __DIR__ . "/../../middleware/tree_group_counter.php";

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

$id = $data["id_tree"] ?? null;
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

    $stmt = $db->prepare("
        SELECT verification_status, id_t_groups
        FROM trees
        WHERE id_tree = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => (int) $id]);
    $tree = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tree) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Pohon tidak ditemukan"]);
        exit;
    }

    $idGroup = (int) $tree["id_t_groups"];

    if ($tree["verification_status"] !== "pending") {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Pohon sudah diverifikasi sebelumnya"
        ]);
        exit;
    }

    $stmt = $db->prepare("
        UPDATE trees
        SET verification_status = :status
        WHERE id_tree = :id
          AND verification_status = 'pending'
    ");

    $stmt->execute([
        "status" => $status,
        "id" => (int) $id
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Status pohon telah berubah"]);
        exit;
    }

    recalculateTreeGroupCounters($db, $idGroup);

    echo json_encode([
        "success" => true,
        "message" => $status === "approved"
            ? "Pohon berhasil disetujui"
            : "Pohon berhasil ditolak",
        "data" => [
            "id_tree" => (int) $id,
            "verification_status" => $status,
            "verified_by" => $user["id_user"]
        ]
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}

