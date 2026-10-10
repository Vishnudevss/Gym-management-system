$(document).ready(function () {

    let allPlans = [];

    function escapeHtml(value) {
        return String(value ?? "").replace(/[&<>"']/g, function (character) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;"
            }[character];
        });
    }

    function showPageMessage(message, type) {
        const alert = $("<div>", {
            class: "alert alert-" + type,
            role: "alert",
            text: message
        });

        $("#plansMessage").empty().append(alert);
    }

    function showFormMessage(message, type) {
        const alert = $("<div>", {
            class: "alert alert-" + type,
            role: "alert",
            text: message
        });

        $("#planFormMessage").empty().append(alert);
    }

    function displayPlans(plans) {
        const tableBody = $("#plansTable");

        if (!plans || plans.length === 0) {
            tableBody.html(`
                <tr>
                    <td colspan="7" class="text-center">
                        No membership plans found.
                    </td>
                </tr>
            `);
            return;
        }

        let rows = "";

        plans.forEach(function (plan) {
            const status = String(plan.status || "inactive").toLowerCase();

            const statusClass = status === "active"
                ? "bg-success"
                : "bg-secondary";

            const actionText = status === "active"
                ? "Deactivate"
                : "Activate";

            const actionClass = status === "active"
                ? "btn-outline-warning"
                : "btn-outline-success";

            rows += `
                <tr>
                    <td>${escapeHtml(plan.id)}</td>
                    <td>${escapeHtml(plan.plan_name)}</td>
                    <td>${escapeHtml(plan.duration_months)} month(s)</td>
                    <td>₹${Number(plan.price).toLocaleString("en-IN", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}</td>
                    <td>${escapeHtml(plan.description || "-")}</td>
                    <td>
                        <span class="badge ${statusClass}">
                            ${escapeHtml(status)}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary edit-plan-btn"
                                data-id="${escapeHtml(plan.id)}"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm ${actionClass} toggle-plan-btn"
                                data-id="${escapeHtml(plan.id)}"
                                data-status="${escapeHtml(status)}"
                            >
                                ${actionText}
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.html(rows);
    }

    function filterPlans(searchValue) {
        const searchTerm = String(searchValue || "")
            .trim()
            .toLowerCase();

        if (!searchTerm) {
            return allPlans;
        }

        return allPlans.filter(function (plan) {
            return String(plan.plan_name || "")
                .toLowerCase()
                .includes(searchTerm);
        });
    }

    function loadPlans() {
        $("#plansTable").html(`
            <tr>
                <td colspan="7" class="text-center">
                    Loading membership plans...
                </td>
            </tr>
        `);

        $.ajax({
            url: "api/plans/list.php",
            type: "GET",
            dataType: "json",

            success: function (response) {
                if (!response.success) {
                    $("#plansTable").html(`
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                Unable to load membership plans.
                            </td>
                        </tr>
                    `);
                    return;
                }

                allPlans = response.plans || [];
                displayPlans(filterPlans($("#planSearch").val()));
            },

            error: function (xhr) {
                console.error(
                    "Load plans API error:",
                    xhr.responseText
                );

                $("#plansTable").html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Failed to load membership plans.
                        </td>
                    </tr>
                `);
            }
        });
    }

    function openAddPlanModal() {
        $("#planForm")[0].reset();
        $("#planId").val("");
        $("#planFormMessage").empty();

        $("#planModalLabel").text("Add Membership Plan");
        $("#savePlanButton").text("Save Plan");

        const modalElement = document.getElementById("planModal");

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    function openEditPlanModal(planId) {
        const plan = allPlans.find(function (item) {
            return String(item.id) === String(planId);
        });

        if (!plan) {
            showPageMessage(
                "Membership plan not found. Please refresh the page.",
                "danger"
            );
            return;
        }

        $("#planForm")[0].reset();
        $("#planFormMessage").empty();

        $("#planId").val(plan.id);
        $("#planName").val(plan.plan_name || "");
        $("#planDuration").val(plan.duration_months || "");
        $("#planPrice").val(plan.price || "");
        $("#planDescription").val(plan.description || "");

        $("#planModalLabel").text("Edit Membership Plan");
        $("#savePlanButton").text("Update Plan");

        const modalElement = document.getElementById("planModal");

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    $("#addPlanButton").on("click", function () {
        openAddPlanModal();
    });

    $("#plansTable").on("click", ".edit-plan-btn", function () {
        openEditPlanModal($(this).data("id"));
    });

    $("#plansTable").on("click", ".toggle-plan-btn", function () {
        const button = $(this);
        const planId = button.data("id");
        const currentStatus = button.data("status");

        const newStatus = currentStatus === "active"
            ? "inactive"
            : "active";

        const actionText = newStatus === "active"
            ? "activate"
            : "deactivate";

        const plan = allPlans.find(function (item) {
            return String(item.id) === String(planId);
        });

        if (!plan) {
            showPageMessage("Membership plan not found.", "danger");
            return;
        }

        const confirmed = confirm(
            `Are you sure you want to ${actionText} "${plan.plan_name}"?`
        );

        if (!confirmed) {
            return;
        }

        button.prop("disabled", true).text("Updating...");

        $.ajax({
            url: "api/plans/toggle-status.php",
            type: "POST",

            data: {
                id: planId,
                status: newStatus
            },

            dataType: "json",

            success: function (response) {
                if (!response.success) {
                    showPageMessage(
                        response.message || "Unable to update plan status.",
                        "danger"
                    );
                    return;
                }

                showPageMessage(response.message, "success");

                loadPlans();
            },

            error: function (xhr) {
                const response = xhr.responseJSON;

                showPageMessage(
                    response?.message || "Unable to update plan status.",
                    "danger"
                );

                console.error(
                    "Toggle plan status API error:",
                    xhr.responseText
                );
            },

            complete: function () {
                button.prop("disabled", false);
            }
        });
    });

    $("#planSearch").on("input", function () {
        $("#plansMessage").empty();
        displayPlans(filterPlans($(this).val()));
    });

    $("#planForm").on("submit", function (event) {
        event.preventDefault();

        const form = this;
        const saveButton = $("#savePlanButton");
        const planId = $("#planId").val();
        const isEditing = planId !== "";

        saveButton.prop("disabled", true);
        saveButton.text(isEditing ? "Updating..." : "Saving...");

        $("#planFormMessage").empty();

        $.ajax({
            url: isEditing
                ? "api/plans/update.php"
                : "api/plans/create.php",

            type: "POST",
            data: $(form).serialize(),
            dataType: "json",

            success: function (response) {
                if (!response.success) {
                    showFormMessage(
                        response.message || "Unable to save membership plan.",
                        "danger"
                    );
                    return;
                }

                showFormMessage(
                    response.message || (
                        isEditing
                            ? "Membership plan updated successfully."
                            : "Membership plan created successfully."
                    ),
                    "success"
                );

                setTimeout(function () {
                    const modalElement =
                        document.getElementById("planModal");

                    bootstrap.Modal
                        .getOrCreateInstance(modalElement)
                        .hide();

                    form.reset();

                    $("#planId").val("");
                    $("#planFormMessage").empty();
                    $("#planSearch").val("");

                    loadPlans();
                }, 800);
            },

            error: function (xhr) {
                const response = xhr.responseJSON;

                showFormMessage(
                    response?.message ||
                    "Unable to save membership plan. Please try again.",
                    "danger"
                );

                console.error(
                    "Save plan API error:",
                    xhr.responseText
                );
            },

            complete: function () {
                saveButton.prop("disabled", false);

                saveButton.text(
                    $("#planId").val() !== ""
                        ? "Update Plan"
                        : "Save Plan"
                );
            }
        });
    });

    $("#planModal").on("hidden.bs.modal", function () {
        $("#planForm")[0].reset();
        $("#planId").val("");
        $("#planFormMessage").empty();

        $("#planModalLabel").text("Add Membership Plan");

        $("#savePlanButton")
            .prop("disabled", false)
            .text("Save Plan");
    });

    loadPlans();

});