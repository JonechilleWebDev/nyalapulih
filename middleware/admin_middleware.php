<?php

function requireAdmin(array $user): void
{
    if (($user["role"] ?? "") !== "admin") {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Akses ditolak. Khusus admin."
        ]);
        exit;
    }
}
