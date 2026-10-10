
<?php

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
$pageScript = 'assets/js/dashboard.js';
require_once __DIR__ . '/includes/header.php';

?>

<div class="dashboard-heading">
    <h2>Dashboard Overview</h2>
    <p class="text-muted mb-0">
        Here's what's happening at your gym.
    </p>
</div>

<!-- Statistics cards -->

<div class="row g-4 mb-4">

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="stat-label">Total Members</span>
                <span class="stat-icon">M</span>
            </div>

            <h3 class="stat-value" id="totalMembers">--</h3>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="stat-label">Active Members</span>
                <span class="stat-icon">A</span>
            </div>

            <h3 class="stat-value" id="activeMembers">--</h3>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="stat-label">Total Trainers</span>
                <span class="stat-icon">T</span>
            </div>

            <h3 class="stat-value" id="totalTrainers">--</h3>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <span class="stat-label">Membership Plans</span>
                <span class="stat-icon">P</span>
            </div>

            <h3 class="stat-value" id="totalPlans">--</h3>
        </div>
    </div>

</div>

<!-- Payment summary -->

<div class="row g-4 mb-4">

    <div class="col-12 col-lg-6">
        <div class="stat-card">
            <span class="stat-label">Completed Payments</span>

            <h3 class="stat-value" id="completedPayments">--</h3>

            <p class="text-muted small mb-0 mt-2">
                Number of successfully completed payments
            </p>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="stat-card">
            <span class="stat-label">Total Revenue</span>

            <h3 class="stat-value" id="totalRevenue">--</h3>

            <p class="text-muted small mb-0 mt-2">
                Sum of completed payments
            </p>
        </div>
    </div>

</div>

<!-- Recent members -->

<div class="dashboard-panel">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Recent Members</h5>

        <a href="members.php" class="btn btn-outline-primary btn-sm">
            View Members
        </a>
    </div>

    <div id="recentMembersMessage" class="text-muted small mb-3">
        Loading recent members...
    </div>

    <div class="table-responsive">

        <table class="table table-hover mb-0">

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Join Date</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody id="recentMembersTable">
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Loading...
                    </td>
                </tr>
            </tbody>

        </table>

    </div>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

<script src="assets/js/dashboard.js"></script>

