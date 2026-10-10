<?php

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Membership Plans';
$currentPage = 'plans';
$pageScript = 'assets/js/plans.js';

require_once __DIR__ . '/includes/header.php';

?>

<div class="dashboard-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h2>Membership Plans</h2>
        <p class="text-muted mb-0">Manage your gym membership plans and pricing.</p>
    </div>

    <button
        type="button"
        class="btn btn-primary"
        id="addPlanButton"
    >
        + Add Membership Plan
    </button>
</div>

<div class="dashboard-panel">

    <div class="row mb-4">
        <div class="col-md-6">
            <input
                type="search"
                class="form-control"
                id="planSearch"
                placeholder="Search by plan name..."
            >
        </div>
    </div>

    <div id="plansMessage" class="mb-3"></div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plan Name</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody id="plansTable">
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Loading membership plans...
                    </td>
                </tr>
            </tbody>

        </table>
    </div>

</div>

<div
    class="modal fade"
    id="planModal"
    tabindex="-1"
    aria-labelledby="planModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="planModalLabel">
                    Add Membership Plan
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <form id="planForm">

                <input type="hidden" name="id" id="planId">

                <div class="modal-body">

                    <div id="planFormMessage" class="mb-3"></div>

                    <div class="mb-3">
                        <label for="planName" class="form-label">
                            Plan Name *
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="planName"
                            name="plan_name"
                            maxlength="100"
                            placeholder="e.g. Monthly Plan"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="planDuration" class="form-label">
                            Duration (Months) *
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            id="planDuration"
                            name="duration_months"
                            min="1"
                            max="120"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="planPrice" class="form-label">
                            Price (₹) *
                        </label>

                        <input
                            type="number"
                            class="form-control"
                            id="planPrice"
                            name="price"
                            min="0.01"
                            max="99999999.99"
                            step="0.01"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="planDescription" class="form-label">
                            Description
                        </label>

                        <textarea
                            class="form-control"
                            id="planDescription"
                            name="description"
                            rows="3"
                            placeholder="Describe what's included in this plan"
                        ></textarea>
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
                        id="savePlanButton"
                    >
                        Save Plan
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>