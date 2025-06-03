<?php
/**
 * Plugin Name: WP WebP Image Converter
 * Plugin URI: https://creativewebconcept.ca
 * Description: Convert all website images to WebP format with a beautiful, easy-to-use interface. Perfect for WordPress + Elementor Pro sites.
 * Version: 1.0.0
 * Author: Charles Wilkin (localkush@github)
 * License: GPL v2 or later
 * Text Domain: wp-webp-converter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPWEBP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPWEBP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WPWEBP_VERSION', '1.0.0');

// Main plugin class
class WP_WebP_Image_Converter {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_scan_images', array($this, 'ajax_scan_images'));
        add_action('wp_ajax_convert_image', array($this, 'ajax_convert_image'));
        add_action('wp_ajax_bulk_convert', array($this, 'ajax_bulk_convert'));
        
        // Create database table on activation
        register_activation_hook(__FILE__, array($this, 'create_conversion_table'));
    }
    
    public function init() {
        // Plugin initialization
        load_plugin_textdomain('wp-webp-converter', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function add_admin_menu() {
        add_management_page(
            __('WebP Image Converter', 'wp-webp-converter'),
            __('WebP Converter', 'wp-webp-converter'),
            'manage_options',
            'wp-webp-converter',
            array($this, 'admin_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'tools_page_wp-webp-converter') {
            return;
        }
        
        wp_enqueue_script('wp-webp-converter-js', WPWEBP_PLUGIN_URL . 'assets/admin.js', array('jquery'), WPWEBP_VERSION, true);
        wp_enqueue_style('wp-webp-converter-css', WPWEBP_PLUGIN_URL . 'assets/admin.css', array(), WPWEBP_VERSION);
        
        // Localize script for AJAX
        wp_localize_script('wp-webp-converter-js', 'wpwebp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpwebp_nonce'),
            'strings' => array(
                'converting' => __('Converting...', 'wp-webp-converter'),
                'converted' => __('Converted!', 'wp-webp-converter'),
                'error' => __('Error occurred', 'wp-webp-converter'),
                'scanning' => __('Scanning images...', 'wp-webp-converter'),
                'scan_complete' => __('Scan complete!', 'wp-webp-converter'),
            )
        ));
    }
    
    public function admin_page() {
        include WPWEBP_PLUGIN_PATH . 'includes/admin-page.php';
    }
    
    public function create_conversion_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'webp_conversions';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            attachment_id bigint(20) NOT NULL,
            original_path varchar(500) NOT NULL,
            webp_path varchar(500) NOT NULL,
            original_size bigint(20) NOT NULL,
            webp_size bigint(20) NOT NULL,
            conversion_date datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'converted',
            PRIMARY KEY (id),
            UNIQUE KEY attachment_id (attachment_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function ajax_scan_images() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpwebp_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $images = $this->scan_all_images();
        
        wp_send_json_success($images);
    }
    
    public function ajax_convert_image() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpwebp_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        $result = $this->convert_to_webp($attachment_id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_bulk_convert() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wpwebp_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $attachment_ids = array_map('intval', $_POST['attachment_ids']);
        $results = array();
        
        foreach ($attachment_ids as $attachment_id) {
            $results[$attachment_id] = $this->convert_to_webp($attachment_id);
        }
        
        wp_send_json_success($results);
    }
    
    private function scan_all_images() {
        global $wpdb;
        
        // Get all image attachments
        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => array('image/jpeg', 'image/jpg', 'image/png'),
            'posts_per_page' => -1,
            'post_status' => 'inherit'
        ));
        
        $images = array();
        $conversion_table = $wpdb->prefix . 'webp_conversions';
        
        foreach ($attachments as $attachment) {
            $file_path = get_attached_file($attachment->ID);
            $file_url = wp_get_attachment_url($attachment->ID);
            $file_size = filesize($file_path);
            
            // Check if already converted
            $converted = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $conversion_table WHERE attachment_id = %d",
                $attachment->ID
            ));
            
            $images[] = array(
                'id' => $attachment->ID,
                'title' => $attachment->post_title,
                'url' => $file_url,
                'path' => $file_path,
                'size' => $file_size,
                'size_human' => size_format($file_size),
                'mime_type' => $attachment->post_mime_type,
                'converted' => $converted ? true : false,
                'webp_size' => $converted ? $converted->webp_size : 0,
                'savings' => $converted ? $file_size - $converted->webp_size : 0
            );
        }
        
        return $images;
    }
    
    private function convert_to_webp($attachment_id) {
        global $wpdb;
        
        $file_path = get_attached_file($attachment_id);
        $file_info = pathinfo($file_path);
        $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
        
        // Check if WebP support is available
        if (!function_exists('imagewebp')) {
            return array(
                'success' => false,
                'message' => __('WebP support is not available on this server.', 'wp-webp-converter')
            );
        }
        
        // Create WebP image
        $original_size = filesize($file_path);
        $mime_type = get_post_mime_type($attachment_id);
        
        switch ($mime_type) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file_path);
                // Preserve transparency
                imagealphablending($image, false);
                imagesavealpha($image, true);
                break;
            default:
                return array(
                    'success' => false,
                    'message' => __('Unsupported image format.', 'wp-webp-converter')
                );
        }
        
        if (!$image) {
            return array(
                'success' => false,
                'message' => __('Failed to create image resource.', 'wp-webp-converter')
            );
        }
        
        // Convert to WebP
        $quality = 80; // You can make this configurable
        $webp_created = imagewebp($image, $webp_path, $quality);
        imagedestroy($image);
        
        if (!$webp_created) {
            return array(
                'success' => false,
                'message' => __('Failed to create WebP image.', 'wp-webp-converter')
            );
        }
        
        $webp_size = filesize($webp_path);
        
        // Update attachment to use WebP
        $this->update_attachment_to_webp($attachment_id, $file_path, $webp_path);
        
        // Save conversion record
        $conversion_table = $wpdb->prefix . 'webp_conversions';
        $wpdb->replace($conversion_table, array(
            'attachment_id' => $attachment_id,
            'original_path' => $file_path,
            'webp_path' => $webp_path,
            'original_size' => $original_size,
            'webp_size' => $webp_size,
            'conversion_date' => current_time('mysql'),
            'status' => 'converted'
        ));
        
        return array(
            'success' => true,
            'message' => __('Image converted successfully!', 'wp-webp-converter'),
            'original_size' => $original_size,
            'webp_size' => $webp_size,
            'savings' => $original_size - $webp_size,
            'savings_percent' => round((($original_size - $webp_size) / $original_size) * 100, 2)
        );
    }
    
    private function update_attachment_to_webp($attachment_id, $original_path, $webp_path) {
        // Create backup of original file
        $backup_dir = wp_upload_dir()['basedir'] . '/webp-backups/';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        $backup_path = $backup_dir . basename($original_path);
        copy($original_path, $backup_path);
        
        // Replace original file with WebP
        unlink($original_path);
        copy($webp_path, $original_path);
        
        // Update attachment metadata
        $metadata = wp_get_attachment_metadata($attachment_id);
        if ($metadata) {
            $metadata['file'] = str_replace(pathinfo($metadata['file'], PATHINFO_EXTENSION), 'webp', $metadata['file']);
            wp_update_attachment_metadata($attachment_id, $metadata);
        }
        
        // Update post mime type
        wp_update_post(array(
            'ID' => $attachment_id,
            'post_mime_type' => 'image/webp'
        ));
    }
}

// Initialize the plugin
new WP_WebP_Image_Converter(); 