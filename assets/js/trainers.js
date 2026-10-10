$(document).ready(function () {

    let allTrainers = [];

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

        $("#trainersMessage").empty().append(alert);
    }

    function showFormMessage(message, type) {
        const alert = $("<div>", {
            class: "alert alert-" + type,
            role: "alert",
            text: message
        });

        $("#trainerFormMessage").empty().append(alert);
    }

    function displayTrainers(trainers) {
        const tableBody = $("#trainersTable");

        if (!trainers || trainers.length === 0) {
            tableBody.html(`
                <tr>
                    <td colspan="8" class="text-center">
                        No trainers found.
                    </td>
                </tr>
            `);
            return;
        }

        let rows = "";

        trainers.forEach(function (trainer) {
            const status = String(trainer.status || "inactive").toLowerCase();
            const isActive = status === "active";

            const statusClass = isActive
                ? "bg-success"
                : "bg-secondary";

            const toggleButtonClass = isActive
                ? "btn-outline-danger"
                : "btn-outline-success";

            const toggleButtonText = isActive
                ? "Deactivate"
                : "Activate";

            const nextStatus = isActive
                ? "inactive"
                : "active";

            rows += `
                <tr>
                    <td>${escapeHtml(trainer.id)}</td>
                    <td>${escapeHtml(trainer.name)}</td>
                    <td>${escapeHtml(trainer.email || "-")}</td>
                    <td>${escapeHtml(trainer.phone || "-")}</td>
                    <td>${escapeHtml(trainer.specialization || "-")}</td>
                    <td>₹${Number(trainer.salary || 0).toLocaleString("en-IN", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}</td>
                    <td>
                        <span class="badge ${statusClass}">
                            ${escapeHtml(status)}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary edit-trainer-btn"
                                data-id="${escapeHtml(trainer.id)}"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm ${toggleButtonClass} toggle-trainer-btn"
                                data-id="${escapeHtml(trainer.id)}"
                                data-status="${nextStatus}"
                                data-name="${escapeHtml(trainer.name)}"
                            >
                                ${toggleButtonText}
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.html(rows);
    }

    function filterTrainers(searchValue) {
        const searchTerm = String(searchValue || "")
            .trim()
            .toLowerCase();

        if (!searchTerm) {
            return allTrainers;
        }

        return allTrainers.filter(function (trainer) {
            return (
                String(trainer.name || "").toLowerCase().includes(searchTerm) ||
                String(trainer.specialization || "").toLowerCase().includes(searchTerm)
            );
        });
    }

    function loadTrainers() {
        $("#trainersTable").html(`
            <tr>
                <td colspan="8" class="text-center">
                    Loading trainers...
                </td>
            </tr>
        `);

        $.ajax({
            url: "api/trainers/list.php",
            type: "GET",
            dataType: "json",

            success: function (response) {
                if (!response.success) {
                    $("#trainersTable").html(`
                        <tr>
                            <td colspan="8" class="text-center text-danger">
                                Unable to load trainers.
                            </td>
                        </tr>
                    `);
                    return;
                }

                allTrainers = response.trainers || [];

                displayTrainers(
                    filterTrainers($("#trainerSearch").val())
                );
            },

            error: function (xhr) {
                console.error(
                    "Load trainers API error:",
                    xhr.responseText
                );

                $("#trainersTable").html(`
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            Failed to load trainers.
                        </td>
                    </tr>
                `);
            }
        });
    }

    function openAddTrainerModal() {
        $("#trainerForm")[0].reset();
        $("#trainerId").val("");
        $("#trainerFormMessage").empty();

        $("#trainerModalLabel").text("Add Trainer");
        $("#saveTrainerButton").text("Save Trainer");

        bootstrap.Modal
            .getOrCreateInstance(document.getElementById("trainerModal"))
            .show();
    }

    function openEditTrainerModal(trainerId) {
        const trainer = allTrainers.find(function (item) {
            return String(item.id) === String(trainerId);
        });

        if (!trainer) {
            showPageMessage(
                "Trainer not found. Please refresh the page.",
                "danger"
            );
            return;
        }

        $("#trainerForm")[0].reset();
        $("#trainerFormMessage").empty();

        $("#trainerId").val(trainer.id);
        $("#trainerName").val(trainer.name || "");
        $("#trainerEmail").val(trainer.email || "");
        $("#trainerPhone").val(trainer.phone || "");
        $("#trainerSpecialization").val(trainer.specialization || "");
        $("#trainerSalary").val(trainer.salary ?? 0);

        $("#trainerModalLabel").text("Edit Trainer");
        $("#saveTrainerButton").text("Update Trainer");

        bootstrap.Modal
            .getOrCreateInstance(document.getElementById("trainerModal"))
            .show();
    }

    $("#addTrainerButton").on("click", function () {
        openAddTrainerModal();
    });

    $("#trainersTable").on("click", ".edit-trainer-btn", function () {
        openEditTrainerModal($(this).data("id"));
    });

    $("#trainersTable").on("click", ".toggle-trainer-btn", function () {
        const trainerId = $(this).data("id");
        const nextStatus = $(this).data("status");
        const trainerName = $(this).attr("data-name");

        const action = nextStatus === "active"
            ? "activate"
            : "deactivate";

        if (!confirm(`Are you sure you want to ${action} ${trainerName}?`)) {
            return;
        }

        const button = $(this);

        button.prop("disabled", true);

        $.ajax({
            url: "api/trainers/toggle-status.php",
            type: "POST",
            data: {
                id: trainerId,
                status: nextStatus
            },
            dataType: "json",

            success: function (response) {
                if (response.success) {
                    showPageMessage(response.message, "success");
                    loadTrainers();
                } else {
                    showPageMessage(
                        response.message || "Unable to update trainer status.",
                        "danger"
                    );
                    button.prop("disabled", false);
                }
            },

            error: function (xhr) {
                showPageMessage(
                    xhr.responseJSON?.message ||
                    "Unable to update trainer status. Please try again.",
                    "danger"
                );

                console.error(
                    "Toggle trainer status API error:",
                    xhr.responseText
                );

                button.prop("disabled", false);
            }
        });
    });

    $("#trainerSearch").on("input", function () {
        $("#trainersMessage").empty();

        displayTrainers(
            filterTrainers($(this).val())
        );
    });

    $("#trainerForm").on("submit", function (event) {
        event.preventDefault();

        const form = this;
        const saveButton = $("#saveTrainerButton");
        const trainerId = $("#trainerId").val();
        const isEditing = trainerId !== "";

        const url = isEditing
            ? "api/trainers/update.php"
            : "api/trainers/create.php";

        saveButton.prop("disabled", true).text(
            isEditing ? "Updating..." : "Saving..."
        );

        $("#trainerFormMessage").empty();

        $.ajax({
            url: url,
            type: "POST",
            data: $(form).serialize(),
            dataType: "json",

            success: function (response) {
                if (!response.success) {
                    showFormMessage(
                        response.message || "Unable to save trainer.",
                        "danger"
                    );
                    return;
                }

                showFormMessage(
                    response.message || "Trainer saved successfully.",
                    "success"
                );

                setTimeout(function () {
                    bootstrap.Modal
                        .getOrCreateInstance(
                            document.getElementById("trainerModal")
                        )
                        .hide();

                    $("#trainerSearch").val("");
                    loadTrainers();
                }, 800);
            },

            error: function (xhr) {
                showFormMessage(
                    xhr.responseJSON?.message ||
                    "Unable to save trainer. Please try again.",
                    "danger"
                );

                console.error(
                    "Save trainer API error:",
                    xhr.responseText
                );
            },

            complete: function () {
                saveButton.prop("disabled", false).text(
                    isEditing ? "Update Trainer" : "Save Trainer"
                );
            }
        });
    });

    $("#trainerModal").on("hidden.bs.modal", function () {
        $("#trainerForm")[0].reset();
        $("#trainerId").val("");
        $("#trainerFormMessage").empty();

        $("#trainerModalLabel").text("Add Trainer");

        $("#saveTrainerButton")
            .prop("disabled", false)
            .text("Save Trainer");
    });

    loadTrainers();

});