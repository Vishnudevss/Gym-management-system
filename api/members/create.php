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

// Get form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = $_POST['gender'] ?? '';
$dateOfBirth = $_POST['date_of_birth'] ?? '';
$address = trim($_POST['address'] ?? '');
$joinDate = $_POST['join_date'] ?? '';
$planId = $_POST['membership_plan_id'] ?? '';
$trainerId = $_POST['trainer_id'] ?? '';
$membershipStart = $_POST['membership_start'] ?? '';
$membershipEnd = $_POST['membership_end'] ?? '';

// Validate required fields
if ($name === '' || $email === '' || $phone === '' || $joinDate === '') {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Name, email, phone, and join date are required.'
    ]);

    exit;
}

// Validate field lengths
if (
    mb_strlen($name) > 150 ||
    mb_strlen($email) > 150 ||
    mb_strlen($phone) > 20
) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'One or more fields exceed the allowed length.'
    ]);

    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ]);

    exit;
}

// Validate gender
if (!in_array($gender, ['', 'male', 'female', 'other'], true)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please select a valid gender.'
    ]);

    exit;
}

// Validate dates
function isValidDate($date)
{
    if ($date === '') {
        return true;
    }

    $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);

    return $parsedDate && $parsedDate->format('Y-m-d') === $date;
}

if (
    !isValidDate($dateOfBirth) ||
    !isValidDate($joinDate) ||
    !isValidDate($membershipStart) ||
    !isValidDate($membershipEnd)
) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Please provide valid dates.'
    ]);

    exit;
}

// Date of birth cannot be in the future
if ($dateOfBirth !== '' && $dateOfBirth > date('Y-m-d')) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Date of birth cannot be in the future.'
    ]);

    exit;
}

// Membership end date cannot be before the start date
if (
    $membershipStart !== '' &&
    $membershipEnd !== '' &&
    $membershipEnd < $membershipStart
) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Membership end date cannot be before its start date.'
    ]);

    exit;
}

// Validate optional foreign keys
$planId = $planId === '' ? null : filter_var($planId, FILTER_VALIDATE_INT);
$trainerId = $trainerId === '' ? null : filter_var($trainerId, FILTER_VALIDATE_INT);

if (($planId === false) || ($trainerId === false)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid membership plan or trainer.'
    ]);

    exit;
}

try {

    // Check whether the email already exists
    $checkEmail = $pdo->prepare(
        'SELECT id FROM members WHERE email = :email LIMIT 1'
    );

    $checkEmail->execute([
        ':email' => $email
    ]);

    if ($checkEmail->fetch()) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'A member with this email already exists.'
        ]);

        exit;
    }

    // Verify that the selected plan is active
    if ($planId !== null) {
        $checkPlan = $pdo->prepare(
            "SELECT id FROM membership_plans
             WHERE id = :id AND status = 'active'"
        );

        $checkPlan->execute([
            ':id' => $planId
        ]);

        if (!$checkPlan->fetch()) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'The selected membership plan is unavailable.'
            ]);

            exit;
        }
    }

    // Verify that the selected trainer is active
    if ($trainerId !== null) {
        $checkTrainer = $pdo->prepare(
            "SELECT id FROM trainers
             WHERE id = :id AND status = 'active'"
        );

        $checkTrainer->execute([
            ':id' => $trainerId
        ]);

        if (!$checkTrainer->fetch()) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'The selected trainer is unavailable.'
            ]);

            exit;
        }
    }

    // Insert the member into the database
    $sql = "
        INSERT INTO members (
            name,
            email,
            phone,
            gender,
            date_of_birth,
            address,
            join_date,
            membership_plan_id,
            trainer_id,
            membership_start,
            membership_end
        ) VALUES (
            :name,
            :email,
            :phone,
            :gender,
            :date_of_birth,
            :address,
            :join_date,
            :membership_plan_id,
            :trainer_id,
            :membership_start,
            :membership_end
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':gender' => $gender === '' ? null : $gender,
        ':date_of_birth' => $dateOfBirth === '' ? null : $dateOfBirth,
        ':address' => $address === '' ? null : $address,
        ':join_date' => $joinDate,
        ':membership_plan_id' => $planId,
        ':trainer_id' => $trainerId,
        ':membership_start' => $membershipStart === '' ? null : $membershipStart,
        ':membership_end' => $membershipEnd === '' ? null : $membershipEnd
    ]);

    http_response_code(201);

    echo json_encode([
        'success' => true,
        'message' => 'Member added successfully.',
        'member_id' => (int) $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    error_log($e->getMessage());

    // Handle duplicate email errors, including a concurrent duplicate request
    if ($e->getCode() === '23000') {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' => 'The email or another selected value conflicts with an existing record.'
        ]);

        exit;
    }

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to add the member. Please try again.'
    ]);
}