<?php

session_start();

require_once __DIR__ . '/config/db.php';

// Protect the dashboard from unauthorized access.
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch dashboard statistics.
$totalMembers = $pdo->query(
    "SELECT COUNT(*) FROM members"
)->fetchColumn();

$totalTrainers = $pdo->query(
    "SELECT COUNT(*) FROM trainers"
)->fetchColumn();

$totalPlans = $pdo->query(
    "SELECT COUNT(*) FROM membership_plans WHERE status = 'active'"
)->fetchColumn();

$totalPayments = $pdo->query(
    "SELECT COALESCE(SUM(amount), 0)
     FROM payments
     WHERE payment_status = 'completed'"
)->fetchColumn();

$adminName = $_SESSION['admin_username'] ?? 'Admin';

?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<title>Dashboard | Gym Management System</title>

<link rel="stylesheet" href="assets/css/style.css">
```

</head>

<body>

<div class="dashboard-layout">

```
<aside class="sidebar">

    <h2>FitZone</h2>

    <p class="sidebar-label">MAIN MENU</p>

    <nav>
        <a href="index.php" class="active">Dashboard</a>
        <a href="members.php">Members</a>
        <a href="plans.php">Membership Plans</a>
        <a href="trainers.php">Trainers</a>
        <a href="payments.php">Payments</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

</aside>

<main class="main-content">

    <header class="topbar">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>!</p>
        </div>

        <div class="admin-badge">
            Administrator
        </div>
    </header>

    <section class="welcome-banner">
        <div>
            <h2>Gym Management Overview</h2>
            <p>Manage your members, trainers, memberships, and payments in one place.</p>
        </div>
    </section>

    <section class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon members-icon">M</div>
            <p>Total Members</p>
            <h2><?= number_format((int) $totalMembers) ?></h2>
            <span>Registered members</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon trainers-icon">T</div>
            <p>Total Trainers</p>
            <h2><?= number_format((int) $totalTrainers) ?></h2>
            <span>Registered trainers</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon plans-icon">P</div>
            <p>Active Plans</p>
            <h2><?= number_format((int) $totalPlans) ?></h2>
            <span>Available membership plans</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon payments-icon">₹</div>
            <p>Total Payments</p>
            <h2>₹<?= number_format((float) $totalPayments, 2) ?></h2>
            <span>Completed payments recorded</span>
        </div>

    </section>

    <section class="quick-actions">

        <h2>Quick Actions</h2>

        <div class="action-grid">
            <a href="members.php" class="action-card">
                <h3>Manage Members</h3>
                <p>Register members and update their information.</p>
                <span>View Members →</span>
            </a>

            <a href="trainers.php" class="action-card">
                <h3>Manage Trainers</h3>
                <p>Maintain trainer records and assignments.</p>
                <span>View Trainers →</span>
            </a>

            <a href="plans.php" class="action-card">
                <h3>Membership Plans</h3>
                <p>Manage plans and membership pricing.</p>
                <span>View Plans →</span>
            </a>

            <a href="payments.php" class="action-card">
                <h3>Payment Records</h3>
                <p>Record payments and review transaction history.</p>
                <span>View Payments →</span>
            </a>
        </div>

    </section>

    <footer class="dashboard-footer">
        Gym Management System &copy; <?= date('Y') ?>
    </footer>

</main>
```

</div>

</body>
</html>
