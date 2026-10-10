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

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');

if (!$id || $id < 1) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid trainer ID.'
    ]);
    exit;
}

if (!in_array($status, ['active', 'inactive'], true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid trainer status.'
    ]);
    exit;
}

try {
    $check = $pdo->prepare(
        'SELECT id FROM trainers WHERE id = ? LIMIT 1'
    );

    $check->execute([$id]);

    if (!$check->fetch()) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Trainer not found.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'UPDATE trainers SET status = ? WHERE id = ?'
    );

    $stmt->execute([$status, $id]);

    echo json_encode([
        'success' => true,
        'message' => $status === 'active'
            ? 'Trainer activated successfully.'
            : 'Trainer deactivated successfully.',
        'status' => $status
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update trainer status.'
    ]);
}