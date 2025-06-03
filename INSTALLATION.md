# 🚀 Quick Installation Guide

## Method 1: ZIP Installation (Recommended)

1. **Create ZIP file**:
   - Select all plugin files: `wp-webp-image-converter.php`, `assets/`, `includes/`, `README.md`
   - Create a ZIP archive named `wp-webp-image-converter.zip`

2. **Upload to WordPress**:
   - Go to your WordPress admin panel
   - Navigate to **Plugins** → **Add New** → **Upload Plugin**
   - Choose the ZIP file and click **Install Now**
   - Click **Activate Plugin**

3. **Access the plugin**:
   - Go to **Tools** → **WebP Converter**
   - Start converting your images!

## Method 2: Manual Installation

1. **Upload files**:
   - Upload the entire plugin folder to `/wp-content/plugins/wp-webp-image-converter/`
   - Ensure all files maintain their directory structure

2. **Set permissions**:

   ```bash
   chmod 755 wp-content/plugins/wp-webp-image-converter/
   chmod 644 wp-content/plugins/wp-webp-image-converter/*.php
   chmod -R 644 wp-content/plugins/wp-webp-image-converter/assets/
   chmod -R 644 wp-content/plugins/wp-webp-image-converter/includes/
   ```

3. **Activate**:
   - Go to **Plugins** in WordPress admin
   - Find "WP WebP Image Converter" and click **Activate**

## Requirements Check

Before installation, ensure your server has:

- ✅ WordPress 5.0+
- ✅ PHP 7.4+
- ✅ GD Library with WebP support
- ✅ Memory limit: 128MB+ (256MB recommended)

## Quick Test

1. Go to **Tools** → **WebP Converter**
2. Click **"Scan All Images"**
3. Select an image and click **"Convert"**
4. Check if conversion was successful!

## Need Help?

- Check the main [README.md](README.md) for detailed documentation
- Review the troubleshooting section for common issues
- Ensure your server meets all requirements

---

**That's it! Your website images will now load faster with WebP format! 🎉**
