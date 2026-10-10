
$(document).ready(function () {

    $("#loginForm").on("submit", function (event) {

        // Prevent the browser's normal form submission.
        event.preventDefault();

        const username = $("#username").val().trim();
        const password = $("#password").val();

        const $button = $("#loginButton");
        const $message = $("#loginMessage");

        // Hide previous messages.
        $message.addClass("d-none").removeClass(
            "alert-danger alert-success"
        );

        // Basic frontend validation.
        if (!username || !password) {
            $message
                .removeClass("d-none")
                .addClass("alert-danger")
                .text("Please enter your username and password.");

            return;
        }

        // Prevent repeated submissions while the request is running.
        $button.prop("disabled", true).text("Logging in...");

        $.ajax({
            url: "api/auth/login.php",
            method: "POST",
            dataType: "json",

            data: {
                username: username,
                password: password
            },

            success: function (response) {

                if (response.success) {
                    $message
                        .removeClass("d-none")
                        .addClass("alert-success")
                        .text(response.message);

                    // Redirect to the dashboard.
                    window.location.href =
                        response.redirect || "dashboard.php";

                } else {
                    $message
                        .removeClass("d-none")
                        .addClass("alert-danger")
                        .text(response.message || "Login failed.");
                }
            },

            error: function (xhr) {

                const response = xhr.responseJSON;

                let message = "Unable to log in. Please try again.";

                if (response && response.message) {
                    message = response.message;
                }

                $message
                    .removeClass("d-none")
                    .addClass("alert-danger")
                    .text(message);
            },

            complete: function () {
                $button.prop("disabled", false).text("Login");
            }
        });

    });

});
