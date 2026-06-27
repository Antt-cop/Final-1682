// js/admin_products.js
function resetForm() {
    $('#modalTitle').text('Add New Product');
    $('#formAction').val('add');
    $('#productId').val('');
    $('#productForm')[0].reset();
}

function editProduct(id) {
    $.ajax({
        url: 'php/admin_products_handler.php',
        type: 'GET',
        data: { action: 'get', id: id },
        success: function(response) {
            try {
                var res = JSON.parse(response);
                if(res.status === 'success') {
                    var p = res.data;
                    $('#modalTitle').text('Edit Product');
                    $('#formAction').val('edit');
                    $('#productId').val(p.id);
                    
                    $('#productName').val(p.name);
                    $('#productCategory').val(p.category_id);
                    $('#productPrice').val(p.base_price);
                    $('#productStock').val(p.stock_quantity);
                    $('#productUnit').val(p.unit_type);
                    $('#productDesc').val(p.description);
                    $('#productStatus').val(p.status);
                    
                    $('#productModal').modal('show');
                } else {
                    alert(res.message);
                }
            } catch(e) {
                console.error(e);
                alert("Error parsing server response.");
            }
        },
        error: function() {
            alert("Error connecting to server.");
        }
    });
}

function deleteProduct(id) {
    if(confirm("Are you sure you want to delete this product? This action cannot be undone.")) {
        $.ajax({
            url: 'php/admin_products_handler.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            success: function(response) {
                try {
                    var res = JSON.parse(response);
                    if(res.status === 'success') {
                        location.reload();
                    } else {
                        alert("Error: " + res.message);
                    }
                } catch(e) {
                    console.error(e);
                    alert("Error parsing server response.");
                }
            },
            error: function() {
                alert("Error connecting to server.");
            }
        });
    }
}
