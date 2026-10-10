<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Payment Management';
$currentPage = 'payments';
$pageScript = 'assets/js/payments.js';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">Payment Management</h2>
            <p class="text-muted mb-0">
                Record and manage gym membership payments.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            id="addPaymentButton"
        >
            + Record Payment
        </button>
    </div>

    <div id="paymentsMessage" class="mb-3"></div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="paymentSearch" class="form-label">
                        Search Payments
                    </label>

                    <input
                        type="search"
                        class="form-control"
                        id="paymentSearch"
                        placeholder="Search by member name or transaction reference..."
                    >
                </div>

                <div class="col-md-3">
                    <label for="paymentStatusFilter" class="form-label">
                        Payment Status
                    </label>

                    <select
                        class="form-select"
                        id="paymentStatusFilter"
                    >
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="paymentMethodFilter" class="form-label">
                        Payment Method
                    </label>

                    <select
                        class="form-select"
                        id="paymentMethodFilter"
                    >
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Payment History</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Member</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Method</th>
                            <th>Transaction Reference</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="paymentsTable">
                        <tr>
                            <td colspan="8" class="text-center">
                                Loading payments...
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>
        </div>
    </div>

</div>

<div
    class="modal fade"
    id="paymentModal"
    tabindex="-1"
    aria-labelledby="paymentModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="paymentForm">

                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">
                        Record Payment
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body">

                    <div id="paymentFormMessage" class="mb-3"></div>

                    <input type="hidden" id="paymentId" name="id">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="paymentMember" class="form-label">
                                Member <span class="text-danger">*</span>
                            </label>

                            <select
                                class="form-select"
                                id="paymentMember"
                                name="member_id"
                                required
                            >
                                <option value="">Select Member</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="paymentAmount" class="form-label">
                                Amount (₹) <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                id="paymentAmount"
                                name="amount"
                                min="0.01"
                                max="99999999.99"
                                step="0.01"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="paymentDate" class="form-label">
                                Payment Date <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="paymentDate"
                                name="payment_date"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="paymentMethod" class="form-label">
                                Payment Method <span class="text-danger">*</span>
                            </label>

                            <select
                                class="form-select"
                                id="paymentMethod"
                                name="payment_method"
                                required
                            >
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="transactionReference" class="form-label">
                                Transaction Reference
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="transactionReference"
                                name="transaction_reference"
                                maxlength="150"
                                placeholder="Optional transaction ID"
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="paymentStatus" class="form-label">
                                Payment Status <span class="text-danger">*</span>
                            </label>

                            <select
                                class="form-select"
                                id="paymentStatus"
                                name="payment_status"
                                required
                            >
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="paymentNotes" class="form-label">
                                Notes
                            </label>

                            <textarea
                                class="form-control"
                                id="paymentNotes"
                                name="notes"
                                rows="3"
                                placeholder="Optional payment notes"
                            ></textarea>
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
                        id="savePaymentButton"
                    >
                        Save Payment
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>