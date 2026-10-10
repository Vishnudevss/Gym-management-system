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

    // Fetch active membership plans
    $planSql = "
        SELECT id, plan_name, duration_months, price
        FROM membership_plans
        WHERE status = 'active'
        ORDER BY duration_months ASC
    ";

    $planStmt = $pdo->query($planSql);
    $plans = $planStmt->fetchAll(PDO::FETCH_ASSOC);


    // Fetch active trainers
    $trainerSql = "
        SELECT id, name, specialization
        FROM trainers
        WHERE status = 'active'
        ORDER BY name ASC
    ";

    $trainerStmt = $pdo->query($trainerSql);
    $trainers = $trainerStmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([
        'success' => true,
        'plans' => $plans,
        'trainers' => $trainers
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load membership options.'
    ]);
}