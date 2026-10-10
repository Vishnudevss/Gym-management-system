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

if (!$id || $id < 1) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid member ID.'
    ]);
    exit;
}

try {
    $check = $pdo->prepare('SELECT id FROM members WHERE id = ?');
    $check->execute([$id]);

    if (!$check->fetch()) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Member not found.'
        ]);
        exit;
    }

    $paymentCheck = $pdo->prepare(
        'SELECT id FROM payments WHERE member_id = ? LIMIT 1'
    );
    $paymentCheck->execute([$id]);

    if ($paymentCheck->fetch()) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'This member has payment records and cannot be deleted. You can mark the member as inactive instead.'
        ]);
        exit;
    }

    $delete = $pdo->prepare('DELETE FROM members WHERE id = ?');
    $delete->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Member deleted successfully.'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to delete member. Please try again.'
    ]);
}