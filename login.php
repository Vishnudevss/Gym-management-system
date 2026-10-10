
<?php
session_start();

// Already logged in? Go to the dashboard.
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | FitTrack</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-page">

<div class="container">
    <div class="row min-vh-100 justify-content-center align-items-center">
        <div class="col-12 col-sm-10 col-md-6 col-lg-4">

            <div class="card login-card border-0 shadow">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">FitTrack</h2>
                        <p class="text-muted">Gym Management System</p>
                    </div>

                    <h4 class="text-center mb-4">Admin Login</h4>

                    <div
                        id="loginMessage"
                        class="alert d-none"
                        role="alert"
                    ></div>

                    <form id="loginForm">

                        <div class="mb-3">
                            <label for="username" class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-control"
                                placeholder="Enter username"
                                maxlength="50"
                                autocomplete="username"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Enter password"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            id="loginButton"
                            class="btn btn-primary w-100"
                        >
                            Login
                        </button>

                    </form>

                    <p class="text-center text-muted small mt-4 mb-0">
                        Authorized administrators only
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="assets/js/login.js"></script>

</body>
</html>

