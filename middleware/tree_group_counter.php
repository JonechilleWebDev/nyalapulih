<?php

function recalculateTreeGroupCounters(PDO $db, int $idGroup): void
{
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
