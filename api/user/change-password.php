<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../api/cors.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}

try {
    $db = (new Database())->connect();

    // Autentikasi pengguna
    $user = authenticateUser($db);

    // Membaca JSON
    $input = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Format JSON tidak valid"
        ]);
        exit;
    }

    $currentPassword = $input["current_password"] ?? "";
    $newPassword = $input["new_password"] ?? "";
    $confirmPassword = $input["confirm_password"] ?? "";

    // Validasi input
    if (
        !is_string($currentPassword) ||
        !is_string($newPassword) ||
        !is_string($confirmPassword) ||
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "message" => "Semua field password wajib diisi"
        ]);
        exit;
    }

    // Validasi panjang password baru
    if (strlen($newPassword) < 8) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "message" => "Password baru minimal 8 karakter"
        ]);
        exit;
    }

    if (strlen($newPassword) > 72) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "message" => "Password baru maksimal 72 byte"
        ]);
        exit;
    }

    // Pastikan konfirmasi password cocok
    if ($newPassword !== $confirmPassword) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "message" => "Konfirmasi password tidak cocok"
        ]);
        exit;
    }

    // Ambil password lama dari database
    $query = $db->prepare(
        "SELECT password
         FROM users
         WHERE id_user = :id_user"
    );

    $query->execute([
        "id_user" => $user["id_user"]
    ]);

    $account = $query->fetch(PDO::FETCH_ASSOC);

    if (!$account) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Akun tidak ditemukan"
        ]);
        exit;
    }

    // Verifikasi password saat ini
    if (!password_verify(
        $currentPassword,
        $account["password"]
    )) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Password saat ini salah"
        ]);
        exit;
    }

    // Pastikan password baru berbeda
    if (password_verify(
        $newPassword,
        $account["password"]
    )) {
        http_response_code(422);
        echo json_encode([
            "success" => false,
            "message" => "Password baru harus berbeda dari password saat ini"
        ]);
        exit;
    }

    // Hash password baru
    $hashedPassword = password_hash(
        $newPassword,
        PASSWORD_DEFAULT
    );

    // Update password dan hapus semua token login
    $db->beginTransaction();

    $update = $db->prepare(
        "UPDATE users
         SET password = :password
         WHERE id_user = :id_user"
    );

    $update->execute([
        "password" => $hashedPassword,
        "id_user" => $user["id_user"]
    ]);

    // Cabut semua sesi login pengguna
    $deleteTokens = $db->prepare(
        "DELETE FROM auth_tokens
         WHERE user_id = :user_id"
    );

    $deleteTokens->execute([
        "user_id" => $user["id_user"]
    ]);

    $db->commit();

    echo json_encode([
        "success" => true,
        "message" => "Password berhasil diubah. Silakan login kembali."
    ]);

} catch (Throwable $e) {

    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log("Change Password Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
