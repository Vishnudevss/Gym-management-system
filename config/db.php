
<?php

$host = "localhost";
$dbname = "gym_management";
$username = "root";
$password = "Vishnu1234&&";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    exit("Unable to connect to the database. Please try again later.");
}
?>
