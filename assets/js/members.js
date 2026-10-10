$(document).ready(function () {

    let allMembers = [];
    let editingMemberId = null;

    function loadMemberOptions(selectedPlanId = "", selectedTrainerId = "") {
        const planDropdown = $("#membershipPlan");
        const trainerDropdown = $("#assignedTrainer");

        planDropdown.html('<option value="">Loading plans...</option>');
        trainerDropdown.html('<option value="">Loading trainers...</option>');

        return $.ajax({
            url: "api/members/options.php",
            type: "GET",
            dataType: "json",

            success: function (response) {
                if (!response.success) {
                    showFormMessage("Unable to load membership plans and trainers.", "danger");
                    return;
                }

                planDropdown.empty().append(
                    $("<option>", {
                        value: "",
                        text: "Select a membership plan"
                    })
                );

                response.plans.forEach(function (plan) {
                    planDropdown.append(
                        $("<option>", {
                            value: plan.id,
                            text: plan.plan_name + " - " +
                                plan.duration_months + " month(s) - ₹" +
                                Number(plan.price).toLocaleString("en-IN")
                        })
                    );
                });

                if (response.plans.length === 0) {
                    planDropdown.append(
                        $("<option>", {
                            value: "",
                            text: "No active plans available",
                            disabled: true
                        })
                    );
                }

                trainerDropdown.empty().append(
                    $("<option>", {
                        value: "",
                        text: "No trainer assigned"
                    })
                );

                response.trainers.forEach(function (trainer) {
                    let trainerText = trainer.name;

                    if (trainer.specialization) {
                        trainerText += " - " + trainer.specialization;
                    }

                    trainerDropdown.append(
                        $("<option>", {
                            value: trainer.id,
                            text: trainerText
                        })
                    );
                });

                if (response.trainers.length === 0) {
                    trainerDropdown.append(
                        $("<option>", {
                            value: "",
                            text: "No active trainers available",
                            disabled: true
                        })
                    );
                }

                planDropdown.val(selectedPlanId == null ? "" : String(selectedPlanId));
                trainerDropdown.val(selectedTrainerId == null ? "" : String(selectedTrainerId));
            },

            error: function (xhr) {
                console.error("Membership options API error:", xhr.responseText);

                planDropdown.html('<option value="">Unable to load plans</option>');
                trainerDropdown.html('<option value="">Unable to load trainers</option>');

                showFormMessage(
                    "Could not load membership plans and trainers. Please try again.",
                    "danger"
                );
            }
        });
    }

    function showFormMessage(message, type) {
        const alert = $("<div>", {
            class: "alert alert-" + type,
            role: "alert",
            text: message
        });

        $("#memberFormMessage").empty().append(alert);
    }

    function showPageMessage(message, type) {
        const alert = $("<div>", {
            class: "alert alert-" + type,
            role: "alert",
            text: message
        });

        $("#membersMessage").empty().append(alert);
    }

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

    function displayMembers(members) {
        const tableBody = $("#membersTable");

        if (!members || members.length === 0) {
            tableBody.html(`
                <tr>
                    <td colspan="7" class="text-center">No members found.</td>
                </tr>
            `);
            return;
        }

        let rows = "";

        members.forEach(function (member) {
            const status = String(member.status || "inactive").toLowerCase();

            let statusClass = "bg-secondary";

            if (status === "active") {
                statusClass = "bg-success";
            } else if (status === "inactive") {
                statusClass = "bg-warning text-dark";
            } else if (status === "expired") {
                statusClass = "bg-danger";
            }

            rows += `
                <tr>
                    <td>${escapeHtml(member.id)}</td>
                    <td>${escapeHtml(member.name)}</td>
                    <td>${escapeHtml(member.email)}</td>
                    <td>${escapeHtml(member.phone)}</td>
                    <td>${escapeHtml(member.join_date || "-")}</td>
                    <td>
                        <span class="badge ${statusClass}">
                            ${escapeHtml(status)}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary edit-member-btn"
                                data-id="${escapeHtml(member.id)}"
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger delete-member-btn"
                                data-id="${escapeHtml(member.id)}"
                                data-name="${escapeHtml(member.name)}"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.html(rows);
    }

    function filterMembers(searchValue) {
        const searchTerm = String(searchValue || "").trim().toLowerCase();

        if (!searchTerm) {
            return allMembers;
        }

        return allMembers.filter(function (member) {
            return (
                String(member.name || "").toLowerCase().includes(searchTerm) ||
                String(member.email || "").toLowerCase().includes(searchTerm) ||
                String(member.phone || "").toLowerCase().includes(searchTerm)
            );
        });
    }

    function loadMembers() {
        const tableBody = $("#membersTable");

        tableBody.html(`
            <tr>
                <td colspan="7" class="text-center">Loading members...</td>
            </tr>
        `);

        $.ajax({
            url: "api/members/list.php",
            type: "GET",
            dataType: "json",

            success: function (response) {
                if (!response.success) {
                    tableBody.html(`
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                Unable to load members.
                            </td>
                        </tr>
                    `);
                    return;
                }

                allMembers = response.members || [];
                displayMembers(filterMembers($("#memberSearch").val()));
            },

            error: function (xhr) {
                console.error("Load members API error:", xhr.responseText);

                tableBody.html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Failed to load members. Please refresh the page.
                        </td>
                    </tr>
                `);
            }
        });
    }

    function openAddMemberModal() {
        editingMemberId = null;

        $("#memberForm")[0].reset();
        $("#memberFormMessage").empty();
        $("#memberModalLabel").text("Add New Member");
        $("#saveMemberButton").text("Save Member");

        loadMemberOptions();

        const modalElement = document.getElementById("memberModal");
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    function openEditMemberModal(memberId) {
        const member = allMembers.find(function (item) {
            return String(item.id) === String(memberId);
        });

        if (!member) {
            showPageMessage("Member not found. Please refresh the page.", "danger");
            return;
        }

        editingMemberId = member.id;

        $("#memberForm")[0].reset();
        $("#memberFormMessage").empty();

        $("#memberModalLabel").text("Edit Member");
        $("#saveMemberButton").text("Update Member");

        $("#memberName").val(member.name || "");
        $("#memberEmail").val(member.email || "");
        $("#memberPhone").val(member.phone || "");
        $("#memberGender").val(member.gender || "");
        $("#memberDob").val(member.date_of_birth || "");
        $("#memberJoinDate").val(member.join_date || "");
        $("#memberAddress").val(member.address || "");
        $("#membershipStart").val(member.membership_start || "");
        $("#membershipEnd").val(member.membership_end || "");

        loadMemberOptions(member.membership_plan_id, member.trainer_id)
            .done(function () {
                const modalElement = document.getElementById("memberModal");
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            });
    }

    function deleteMember(memberId, memberName, button) {
        const confirmed = confirm(
            `Are you sure you want to delete "${memberName}"?\n\nThis action cannot be undone.`
        );

        if (!confirmed) {
            return;
        }

        button.prop("disabled", true).text("Deleting...");

        $.ajax({
            url: "api/members/delete.php",
            type: "POST",
            data: {
                id: memberId
            },
            dataType: "json",

            success: function (response) {
                if (response.success) {
                    showPageMessage(
                        response.message || "Member deleted successfully.",
                        "success"
                    );

                    loadMembers();
                } else {
                    showPageMessage(
                        response.message || "Unable to delete member.",
                        "danger"
                    );

                    button.prop("disabled", false).text("Delete");
                }
            },

            error: function (xhr) {
                const response = xhr.responseJSON;

                showPageMessage(
                    response?.message || "Unable to delete member. Please try again.",
                    "danger"
                );

                button.prop("disabled", false).text("Delete");

                console.error("Delete member API error:", xhr.responseText);
            }
        });
    }

    $("#addMemberButton").on("click", function () {
        openAddMemberModal();
    });

    $("#membersTable").on("click", ".edit-member-btn", function () {
        openEditMemberModal($(this).data("id"));
    });

    $("#membersTable").on("click", ".delete-member-btn", function () {
        const memberId = $(this).data("id");
        const memberName = $(this).data("name");

        deleteMember(memberId, memberName, $(this));
    });

    $("#memberSearch").on("input", function () {
        $("#membersMessage").empty();
        displayMembers(filterMembers($(this).val()));
    });

    $("#memberForm").on("submit", function (event) {
        event.preventDefault();

        const form = this;
        const saveButton = $("#saveMemberButton");
        const isEditing = editingMemberId !== null;

        saveButton.prop("disabled", true);
        saveButton.text(isEditing ? "Updating..." : "Saving...");
        $("#memberFormMessage").empty();

        let formData = $(form).serialize();

        if (isEditing) {
            formData += "&id=" + encodeURIComponent(editingMemberId);
        }

        $.ajax({
            url: isEditing ? "api/members/update.php" : "api/members/create.php",
            type: "POST",
            data: formData,
            dataType: "json",

            success: function (response) {
                if (!response.success) {
                    showFormMessage(
                        response.message || "Unable to save member.",
                        "danger"
                    );
                    return;
                }

                showFormMessage(
                    response.message || (
                        isEditing
                            ? "Member updated successfully."
                            : "Member added successfully."
                    ),
                    "success"
                );

                setTimeout(function () {
                    const modalElement = document.getElementById("memberModal");
                    bootstrap.Modal.getOrCreateInstance(modalElement).hide();

                    form.reset();
                    $("#memberFormMessage").empty();
                    $("#memberSearch").val("");

                    editingMemberId = null;
                    loadMembers();
                }, 1000);
            },

            error: function (xhr) {
                const response = xhr.responseJSON;

                showFormMessage(
                    response?.message || "Unable to save member. Please try again.",
                    "danger"
                );

                console.error("Save member API error:", xhr.responseText);
            },

            complete: function () {
                saveButton.prop("disabled", false);
                saveButton.text(editingMemberId !== null ? "Update Member" : "Save Member");
            }
        });
    });

    $("#memberModal").on("hidden.bs.modal", function () {
        editingMemberId = null;
        $("#memberFormMessage").empty();
        $("#saveMemberButton").prop("disabled", false).text("Save Member");
    });

    loadMembers();

});