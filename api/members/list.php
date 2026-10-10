<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

try {
    $sql = "SELECT
                m.id,
                m.name,
                m.email,
                m.phone,
                m.gender,
                m.join_date,
                m.membership_start,
                m.membership_end,
                m.status,
                mp.plan_name,
                t.name AS trainer_name
            FROM members m
            LEFT JOIN membership_plans mp
                ON m.membership_plan_id = mp.id
            LEFT JOIN trainers t
                ON m.trainer_id = t.id
            ORDER BY m.id DESC";

    $stmt = $pdo->query($sql);

    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'members' => $members
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load members.'
    ]);
}
?>
