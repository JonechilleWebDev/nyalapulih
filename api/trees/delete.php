<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";
require_once __DIR__ . "/../../middleware/tree_group_counter.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method tidak diizinkan"]);
    exit;
}

$id = $_GET["id"] ?? "";

if (!ctype_digit((string) $id) || (int) $id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID pohon tidak valid"]);
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    $stmt = $db->prepare("
        SELECT id_t_groups
        FROM trees
        WHERE id_tree = :id
        LIMIT 1
    ");
    $stmt->execute(["id" => (int) $id]);
    $idGroup = $stmt->fetchColumn();

    if ($idGroup === false) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Pohon tidak ditemukan"]);
        exit;
    }

    $stmt = $db->prepare("
        DELETE FROM trees
        WHERE id_tree = :id
    ");

    $stmt->execute(["id" => (int) $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Pohon tidak ditemukan"]);
        exit;
    }

    recalculateTreeGroupCounters($db, (int) $idGroup);

    echo json_encode([
        "success" => true,
        "message" => "Pohon berhasil dihapus"
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Pohon tidak dapat dihapus karena masih memiliki riwayat pemantauan"
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Terjadi kesalahan server"]);
}
