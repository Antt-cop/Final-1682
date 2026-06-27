function toggleStockField() {
    var type = document.getElementById('storage_type').value;
    var stockDiv = document.getElementById('stock_div');
    var stockInput = document.getElementById('stock_quantity');
    if (type === 'non_storable') {
        stockDiv.style.display = 'none';
        stockInput.value = 0;
    } else {
        stockDiv.style.display = 'block';
    }
}

function resetProductForm() {
    document.getElementById('sellerProductForm').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('formAlert').className = 'alert d-none';
    document.querySelector('.modal-title').innerText = 'Register New Product';
    
    // File inputs are required for new products
    document.querySelector('input[name="safety_certificate"]').required = true;
    document.querySelector('input[name="image"]').required = true;
    
    toggleStockField();
}

function openEditModal(productId) {
    resetProductForm();
    
    // Find product
    const product = sellerProducts.find(p => p.id == productId);
    if (!product) return;
    
    document.getElementById('product_id').value = product.id;
    document.querySelector('.modal-title').innerText = 'Edit Product';
    
    // Populate fields
    document.querySelector('input[name="name"]').value = product.name;
    document.querySelector('select[name="category_id"]').value = product.category_id;
    document.querySelector('select[name="storage_type"]').value = product.storage_type;
    document.querySelector('input[name="base_price"]').value = product.base_price;
    document.querySelector('input[name="stock_quantity"]').value = product.stock_quantity;
    document.querySelector('input[name="unit_type"]').value = product.unit_type;
    document.querySelector('input[name="production_date"]').value = product.production_date || '';
    document.querySelector('input[name="expiration_date"]').value = product.expiration_date || '';
    document.querySelector('input[name="origin"]').value = product.origin;
    document.querySelector('textarea[name="description"]').value = product.description;
    
    // File inputs are optional when editing (keep old if empty)
    document.querySelector('input[name="safety_certificate"]').required = false;
    document.querySelector('input[name="image"]').required = false;
    
    toggleStockField();
    
    $('#sellerProductModal').modal('show');
}

function openAddMore() {
    $('#successModal').modal('hide');
    resetProductForm();
    setTimeout(() => {
        $('#sellerProductModal').modal('show');
    }, 500);
}

function goToCatalog() {
    window.location.reload();
}

document.getElementById('sellerProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var form = this;
    var submitBtn = document.getElementById('btnSubmit');
    var alertBox = document.getElementById('formAlert');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    var formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Save & Submit';
        
        if (data.status === 'success') {
            $('#sellerProductModal').modal('hide');
            $('#successModal').modal('show');
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.innerHTML = data.message || 'An error occurred. Please try again.';
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Save & Submit';
        alertBox.className = 'alert alert-danger';
        alertBox.innerHTML = 'System error. Please try again.';
        console.error('Error:', error);
    });
});

// Init on load
document.addEventListener('DOMContentLoaded', function() {
    toggleStockField();
});
