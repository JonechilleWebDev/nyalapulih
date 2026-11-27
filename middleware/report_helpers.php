<?php

function saveReportPdf(array $file): string
{
    if (!isset($file["error"]) || $file["error"] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("File laporan PDF wajib diunggah");
    }

    if ($file["size"] > 10 * 1024 * 1024) {
        throw new RuntimeException("Ukuran PDF maksimal 10 MB");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    if ($mime !== "application/pdf") {
        throw new RuntimeException("File harus berformat PDF");
    }

    $handle = fopen($file["tmp_name"], "rb");
    $signature = $handle ? fread($handle, 5) : "";
    if ($handle) {
        fclose($handle);
    }

    if ($signature !== "%PDF-") {
        throw new RuntimeException("File PDF tidak valid");
    }

    $directory = dirname(__DIR__) . "/uploads/reports";

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException("Folder penyimpanan laporan gagal dibuat");
    }

    $filename = bin2hex(random_bytes(16)) . ".pdf";

    if (!move_uploaded_file($file["tmp_name"], $directory . "/" . $filename)) {
        throw new RuntimeException("Gagal menyimpan file PDF");
    }

    return "uploads/reports/" . $filename;
}

function deleteReportFile(string $path): void
{
    $prefix = "uploads/reports/";

    if (strpos($path, $prefix) !== 0) {
        return;
    }

    $filename = basename($path);
    $fullPath = dirname(__DIR__) . "/" . $prefix . $filename;

    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}

function calculateLocationReportStats(PDO $db, int $idLocation): array
{
    $stmt = $db->prepare("
        SELECT
            COUNT(*) AS total_trees,
            COALESCE(SUM(
                CASE
                    WHEN current_status != 'mati' THEN 1
                    ELSE 0
                END
            ), 0) AS alive_trees
        FROM trees
        WHERE id_p_location = :location
          AND verification_status = 'approved'
    ");

    $stmt->execute(["location" => $idLocation]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $total = (int) $stats["total_trees"];
    $alive = (int) $stats["alive_trees"];

    $survivalRate = $total > 0
        ? round(($alive / $total) * 100, 2)
        : 0;

    return [
        "total_trees" => $total,
        "survival_rate" => $survivalRate
    ];
}
