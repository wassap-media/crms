# Vercel Deployment Guide for RISE CRM

## 🚀 Quick Deploy to Vercel

### **Method 1: One-Click Deploy**
[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https://github.com/wassap-media/crms)

### **Method 2: Manual Deployment**

#### 1. **Install Vercel CLI**
```bash
npm i -g vercel
```

#### 2. **Login to Vercel**
```bash
vercel login
```

#### 3. **Deploy from Project Directory**
```bash
vercel
```

## 🔧 **Environment Variables Setup**

After deployment, add these environment variables in Vercel Dashboard:

### **Required Environment Variables:**
```
CI_ENVIRONMENT=production
AWS_ACCESS_KEY_ID=your_aws_access_key_here
AWS_SECRET_ACCESS_KEY=your_aws_secret_access_key_here
AWS_REGION=us-west-2
S3_BUCKET=your-s3-bucket-name
S3_REGION=us-west-2
RDS_ENDPOINT=your-rds-endpoint.rds.amazonaws.com
RDS_DATABASE=rise_crm
RDS_USERNAME=your_rds_username
RDS_PASSWORD=your_rds_password
RDS_PORT=3306
```

**Note**: Replace the placeholder values above with your actual AWS credentials and endpoints.

## 📁 **Project Structure for Vercel**

```
crms/
├── api/
│   ├── index.php          # Main entry point
│   └── health.php         # Health check
├── app/                   # CodeIgniter app
├── system/                # CodeIgniter system
├── assets/                # Static assets
├── files/                 # File uploads
├── vercel.json           # Vercel configuration
├── .vercelignore         # Files to ignore
└── package.json          # Node.js fallback
```

## 🔗 **URLs After Deployment**

- **Main App**: `https://your-app.vercel.app/`
- **Health Check**: `https://your-app.vercel.app/health`
- **Static Assets**: `https://your-app.vercel.app/assets/`

## ✅ **What Works on Vercel**

- ✅ **PHP 8.2** via serverless functions
- ✅ **CodeIgniter 4** framework
- ✅ **AWS S3** file storage
- ✅ **AWS RDS** database
- ✅ **Static assets** (CSS, JS, images)
- ✅ **Auto-scaling** serverless
- ✅ **Global CDN** for fast loading
- ✅ **SSL certificates** automatic

## ⚙️ **Vercel Configuration Details**

### **Runtime**: `vercel-php@0.6.0`
### **Functions**: All PHP files in `/api/` directory
### **Static Files**: `/assets/`, `/files/` served directly
### **Routing**: All requests routed through `/api/index.php`

## 🔧 **Local Development**

For local development, use the existing setup:
```bash
php -S localhost:3000
```

## 🚀 **Deployment Comparison**

| Platform | Type | PHP Support | Cost | Scaling |
|----------|------|-------------|------|---------|
| **Vercel** | Serverless | ✅ Full | Free/Paid | Auto |
| **Render** | Container | ✅ Full | Free/Paid | Manual |
| **Local** | Development | ✅ Full | Free | N/A |

## 🔍 **Troubleshooting**

### **Common Issues:**

1. **Function Timeout**: Increase timeout in `vercel.json`
2. **File Uploads**: Use S3 for file storage (already configured)
3. **Database**: Ensure RDS security group allows connections
4. **Environment Variables**: Check all variables are set in Vercel dashboard

### **Debug Commands:**
```bash
vercel logs
vercel env ls
vercel inspect
```

## 📊 **Performance Benefits**

- **Global Edge Network**: Sub-100ms response times
- **Automatic Scaling**: Handle traffic spikes
- **Zero Cold Starts**: PHP functions stay warm
- **CDN Integration**: Static assets served globally
- **AWS Integration**: Direct S3/RDS connections

## 🎯 **Next Steps**

1. Deploy to Vercel
2. Configure environment variables
3. Test all functionality
4. Set up custom domain (optional)
5. Monitor performance

---

**🎉 Your RISE CRM will be live on Vercel with full AWS integration!**
