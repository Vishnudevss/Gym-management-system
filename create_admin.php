<?php

require_once __DIR__ . '/config/db.php';

$username = 'admin';
$password = 'Admin@12345';

// Generate a secure password hash.
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO admins (username, password)
            VALUES (:username, :password)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':username' => $username,
        ':password' => $hashedPassword
    ]);

    echo "Admin account created successfully.";

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo "This admin username already exists.";
    } else {
        error_log($e->getMessage());
        echo "Unable to create the admin account.";
    }
}
?>
