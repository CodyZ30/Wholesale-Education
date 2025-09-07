# 🚀 RunCloud.io Deployment Guide - Dev V0.1

> **Complete guide to deploy your Wholesale Education Platform to RunCloud.io on Google Cloud VM**

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ RunCloud.io account
- ✅ Google Cloud VM instance connected to RunCloud
- ✅ SSH access to your VM
- ✅ Your GitHub repository: `https://github.com/CodyZ30/Wholesale-Education.git`

## 🛠️ Step 1: RunCloud.io Setup

### 1.1 Connect Your Google Cloud VM to RunCloud
1. Go to [RunCloud.io](https://runcloud.io)
2. Add your Google Cloud VM instance
3. Install RunCloud agent on your VM
4. Verify connection in RunCloud dashboard

### 1.2 Create Web Application
1. In RunCloud dashboard, go to **Web Applications**
2. Click **Add Web Application**
3. Choose **PHP** as application type
4. Set domain: `your-domain.com` (or use RunCloud subdomain)
5. Select your Google Cloud VM
6. Choose **PHP 8.2** (recommended)

## 🔧 Step 2: Configure RunCloud Application

### 2.1 Application Settings
- **Application Name**: `wholesale-education`
- **Domain**: Your domain or RunCloud subdomain
- **PHP Version**: 8.2
- **Document Root**: `/home/runcloud/webapps/wholesale-education/public`
- **Web Directory**: `/home/runcloud/webapps/wholesale-education`

### 2.2 Enable Required Extensions
In RunCloud dashboard, go to **PHP Settings** and enable:
- ✅ PDO
- ✅ PDO SQLite
- ✅ JSON
- ✅ MBString
- ✅ cURL
- ✅ OpenSSL
- ✅ FileInfo
- ✅ GD
- ✅ ZIP

## 📦 Step 3: Deploy from GitHub

### 3.1 Method A: Direct Git Clone (Recommended)

SSH into your Google Cloud VM:
```bash
# SSH to your VM
ssh your-username@your-vm-ip

# Navigate to webapps directory
cd /home/runcloud/webapps/wholesale-education

# Clone your repository
git clone https://github.com/CodyZ30/Wholesale-Education.git .

# Set proper permissions
sudo chown -R runcloud:runcloud /home/runcloud/webapps/wholesale-education/
sudo chmod -R 755 /home/runcloud/webapps/wholesale-education/
```

### 3.2 Method B: RunCloud File Manager
1. Go to RunCloud dashboard → **File Manager**
2. Navigate to `/home/runcloud/webapps/wholesale-education/`
3. Upload your files or use Git integration

## ⚙️ Step 4: Configure Application

### 4.1 Set Up Environment Variables
Create `.env` file in your application root:
```bash
# SSH to your VM
ssh your-username@your-vm-ip

# Navigate to application directory
cd /home/runcloud/webapps/wholesale-education

# Create environment file
nano .env
```

Add these environment variables:
```env
# Application Settings
APP_NAME="Wholesale Education Platform"
APP_VERSION="0.1.0"
APP_ENV="production"
APP_DEBUG=false
APP_URL="https://your-domain.com"

# Database Configuration
DB_CONNECTION="sqlite"
DB_PATH="/home/runcloud/webapps/wholesale-education/wholesale_education.db"

# Security
APP_KEY="your-32-character-secret-key-here"
JWT_SECRET="your-jwt-secret-key-here"
SESSION_LIFETIME=86400

# Email Configuration
MAIL_MAILER="smtp"
MAIL_HOST="smtp.gmail.com"
MAIL_PORT=587
MAIL_USERNAME="your-email@gmail.com"
MAIL_PASSWORD="your-app-password"
MAIL_ENCRYPTION="tls"
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="Wholesale Education"

# File Upload
UPLOAD_MAX_SIZE=10485760
UPLOAD_PATH="/home/runcloud/webapps/wholesale-education/uploads"

# API Settings
API_RATE_LIMIT=100
API_RATE_WINDOW=60
```

### 4.2 Set Proper Permissions
```bash
# Set ownership
sudo chown -R runcloud:runcloud /home/runcloud/webapps/wholesale-education/

# Set directory permissions
sudo find /home/runcloud/webapps/wholesale-education -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /home/runcloud/webapps/wholesale-education -type f -exec chmod 644 {} \;

# Set executable permissions for PHP files
sudo chmod +x /home/runcloud/webapps/wholesale-education/*.php

# Set writable permissions for uploads and database
sudo chmod 666 /home/runcloud/webapps/wholesale-education/wholesale_education.db
sudo chmod -R 777 /home/runcloud/webapps/wholesale-education/uploads/
```

## 🗄️ Step 5: Database Setup

### 5.1 Initialize SQLite Database
```bash
# SSH to your VM
ssh your-username@your-vm-ip

# Navigate to application directory
cd /home/runcloud/webapps/wholesale-education

# Create database file
touch wholesale_education.db

# Set permissions
sudo chown runcloud:runcloud wholesale_education.db
sudo chmod 666 wholesale_education.db

# Initialize database (if you have setup script)
php setup-database.js
```

## 🌐 Step 6: Configure Web Server

### 6.1 RunCloud Nginx Configuration
In RunCloud dashboard, go to **Nginx Settings**:

```nginx
# Add to server block
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
```

### 6.2 PHP-FPM Configuration
In RunCloud dashboard, go to **PHP-FPM Settings**:
- **Max Execution Time**: 300
- **Memory Limit**: 512M
- **Upload Max Filesize**: 10M
- **Post Max Size**: 10M
- **Max Input Vars**: 3000

## 🔒 Step 7: SSL Certificate

### 7.1 Enable SSL in RunCloud
1. Go to RunCloud dashboard → **SSL**
2. Click **Let's Encrypt**
3. Enter your domain name
4. Click **Install Certificate**
5. Enable **Force HTTPS**

## 🚀 Step 8: Deploy and Test

### 8.1 Final Deployment Steps
```bash
# SSH to your VM
ssh your-username@your-vm-ip

# Navigate to application directory
cd /home/runcloud/webapps/wholesale-education

# Pull latest changes
git pull origin main

# Install dependencies (if needed)
composer install --no-dev --optimize-autoloader

# Clear any caches
php artisan cache:clear 2>/dev/null || true

# Set final permissions
sudo chown -R runcloud:runcloud /home/runcloud/webapps/wholesale-education/
sudo chmod -R 755 /home/runcloud/webapps/wholesale-education/
sudo chmod 666 wholesale_education.db
sudo chmod -R 777 uploads/
```

### 8.2 Test Your Deployment
1. **Main Site**: `https://your-domain.com`
2. **Admin Panel**: `https://your-domain.com/admin`
3. **API Endpoints**: `https://your-domain.com/api/*`

## 🔄 Step 9: Set Up Auto-Deployment

### 9.1 Create Deployment Script
Create `deploy.sh` in your application root:
```bash
#!/bin/bash
# Deployment script for RunCloud.io

echo "🚀 Starting deployment..."

# Navigate to application directory
cd /home/runcloud/webapps/wholesale-education

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Set permissions
echo "🔒 Setting permissions..."
sudo chown -R runcloud:runcloud /home/runcloud/webapps/wholesale-education/
sudo chmod -R 755 /home/runcloud/webapps/wholesale-education/
sudo chmod 666 wholesale_education.db
sudo chmod -R 777 uploads/

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear 2>/dev/null || true

echo "✅ Deployment complete!"
```

Make it executable:
```bash
chmod +x deploy.sh
```

### 9.2 GitHub Actions for Auto-Deployment
Create `.github/workflows/deploy-runcloud.yml`:
```yaml
name: Deploy to RunCloud.io

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Deploy to RunCloud
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.RUNCLOUD_HOST }}
        username: ${{ secrets.RUNCLOUD_USER }}
        key: ${{ secrets.RUNCLOUD_SSH_KEY }}
        script: |
          cd /home/runcloud/webapps/wholesale-education
          ./deploy.sh
```

## 📊 Step 10: Monitoring and Maintenance

### 10.1 Set Up Monitoring
1. **RunCloud Dashboard**: Monitor server resources
2. **Application Logs**: Check `/home/runcloud/webapps/wholesale-education/error_log`
3. **Server Logs**: Check RunCloud dashboard → Logs

### 10.2 Regular Maintenance
```bash
# Weekly maintenance script
#!/bin/bash
cd /home/runcloud/webapps/wholesale-education

# Update application
git pull origin main

# Clean up old logs
find . -name "*.log" -mtime +7 -delete

# Optimize database
sqlite3 wholesale_education.db "VACUUM;"

# Set permissions
sudo chown -R runcloud:runcloud /home/runcloud/webapps/wholesale-education/
```

## 🚨 Troubleshooting

### Common Issues:

1. **Permission Errors**:
   ```bash
   sudo chown -R runcloud:runcloud /home/runcloud/webapps/wholesale-education/
   sudo chmod -R 755 /home/runcloud/webapps/wholesale-education/
   ```

2. **Database Issues**:
   ```bash
   sudo chmod 666 wholesale_education.db
   ```

3. **Upload Issues**:
   ```bash
   sudo chmod -R 777 uploads/
   ```

4. **PHP Errors**:
   - Check RunCloud dashboard → PHP Settings
   - Verify all required extensions are enabled
   - Check error logs in RunCloud dashboard

## 🎉 Success Checklist

- ✅ RunCloud.io account set up
- ✅ Google Cloud VM connected
- ✅ Web application created
- ✅ GitHub repository cloned
- ✅ Environment variables configured
- ✅ Database initialized
- ✅ Permissions set correctly
- ✅ SSL certificate installed
- ✅ Application deployed and tested
- ✅ Auto-deployment configured
- ✅ Monitoring set up

## 📞 Support

If you encounter issues:
1. Check RunCloud dashboard logs
2. Verify file permissions
3. Check PHP error logs
4. Contact RunCloud.io support
5. Review Google Cloud VM status

---

**Your Wholesale Education Platform is now live on RunCloud.io! 🚀**

**Application URL**: `https://your-domain.com`
**Admin Panel**: `https://your-domain.com/admin`
**RunCloud Dashboard**: `https://runcloud.io`
