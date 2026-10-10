<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$status = strtolower(trim($_POST['status'] ?? ''));

if (!$id || $id < 1 || !in_array($status, ['active', 'inactive'], true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid membership plan or status.'
    ]);
    exit;
}

try {
    $check = $pdo->prepare(
        'SELECT id, plan_name, status
         FROM membership_plans
         WHERE id = ?
         LIMIT 1'
    );

    $check->execute([$id]);
    $plan = $check->fetch(PDO::FETCH_ASSOC);

    if (!$plan) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Membership plan not found.'
        ]);
        exit;
    }

    if ($plan['status'] === $status) {
        echo json_encode([
            'success' => true,
            'message' => 'Membership plan is already ' . $status . '.',
            'status' => $status
        ]);
        exit;
    }

    $update = $pdo->prepare(
        'UPDATE membership_plans
         SET status = ?
         WHERE id = ?'
    );

    $update->execute([$status, $id]);

    echo json_encode([
        'success' => true,
        'message' => $status === 'active'
            ? 'Membership plan activated successfully.'
            : 'Membership plan deactivated successfully.',
        'status' => $status
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update membership plan status.'
    ]);
}