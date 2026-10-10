
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Gym Management System';
$currentPage = $currentPage ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="dashboard-body">

<div class="app-layout">

    <aside class="sidebar">

        <div class="sidebar-brand">
            <h4>FitTrack</h4>
            <small>Gym Management</small>
        </div>

        <nav class="sidebar-nav">

            <a
                href="dashboard.php"
                class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>"
            >
                Dashboard
            </a>

            <a
                href="members.php"
                class="<?= $currentPage === 'members' ? 'active' : '' ?>"
            >
                Members
            </a>

            <a
                href="plans.php"
                class="<?= $currentPage === 'plans' ? 'active' : '' ?>"
            >
                Membership Plans
            </a>

            <a
                href="trainers.php"
                class="<?= $currentPage === 'trainers' ? 'active' : '' ?>"
            >
                Trainers
            </a>

            <a
                href="payments.php"
                class="<?= $currentPage === 'payments' ? 'active' : '' ?>"
            >
                Payments
            </a>

        </nav>

        <div class="sidebar-footer">
            <a href="logout.php">Logout</a>
        </div>

    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h5 class="mb-1"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h5>
                <small class="text-muted">
                    Welcome,
                    <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>
                </small>
            </div>
        </header>

        <section class="page-content">
