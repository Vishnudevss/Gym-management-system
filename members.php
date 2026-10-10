<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Members';
$currentPage = 'members';
$pageScript = 'assets/js/members.js';

require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h2>Members</h2>
        <p class="text-muted mb-0">Manage your gym members.</p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        id="addMemberButton"
    >
        + Add Member
    </button>
</div>

<div class="dashboard-panel">

    <div class="row mb-4">
        <div class="col-md-6">
            <input
                type="search"
                class="form-control"
                id="memberSearch"
                placeholder="Search by name, email, or phone..."
            >
        </div>
    </div>

    <div id="membersMessage" class="mb-3"></div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Join Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody id="membersTable">
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Loading members...
                    </td>
                </tr>
            </tbody>

        </table>
    </div>

</div>

<div
    class="modal fade"
    id="memberModal"
    tabindex="-1"
    aria-labelledby="memberModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="memberModalLabel">
                    Add New Member
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <form id="memberForm">
                <div class="modal-body">

                    <div id="memberFormMessage" class="mb-3"></div>

                    <h6 class="mb-3">Personal Details</h6>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <label for="memberName" class="form-label">
                                Full Name *
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="memberName"
                                name="name"
                                maxlength="150"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="memberEmail" class="form-label">
                                Email *
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="memberEmail"
                                name="email"
                                maxlength="150"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="memberPhone" class="form-label">
                                Phone *
                            </label>

                            <input
                                type="tel"
                                class="form-control"
                                id="memberPhone"
                                name="phone"
                                maxlength="20"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="memberGender" class="form-label">
                                Gender
                            </label>

                            <select
                                class="form-select"
                                id="memberGender"
                                name="gender"
                            >
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="memberDob" class="form-label">
                                Date of Birth
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="memberDob"
                                name="date_of_birth"
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="memberJoinDate" class="form-label">
                                Join Date *
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="memberJoinDate"
                                name="join_date"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label for="memberAddress" class="form-label">
                                Address
                            </label>

                            <textarea
                                class="form-control"
                                id="memberAddress"
                                name="address"
                                rows="2"
                            ></textarea>
                        </div>

                    </div>

                    <h6 class="mb-3">Membership Details</h6>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="membershipPlan" class="form-label">
                                Membership Plan
                            </label>

                            <select
                                class="form-select"
                                id="membershipPlan"
                                name="membership_plan_id"
                            >
                                <option value="">Select a plan</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="assignedTrainer" class="form-label">
                                Assigned Trainer
                            </label>

                            <select
                                class="form-select"
                                id="assignedTrainer"
                                name="trainer_id"
                            >
                                <option value="">No trainer assigned</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="membershipStart" class="form-label">
                                Membership Start Date
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="membershipStart"
                                name="membership_start"
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="membershipEnd" class="form-label">
                                Membership End Date
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="membershipEnd"
                                name="membership_end"
                            >
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="saveMemberButton"
                    >
                        Save Member
                    </button>

                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>