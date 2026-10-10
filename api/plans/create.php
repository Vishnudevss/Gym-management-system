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

$planName = trim($_POST['plan_name'] ?? '');
$duration = filter_var(
    $_POST['duration_months'] ?? null,
    FILTER_VALIDATE_INT
);
$priceInput = trim($_POST['price'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($planName === '' || $duration === false || $duration === null || $priceInput === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields.'
    ]);
    exit;
}

if (strlen($planName) > 100 || $duration < 1 || $duration > 120) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Enter a valid plan name and duration between 1 and 120 months.'
    ]);
    exit;
}

if (!is_numeric($priceInput)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid price.'
    ]);
    exit;
}

$price = (float) $priceInput;

if (!is_finite($price) || $price <= 0 || $price > 99999999.99) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Price must be greater than zero and within the allowed limit.'
    ]);
    exit;
}

if (strlen($description) > 65535) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'The description is too long.'
    ]);
    exit;
}

try {
    $check = $pdo->prepare(
        'SELECT id FROM membership_plans WHERE LOWER(plan_name) = LOWER(?) LIMIT 1'
    );
    $check->execute([$planName]);

    if ($check->fetch()) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'A membership plan with this name already exists.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO membership_plans
            (plan_name, duration_months, price, description, status)
         VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $planName,
        $duration,
        $price,
        $description === '' ? null : $description,
        'active'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Membership plan created successfully.',
        'plan_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to create membership plan. Please try again.'
    ]);
}