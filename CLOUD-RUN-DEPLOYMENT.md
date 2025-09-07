# 🚀 Google Cloud Run Deployment Guide - Dev V0.1

> **Complete guide to deploy your Wholesale Education Platform to Google Cloud Run**

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ Google Cloud Platform account
- ✅ Google Cloud CLI installed (`gcloud`)
- ✅ Docker installed (for local testing)
- ✅ Your GitHub repository: `https://github.com/CodyZ30/Wholesale-Education.git`

## 🛠️ Step 1: Install Google Cloud CLI

### macOS (using Homebrew):
```bash
brew install google-cloud-sdk
```

### Windows:
Download from: https://cloud.google.com/sdk/docs/install

### Linux:
```bash
curl https://sdk.cloud.google.com | bash
exec -l $SHELL
```

## 🔐 Step 2: Authenticate with Google Cloud

```bash
# Login to Google Cloud
gcloud auth login

# Set your project (replace with your project ID)
gcloud config set project YOUR_PROJECT_ID

# Enable required APIs
gcloud services enable run.googleapis.com
gcloud services enable cloudbuild.googleapis.com
gcloud services enable containerregistry.googleapis.com
```

## 🐳 Step 3: Create Dockerfile for Cloud Run

Create a `Dockerfile` in your project root:

```dockerfile
# Use Node.js 18 as base image
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json ./
COPY deployment/package*.json ./deployment/

# Install dependencies
RUN npm install
RUN cd deployment && npm install

# Copy application files
COPY . .

# Create non-root user
RUN addgroup -g 1001 -S nodejs
RUN adduser -S nextjs -u 1001

# Set proper permissions
RUN chown -R nextjs:nodejs /app
USER nextjs

# Expose port (Cloud Run uses PORT environment variable)
EXPOSE 8080

# Set environment variables
ENV NODE_ENV=production
ENV PORT=8080

# Start the application
CMD ["node", "deployment/server.js"]
```

## 📦 Step 4: Create .dockerignore

Create a `.dockerignore` file to exclude unnecessary files:

```dockerignore
# Git
.git
.gitignore

# Documentation
README.md
CHANGELOG.md
CONTRIBUTING.md
CLOUD-RUN-DEPLOYMENT.md

# Development files
node_modules
npm-debug.log*
yarn-debug.log*
yarn-error.log*

# IDE
.vscode
.idea
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Logs
*.log
logs/

# Environment
.env
.env.local
.env.development
.env.test

# Test files
coverage/
.nyc_output/

# Build files
dist/
build/

# Temporary files
tmp/
temp/
```

## 🚀 Step 5: Deploy to Cloud Run

### Option A: Deploy from Source (Recommended)

```bash
# Deploy directly from GitHub
gcloud run deploy wholesale-education \
  --source . \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated \
  --port 8080 \
  --memory 1Gi \
  --cpu 1 \
  --max-instances 10 \
  --set-env-vars NODE_ENV=production
```

### Option B: Deploy from Container Registry

```bash
# Build and push to Container Registry
gcloud builds submit --tag gcr.io/YOUR_PROJECT_ID/wholesale-education

# Deploy from Container Registry
gcloud run deploy wholesale-education \
  --image gcr.io/YOUR_PROJECT_ID/wholesale-education \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated \
  --port 8080 \
  --memory 1Gi \
  --cpu 1 \
  --max-instances 10
```

## ⚙️ Step 6: Configure Environment Variables

Set up environment variables for your application:

```bash
gcloud run services update wholesale-education \
  --region us-central1 \
  --set-env-vars \
    NODE_ENV=production,\
    APP_NAME="Wholesale Education Platform",\
    APP_VERSION="0.1.0",\
    DB_PATH="/tmp/wholesale_education.db",\
    SESSION_SECRET="your-session-secret-here",\
    JWT_SECRET="your-jwt-secret-here"
```

## 🔧 Step 7: Configure Custom Domain (Optional)

```bash
# Map custom domain
gcloud run domain-mappings create \
  --service wholesale-education \
  --domain your-domain.com \
  --region us-central1
```

## 📊 Step 8: Monitor Your Deployment

### Check deployment status:
```bash
gcloud run services describe wholesale-education --region us-central1
```

### View logs:
```bash
gcloud logs read --service wholesale-education --limit 50
```

### Check metrics:
```bash
# Open Cloud Console
gcloud run services list
```

## 🔄 Step 9: Continuous Deployment Setup

### Option A: GitHub Actions (Recommended)

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Cloud Run

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup Google Cloud CLI
      uses: google-github-actions/setup-gcloud@v1
      with:
        service_account_key: ${{ secrets.GCP_SA_KEY }}
        project_id: ${{ secrets.GCP_PROJECT_ID }}
    
    - name: Deploy to Cloud Run
      run: |
        gcloud run deploy wholesale-education \
          --source . \
          --platform managed \
          --region us-central1 \
          --allow-unauthenticated
```

### Option B: Cloud Build Triggers

```bash
# Create build trigger
gcloud builds triggers create github \
  --repo-name=Wholesale-Education \
  --repo-owner=CodyZ30 \
  --branch-pattern="^main$" \
  --build-config=cloudbuild.yaml
```

## 🛡️ Step 10: Security Configuration

### Enable HTTPS only:
```bash
gcloud run services update wholesale-education \
  --region us-central1 \
  --set-env-vars FORCE_HTTPS=true
```

### Configure CORS:
```bash
gcloud run services update wholesale-education \
  --region us-central1 \
  --set-env-vars \
    CORS_ORIGIN="https://your-domain.com",\
    CORS_CREDENTIALS=true
```

## 📈 Step 11: Performance Optimization

### Configure auto-scaling:
```bash
gcloud run services update wholesale-education \
  --region us-central1 \
  --min-instances 0 \
  --max-instances 100 \
  --concurrency 1000 \
  --cpu-throttling
```

### Enable CDN:
```bash
gcloud run services update wholesale-education \
  --region us-central1 \
  --set-env-vars ENABLE_CDN=true
```

## 🔍 Step 12: Testing Your Deployment

### Test locally with Cloud Run emulator:
```bash
# Install Cloud Run emulator
gcloud components install cloud-run-emulator

# Run locally
gcloud run emulator --source .
```

### Test production deployment:
```bash
# Get service URL
SERVICE_URL=$(gcloud run services describe wholesale-education \
  --region us-central1 \
  --format 'value(status.url)')

# Test endpoints
curl $SERVICE_URL
curl $SERVICE_URL/api/health
curl $SERVICE_URL/admin
```

## 🚨 Troubleshooting

### Common Issues:

1. **Port Configuration**:
   ```bash
   # Ensure your app listens on PORT environment variable
   const port = process.env.PORT || 8080;
   ```

2. **Memory Issues**:
   ```bash
   # Increase memory allocation
   gcloud run services update wholesale-education \
     --region us-central1 \
     --memory 2Gi
   ```

3. **Cold Start Issues**:
   ```bash
   # Set minimum instances
   gcloud run services update wholesale-education \
     --region us-central1 \
     --min-instances 1
   ```

4. **Database Issues**:
   ```bash
   # Use Cloud SQL for persistent data
   gcloud run services update wholesale-education \
     --region us-central1 \
     --add-cloudsql-instances YOUR_INSTANCE_CONNECTION_NAME
   ```

## 📊 Monitoring & Analytics

### Set up monitoring:
```bash
# Enable monitoring
gcloud services enable monitoring.googleapis.com

# Create alerting policy
gcloud alpha monitoring policies create --policy-from-file=alert-policy.yaml
```

### View metrics in Cloud Console:
- Go to Cloud Run → wholesale-education → Metrics
- Monitor: Requests, Latency, Error rate, Memory usage

## 💰 Cost Optimization

### Optimize for cost:
```bash
# Use appropriate instance sizes
gcloud run services update wholesale-education \
  --region us-central1 \
  --memory 512Mi \
  --cpu 0.5 \
  --max-instances 5
```

### Set up billing alerts:
- Go to Cloud Console → Billing → Budgets & Alerts
- Create budget with alerts

## 🎉 Success Checklist

- ✅ Service deployed successfully
- ✅ Custom domain configured (if needed)
- ✅ Environment variables set
- ✅ HTTPS enabled
- ✅ Monitoring configured
- ✅ CI/CD pipeline set up
- ✅ Performance optimized
- ✅ Security configured

## 📞 Support

If you encounter issues:
1. Check Cloud Run logs: `gcloud logs read --service wholesale-education`
2. Review Cloud Console metrics
3. Check GitHub Actions logs (if using CI/CD)
4. Consult Google Cloud documentation

---

**Your Wholesale Education Platform is now live on Google Cloud Run! 🚀**

**Service URL**: `https://wholesale-education-xxxxx-uc.a.run.app`
**Admin Panel**: `https://your-service-url/admin`
**API Endpoints**: `https://your-service-url/api/*`
