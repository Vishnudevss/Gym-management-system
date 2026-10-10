<?php

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Trainer Management';
$currentPage = 'trainers';
$pageScript = 'assets/js/trainers.js';

require_once __DIR__ . '/includes/header.php';

?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="mb-1">Trainer Management</h2>
        <p class="text-muted mb-0">
            Manage gym trainers and their information.
        </p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        id="addTrainerButton"
    >
        + Add Trainer
    </button>
</div>

<div id="trainersMessage" class="mb-3"></div>

<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-6 col-lg-4">
                <input
                    type="search"
                    class="form-control"
                    id="trainerSearch"
                    placeholder="Search by name or specialization..."
                >
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Specialization</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="trainersTable">
                    <tr>
                        <td colspan="8" class="text-center">
                            Loading trainers...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<div
    class="modal fade"
    id="trainerModal"
    tabindex="-1"
    aria-labelledby="trainerModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="trainerModalLabel">
                    Add Trainer
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <form id="trainerForm">

                <div class="modal-body">

                    <div id="trainerFormMessage" class="mb-3"></div>

                    <input
                        type="hidden"
                        name="id"
                        id="trainerId"
                    >

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label for="trainerName" class="form-label">
                                Full Name <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="name"
                                id="trainerName"
                                maxlength="150"
                                required
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="trainerEmail" class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                id="trainerEmail"
                                maxlength="150"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="trainerPhone" class="form-label">
                                Phone
                            </label>

                            <input
                                type="tel"
                                class="form-control"
                                name="phone"
                                id="trainerPhone"
                                maxlength="20"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="trainerSpecialization" class="form-label">
                                Specialization
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="specialization"
                                id="trainerSpecialization"
                                maxlength="150"
                                placeholder="e.g. Weight Training, Yoga"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="trainerSalary" class="form-label">
                                Monthly Salary (₹)
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                name="salary"
                                id="trainerSalary"
                                min="0"
                                max="99999999.99"
                                step="0.01"
                                value="0"
                                required
                            >
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="saveTrainerButton"
                    >
                        Save Trainer
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>