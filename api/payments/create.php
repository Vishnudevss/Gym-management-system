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

$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data.'
    ]);
    exit;
}

$memberId = filter_var($data['member_id'] ?? null, FILTER_VALIDATE_INT);
$amount = $data['amount'] ?? null;
$paymentDate = trim($data['payment_date'] ?? '');
$paymentMethod = trim($data['payment_method'] ?? '');
$transactionReference = trim($data['transaction_reference'] ?? '');
$paymentStatus = trim($data['payment_status'] ?? 'completed');
$notes = trim($data['notes'] ?? '');

if (!$memberId || $memberId < 1) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please select a valid member.'
    ]);
    exit;
}

if (!is_numeric($amount) || !is_finite((float) $amount)
    || (float) $amount <= 0 || (float) $amount > 99999999.99) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid payment amount.'
    ]);
    exit;
}

$dateObject = DateTime::createFromFormat('!Y-m-d', $paymentDate);

if (!$dateObject || $dateObject->format('Y-m-d') !== $paymentDate) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid payment date.'
    ]);
    exit;
}

$validMethods = ['cash', 'upi', 'card', 'bank_transfer'];
$validStatuses = ['pending', 'completed', 'failed'];

if (!in_array($paymentMethod, $validMethods, true)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please select a valid payment method.'
    ]);
    exit;
}

if (!in_array($paymentStatus, $validStatuses, true)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please select a valid payment status.'
    ]);
    exit;
}

if (strlen($transactionReference) > 150 || strlen($notes) > 5000) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Transaction reference or notes are too long.'
    ]);
    exit;
}

try {
    $memberStmt = $pdo->prepare(
        'SELECT id FROM members WHERE id = :id'
    );
    $memberStmt->execute(['id' => $memberId]);

    if (!$memberStmt->fetch()) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'The selected member does not exist.'
        ]);
        exit;
    }

    $sql = "INSERT INTO payments (
                member_id,
                amount,
                payment_date,
                payment_method,
                transaction_reference,
                payment_status,
                notes
            ) VALUES (
                :member_id,
                :amount,
                :payment_date,
                :payment_method,
                :transaction_reference,
                :payment_status,
                :notes
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'member_id' => $memberId,
        'amount' => number_format((float) $amount, 2, '.', ''),
        'payment_date' => $paymentDate,
        'payment_method' => $paymentMethod,
        'transaction_reference' => $transactionReference !== ''
            ? $transactionReference
            : null,
        'payment_status' => $paymentStatus,
        'notes' => $notes !== '' ? $notes : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully.',
        'payment_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to record the payment.'
    ]);
}