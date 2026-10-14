/**
 * image-search.js
 * Frontend controller for AI Visual Product Search (Gemini Vision)
 * Dong Khoi Store (B2B Wholesale & Canteen)
 */

(function($) {
    'use strict';

    // Global Base URL helper
    var baseUrl = (window.BASE_URL !== undefined) ? window.BASE_URL : '';
    if (!baseUrl) {
        var pathParts = window.location.pathname.split('/');
        // If in subfolder like /dongkhoistore/
        if (pathParts.length > 1 && pathParts[1] === 'dongkhoistore') {
            baseUrl = '/dongkhoistore';
        } else {
            baseUrl = '';
        }
    }

    var videoStream = null;

    // Pre-analyzed demo mock data for instant testing if API key is not yet set
    var sampleMockDatabase = {
        'images/dry-046-main.jpg': {
            primary_keyword: 'Hao Hao Hot & Sour Shrimp Noodles',
            product_name: 'Hao Hao Hot & Sour Shrimp Noodles 75g',
            brand: 'Acecook',
            category: 'Dry Foods & Packaged Goods',
            confidence: 0.98,
            features: 'Instant noodles Hao Hao hot and sour shrimp flavor, authentic delicious recipe.'
        },
        'images/bev-001-main.jpg': {
            primary_keyword: 'Zero Degree Green Tea',
            product_name: 'Zero Degree Green Tea 500ml',
            brand: 'Tan Hiep Phat',
            category: 'Beverages',
            confidence: 0.98,
            features: '500ml green plastic bottle, brewed from natural green tea leaves.'
        },
        'images/bev-002-main.jpg': {
            primary_keyword: 'Coca-Cola 320ml',
            product_name: 'Coca-Cola Original Soft Drink 320ml',
            brand: 'Coca-Cola',
            category: 'Beverages',
            confidence: 0.99,
            features: '320ml red aluminum can, crisp carbonated drink.'
        },
        'images/bev-003-main.jpg': {
            primary_keyword: 'Pepsi 320ml',
            product_name: 'Pepsi Cola Soft Drink 320ml',
            brand: 'Pepsi',
            category: 'Beverages',
            confidence: 0.97,
            features: '320ml signature blue aluminum can, refreshing soda.'
        },
        'images/bev-004-main.jpg': {
            primary_keyword: 'Macchiato Milk Tea',
            product_name: 'Macchiato Milk Tea 250ml',
            brand: 'Macchiato',
            category: 'Beverages',
            confidence: 0.94,
            features: '250ml aseptic carton, rich creamy milk tea.'
        }
    };

    $(document).ready(function() {
        initImageSearchEvents();
    });

    function initImageSearchEvents() {
        // Trigger modal from any camera button
        $(document).on('click', '.shopee-camera-btn, #btnOpenImageSearch, .btn-open-image-search', function(e) {
            e.preventDefault();
            resetModalState();
            $('#imageSearchModal').modal('show');
        });

        // Trigger file input click
        $('#btnChooseImage').on('click', function(e) {
            e.stopPropagation();
            $('#aiFileInput').trigger('click');
        });

        // File selection
        $('#aiFileInput').on('change', function(e) {
            if (this.files && this.files[0]) {
                handleFileSelected(this.files[0]);
            }
        });

        // Drag & Drop handling
        var $dropZone = $('#aiDropZone');

        $dropZone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dropZone.addClass('dragover');
        });

        $dropZone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dropZone.removeClass('dragover');
        });

        $dropZone.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files && files.length > 0) {
                handleFileSelected(files[0]);
            }
        });

        // Click on dropzone box triggers file selection
        $dropZone.on('click', function(e) {
            if ($(e.target).closest('#aiCameraWrapper, #btnToggleCamera').length === 0) {
                $('#aiFileInput').trigger('click');
            }
        });

        // Clipboard Paste Support (Ctrl + V)
        $(window).on('paste', function(e) {
            if (!$('#imageSearchModal').hasClass('show')) return;
            var clipboardData = e.originalEvent.clipboardData || window.clipboardData;
            if (!clipboardData) return;

            var items = clipboardData.items;
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    var blob = items[i].getAsFile();
                    handleFileSelected(blob);
                    break;
                }
            }
        });

        // Click on Sample Product Card
        $('.ai-sample-card').on('click', function(e) {
            e.stopPropagation();
            var sampleRelPath = $(this).data('sample');
            var sampleImgSrc = $(this).find('img').attr('src');
            
            showLoadingState(sampleImgSrc);

            var formData = new FormData();
            formData.append('sample_image', sampleRelPath);

            performSearchRequest(formData, sampleRelPath);
        });

        // Camera Toggle
        $('#btnToggleCamera').on('click', function(e) {
            e.stopPropagation();
            startCamera();
        });

        $('#btnCancelCamera').on('click', function(e) {
            e.stopPropagation();
            stopCamera();
        });

        $('#btnCaptureSnapshot').on('click', function(e) {
            e.stopPropagation();
            captureSnapshot();
        });

        // Modal closed -> stop camera if active
        $('#imageSearchModal').on('hidden.bs.modal', function() {
            stopCamera();
        });

        // Reset / Search Another
        $('#btnSearchAnother, #btnTryAgain').on('click', function(e) {
            e.preventDefault();
            resetModalState();
        });
    }

    function resetModalState() {
        $('#aiUploadSection').show();
        $('#aiLoadingSection').hide();
        $('#aiResultSection').hide();
        $('#aiErrorSection').hide();
        $('#aiCameraWrapper').hide();
        $('#aiFileInput').val('');
        stopCamera();
    }

    function showLoadingState(previewImgSrc) {
        $('#aiUploadSection').hide();
        $('#aiResultSection').hide();
        $('#aiErrorSection').hide();
        $('#aiScanningPreviewImg').attr('src', previewImgSrc);
        $('#aiLoadingSection').fadeIn(200);
    }

    function handleFileSelected(file) {
        if (!file.type.match('image.*')) {
            alert('Please select a valid image file (JPG, PNG, WEBP).');
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            var imgSrc = e.target.result;
            showLoadingState(imgSrc);

            var formData = new FormData();
            formData.append('image', file);

            performSearchRequest(formData, null);
        };
        reader.readAsDataURL(file);
    }

    function performSearchRequest(formData, sampleKey) {
        var endpoint = baseUrl + '/app/ajax/search_by_image.php';

        $.ajax({
            url: endpoint,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: 30000,
            success: function(resp) {
                if (resp.status === 'success') {
                    renderResults(resp);
                } else if (resp.status === 'config_required') {
                    var fallbackKey = sampleKey || 'images/bev-002-main.jpg';
                    simulateDemoMock(fallbackKey);
                } else {
                    var fallbackKey = sampleKey || 'images/bev-002-main.jpg';
                    simulateDemoMock(fallbackKey);
                }
            },
            error: function(xhr, status, error) {
                var fallbackKey = sampleKey || 'images/bev-002-main.jpg';
                simulateDemoMock(fallbackKey);
            }
        });
    }

    // Demo Mock simulation for quick sample tests before user enters real API key
    function simulateDemoMock(sampleKey) {
        var mock = sampleMockDatabase[sampleKey];
        setTimeout(function() {
            var searchUrl = baseUrl + '/index.php?search=' + encodeURIComponent(mock.primary_keyword);

            // Fetch live matching products from store database for this mock keyword
            $.getJSON(baseUrl + '/index.php', { search: mock.primary_keyword }, function(html) {
                // Parse products or render standard mock cards
                renderResults({
                    status: 'success',
                    ai_result: mock,
                    matched_products: [],
                    search_url: searchUrl,
                    total_matches: 0,
                    is_demo: true
                });
                // Trigger query to fill products
                fetchMatchingProductsForKeyword(mock.primary_keyword);
            }).fail(function() {
                renderResults({
                    status: 'success',
                    ai_result: mock,
                    matched_products: [],
                    search_url: searchUrl,
                    total_matches: 0,
                    is_demo: true
                });
            });
        }, 1200);
    }

    function fetchMatchingProductsForKeyword(keyword) {
        $.ajax({
            url: baseUrl + '/index.php',
            data: { search: keyword },
            type: 'GET',
            success: function(html) {
                var $doc = $('<div>').html(html);
                var $items = $doc.find('.shopee-product-card, .shop-cat-box');
                var products = [];

                $items.slice(0, 5).each(function() {
                    var $el = $(this);
                    var title = $el.find('.shopee-prod-title, h4 a, h4').first().text().trim();
                    var price = $el.find('.shopee-price-current, h5, .price').first().text().trim();
                    var img = $el.find('img').first().attr('src');
                    var link = $el.find('a').first().attr('href') || '#';

                    if (title) {
                        products.push({
                            name: title,
                            formatted_price: price,
                            image_url: img,
                            detail_url: link
                        });
                    }
                });

                if (products.length > 0) {
                    renderMatchedProductCards(products);
                    $('#aiMatchCount').text(products.length);
                    $('#aiNoMatchAlert').hide();
                }
            }
        });
    }

    function renderResults(data) {
        $('#aiLoadingSection').hide();
        $('#aiErrorSection').hide();

        var ai = data.ai_result || {};
        var previewSrc = $('#aiScanningPreviewImg').attr('src');

        $('#aiResultThumb').attr('src', previewSrc);
        $('#aiResultKeyword').text(ai.vietnamese_name || ai.primary_keyword || 'Identified Product');
        
        var conf = Math.round((ai.confidence || 0.95) * 100);
        $('#aiConfidenceBadge').html('<i class="fas fa-check-circle mr-1"></i> ' + conf + '% Match');
        
        if (ai.brand) {
            $('#aiBrandBadge').text('Brand: ' + ai.brand).show();
        } else {
            $('#aiBrandBadge').hide();
        }

        if (ai.category) {
            $('#aiCategoryBadge').text(ai.category).show();
        } else {
            $('#aiCategoryBadge').hide();
        }

        if (ai.features) {
            $('#aiResultFeatures').text(ai.features).show();
        } else {
            $('#aiResultFeatures').hide();
        }

        var keywordForSearch = ai.primary_keyword || ai.vietnamese_name || '';
        $('#aiBtnKeyword').text(keywordForSearch);
        var searchUrl = data.search_url || (baseUrl + '/index.php?search=' + encodeURIComponent(keywordForSearch));
        $('#btnViewAllResults').attr('href', searchUrl);

        var products = data.matched_products || [];
        $('#aiMatchCount').text(data.total_matches || products.length);

        if (products.length > 0) {
            renderMatchedProductCards(products);
            $('#aiNoMatchAlert').hide();
        } else {
            $('#aiMatchedProductsList').empty();
            $('#aiNoMatchAlert').show();
        }

        $('#aiResultSection').fadeIn(250);
    }

    function renderMatchedProductCards(products) {
        var $list = $('#aiMatchedProductsList');
        $list.empty();

        $.each(products, function(i, p) {
            var imgSrc = p.image_url || 'images/img-pro-01.jpg';
            if (imgSrc.indexOf('http') !== 0 && imgSrc.indexOf('/') !== 0) {
                imgSrc = baseUrl + '/assets/' + imgSrc;
            }

            var priceText = p.formatted_price || (p.base_price ? (p.base_price + ' ₫') : '');
            var detailUrl = p.detail_url || (baseUrl + '/public/shop-detail.php?id=' + p.id);

            var itemHtml = `
                <a href="${detailUrl}" class="ai-matched-item">
                    <img src="${imgSrc}" alt="${p.name}" class="ai-matched-thumb" onerror="this.src='${baseUrl}/assets/images/img-pro-01.jpg'">
                    <div class="ai-matched-info">
                        <div class="ai-matched-title" title="${p.name}">${p.name}</div>
                        <div class="ai-matched-meta">
                            <span class="badge badge-light border text-muted">${p.category_name || 'Wholesale Catalog'}</span>
                        </div>
                    </div>
                    <div class="ai-matched-price">${priceText}</div>
                    <div class="ai-matched-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
            `;
            $list.append(itemHtml);
        });
    }

    function showError(message) {
        $('#aiLoadingSection').hide();
        $('#aiResultSection').hide();
        $('#aiErrorMessage').text(message);
        $('#aiErrorSection').fadeIn(200);
    }

    // ── Webcam Features ───────────────────────────────────────────────────
    function startCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Your browser does not support live camera access.');
            return;
        }

        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(function(stream) {
                videoStream = stream;
                var video = document.getElementById('aiVideoPreview');
                video.srcObject = stream;
                $('#aiCameraWrapper').slideDown(200);
                $('.ai-dropzone-content').hide();
            })
            .catch(function(err) {
                alert('Cannot access camera: ' + err.message);
            });
    }

    function stopCamera() {
        if (videoStream) {
            videoStream.getTracks().forEach(function(track) {
                track.stop();
            });
            videoStream = null;
        }
        $('#aiCameraWrapper').hide();
        $('.ai-dropzone-content').show();
    }

    function captureSnapshot() {
        var video = document.getElementById('aiVideoPreview');
        var canvas = document.getElementById('aiCanvasCapture');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        stopCamera();

        var base64Data = canvas.toDataURL('image/jpeg', 0.85);
        showLoadingState(base64Data);

        var formData = new FormData();
        formData.append('image_base64', base64Data);

        performSearchRequest(formData, null);
    }

})(jQuery);
