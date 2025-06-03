<div class="wrap wpwebp-admin">
    <div class="wpwebp-header">
        <h1 class="wpwebp-title">
            <span class="wpwebp-icon">🚀</span>
            <?php _e('WebP Image Converter', 'wp-webp-converter'); ?>
        </h1>
        <p class="wpwebp-subtitle"><?php _e('Convert your images to WebP format for faster loading times and better SEO', 'wp-webp-converter'); ?></p>
    </div>

    <div class="wpwebp-dashboard">
        <!-- Stats Dashboard -->
        <div class="wpwebp-stats-grid">
            <div class="wpwebp-stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3 id="total-images">-</h3>
                    <p><?php _e('Total Images', 'wp-webp-converter'); ?></p>
                </div>
            </div>
            <div class="wpwebp-stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3 id="converted-images">-</h3>
                    <p><?php _e('Converted', 'wp-webp-converter'); ?></p>
                </div>
            </div>
            <div class="wpwebp-stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <h3 id="pending-images">-</h3>
                    <p><?php _e('Pending', 'wp-webp-converter'); ?></p>
                </div>
            </div>
            <div class="wpwebp-stat-card">
                <div class="stat-icon">💾</div>
                <div class="stat-content">
                    <h3 id="total-savings">-</h3>
                    <p><?php _e('Space Saved', 'wp-webp-converter'); ?></p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="wpwebp-actions">
            <button id="scan-images" class="wpwebp-btn wpwebp-btn-primary">
                <span class="btn-icon">🔍</span>
                <?php _e('Scan All Images', 'wp-webp-converter'); ?>
            </button>
            <button id="convert-all" class="wpwebp-btn wpwebp-btn-success" style="display: none;">
                <span class="btn-icon">⚡</span>
                <?php _e('Convert All Images', 'wp-webp-converter'); ?>
            </button>
            <button id="convert-pending" class="wpwebp-btn wpwebp-btn-warning" style="display: none;">
                <span class="btn-icon">📦</span>
                <?php _e('Convert Pending Only', 'wp-webp-converter'); ?>
            </button>
        </div>

        <!-- Progress Bar -->
        <div id="conversion-progress" class="wpwebp-progress-container" style="display: none;">
            <div class="wpwebp-progress-header">
                <span id="progress-text"><?php _e('Processing...', 'wp-webp-converter'); ?></span>
                <span id="progress-percentage">0%</span>
            </div>
            <div class="wpwebp-progress-bar">
                <div id="progress-fill" class="wpwebp-progress-fill"></div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div id="image-filters" class="wpwebp-filters" style="display: none;">
            <div class="filter-group">
                <label for="filter-status"><?php _e('Filter by Status:', 'wp-webp-converter'); ?></label>
                <select id="filter-status">
                    <option value="all"><?php _e('All Images', 'wp-webp-converter'); ?></option>
                    <option value="converted"><?php _e('Converted', 'wp-webp-converter'); ?></option>
                    <option value="pending"><?php _e('Pending', 'wp-webp-converter'); ?></option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-type"><?php _e('Filter by Type:', 'wp-webp-converter'); ?></label>
                <select id="filter-type">
                    <option value="all"><?php _e('All Types', 'wp-webp-converter'); ?></option>
                    <option value="image/jpeg"><?php _e('JPEG', 'wp-webp-converter'); ?></option>
                    <option value="image/png"><?php _e('PNG', 'wp-webp-converter'); ?></option>
                </select>
            </div>
            <div class="filter-group">
                <input type="text" id="search-images" placeholder="<?php _e('Search images...', 'wp-webp-converter'); ?>">
            </div>
        </div>

        <!-- Images List -->
        <div id="images-container" class="wpwebp-images-container" style="display: none;">
            <div class="wpwebp-table-header">
                <h3><?php _e('Your Images', 'wp-webp-converter'); ?></h3>
                <div class="table-actions">
                    <label class="wpwebp-checkbox">
                        <input type="checkbox" id="select-all">
                        <span class="checkmark"></span>
                        <?php _e('Select All', 'wp-webp-converter'); ?>
                    </label>
                </div>
            </div>
            
            <div class="wpwebp-images-grid" id="images-grid">
                <!-- Images will be populated here via JavaScript -->
            </div>
        </div>

        <!-- Loading States -->
        <div id="loading-state" class="wpwebp-loading" style="display: none;">
            <div class="loading-spinner"></div>
            <p><?php _e('Scanning your images...', 'wp-webp-converter'); ?></p>
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="wpwebp-empty-state" style="display: none;">
            <div class="empty-icon">📷</div>
            <h3><?php _e('No Images Found', 'wp-webp-converter'); ?></h3>
            <p><?php _e('No JPEG or PNG images were found in your media library.', 'wp-webp-converter'); ?></p>
        </div>
    </div>

    <!-- Settings Panel -->
    <div class="wpwebp-settings-panel">
        <h3><?php _e('Conversion Settings', 'wp-webp-converter'); ?></h3>
        <div class="settings-grid">
            <div class="setting-item">
                <label for="webp-quality"><?php _e('WebP Quality (1-100):', 'wp-webp-converter'); ?></label>
                <input type="range" id="webp-quality" min="1" max="100" value="80">
                <span id="quality-value">80</span>
            </div>
            <div class="setting-item">
                <label class="wpwebp-checkbox">
                    <input type="checkbox" id="create-backup" checked>
                    <span class="checkmark"></span>
                    <?php _e('Create backup of original images', 'wp-webp-converter'); ?>
                </label>
            </div>
            <div class="setting-item">
                <label class="wpwebp-checkbox">
                    <input type="checkbox" id="preserve-metadata">
                    <span class="checkmark"></span>
                    <?php _e('Preserve image metadata', 'wp-webp-converter'); ?>
                </label>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="wpwebp-messages" class="wpwebp-messages">
        <!-- Messages will be inserted here -->
    </div>
</div>

<!-- Image Item Template (hidden) -->
<template id="image-item-template">
    <div class="wpwebp-image-item" data-id="">
        <div class="image-checkbox">
            <label class="wpwebp-checkbox">
                <input type="checkbox" class="image-select">
                <span class="checkmark"></span>
            </label>
        </div>
        <div class="image-preview">
            <img src="" alt="" loading="lazy">
            <div class="image-overlay">
                <span class="image-type"></span>
            </div>
        </div>
        <div class="image-details">
            <h4 class="image-title"></h4>
            <div class="image-meta">
                <span class="image-size"></span>
                <span class="image-dimensions"></span>
            </div>
            <div class="conversion-info" style="display: none;">
                <div class="savings-info">
                    <span class="savings-amount"></span>
                    <span class="savings-percent"></span>
                </div>
            </div>
        </div>
        <div class="image-actions">
            <button class="wpwebp-btn wpwebp-btn-small wpwebp-btn-primary convert-btn">
                <span class="btn-icon">⚡</span>
                <?php _e('Convert', 'wp-webp-converter'); ?>
            </button>
            <div class="conversion-status" style="display: none;">
                <span class="status-icon">✅</span>
                <span class="status-text"><?php _e('Converted!', 'wp-webp-converter'); ?></span>
            </div>
            <div class="conversion-loading" style="display: none;">
                <div class="mini-spinner"></div>
                <span><?php _e('Converting...', 'wp-webp-converter'); ?></span>
            </div>
        </div>
    </div>
</template> 