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
                p.id,
                p.member_id,
                m.name AS member_name,
                p.amount,
                p.payment_date,
                p.payment_method,
                p.transaction_reference,
                p.payment_status,
                p.notes,
                p.created_at
            FROM payments p
            INNER JOIN members m ON p.member_id = m.id
            ORDER BY p.id DESC";

    $stmt = $pdo->query($sql);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'payments' => $payments
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load payment records.'
    ]);
}