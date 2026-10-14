    <!-- Start Instagram Feed  
    <div class="instagram-box">
        <div class="main-instagram owl-carousel owl-theme">
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-01.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-02.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-03.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-04.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-05.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-06.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-07.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-08.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-09.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="item">
                <div class="ins-inner-box">
                    <img src="<?= BASE_URL ?>/assets/images/instagram-img-05.jpg" alt="" />
                    <div class="hov-in">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
     End Instagram Feed  -->


    <!-- Start Footer  -->
    <footer>
    </footer>
    <!-- End Footer  -->

    <!-- Start copyright  -->
    <div class="footer-copyright">
        <p class="footer-company">All Rights Reserved. &copy; <?= date('Y') ?> <a href="<?= BASE_URL ?>/index.php" style="color: #ffd839; font-weight: 700;">Dong Khoi Store</a> &mdash; B2B Wholesale &amp; Canteen Distribution Platform</p>
    </div>

    <!-- Modern Floating Back to Top Button -->
    <a href="#" id="back-to-top" title="Back to top" style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- ALL JS FILES -->
    <script src="<?= BASE_URL ?>/assets/js/jquery-3.2.1.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/jquery-ui.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/jquery.nicescroll.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/popper.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/bootstrap.min.js"></script>
    <!-- ALL PLUGINS -->
    <script src="<?= BASE_URL ?>/assets/js/jquery.superslides.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/bootstrap-select.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/inewsticker.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/bootsnav.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/images-loded.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/isotope.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/owl.carousel.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/baguetteBox.min.js"></script>
    <?php
    if (isset($extra_scripts)) {
        foreach ($extra_scripts as $script) {
            echo '    <script src="' . BASE_URL . '/assets/' . htmlspecialchars($script) . '"></script>' . "\n";
        }
    }
    ?>
    <script src="<?= BASE_URL ?>/assets/js/form-validator.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/contact-form-script.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/custom.js"></script>
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.isLoggedIn = <?= (isset($_SESSION['user_id']) || isset($_SESSION['user_name'])) ? 'true' : 'false' ?>;
        var isLoggedIn = window.isLoggedIn;
    </script>
    <script src="<?= BASE_URL ?>/assets/js/image-search.js?v=<?= time() ?>"></script>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" role="dialog" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form id="addToCartForm">
              <div class="modal-header">
                <h5 class="modal-title" id="productDetailsModalLabel">Product Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="id" id="modalProductId" value="">
                  <h4 id="modalProductName">Product Name</h4>
                  <p>Price: $<span id="modalProductPrice">0.00</span></p>
                  <p>Stock: <span id="modalProductStock">0</span> <span id="modalProductUnit"></span></p>
                  
                  <div class="form-group">
                      <label for="modalProductQty">Quantity</label>
                      <input type="number" class="form-control" name="qty" id="modalProductQty" value="1" min="1">
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Add to Cart</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Shopee-style Toast Notification -->
    <div class="shopee-toast" id="shopeeToast">
        <i class="fas fa-check-circle"></i>
        <span>Added successfully to cart</span>
    </div>

    <script>
    (function($) {
        if (!$) return;
        var isLoggedIn = (typeof window.isLoggedIn !== 'undefined') ? window.isLoggedIn : <?php echo (isset($_SESSION['user_id']) || isset($_SESSION['user_name'])) ? 'true' : 'false'; ?>;
        
        $(document).ready(function() {
            $(document).on('click', '.add-to-cart-btn', function(e) {
                e.preventDefault();
                
                if (!isLoggedIn) {
                    window.location.href = '<?= BASE_URL ?>/public/login.php';
                    return;
                }

                var productId = $(this).data('id');
                
                $.ajax({
                    url: '<?= BASE_URL ?>/app/ajax/get_product.php',
                    type: 'GET',
                    data: { id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var p = response.data;
                            $('#modalProductId').val(p.id);
                            $('#modalProductName').text(p.name);
                            $('#modalProductPrice').text(parseFloat(p.base_price).toFixed(2));
                            
                            var stockText = p.storage_type === 'non_storable' ? 'Pre-order' : p.stock_quantity;
                            $('#modalProductStock').text(stockText);
                            $('#modalProductUnit').text(p.unit_type);
                            
                            if (p.storage_type !== 'non_storable') {
                                $('#modalProductQty').attr('max', p.stock_quantity);
                            } else {
                                $('#modalProductQty').removeAttr('max');
                            }
                            
                            $('#productDetailsModal').modal('show');
                        } else {
                            alert('Error loading product details');
                        }
                    }
                });
            });

            $('#addToCartForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                
                $.ajax({
                    url: '<?= BASE_URL ?>/public/cart.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#productDetailsModal').modal('hide');
                            var toast = $('#shopeeToast');
                            toast.addClass('show');
                            setTimeout(function() {
                                toast.removeClass('show');
                            }, 2000);
                            
                            if (response.cart_count !== undefined) {
                                $('.badge').text(response.cart_count);
                                $('.cart-list').html(response.cart_html);
                            }
                        }
                    }
                });
            });
        });
    })(window.jQuery || window.$);
    </script>
    <!-- AI Image Search Modal -->
    <?php include_once INCLUDES_PATH . '/image_search_modal.php'; ?>
</body>

</html>


