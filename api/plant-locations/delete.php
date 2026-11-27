<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";
require_once __DIR__ . "/../../middleware/admin_middleware.php";

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

$id = $_GET["id"] ?? "";

if (
    !ctype_digit((string) $id) ||
    (int) $id < 1
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "ID lokasi tidak valid"
    ]);
    exit;
}

try {
    $db = (new Database())->connect();

    $user = authenticateUser($db);
    requireAdmin($user);

    $query = $db->prepare(
        "DELETE FROM plant_locations
         WHERE id_p_location = :id"
    );

    $query->execute(["id" => $id]);

    if ($query->rowCount() === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Lokasi tidak ditemukan"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Lokasi berhasil dihapus"
    ]);

} catch (PDOException $e) {
    error_log("Plant Location Delete Error: " . $e->getMessage());

    // Lokasi masih digunakan oleh tabel lain
    if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Lokasi tidak dapat dihapus karena masih memiliki data terkait"
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
