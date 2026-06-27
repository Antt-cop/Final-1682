// js/admin_orders.js
function viewOrder(id) {
    $('#modalOrderId').text('#' + id);
    $('#orderModalBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i></div>');
    $('#orderModal').modal('show');
    
    $.ajax({
        url: 'php/admin_orders_handler.php',
        type: 'GET',
        data: { action: 'get_details', id: id },
        success: function(response) {
            try {
                var res = JSON.parse(response);
                if(res.status === 'success') {
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
    html += '<p class="mb-1"><strong>Shipping Address:</strong><br>' + (order.shipping_address || 'N/A') + '</p>';
    html += '</div>';
    html += '</div>';
    
    // Status Controls
    html += '<div class="row border-top border-bottom py-3 mb-4 bg-light">';
    html += '<div class="col-md-6">';
    html += '<div class="form-group mb-0">';
    html += '<label class="font-weight-bold">Order Status</label>';
    html += '<select class="form-control" onchange="updateStatus(' + order.id + ', this.value)">';
    var statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
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
    
    // Items Table
    html += '<h6 class="font-weight-bold">Order Items</h6>';
    if(order.items && order.items.length > 0) {
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
        html += '<p class="text-muted">No items found for this order.</p>';
    }
    
    $('#orderModalBody').html(html);
}

function updateStatus(id, status) {
    $.post('php/admin_orders_handler.php', {action: 'update_status', id: id, status: status}, function(response) {
        var res = JSON.parse(response);
        if(res.status === 'success') {
            alert('Order status updated!');
            location.reload();
        } else {
            alert('Error updating status: ' + res.message);
        }
    });
}

function updatePaymentStatus(id, payment_status) {
    $.post('php/admin_orders_handler.php', {action: 'update_payment', id: id, payment_status: payment_status}, function(response) {
        var res = JSON.parse(response);
        if(res.status === 'success') {
            alert('Payment status updated!');
            location.reload();
        } else {
            alert('Error updating payment status: ' + res.message);
        }
    });
}
