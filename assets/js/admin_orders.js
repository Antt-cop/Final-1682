// js/admin_orders.js
function viewOrder(id) {
    $('#modalOrderId').text('#' + id);
    $('#orderModalBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i></div>');
    $('#orderModal').modal('show');

    $.ajax({
        url: '../app/handlers/admin_orders.php',
        type: 'GET',
        data: { action: 'get_details', id: id },
        success: function(response) {
            try {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    renderOrderDetails(res.data);
                } else {
                    $('#orderModalBody').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            } catch(e) {
                console.error(e);
                $('#orderModalBody').html('<div class="alert alert-danger">Error parsing server response.</div>');
            }
        },
        error: function() {
            $('#orderModalBody').html('<div class="alert alert-danger">Error connecting to server.</div>');
        }
    });
}

function renderOrderDetails(order) {
    var html = '<div class="row mb-4">';

    // Customer Info
    html += '<div class="col-md-6">';
    html += '<h6 class="font-weight-bold">Customer Details</h6>';
    html += '<p class="mb-1"><strong>Name:</strong> ' + (order.username || 'N/A') + '</p>';
    html += '<p class="mb-1"><strong>Email:</strong> ' + (order.email || 'N/A') + '</p>';
    html += '<p class="mb-1"><strong>Phone:</strong> ' + (order.contact_phone || 'N/A') + '</p>';
    html += '</div>';

    // Order Info
    html += '<div class="col-md-6">';
    html += '<h6 class="font-weight-bold">Order Summary</h6>';
    html += '<p class="mb-1"><strong>Date:</strong> ' + new Date(order.created_at).toLocaleString() + '</p>';
    html += '<p class="mb-1"><strong>Total Amount:</strong> $' + parseFloat(order.total_amount).toFixed(2) + '</p>';
    html += '<p class="mb-1"><strong>Payment Method:</strong> ' + (order.payment_method === 'cod' ? 'Cash On Delivery (COD)' : order.payment_method) + '</p>';
    html += '<p class="mb-1"><strong>Shipping Address:</strong><br>' + (order.shipping_address || 'N/A') + '</p>';
    html += '</div>';
    html += '</div>';

    // Status Controls
    html += '<div class="row border-top border-bottom py-3 mb-4 bg-light">';
    html += '<div class="col-md-6">';
    html += '<div class="form-group mb-0">';
    html += '<label class="font-weight-bold">Order Status</label>';
    html += '<select class="form-control" onchange="updateStatus(' + order.id + ', this.value)">';
    var statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
    statuses.forEach(function(s) {
        var sel = s === order.status ? 'selected' : '';
        html += '<option value="' + s + '" ' + sel + '>' + s.charAt(0).toUpperCase() + s.slice(1) + '</option>';
    });
    html += '</select>';
    html += '</div></div>';

    html += '<div class="col-md-6">';
    html += '<div class="form-group mb-0">';
    html += '<label class="font-weight-bold">Payment Status</label>';
    html += '<select class="form-control" onchange="updatePaymentStatus(' + order.id + ', this.value)">';
    var payStatuses = ['unpaid', 'paid', 'refunded'];
    payStatuses.forEach(function(s) {
        var sel = s === order.payment_status ? 'selected' : '';
        html += '<option value="' + s + '" ' + sel + '>' + s.charAt(0).toUpperCase() + s.slice(1) + '</option>';
    });
    html += '</select>';
    html += '</div></div>';
    html += '</div>';

    // Tracking code display (if already set)
    if (order.tracking_code) {
        html += '<div class="alert alert-info d-flex align-items-center mb-3">';
        html += '<i class="fas fa-truck mr-2"></i>';
        html += '<span><strong>GHN Tracking Code:</strong> ' + order.tracking_code + '</span>';
        html += '<a href="https://donhang.ghn.vn/?order_code=' + order.tracking_code + '" target="_blank" class="btn btn-sm btn-info ml-auto"><i class="fas fa-external-link-alt mr-1"></i>Track on GHN</a>';
        html += '</div>';
    }

    // Items Table
    html += '<h6 class="font-weight-bold">Order Items</h6>';
    if (order.items && order.items.length > 0) {
        html += '<div class="table-responsive"><table class="table table-bordered table-sm">';
        html += '<thead class="bg-light"><tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead><tbody>';
        order.items.forEach(function(item) {
            html += '<tr>';
            html += '<td>' + (item.product_name || 'Product #' + item.product_id) + '</td>';
            html += '<td>$' + parseFloat(item.price_per_unit).toFixed(2) + '</td>';
            html += '<td>' + item.quantity + '</td>';
            html += '<td>$' + parseFloat(item.total_price).toFixed(2) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
    } else {
        html += '<p class="text-muted mb-4">No items found for this order.</p>';
    }

    // Order Logs Table
    html += '<h6 class="font-weight-bold mt-4">Order Logs (History)</h6>';
    if (order.logs && order.logs.length > 0) {
        html += '<div class="table-responsive"><table class="table table-bordered table-sm">';
        html += '<thead class="bg-light"><tr><th>Date</th><th>Updated By</th><th>Note</th></tr></thead><tbody>';
        order.logs.forEach(function(log) {
            html += '<tr>';
            html += '<td>' + new Date(log.created_at).toLocaleString() + '</td>';
            html += '<td>' + (log.username || 'User #' + log.user_id) + '</td>';
            html += '<td>' + log.note + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';
    } else {
        html += '<p class="text-muted">No history logs found for this order.</p>';
    }

    $('#orderModalBody').html(html);
}

// Called when admin changes the status dropdown (in table or modal)
function updateStatus(id, status) {
    if (status === 'shipped') {
        // Show tracking code popup modal
        $('#trackingOrderId').val(id);
        $('#trackingCodeInput').val('').removeClass('is-invalid');
        $('#trackingModal').modal('show');
        return;
    }
    _doUpdateStatus(id, status, '');
}

// Internal: send update to server
function _doUpdateStatus(id, status, tracking_code) {
    $.post('../app/handlers/admin_orders.php', {
        action: 'update_status',
        id: id,
        status: status,
        tracking_code: tracking_code
    }, function(response) {
        try {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                location.reload();
            } else {
                alert('Error updating status: ' + res.message);
            }
        } catch(e) {
            alert('Error connecting to server.');
        }
    });
}

// Confirm button in tracking modal
function submitTrackingCode() {
    var orderId = $('#trackingOrderId').val();
    var code = $('#trackingCodeInput').val().trim();
    if (!code) {
        $('#trackingCodeInput').addClass('is-invalid');
        return;
    }
    $('#trackingModal').modal('hide');
    _doUpdateStatus(orderId, 'shipped', code);
}

// Reset invalid state if modal is dismissed
$(document).on('hidden.bs.modal', '#trackingModal', function () {
    $('#trackingCodeInput').removeClass('is-invalid');
});

function updatePaymentStatus(id, payment_status) {
    $.post('../app/handlers/admin_orders.php', {action: 'update_payment', id: id, payment_status: payment_status}, function(response) {
        try {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                location.reload();
            } else {
                alert('Error updating payment status: ' + res.message);
            }
        } catch(e) {
            alert('Error connecting to server.');
        }
    });
}
