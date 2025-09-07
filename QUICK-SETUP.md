# ⚡ Quick Setup Guide - RunCloud.io Deployment

> **Get your Wholesale Education Platform live in 10 minutes!**

## 🚀 Quick Start (10 Minutes)

### Step 1: RunCloud.io Setup (3 minutes)
1. Go to [RunCloud.io](https://runcloud.io) and create account
2. Add your Google Cloud VM instance
3. Install RunCloud agent on your VM
4. Create new web application:
   - **Name**: `wholesale-education`
   - **Type**: PHP 8.2
   - **Domain**: Your domain or RunCloud subdomain

### Step 2: Configure Deployment Script (2 minutes)
1. Edit `deploy-runcloud.sh`:
   ```bash
   RUNCLOUD_HOST="YOUR_VM_IP_HERE"     # Replace with your VM IP
   RUNCLOUD_USER="YOUR_USERNAME"       # Replace with your username
   ```

### Step 3: Deploy (5 minutes)
```bash
# Run the deployment script
./deploy-runcloud.sh
```

### Step 4: Configure in RunCloud Dashboard
1. Go to RunCloud dashboard → Your app → **SSL**
2. Install Let's Encrypt certificate
3. Enable **Force HTTPS**
4. Go to **PHP Settings** and enable required extensions

## ✅ That's It!

Your Wholesale Education Platform is now live on RunCloud.io!

**Access your site**: Check RunCloud dashboard for the URL
**Admin panel**: `https://your-domain.com/admin`
**API**: `https://your-domain.com/api/*`

## 🔧 Optional: Auto-Deployment

For automatic deployments when you push to GitHub:

1. Go to GitHub → Settings → Secrets
2. Add these secrets:
   - `RUNCLOUD_HOST`: Your VM IP
   - `RUNCLOUD_USER`: Your username  
   - `RUNCLOUD_SSH_KEY`: Your SSH private key

3. The GitHub Actions workflow will automatically deploy on every push to main branch.

## 📞 Need Help?

- **RunCloud Support**: Check RunCloud dashboard
- **Deployment Issues**: Check `RUNCLOUD-DEPLOYMENT.md` for detailed guide
- **GitHub Repository**: `https://github.com/CodyZ30/Wholesale-Education.git`

---

**Your professional wholesale education platform is ready! 🎉**
