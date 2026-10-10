<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

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

    // Total members.
    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM members"
    );

    $totalMembers = (int) $stmt->fetchColumn();


    // Active members.
    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM members
         WHERE status = 'active'"
    );

    $activeMembers = (int) $stmt->fetchColumn();


    // Total trainers.
    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM trainers"
    );

    $totalTrainers = (int) $stmt->fetchColumn();


    // Active membership plans.
    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM membership_plans
         WHERE status = 'active'"
    );

    $totalPlans = (int) $stmt->fetchColumn();


    // Number of completed payments.
    $stmt = $pdo->query(
        "SELECT COUNT(*)
         FROM payments
         WHERE payment_status = 'completed'"
    );

    $completedPayments = (int) $stmt->fetchColumn();


    // Total revenue from completed payments.
    $stmt = $pdo->query(
        "SELECT COALESCE(SUM(amount), 0)
         FROM payments
         WHERE payment_status = 'completed'"
    );

    $totalRevenue = (float) $stmt->fetchColumn();


    // Five most recently registered members.
    $stmt = $pdo->query(
        "SELECT id, name, email, phone, join_date, status
         FROM members
         ORDER BY created_at DESC, id DESC
         LIMIT 5"
    );

    $recentMembers = $stmt->fetchAll();


    echo json_encode([
        'success' => true,

        'stats' => [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'total_trainers' => $totalTrainers,
            'total_plans' => $totalPlans,
            'completed_payments' => $completedPayments,
            'total_revenue' => $totalRevenue
        ],

        'recent_members' => $recentMembers
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load dashboard statistics.'
    ]);
}
