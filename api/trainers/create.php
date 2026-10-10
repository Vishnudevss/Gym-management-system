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

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$salaryInput = trim($_POST['salary'] ?? '');

if ($name === '' || $salaryInput === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Name and salary are required.'
    ]);
    exit;
}

if (strlen($name) > 150) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Name must not exceed 150 characters.'
    ]);
    exit;
}

if ($email !== '' && (
    strlen($email) > 150 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);
    exit;
}

if (strlen($phone) > 20 || strlen($specialization) > 150) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Phone or specialization exceeds the allowed length.'
    ]);
    exit;
}

if (!is_numeric($salaryInput)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid salary.'
    ]);
    exit;
}

$salary = (float) $salaryInput;

if (!is_finite($salary) || $salary < 0 || $salary > 99999999.99) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Salary must be between 0 and 99,999,999.99.'
    ]);
    exit;
}

try {
    if ($email !== '') {
        $check = $pdo->prepare(
            'SELECT id FROM trainers WHERE email = ? LIMIT 1'
        );

        $check->execute([$email]);

        if ($check->fetch()) {
            http_response_code(409);

            echo json_encode([
                'success' => false,
                'message' => 'A trainer with this email already exists.'
            ]);
            exit;
        }
    }

    $stmt = $pdo->prepare(
        'INSERT INTO trainers
            (name, email, phone, specialization, salary, status)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $name,
        $email === '' ? null : $email,
        $phone === '' ? null : $phone,
        $specialization === '' ? null : $specialization,
        $salary,
        'active'
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Trainer added successfully.',
        'trainer_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to add trainer. Please try again.'
    ]);
}