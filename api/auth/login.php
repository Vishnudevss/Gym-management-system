
<?php

session_start();

require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}

// If already authenticated, don't create another login session.
if (isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => true,
        'message' => 'Already logged in.'
    ]);

    exit;
}

// Read submitted values.
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Basic input validation.
if ($username === '' || $password === '') {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Username and password are required.'
    ]);

    exit;
}

try {
    // Find the admin by username.
    $sql = "SELECT id, username, password
            FROM admins
            WHERE username = :username
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':username' => $username
    ]);

    $admin = $stmt->fetch();

    // Check the submitted password against the stored hash.
    if (!$admin || !password_verify($password, $admin['password'])) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password.'
        ]);

        exit;
    }

    // Generate a new session ID after successful authentication.
    session_regenerate_id(true);

    // Store the authenticated admin's details in the session.
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];

    echo json_encode([
        'success' => true,
        'message' => 'Login successful.',
        'redirect' => 'dashboard.php'
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'A server error occurred. Please try again.'
    ]);
}
