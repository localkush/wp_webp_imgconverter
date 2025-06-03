jQuery(document).ready(function($) {
    'use strict';

    // Global variables
    let allImages = [];
    let filteredImages = [];
    let isConverting = false;
    let conversionQueue = [];
    let currentConversionIndex = 0;

    // Initialize the plugin
    init();

    function init() {
        bindEvents();
        updateQualityDisplay();
        setupFilterHandlers();
        setupSearchHandler();
    }

    function bindEvents() {
        $('#scan-images').on('click', scanImages);
        $('#convert-all').on('click', convertAllImages);
        $('#convert-pending').on('click', convertPendingImages);
        $('#select-all').on('change', toggleSelectAll);
        $('#webp-quality').on('input', updateQualityDisplay);
        
        // Delegate events for dynamically created elements
        $(document).on('click', '.convert-btn', convertSingleImage);
        $(document).on('change', '.image-select', updateSelectedCount);
    }

    function scanImages() {
        showLoading();
        hideAllSections();

        const $scanBtn = $('#scan-images');
        $scanBtn.prop('disabled', true).html('<span class="btn-icon">🔄</span> Scanning...');

        $.ajax({
            url: wpwebp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'scan_images',
                nonce: wpwebp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    allImages = response.data;
                    filteredImages = [...allImages];
                    
                    hideLoading();
                    updateStats();
                    renderImages();
                    showImagesSections();
                    showSuccessMessage(wpwebp_ajax.strings.scan_complete + ' Found ' + allImages.length + ' images.');
                } else {
                    hideLoading();
                    showErrorMessage('Failed to scan images: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                showErrorMessage('Failed to scan images: ' + error);
            },
            complete: function() {
                $scanBtn.prop('disabled', false).html('<span class="btn-icon">🔍</span> Scan All Images');
            }
        });
    }

    function convertSingleImage() {
        const $btn = $(this);
        const $item = $btn.closest('.wpwebp-image-item');
        const attachmentId = $item.data('id');

        if (isConverting) {
            showWarningMessage('Another conversion is in progress. Please wait.');
            return;
        }

        startSingleConversion($btn, $item, attachmentId);
    }

    function startSingleConversion($btn, $item, attachmentId) {
        // Show loading state
        $btn.hide();
        $item.find('.conversion-loading').show();
        
        $.ajax({
            url: wpwebp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convert_image',
                attachment_id: attachmentId,
                nonce: wpwebp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update image item
                    $item.addClass('converted');
                    $item.find('.conversion-loading').hide();
                    $item.find('.conversion-status').show();
                    
                    // Show savings info
                    const $conversionInfo = $item.find('.conversion-info');
                    $conversionInfo.show();
                    $conversionInfo.find('.savings-amount').text('Saved: ' + formatBytes(data.savings));
                    $conversionInfo.find('.savings-percent').text(data.savings_percent + '% smaller');
                    
                    // Update the image in allImages array
                    const imageIndex = allImages.findIndex(img => img.id === attachmentId);
                    if (imageIndex !== -1) {
                        allImages[imageIndex].converted = true;
                        allImages[imageIndex].webp_size = data.webp_size;
                        allImages[imageIndex].savings = data.savings;
                    }
                    
                    updateStats();
                    showSuccessMessage('Image converted successfully! Saved ' + formatBytes(data.savings));
                    
                } else {
                    $item.find('.conversion-loading').hide();
                    $btn.show();
                    showErrorMessage('Conversion failed: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                $item.find('.conversion-loading').hide();
                $btn.show();
                showErrorMessage('Conversion failed: ' + error);
            }
        });
    }

    function convertAllImages() {
        const unconvertedImages = allImages.filter(img => !img.converted);
        if (unconvertedImages.length === 0) {
            showWarningMessage('All images are already converted!');
            return;
        }
        
        startBulkConversion(unconvertedImages);
    }

    function convertPendingImages() {
        const selectedImages = getSelectedImages().filter(img => !img.converted);
        if (selectedImages.length === 0) {
            showWarningMessage('No pending images selected!');
            return;
        }
        
        startBulkConversion(selectedImages);
    }

    function startBulkConversion(imagesToConvert) {
        if (isConverting) {
            showWarningMessage('Another conversion is in progress. Please wait.');
            return;
        }

        isConverting = true;
        conversionQueue = [...imagesToConvert];
        currentConversionIndex = 0;

        // Show progress bar
        $('#conversion-progress').show();
        updateProgress(0, 'Starting conversion...');

        // Disable buttons
        $('.wpwebp-btn').prop('disabled', true);

        // Start processing queue
        processConversionQueue();
    }

    function processConversionQueue() {
        if (currentConversionIndex >= conversionQueue.length) {
            // Conversion complete
            completeConversion();
            return;
        }

        const currentImage = conversionQueue[currentConversionIndex];
        const progress = (currentConversionIndex / conversionQueue.length) * 100;
        
        updateProgress(progress, `Converting: ${currentImage.title} (${currentConversionIndex + 1}/${conversionQueue.length})`);

        // Find and update the UI for current image
        const $item = $(`.wpwebp-image-item[data-id="${currentImage.id}"]`);
        $item.find('.convert-btn').hide();
        $item.find('.conversion-loading').show();

        $.ajax({
            url: wpwebp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convert_image',
                attachment_id: currentImage.id,
                nonce: wpwebp_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Update UI
                    $item.addClass('converted');
                    $item.find('.conversion-loading').hide();
                    $item.find('.conversion-status').show();
                    
                    // Show savings info
                    const $conversionInfo = $item.find('.conversion-info');
                    $conversionInfo.show();
                    $conversionInfo.find('.savings-amount').text('Saved: ' + formatBytes(data.savings));
                    $conversionInfo.find('.savings-percent').text(data.savings_percent + '% smaller');
                    
                    // Update data
                    const imageIndex = allImages.findIndex(img => img.id === currentImage.id);
                    if (imageIndex !== -1) {
                        allImages[imageIndex].converted = true;
                        allImages[imageIndex].webp_size = data.webp_size;
                        allImages[imageIndex].savings = data.savings;
                    }
                    
                } else {
                    $item.find('.conversion-loading').hide();
                    $item.find('.convert-btn').show();
                    console.error('Conversion failed for image ' + currentImage.id + ': ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                $item.find('.conversion-loading').hide();
                $item.find('.convert-btn').show();
                console.error('Conversion failed for image ' + currentImage.id + ': ' + error);
            },
            complete: function() {
                currentConversionIndex++;
                
                // Small delay between conversions to prevent server overload
                setTimeout(processConversionQueue, 500);
            }
        });
    }

    function completeConversion() {
        isConverting = false;
        updateProgress(100, 'Conversion complete!');
        updateStats();
        
        // Hide progress bar after a delay
        setTimeout(() => {
            $('#conversion-progress').hide();
        }, 2000);

        // Re-enable buttons
        $('.wpwebp-btn').prop('disabled', false);

        const convertedCount = conversionQueue.length;
        showSuccessMessage(`Successfully converted ${convertedCount} image${convertedCount > 1 ? 's' : ''}!`);
    }

    function renderImages() {
        const $grid = $('#images-grid');
        $grid.empty();

        if (filteredImages.length === 0) {
            $('#empty-state').show();
            return;
        }

        $('#empty-state').hide();

        filteredImages.forEach((image, index) => {
            const $item = createImageItem(image, index);
            $grid.append($item);
        });
    }

    function createImageItem(image, index) {
        const template = document.getElementById('image-item-template');
        const $item = $(template.content.cloneNode(true));
        
        const $container = $item.find('.wpwebp-image-item');
        $container.attr('data-id', image.id);
        $container.css('animation-delay', (index * 0.1) + 's');
        
        // Set image data
        $item.find('img').attr('src', image.url).attr('alt', image.title);
        $item.find('.image-title').text(image.title || 'Untitled');
        $item.find('.image-size').text(image.size_human);
        $item.find('.image-type').text(image.mime_type.split('/')[1].toUpperCase());
        
        // Handle conversion status
        if (image.converted) {
            $container.addClass('converted');
            $item.find('.convert-btn').hide();
            $item.find('.conversion-status').show();
            
            const $conversionInfo = $item.find('.conversion-info');
            $conversionInfo.show();
            $conversionInfo.find('.savings-amount').text('Saved: ' + formatBytes(image.savings));
            $conversionInfo.find('.savings-percent').text(
                Math.round(((image.savings / image.size) * 100)) + '% smaller'
            );
        }

        return $item;
    }

    function updateStats() {
        const totalImages = allImages.length;
        const convertedImages = allImages.filter(img => img.converted).length;
        const pendingImages = totalImages - convertedImages;
        const totalSavings = allImages.reduce((sum, img) => sum + (img.savings || 0), 0);

        $('#total-images').text(totalImages);
        $('#converted-images').text(convertedImages);
        $('#pending-images').text(pendingImages);
        $('#total-savings').text(formatBytes(totalSavings));

        // Show/hide action buttons based on status
        if (totalImages > 0) {
            if (pendingImages > 0) {
                $('#convert-all, #convert-pending').show();
            } else {
                $('#convert-all, #convert-pending').hide();
            }
        }
    }

    function setupFilterHandlers() {
        $('#filter-status, #filter-type').on('change', applyFilters);
    }

    function setupSearchHandler() {
        $('#search-images').on('input', debounce(applyFilters, 300));
    }

    function applyFilters() {
        const statusFilter = $('#filter-status').val();
        const typeFilter = $('#filter-type').val();
        const searchTerm = $('#search-images').val().toLowerCase();

        filteredImages = allImages.filter(image => {
            // Status filter
            if (statusFilter === 'converted' && !image.converted) return false;
            if (statusFilter === 'pending' && image.converted) return false;

            // Type filter
            if (typeFilter !== 'all' && image.mime_type !== typeFilter) return false;

            // Search filter
            if (searchTerm && !image.title.toLowerCase().includes(searchTerm)) return false;

            return true;
        });

        renderImages();
    }

    function toggleSelectAll() {
        const isChecked = $('#select-all').is(':checked');
        $('.image-select').prop('checked', isChecked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selectedCount = $('.image-select:checked').length;
        // You can update a counter here if needed
    }

    function getSelectedImages() {
        const selectedIds = $('.image-select:checked').map(function() {
            return parseInt($(this).closest('.wpwebp-image-item').data('id'));
        }).get();

        return allImages.filter(img => selectedIds.includes(img.id));
    }

    function updateProgress(percentage, text) {
        $('#progress-fill').css('width', percentage + '%');
        $('#progress-percentage').text(Math.round(percentage) + '%');
        $('#progress-text').text(text);
    }

    function updateQualityDisplay() {
        const quality = $('#webp-quality').val();
        $('#quality-value').text(quality);
    }

    function showLoading() {
        $('#loading-state').show();
    }

    function hideLoading() {
        $('#loading-state').hide();
    }

    function hideAllSections() {
        $('#images-container, #image-filters, #empty-state').hide();
    }

    function showImagesSections() {
        if (allImages.length > 0) {
            $('#images-container, #image-filters').show();
        } else {
            $('#empty-state').show();
        }
    }

    function showSuccessMessage(message) {
        showMessage(message, 'success');
    }

    function showErrorMessage(message) {
        showMessage(message, 'error');
    }

    function showWarningMessage(message) {
        showMessage(message, 'warning');
    }

    function showMessage(message, type = 'success') {
        const $message = $(`
            <div class="wpwebp-message ${type}">
                ${message}
            </div>
        `);

        $('#wpwebp-messages').append($message);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);

        // Allow manual dismissal
        $message.on('click', function() {
            $(this).fadeOut(300, function() {
                $(this).remove();
            });
        });
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + A to select all
        if ((e.ctrlKey || e.metaKey) && e.key === 'a' && !$(e.target).is('input, textarea')) {
            e.preventDefault();
            $('#select-all').trigger('click');
        }
        
        // Escape to cancel conversion (future feature)
        if (e.key === 'Escape' && isConverting) {
            // Could implement cancellation logic here
        }
    });

    // Prevent accidental page refresh during conversion
    $(window).on('beforeunload', function(e) {
        if (isConverting) {
            const message = 'Image conversion is in progress. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }
    });

    // Handle visibility change (tab switching)
    $(document).on('visibilitychange', function() {
        if (document.hidden && isConverting) {
            console.log('Page hidden during conversion - conversion continues in background');
        }
    });
});