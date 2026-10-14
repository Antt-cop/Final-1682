<?php
/**
 * image_search_modal.php
 * Modal dialog for AI Visual Product Search (Gemini Vision)
 * Included in includes/footer.php or directly in pages
 */
$geminiConfigured = !empty($_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? '');
?>
<!-- Modal: AI Image Search -->
<div class="modal fade ai-image-search-modal" id="imageSearchModal" tabindex="-1" role="dialog" aria-labelledby="imageSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow-lg border-0">
            <!-- Modal Header -->
            <div class="modal-header ai-modal-header text-white">
                <div class="d-flex align-items-center">
                    <div class="ai-header-icon mr-3">
                        <i class="fas fa-camera-retro"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" id="imageSearchModalLabel">
                            Search by Image <span class="badge badge-warning text-dark ml-2 ai-badge-glow"><i class="fas fa-sparkles"></i> AI Vision</span>
                        </h5>
                        <small class="text-white-50">Powered by Google Gemini Vision to automatically identify products in Dong Khoi Store</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <?php if (!$geminiConfigured): ?>
                <!-- Notice: AI Vision Simulation Mode -->
                <div class="alert alert-info ai-config-alert mb-3" id="aiConfigAlert" role="alert" style="background: #eff6ff; border-color: #bfdbfe; color: #1e3a8a;">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-magic fa-2x mr-3 text-primary"></i>
                        <div class="flex-grow-1" style="font-size: 13px;">
                            <strong>AI Vision Smart Simulation Mode is Active!</strong>
                            <p class="mb-1 text-muted" style="color: #475569 !important;">
                                You can upload any product image, take a photo with your webcam, or select sample products below.
                                <br><small>To connect direct Google Gemini 1.5 Cloud Vision API, set <code>GEMINI_API_KEY=...</code> in <code>.env</code> (Free key at <a href="https://aistudio.google.com/" target="_blank" class="font-weight-bold text-primary">Google AI Studio <i class="fas fa-external-link-alt"></i></a>).</small>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Step 1: Upload Zone -->
                <div id="aiUploadSection">
                    <div class="ai-dropzone text-center p-4" id="aiDropZone">
                        <input type="file" id="aiFileInput" accept="image/jpeg,image/png,image/webp" style="display: none;">
                        <div class="ai-dropzone-content">
                            <div class="ai-upload-icon-wrapper mb-3">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary"></i>
                            </div>
                            <h5 class="font-weight-bold mb-1">Drag and drop an image here</h5>
                            <p class="text-muted mb-3" style="font-size: 13px;">Supports JPG, PNG, WEBP (up to 6MB). Or paste directly with <kbd>Ctrl + V</kbd></p>
                            
                            <div class="d-flex justify-content-center align-items-center flex-wrap" style="gap: 10px;">
                                <button type="button" class="btn btn-primary px-4 py-2 font-weight-bold ai-btn-upload" id="btnChooseImage">
                                    <i class="fas fa-folder-open mr-2"></i> Browse from Device
                                </button>
                                <button type="button" class="btn btn-outline-secondary px-3 py-2" id="btnToggleCamera" title="Capture photo with device camera">
                                    <i class="fas fa-camera mr-1"></i> Take Photo
                                </button>
                            </div>
                        </div>

                        <!-- Camera Preview (hidden by default) -->
                        <div id="aiCameraWrapper" class="mt-3" style="display: none;">
                            <video id="aiVideoPreview" autoplay playsinline style="width: 100%; max-height: 260px; border-radius: 8px; background: #000; object-fit: cover;"></video>
                            <canvas id="aiCanvasCapture" style="display: none;"></canvas>
                            <div class="mt-2">
                                <button type="button" class="btn btn-danger btn-sm px-3" id="btnCaptureSnapshot">
                                    <i class="fas fa-circle mr-1"></i> Snap Now
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm px-3 ml-2" id="btnCancelCamera">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Sample Products (Instant 1-Click Testing) -->
                    <div class="ai-sample-section mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase;">
                                <i class="fas fa-bolt text-warning mr-1"></i> Quick test with sample product images:
                            </span>
                        </div>
                        <div class="row no-gutters ai-sample-grid">
                            <div class="col-6 col-sm-3 p-1">
                                <div class="ai-sample-card" data-sample="images/dry-046-main.jpg" data-title="Hao Hao Hot & Sour Shrimp Noodles" data-brand="Acecook" data-cat="Dry Food">
                                    <img src="<?= BASE_URL ?>/assets/images/dry-046-main.jpg" alt="Hao Hao Noodles" class="img-fluid" onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg'">
                                    <span class="ai-sample-name">Hao Hao Noodles</span>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 p-1">
                                <div class="ai-sample-card" data-sample="images/bev-003-main.jpg" data-title="Pepsi Cola 320ml" data-brand="Pepsi" data-cat="Beverages">
                                    <img src="<?= BASE_URL ?>/assets/images/bev-003-main.jpg" alt="Pepsi" class="img-fluid" onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg'">
                                    <span class="ai-sample-name">Pepsi Cola</span>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 p-1">
                                <div class="ai-sample-card" data-sample="images/bev-002-main.jpg" data-title="Coca-Cola 320ml" data-brand="Coca-Cola" data-cat="Beverages">
                                    <img src="<?= BASE_URL ?>/assets/images/bev-002-main.jpg" alt="Coca-Cola" class="img-fluid" onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg'">
                                    <span class="ai-sample-name">Coca-Cola 320ml</span>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3 p-1">
                                <div class="ai-sample-card" data-sample="images/bev-001-main.jpg" data-title="Zero Degree Green Tea 500ml" data-brand="Tan Hiep Phat" data-cat="Beverages">
                                    <img src="<?= BASE_URL ?>/assets/images/bev-001-main.jpg" alt="Zero Degree Green Tea" class="img-fluid" onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg'">
                                    <span class="ai-sample-name">Zero Degree Green Tea</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Scanning / Analyzing State (Hidden by default) -->
                <div id="aiLoadingSection" class="text-center py-4" style="display: none;">
                    <div class="ai-preview-scanner-wrapper mx-auto mb-3">
                        <img id="aiScanningPreviewImg" src="" alt="Scanning Preview" class="ai-preview-img">
                        <div class="ai-scanner-laser"></div>
                        <div class="ai-scanner-grid"></div>
                    </div>
                    <h5 class="font-weight-bold text-primary mb-1">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Gemini AI is analyzing the image...
                    </h5>
                    <p class="text-muted mb-0" style="font-size: 13px;" id="aiLoadingStatusText">
                        Detecting packaging, brand, and extracting product keywords...
                    </p>
                </div>

                <!-- Step 3: Result View (Hidden by default) -->
                <div id="aiResultSection" style="display: none;">
                    <!-- Result Summary Card -->
                    <div class="ai-result-summary-card mb-3 p-3">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img id="aiResultThumb" src="" alt="Product Thumb" class="ai-result-thumb border rounded shadow-sm">
                            </div>
                            <div class="col">
                                <div class="d-flex align-items-center flex-wrap mb-1" style="gap: 6px;">
                                    <span class="badge badge-success px-2 py-1" id="aiConfidenceBadge">
                                        <i class="fas fa-check-circle mr-1"></i> 98% Match
                                    </span>
                                    <span class="badge badge-info px-2 py-1" id="aiBrandBadge">Brand</span>
                                    <span class="badge badge-secondary px-2 py-1" id="aiCategoryBadge">Category</span>
                                </div>
                                <h4 class="font-weight-bold text-dark mb-1" id="aiResultKeyword">Product Name</h4>
                                <p class="text-muted mb-0 small" id="aiResultFeatures"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Matching Products in Dong Khoi Store -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="font-weight-bold text-dark mb-0">
                                <i class="fas fa-store text-primary mr-1"></i> Matching Products in Catalog (<span id="aiMatchCount">0</span>)
                            </h6>
                            <button type="button" class="btn btn-sm btn-link text-primary p-0 font-weight-bold" id="btnSearchAnother">
                                <i class="fas fa-redo-alt mr-1"></i> Scan Another Image
                            </button>
                        </div>

                        <!-- Product List Container -->
                        <div class="ai-matched-products-list" id="aiMatchedProductsList">
                            <!-- Populated via Javascript -->
                        </div>

                        <!-- Empty Match Message -->
                        <div class="alert alert-light border text-center py-4 my-2" id="aiNoMatchAlert" style="display: none;">
                            <i class="fas fa-box-open fa-2x text-muted mb-2 d-block"></i>
                            <h6 class="font-weight-bold text-dark">No direct match found in catalog</h6>
                            <p class="text-muted small mb-0">You can click below to search for similar keywords across the full store.</p>
                        </div>
                    </div>

                    <!-- Full Search Button -->
                    <div class="text-center pt-2 border-top">
                        <a href="#" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm" id="btnViewAllResults">
                            <i class="fas fa-search mr-2"></i> View all search results for "<span id="aiBtnKeyword"></span>"
                        </a>
                    </div>
                </div>

                <!-- Error State (Hidden by default) -->
                <div id="aiErrorSection" class="text-center py-4" style="display: none;">
                    <div class="mb-3">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <h5 class="font-weight-bold text-danger mb-2">Unable to Analyze Image</h5>
                    <p class="text-muted mb-3" id="aiErrorMessage">An error occurred during recognition.</p>
                    <button type="button" class="btn btn-outline-primary px-4" id="btnTryAgain">
                        <i class="fas fa-redo mr-1"></i> Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
