$(document).ready(function () {

    loadDashboard();

    function loadDashboard() {

        $.ajax({
            url: "api/dashboard/stats.php",
            method: "GET",
            dataType: "json",

            success: function (response) {

                if (!response.success) {
                    showDashboardError(
                        response.message || "Unable to load dashboard."
                    );
                    return;
                }

                const stats = response.stats;

                // Update the statistics cards.
                $("#totalMembers").text(stats.total_members);
                $("#activeMembers").text(stats.active_members);
                $("#totalTrainers").text(stats.total_trainers);
                $("#totalPlans").text(stats.total_plans);

                $("#completedPayments").text(
                    stats.completed_payments
                );

                // Format the revenue as Indian Rupees.
                $("#totalRevenue").text(
                    Number(stats.total_revenue).toLocaleString("en-IN", {
                        style: "currency",
                        currency: "INR",
                        maximumFractionDigits: 2
                    })
                );

                // Display recent members.
                renderRecentMembers(response.recent_members);
            },

            error: function (xhr) {

                if (xhr.status === 401 || xhr.status === 403) {
                    window.location.href = "login.php";
                    return;
                }

                showDashboardError(
                    "Unable to load dashboard data. Please refresh the page."
                );
            }
        });
    }


    function renderRecentMembers(members) {

        const $table = $("#recentMembersTable");

        $table.empty();

        if (!members || members.length === 0) {

            $table.append(
                $("<tr>").append(
                    $("<td>")
                        .attr("colspan", 5)
                        .addClass("text-center text-muted")
                        .text("No members registered yet.")
                )
            );

            $("#recentMembersMessage").text(
                "No recent members to display."
            );

            return;
        }

        $("#recentMembersMessage").text(
            "Showing the five most recently registered members."
        );

        members.forEach(function (member) {

            const $row = $("<tr>");

            $row.append(
                $("<td>").text(member.name)
            );

            $row.append(
                $("<td>").text(member.email)
            );

            $row.append(
                $("<td>").text(member.phone)
            );

            $row.append(
                $("<td>").text(member.join_date)
            );

            const statusClass =
                member.status === "active"
                    ? "bg-success"
                    : member.status === "inactive"
                        ? "bg-secondary"
                        : "bg-warning text-dark";

            const $badge = $("<span>")
                .addClass("badge")
                .addClass(statusClass)
                .text(member.status);

            $row.append(
                $("<td>").append($badge)
            );

            $table.append($row);
        });
    }


    function showDashboardError(message) {

        $("#recentMembersMessage")
            .removeClass("text-muted")
            .addClass("text-danger")
            .text(message);

        $("#recentMembersTable").empty();

        $("#totalMembers").text("--");
        $("#activeMembers").text("--");
        $("#totalTrainers").text("--");
        $("#totalPlans").text("--");
        $("#completedPayments").text("--");
        $("#totalRevenue").text("--");
    }

});

