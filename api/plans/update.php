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
$planName = trim($_POST['plan_name'] ?? '');
$durationInput = $_POST['duration_months'] ?? '';
$priceInput = trim($_POST['price'] ?? '');
$description = trim($_POST['description'] ?? '');

$duration = filter_var($durationInput, FILTER_VALIDATE_INT);

if (
    !$id ||
    $id < 1 ||
    $planName === '' ||
    $duration === false ||
    $duration === null ||
    $priceInput === ''
) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Please provide all required fields.'
    ]);
    exit;
}

if (
    strlen($planName) > 100 ||
    $duration < 1 ||
    $duration > 120
) {
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
    $checkPlan = $pdo->prepare(
        'SELECT id FROM membership_plans WHERE id = ?'
    );
    $checkPlan->execute([$id]);

    if (!$checkPlan->fetch()) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Membership plan not found.'
        ]);
        exit;
    }

    $checkName = $pdo->prepare(
        'SELECT id
         FROM membership_plans
         WHERE LOWER(plan_name) = LOWER(?)
           AND id != ?
         LIMIT 1'
    );

    $checkName->execute([$planName, $id]);

    if ($checkName->fetch()) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'Another membership plan already uses this name.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        'UPDATE membership_plans
         SET plan_name = ?,
             duration_months = ?,
             price = ?,
             description = ?
         WHERE id = ?'
    );

    $stmt->execute([
        $planName,
        $duration,
        $price,
        $description === '' ? null : $description,
        $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Membership plan updated successfully.'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update membership plan. Please try again.'
    ]);
}