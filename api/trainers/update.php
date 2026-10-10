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
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$salaryInput = trim($_POST['salary'] ?? '');

if (!$id || $id < 1) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid trainer ID.'
    ]);
    exit;
}

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
    $checkTrainer = $pdo->prepare(
        'SELECT id FROM trainers WHERE id = ? LIMIT 1'
    );

    $checkTrainer->execute([$id]);

    if (!$checkTrainer->fetch()) {
        http_response_code(404);

        echo json_encode([
            'success' => false,
            'message' => 'Trainer not found.'
        ]);
        exit;
    }

    if ($email !== '') {
        $checkEmail = $pdo->prepare(
            'SELECT id FROM trainers WHERE email = ? AND id != ? LIMIT 1'
        );

        $checkEmail->execute([$email, $id]);

        if ($checkEmail->fetch()) {
            http_response_code(409);

            echo json_encode([
                'success' => false,
                'message' => 'Another trainer already uses this email.'
            ]);
            exit;
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE trainers
         SET name = ?,
             email = ?,
             phone = ?,
             specialization = ?,
             salary = ?
         WHERE id = ?'
    );

    $stmt->execute([
        $name,
        $email === '' ? null : $email,
        $phone === '' ? null : $phone,
        $specialization === '' ? null : $specialization,
        $salary,
        $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Trainer updated successfully.'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update trainer. Please try again.'
    ]);
}