<?php

function authenticateUser(PDO $db): array
{
    $authorization = $_SERVER["HTTP_AUTHORIZATION"] ?? "";

    if (empty($authorization) && function_exists("getallheaders")) {
        $headers = getallheaders();

        $authorization = $headers["Authorization"]
            ?? $headers["authorization"]
            ?? "";
    }

    if (!preg_match("/^Bearer\s+(\S+)$/i", $authorization, $matches)) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Token tidak ditemukan"
        ]);
        exit;
    }

    $tokenHash = hash("sha256", $matches[1]);

    $query = $db->prepare(
        "SELECT
            users.id_user,
            users.name,
            users.email,
            users.role,
            users.organization
         FROM auth_tokens
         INNER JOIN users
            ON users.id_user = auth_tokens.user_id
         WHERE auth_tokens.token_hash = :token_hash
           AND auth_tokens.expires_at > NOW()
         LIMIT 1"
    );

    $query->execute([
        "token_hash" => $tokenHash
    ]);

    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Token tidak valid atau sudah kedaluwarsa"
        ]);
        exit;
    }

    return $user;
}
