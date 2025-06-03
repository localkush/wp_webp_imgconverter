# WP WebP Image Converter

A powerful and beautiful WordPress plugin that converts your website images to WebP format for faster loading times and better SEO performance. Perfect for WordPress + Elementor Pro websites.

## 🚀 Features

- **🔍 Smart Image Scanning**: Automatically finds all JPEG and PNG images in your WordPress media library
- **⚡ One-Click Conversion**: Convert images individually or in bulk with a single click
- **📊 Real-Time Statistics**: See conversion progress, file size savings, and performance metrics
- **🎨 Beautiful UI/UX**: Modern, responsive interface with smooth animations and intuitive design
- **💾 Automatic Backups**: Safely backs up original images before conversion
- **🔧 Customizable Quality**: Adjust WebP quality settings (1-100)
- **📱 Mobile Responsive**: Works perfectly on all devices
- **🔄 Progress Tracking**: Real-time progress bars for bulk conversions
- **🎛️ Advanced Filtering**: Filter by conversion status, image type, or search by name
- **✅ Safety First**: Built-in security checks and nonce verification

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- GD Library with WebP support
- At least 128MB PHP memory limit (recommended: 256MB)

## 🛠️ Installation

1. **Download the Plugin**
   - Download all files from this repository
   - Create a ZIP file containing all plugin files

2. **Upload to WordPress**
   - Go to your WordPress admin panel
   - Navigate to `Plugins` → `Add New` → `Upload Plugin`
   - Select the ZIP file and click `Install Now`
   - Activate the plugin

3. **Alternative: Manual Installation**
   - Upload the plugin folder to `/wp-content/plugins/`
   - Activate the plugin through the WordPress admin panel

## 🎯 How to Use

### Step 1: Access the Plugin

- Go to `Tools` → `WebP Converter` in your WordPress admin panel

### Step 2: Scan Your Images

- Click the **"Scan All Images"** button to find all convertible images
- The plugin will display statistics and a grid of all your images

### Step 3: Convert Images

- **Single Conversion**: Click the "Convert" button on any individual image
- **Bulk Conversion**: Use "Convert All Images" for all unconverted images
- **Selective Conversion**: Select specific images and use "Convert Pending Only"

### Step 4: Monitor Progress

- Watch the real-time progress bar during bulk conversions
- See immediate file size savings and percentage reductions
- View updated statistics in the dashboard

## 🎨 Interface Overview

### Dashboard

- **Total Images**: Count of all scannable images
- **Converted**: Number of successfully converted images
- **Pending**: Images still waiting for conversion
- **Space Saved**: Total file size reduction achieved

### Image Grid

- **Visual Thumbnails**: Preview of each image
- **Conversion Status**: Clear indicators for converted images
- **File Information**: Size, type, and savings data
- **Individual Actions**: Convert button for each image

### Settings Panel

- **WebP Quality**: Adjustable quality slider (1-100)
- **Backup Options**: Toggle for creating backups
- **Metadata Preservation**: Option to preserve image metadata

## ⚙️ Technical Details

### Image Processing

- Supports JPEG and PNG source formats
- Creates high-quality WebP images
- Preserves transparency in PNG images
- Maintains aspect ratios and dimensions

### File Management

- Original images are backed up to `/wp-uploads/webp-backups/`
- WebP files replace original images seamlessly
- Database records track all conversions
- WordPress attachment metadata is updated automatically

### Performance

- Efficient batch processing with queuing system
- Server-friendly conversion delays prevent timeouts
- Memory-optimized image handling
- AJAX-powered interface for smooth user experience

## 🛡️ Security Features

- **Nonce Verification**: All AJAX requests are secured
- **Capability Checks**: Only administrators can use the plugin
- **Input Sanitization**: All user inputs are properly sanitized
- **File Validation**: Strict image type checking

## 🔧 Customization

### Quality Settings

Adjust the WebP quality using the slider in the settings panel:

- **1-50**: High compression, smaller files, lower quality
- **51-80**: Balanced compression and quality (recommended)
- **81-100**: Low compression, larger files, high quality

### Advanced Configuration

For developers, you can customize the plugin by modifying:

- `$quality` variable in the conversion function
- Backup directory location
- Conversion batch size
- Progress update intervals

## 🐛 Troubleshooting

### Common Issues

**"WebP support is not available"**

- Ensure your server has GD library with WebP support
- Contact your hosting provider to enable WebP support

**Memory limit errors**

- Increase PHP memory limit in wp-config.php: `ini_set('memory_limit', '256M');`
- Process images in smaller batches

**Conversion timeouts**

- Increase PHP max execution time
- Use individual image conversion instead of bulk

**Missing images after conversion**

- Check the backup directory: `/wp-uploads/webp-backups/`
- Ensure proper file permissions (755 for directories, 644 for files)

### Server Requirements Check

```php
// Check WebP support
if (function_exists('imagewebp')) {
    echo "✅ WebP support available";
} else {
    echo "❌ WebP support not available";
}

// Check memory limit
echo "Memory limit: " . ini_get('memory_limit');
```

## 📈 Performance Benefits

### File Size Reduction

- **JPEG images**: Typically 25-35% smaller
- **PNG images**: Typically 45-65% smaller
- **Overall**: Average 30-50% file size reduction

### Loading Speed Improvements

- Faster page load times
- Reduced bandwidth usage
- Better user experience
- Improved SEO rankings

## 🔄 Updates & Maintenance

### Automatic Updates

The plugin includes an update mechanism that will notify you of new versions.

### Manual Updates

1. Backup your website
2. Download the latest version
3. Replace plugin files
4. Test functionality

### Database Cleanup

The plugin creates a `wp_webp_conversions` table to track conversions. This table is automatically created on activation and can be safely removed if you uninstall the plugin.

## 🤝 Support

### Getting Help

- Check this README for common solutions
- Review the troubleshooting section
- Contact support with detailed error messages

### Feature Requests

We welcome suggestions for new features! Please provide:

- Detailed description of the requested feature
- Use case scenarios
- Priority level

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 🙏 Credits

Built with ❤️ for the WordPress community.

- **Modern UI**: Inspired by contemporary web design trends
- **Performance**: Optimized for efficiency and user experience
- **Compatibility**: Tested with WordPress core and popular themes

---

**Ready to make your website faster? Install WP WebP Image Converter today!** 🚀
