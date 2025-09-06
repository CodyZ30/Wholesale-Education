# 🚀 Google Cloud VM Upload Guide - Clean Deployment

## Overview
This guide shows you how to upload your Wholesale Education site to Google Cloud VM without all the redundant static pages and development files.

## Method 1: Automated Clean Upload (Recommended)

### Step 1: Configure the Script
1. Edit `clean-deploy.sh` and replace:
   ```bash
   VM_IP="YOUR_VM_IP_HERE"     # Your VM's external IP
   VM_USER="YOUR_USERNAME"     # Your VM username (usually your GCP username)
   REMOTE_PATH="/var/www/html" # Your web root directory
   ```

### Step 2: Run the Clean Deployment
```bash
./clean-deploy.sh
```

This script will:
- ✅ Upload only essential files
- ✅ Exclude redundant static HTML pages
- ✅ Exclude development files (node_modules, temp-clone, etc.)
- ✅ Set proper permissions
- ✅ Test SSH connection first

## Method 2: Manual Upload via Google Cloud Console


### Step 1: Access Your VM
1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Navigate to **Compute Engine** → **VM instances**
3. Click **SSH** next to your instance

### Step 2: Prepare Files Locally
Create a clean folder with only these essential files:

#### Core Files:
- `index.php` (or `index.html` if no PHP)
- `404.html`
- `robots.txt`
- `sitemap.xml`

#### Essential Directories:
- `includes/` - Core PHP includes
- `admin/` - Admin panel
- `api/` - API endpoints
- `support/` - Support system
- `config/` - Configuration files
- `data/` - Data files
- `images/` - Images
- `uploads/` - User uploads

#### Assets:
- `styles.css`
- `script.js`
- `functions.js`
- Any other `.js` files

#### Database:
- `*.db` or `*.sqlite` files
- `composer.json` (if using Composer)

### Step 3: Upload via SSH
1. In the SSH window, click the **upload icon** (arrow pointing up)
2. Select your clean folder
3. Upload to `/var/www/html/` (or your web root)

### Step 4: Set Permissions
In the SSH terminal, run:
```bash
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
```

## What Gets Excluded (The "Bullshit" Files)

### ❌ Redundant Static Pages:
- `amazon-fba-guide.html`
- `ebay-selling-guide.html`
- `walmart-marketplace-guide.html`
- `blog-article-1.html`
- `testimonials.html`
- `pricing.html`
- `education-programs.html`
- `marketplace.html`
- `suppliers.html`
- `about.html`
- `contact.html`
- `blog.html`
- `privacy-policy.html`
- `terms-of-service.html`
- `services.html`
- `guides.html`
- `profit-calculator.html`
- `ecommerce-giants.html`

### ❌ Development Files:
- `node_modules/`
- `temp-clone/`
- `static-site/`
- `frontend-kit/`
- `admin-theme/`
- `previews/`
- `deployment/`

### ❌ Test Files:
- `test_*.php`
- `test_*.html`

### ❌ Archive Files:
- `*.zip`
- `*.tar.gz`

### ❌ Development Scripts:
- `auto-deploy.sh`
- `quick-deploy.sh`
- `deploy.sh`
- `setup-*.sh`
- `start-*.sh`
- `7-hour-auto-system.sh`
- `generate-*.js`

## What Gets Included (Essential Files)

### ✅ Core Application:
- `index.php` - Main entry point
- `*.php` - All PHP application files
- `includes/` - Core includes and functions
- `config/` - Configuration files

### ✅ Admin System:
- `admin/` - Complete admin panel
- `api/` - API endpoints
- `support/` - Support system

### ✅ Data & Content:
- `data/` - JSON data files
- `images/` - All images
- `uploads/` - User uploads
- `*.db` / `*.sqlite` - Database files

### ✅ Assets:
- `styles.css` - Main stylesheet
- `*.js` - JavaScript files
- `robots.txt` - SEO
- `sitemap.xml` - SEO

## File Size Comparison

| Method | Size | Files |
|--------|------|-------|
| Full Upload | ~500MB+ | 1000+ files |
| Clean Upload | ~50MB | ~100 files |

## Troubleshooting

### SSH Connection Issues:
```bash
# Test connection
ssh your-username@your-vm-ip

# Check if SSH is enabled
gcloud compute instances describe YOUR_INSTANCE_NAME --zone=YOUR_ZONE
```

### Permission Issues:
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/html/

# Fix permissions
sudo chmod -R 755 /var/www/html/
sudo chmod 644 /var/www/html/*.php
```

### Web Server Issues:
```bash
# Check Apache/Nginx status
sudo systemctl status apache2
# or
sudo systemctl status nginx

# Restart web server
sudo systemctl restart apache2
# or
sudo systemctl restart nginx
```

## Success Indicators

✅ **Site loads at your VM IP**  
✅ **Admin panel accessible**  
✅ **Images display correctly**  
✅ **Database connections work**  
✅ **No 404 errors on main pages**  

## Next Steps After Upload

1. **Configure Domain**: Point your domain to the VM IP
2. **SSL Certificate**: Set up HTTPS with Let's Encrypt
3. **Database Setup**: Ensure database is properly configured
4. **Email Configuration**: Set up email sending
5. **Backup Strategy**: Set up automated backups

---

**Your clean, professional wholesale education site is now live! 🎉**
