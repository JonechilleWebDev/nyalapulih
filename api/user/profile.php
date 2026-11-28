<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../middleware/auth_middleware.php";

try {
    $db = (new Database())->connect();

    // Autentikasi pengguna
    $user = authenticateUser($db);

    $method = $_SERVER["REQUEST_METHOD"];

    // GET: Mengambil profil
    if ($method === "GET") {

        $query = $db->prepare(
            "SELECT
                id_user,
                name,
                email,
                role,
                organization,
                created_at
             FROM users
             WHERE id_user = :id_user"
        );

        $query->execute([
            "id_user" => $user["id_user"]
        ]);

        $profile = $query->fetch(PDO::FETCH_ASSOC);

        if (!$profile) {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Profil tidak ditemukan"
            ]);
            exit;
        }

        echo json_encode([
            "success" => true,
            "data" => $profile
        ]);
        exit;
    }

    // PUT: Mengubah profil
    if ($method === "PUT") {

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

        $name = trim($input["name"] ?? "");
        $email = trim($input["email"] ?? "");
        $organization = trim($input["organization"] ?? "");

        // Validasi nama
        if ($name === "" || strlen($name) > 120) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "message" => "Nama wajib diisi dan maksimal 120 karakter"
            ]);
            exit;
        }

        // Validasi email
        if (
            $email === "" ||
            strlen($email) > 120 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "message" => "Format email tidak valid"
            ]);
            exit;
        }

        // Validasi organisasi
        if (strlen($organization) > 120) {
            http_response_code(422);
            echo json_encode([
                "success" => false,
                "message" => "Organisasi maksimal 120 karakter"
            ]);
            exit;
        }

        // Periksa apakah email sudah digunakan akun lain
        $checkEmail = $db->prepare(
            "SELECT id_user
             FROM users
             WHERE email = :email
             AND id_user != :id_user"
        );

        $checkEmail->execute([
            "email" => $email,
            "id_user" => $user["id_user"]
        ]);

        if ($checkEmail->fetch()) {
            http_response_code(409);
            echo json_encode([
                "success" => false,
                "message" => "Email sudah digunakan pengguna lain"
            ]);
            exit;
        }

        // Update profil
        $update = $db->prepare(
            "UPDATE users
             SET
                name = :name,
                email = :email,
                organization = :organization
             WHERE id_user = :id_user"
        );

        $update->execute([
            "name" => $name,
            "email" => $email,
            "organization" => $organization !== ""
                ? $organization
                : null,
            "id_user" => $user["id_user"]
        ]);

        // Ambil data terbaru
        $query = $db->prepare(
            "SELECT
                id_user,
                name,
                email,
                role,
                organization,
                created_at
             FROM users
             WHERE id_user = :id_user"
        );

        $query->execute([
            "id_user" => $user["id_user"]
        ]);

        $profile = $query->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "message" => "Profil berhasil diperbarui",
            "data" => $profile
        ]);
        exit;
    }

    // Method selain GET dan PUT
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);

} catch (PDOException $e) {

    error_log("Profile Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);

} catch (Throwable $e) {

    error_log("Profile Error: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi kesalahan server"
    ]);
}
