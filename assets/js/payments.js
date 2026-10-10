$(document).ready(function () {
    const paymentsTable = $('#paymentsTable');
    const paymentsMessage = $('#paymentsMessage');
    const paymentFormMessage = $('#paymentFormMessage');
    const paymentModalElement = document.getElementById('paymentModal');

    const paymentModal = bootstrap.Modal.getOrCreateInstance(
        paymentModalElement
    );

    let payments = [];
    let members = [];

    function showMessage(message, type = 'danger') {
        paymentsMessage
            .removeClass('d-none alert-success alert-danger alert-warning')
            .addClass(`alert alert-${type}`)
            .text(message);
    }

    function hideMessage() {
        paymentsMessage
            .addClass('d-none')
            .removeClass('alert alert-success alert-danger alert-warning')
            .text('');
    }

    function showFormMessage(message, type = 'danger') {
        paymentFormMessage
            .removeClass('d-none alert alert-success alert-danger alert-warning')
            .addClass(`alert alert-${type}`)
            .text(message);
    }

    function hideFormMessage() {
        paymentFormMessage
            .addClass('d-none')
            .removeClass('alert alert-success alert-danger alert-warning')
            .text('');
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function formatAmount(amount) {
        const value = Number(amount);

        if (!Number.isFinite(value)) {
            return '₹0.00';
        }

        return value.toLocaleString('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatMethod(method) {
        const methods = {
            cash: 'Cash',
            upi: 'UPI',
            card: 'Card',
            bank_transfer: 'Bank Transfer'
        };

        return methods[method] || method || '-';
    }

    function formatStatus(status) {
        const statuses = {
            completed: 'Completed',
            pending: 'Pending',
            failed: 'Failed'
        };

        return statuses[status] || status || '-';
    }

    function formatDate(date) {
        if (!date) {
            return '-';
        }

        const parts = String(date).split('-');

        if (parts.length !== 3) {
            return escapeHtml(date);
        }

        return `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    function loadMembers() {
        return $.ajax({
            url: 'api/payments/options.php',
            method: 'GET',
            dataType: 'json'
        })
            .done(function (response) {
                if (!response.success) {
                    showMessage(
                        response.message || 'Unable to load members.'
                    );
                    return;
                }

                members = response.members || [];

                const memberSelect = $('#paymentMember');

                memberSelect.empty();

                memberSelect.append(
                    $('<option>', {
                        value: '',
                        text: 'Select a member'
                    })
                );

                members.forEach(function (member) {
                    memberSelect.append(
                        $('<option>', {
                            value: member.id,
                            text: `${member.name} (${member.email})`
                        })
                    );
                });
            })
            .fail(function (xhr) {
                showMessage(
                    xhr.responseJSON?.message ||
                    'Unable to load members.'
                );
            });
    }

    function loadPayments() {
        hideMessage();

        paymentsTable.html(`
            <tr>
                <td colspan="8" class="text-center">
                    Loading payments...
                </td>
            </tr>
        `);

        return $.ajax({
            url: 'api/payments/list.php',
            method: 'GET',
            dataType: 'json'
        })
            .done(function (response) {
                if (!response.success) {
                    payments = [];
                    renderPayments();

                    showMessage(
                        response.message || 'Unable to load payments.'
                    );
                    return;
                }

                payments = Array.isArray(response.payments)
                    ? response.payments
                    : [];

                renderPayments();
            })
            .fail(function (xhr) {
                payments = [];
                paymentsTable.empty();

                showMessage(
                    xhr.responseJSON?.message ||
                    'Unable to load payments.'
                );
            });
    }

    function renderPayments() {
        const searchTerm = String(
            $('#paymentSearch').val() || ''
        ).trim().toLowerCase();

        const selectedStatus = $('#paymentStatusFilter').val();
        const selectedMethod = $('#paymentMethodFilter').val();

        const filteredPayments = payments.filter(function (payment) {
            const memberName = String(
                payment.member_name || ''
            ).toLowerCase();

            const reference = String(
                payment.transaction_reference || ''
            ).toLowerCase();

            const matchesSearch =
                memberName.includes(searchTerm) ||
                reference.includes(searchTerm);

            const matchesStatus =
                !selectedStatus ||
                selectedStatus === 'all' ||
                payment.payment_status === selectedStatus;

            const matchesMethod =
                !selectedMethod ||
                selectedMethod === 'all' ||
                payment.payment_method === selectedMethod;

            return matchesSearch && matchesStatus && matchesMethod;
        });

        if (filteredPayments.length === 0) {
            paymentsTable.html(`
                <tr>
                    <td colspan="8" class="text-center">
                        No payment records found.
                    </td>
                </tr>
            `);

            return;
        }

        const rows = filteredPayments.map(function (payment) {
            const statusClasses = {
                completed: 'bg-success',
                pending: 'bg-warning text-dark',
                failed: 'bg-danger'
            };

            const statusClass =
                statusClasses[payment.payment_status] || 'bg-secondary';

            return `
                <tr>
                    <td>${escapeHtml(payment.id)}</td>

                    <td>${escapeHtml(payment.member_name)}</td>

                    <td>${escapeHtml(formatAmount(payment.amount))}</td>

                    <td>${formatDate(payment.payment_date)}</td>

                    <td>${escapeHtml(formatMethod(payment.payment_method))}</td>

                    <td>${escapeHtml(
                        payment.transaction_reference || '-'
                    )}</td>

                    <td>
                        <span class="badge ${statusClass}">
                            ${escapeHtml(
                                formatStatus(payment.payment_status)
                            )}
                        </span>
                    </td>

                    <td>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary view-payment"
                            data-id="${escapeHtml(payment.id)}">
                            View
                        </button>
                    </td>
                </tr>
            `;
        });

        paymentsTable.html(rows.join(''));
    }

    function resetPaymentForm() {
        $('#paymentForm')[0].reset();

        $('#paymentId').val('');
        $('#paymentStatus').val('completed');

        $('#paymentModalLabel').text('Record Payment');

        $('#savePaymentButton')
            .text('Record Payment')
            .prop('disabled', false);

        hideFormMessage();

        const today = new Date();

        const localDate = new Date(
            today.getTime() - today.getTimezoneOffset() * 60000
        )
            .toISOString()
            .split('T')[0];

        $('#paymentDate').val(localDate);
    }

    $('#addPaymentButton').on('click', function () {
        resetPaymentForm();
        paymentModal.show();
    });

    $('#paymentForm').on('submit', function (event) {
        event.preventDefault();

        hideFormMessage();

        const memberId = $('#paymentMember').val();
        const amount = $('#paymentAmount').val();
        const paymentDate = $('#paymentDate').val();
        const paymentMethod = $('#paymentMethod').val();

        const transactionReference = $(
            '#transactionReference'
        ).val().trim();

        const paymentStatus = $('#paymentStatus').val();
        const notes = $('#paymentNotes').val().trim();

        if (!memberId) {
            showFormMessage('Please select a member.');
            return;
        }

        if (
            !amount ||
            !Number.isFinite(Number(amount)) ||
            Number(amount) <= 0 ||
            Number(amount) > 99999999.99
        ) {
            showFormMessage('Please enter a valid payment amount.');
            return;
        }

        if (!paymentDate) {
            showFormMessage('Please select a payment date.');
            return;
        }

        if (!paymentMethod) {
            showFormMessage('Please select a payment method.');
            return;
        }

        if (!['completed', 'pending', 'failed'].includes(paymentStatus)) {
            showFormMessage('Please select a valid payment status.');
            return;
        }

        const payload = {
            member_id: Number(memberId),
            amount: Number(amount),
            payment_date: paymentDate,
            payment_method: paymentMethod,
            transaction_reference: transactionReference,
            payment_status: paymentStatus,
            notes: notes
        };

        const saveButton = $('#savePaymentButton');

        saveButton
            .prop('disabled', true)
            .text('Saving...');

        $.ajax({
            url: 'api/payments/create.php',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload)
        })
            .done(function (response) {
                if (!response.success) {
                    showFormMessage(
                        response.message || 'Unable to record payment.'
                    );
                    return;
                }

                paymentModal.hide();
                resetPaymentForm();

                loadPayments().done(function () {
                    showMessage(
                        response.message ||
                        'Payment recorded successfully.',
                        'success'
                    );
                });
            })
            .fail(function (xhr) {
                showFormMessage(
                    xhr.responseJSON?.message ||
                    'Unable to record payment.'
                );
            })
            .always(function () {
                saveButton
                    .prop('disabled', false)
                    .text('Record Payment');
            });
    });

    $('#paymentSearch').on('input', renderPayments);

    $('#paymentStatusFilter, #paymentMethodFilter').on(
        'change',
        renderPayments
    );

    $('#paymentsTable').on('click', '.view-payment', function () {
        const paymentId = Number($(this).data('id'));

        const payment = payments.find(function (item) {
            return Number(item.id) === paymentId;
        });

        if (!payment) {
            return;
        }

        const member = members.find(function (item) {
            return Number(item.id) === Number(payment.member_id);
        });

        const details = [
            `Payment ID: ${payment.id}`,
            `Member: ${payment.member_name}`,
            `Email: ${member?.email || '-'}`,
            `Amount: ${formatAmount(payment.amount)}`,
            `Payment Date: ${formatDate(payment.payment_date)}`,
            `Method: ${formatMethod(payment.payment_method)}`,
            `Reference: ${payment.transaction_reference || '-'}`,
            `Status: ${formatStatus(payment.payment_status)}`,
            `Notes: ${payment.notes || '-'}`
        ].join('\n');

        window.alert(details);
    });

    loadMembers();
    loadPayments();
});