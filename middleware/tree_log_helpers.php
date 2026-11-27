<?php

function saveTreeLogPhoto(array $file): string
{
    if (!isset($file["error"]) || $file["error"] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Foto dokumentasi wajib diunggah");
    }

    if ($file["size"] > 5 * 1024 * 1024) {
        throw new RuntimeException("Ukuran foto maksimal 5 MB");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowedTypes[$mime])) {
        throw new RuntimeException("Format foto harus JPG, PNG, atau WEBP");
    }

    $directory = dirname(__DIR__) . "/uploads/tree-photos";

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException("Folder penyimpanan foto tidak dapat dibuat");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mime];

    if (!move_uploaded_file($file["tmp_name"], $directory . "/" . $filename)) {
        throw new RuntimeException("Gagal menyimpan foto");
    }

    return "uploads/tree-photos/" . $filename;
}

function deleteTreeLogPhoto(string $path): void
{
    $prefix = "uploads/tree-photos/";

    if (strpos($path, $prefix) !== 0) {
        return;
    }

    $filename = basename($path);
    $fullPath = dirname(__DIR__) . "/" . $prefix . $filename;

    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}

function syncTreeAfterLogChange(PDO $db, int $idTree): void
{
    $stmt = $db->prepare("
        SELECT id_t_groups
        FROM trees
        WHERE id_tree = :id
        LIMIT 1
    ");

    $stmt->execute(["id" => $idTree]);
    $tree = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tree) {
        return;
    }

    $idGroup = (int) $tree["id_t_groups"];

    // Ambil kondisi dari laporan approved terbaru.
    $stmt = $db->prepare("
        SELECT health_status
        FROM tree_logs
        WHERE id_tree = :id
          AND verification_status = 'approved'
        ORDER BY captured_at DESC, id_t_logs DESC
        LIMIT 1
    ");

    $stmt->execute(["id" => $idTree]);
    $latestLog = $stmt->fetch(PDO::FETCH_ASSOC);

    $currentStatus = $latestLog
        ? $latestLog["health_status"]
        : "sehat";

    $stmt = $db->prepare("
        UPDATE trees
        SET current_status = :status
        WHERE id_tree = :id
    ");

    $stmt->execute([
        "status" => $currentStatus,
        "id" => $idTree
    ]);

    // Hitung ulang jumlah pohon yang telah disetujui.
    $stmt = $db->prepare("
        SELECT
            COUNT(*) AS total_planted,
            COALESCE(SUM(
                CASE
                    WHEN current_status != 'mati' THEN 1
                    ELSE 0
                END
            ), 0) AS alive_count
        FROM trees
        WHERE id_t_groups = :id
          AND verification_status = 'approved'
    ");

    $stmt->execute(["id" => $idGroup]);
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("
        UPDATE tree_groups
        SET
            total_planted = :total,
            alive_count = :alive
        WHERE id_t_groups = :id
    ");

    $stmt->execute([
        "total" => $counts["total_planted"],
        "alive" => $counts["alive_count"],
        "id" => $idGroup
    ]);
}
