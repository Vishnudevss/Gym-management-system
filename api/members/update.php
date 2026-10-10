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

if (!$id || $name === '' || $email === '' || $phone === '' || $joinDate === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please provide all required fields.'
    ]);
    exit;
}

if (
    strlen($name) > 150 ||
    strlen($email) > 150 ||
    strlen($phone) > 20 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please enter valid member details.'
    ]);
    exit;
}

if (!in_array($gender, ['', 'male', 'female', 'other'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid gender.'
    ]);
    exit;
}

function validDate($date) {
    if ($date === '') {
        return true;
    }

    $parsed = DateTime::createFromFormat('!Y-m-d', $date);

    return $parsed && $parsed->format('Y-m-d') === $date;
}

if (
    !validDate($dateOfBirth) ||
    !validDate($joinDate) ||
    !validDate($membershipStart) ||
    !validDate($membershipEnd)
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please provide valid dates.'
    ]);
    exit;
}

if ($dateOfBirth !== '' && $dateOfBirth > date('Y-m-d')) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Date of birth cannot be in the future.'
    ]);
    exit;
}

if (
    $membershipStart !== '' &&
    $membershipEnd !== '' &&
    $membershipEnd < $membershipStart
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Membership end date cannot be before the start date.'
    ]);
    exit;
}

$planId = $planId === '' ? null : filter_var($planId, FILTER_VALIDATE_INT);
$trainerId = $trainerId === '' ? null : filter_var($trainerId, FILTER_VALIDATE_INT);

if (($planId !== null && $planId === false) ||
    ($trainerId !== null && $trainerId === false)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid membership plan or trainer.'
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

    $emailCheck = $pdo->prepare(
        'SELECT id FROM members WHERE email = ? AND id != ?'
    );
    $emailCheck->execute([$email, $id]);

    if ($emailCheck->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Another member already uses this email.'
        ]);
        exit;
    }

    if ($planId !== null) {
        $planCheck = $pdo->prepare(
            "SELECT id FROM membership_plans WHERE id = ? AND status = 'active'"
        );
        $planCheck->execute([$planId]);

        if (!$planCheck->fetch()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please select a valid active membership plan.'
            ]);
            exit;
        }
    }

    if ($trainerId !== null) {
        $trainerCheck = $pdo->prepare(
            "SELECT id FROM trainers WHERE id = ? AND status = 'active'"
        );
        $trainerCheck->execute([$trainerId]);

        if (!$trainerCheck->fetch()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Please select a valid active trainer.'
            ]);
            exit;
        }
    }

    $sql = "UPDATE members SET
                name = ?,
                email = ?,
                phone = ?,
                gender = ?,
                date_of_birth = ?,
                address = ?,
                join_date = ?,
                membership_plan_id = ?,
                trainer_id = ?,
                membership_start = ?,
                membership_end = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $name,
        $email,
        $phone,
        $gender === '' ? null : $gender,
        $dateOfBirth === '' ? null : $dateOfBirth,
        $address === '' ? null : $address,
        $joinDate,
        $planId,
        $trainerId,
        $membershipStart === '' ? null : $membershipStart,
        $membershipEnd === '' ? null : $membershipEnd,
        $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Member updated successfully.'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to update member.'
    ]);
}
?>