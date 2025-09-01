# AWS Integration Setup Guide for RISE CRM

## 🎯 Overview
This guide provides complete instructions for setting up AWS backend and storage integration with your RISE CRM system.

## ✅ What's Already Completed

### 1. AWS SDK Installation
- ✅ AWS SDK for PHP installed via Composer
- ✅ All dependencies resolved and working

### 2. Configuration Files Created
- ✅ `app/Config/AWS.php` - Main AWS configuration
- ✅ `app/Services/AWSS3Service.php` - S3 file management service
- ✅ `app/Services/AWSRDSService.php` - RDS database service
- ✅ `app/Controllers/AWSController.php` - API endpoints for testing
- ✅ `app/Helpers/aws_helper.php` - Helper functions for AWS operations

### 3. Environment Configuration
- ✅ `.env` file updated with AWS variables
- ✅ `env.example` template with all AWS settings
- ✅ SSL certificate downloaded for RDS connections

### 4. Security & Git Setup
- ✅ `.gitignore` configured to exclude sensitive files
- ✅ Template files for safe version control
- ✅ Project pushed to GitHub repository

## 🔧 Next Steps - AWS Resource Creation

### Step 1: Configure AWS Credentials

1. **Get AWS Access Keys:**
   ```
   - Log into AWS Console
   - Go to IAM → Users → Create User
   - Attach policies: AmazonS3FullAccess, AmazonRDSFullAccess
   - Create Access Key → Save Key ID and Secret
   ```

2. **Update .env file:**
   ```env
   AWS_ACCESS_KEY_ID=your_actual_access_key_here
   AWS_SECRET_ACCESS_KEY=your_actual_secret_key_here
   AWS_REGION=us-east-1
   ```

### Step 2: Create S3 Bucket

1. **Via AWS Console:**
   ```
   - Go to S3 Console
   - Create Bucket: "rise-crm-storage"
   - Region: us-east-1
   - Block Public Access: Keep enabled
   - Versioning: Optional
   ```

2. **Update .env:**
   ```env
   S3_BUCKET=rise-crm-storage
   S3_REGION=us-east-1
   ```

### Step 3: Create RDS MySQL Instance

1. **Via AWS Console:**
   ```
   - Go to RDS Console
   - Create Database → MySQL
   - Template: Free Tier (for testing)
   - DB Instance: rise-crm-db
   - Master Username: admin
   - Auto-generate password or set custom
   - VPC: Default
   - Public Access: Yes (for testing)
   - Security Group: Allow port 3306
   ```

2. **Update .env:**
   ```env
   RDS_ENDPOINT=rise-crm-db.xxxxxxxxx.us-east-1.rds.amazonaws.com
   RDS_USERNAME=admin
   RDS_PASSWORD=your_rds_password
   RDS_DATABASE=rise_crm
   ```

### Step 4: Create CloudFront Distribution (Optional)

1. **For CDN/Fast File Delivery:**
   ```
   - Go to CloudFront Console
   - Create Distribution
   - Origin: Your S3 bucket
   - Cache Behaviors: Default settings
   - Note the Distribution Domain
   ```

2. **Update .env:**
   ```env
   CLOUDFRONT_DOMAIN=d1234567890.cloudfront.net
   CLOUDFRONT_DISTRIBUTION_ID=E1234567890ABC
   ```

## 🧪 Testing Your Setup

### 1. Run Integration Test
```bash
php aws_integration_test.php
```

### 2. Test via Web Interface
Start your PHP server and visit:
```
http://localhost:3000/awscontroller/getStatus
http://localhost:3000/awscontroller/testS3
http://localhost:3000/awscontroller/testRDS
```

### 3. Test File Upload
```bash
curl -X POST -F "file=@test.jpg" -F "folder=uploads" \
  http://localhost:3000/awscontroller/uploadFile
```

## 📁 File Structure

```
crms/
├── app/
│   ├── Config/
│   │   ├── AWS.php                 # AWS configuration
│   │   └── rds-ca-2019-root.pem   # RDS SSL certificate
│   ├── Services/
│   │   ├── AWSS3Service.php       # S3 operations
│   │   └── AWSRDSService.php      # RDS operations
│   ├── Controllers/
│   │   └── AWSController.php      # API endpoints
│   └── Helpers/
│       └── aws_helper.php         # Helper functions
├── .env                           # Environment variables
├── env.example                    # Environment template
├── aws_integration_test.php       # Standalone test
└── AWS_SETUP_GUIDE.md            # This guide
```

## 🔌 API Endpoints

### Available Endpoints:
- `GET /awscontroller/getStatus` - Check AWS configuration
- `GET /awscontroller/testS3` - Test S3 connection
- `GET /awscontroller/testRDS` - Test RDS connection
- `POST /awscontroller/uploadFile` - Upload file to S3
- `GET /awscontroller/getFile/{key}` - Get file URL
- `DELETE /awscontroller/deleteFile/{key}` - Delete file

### Example Usage in Code:
```php
// Upload file to S3
$result = upload_file_to_s3($uploadedFile, 'profile_images');

// Get file URL
$url = get_s3_file_url('profile_images/user_123.jpg');

// Delete file
$deleted = delete_s3_file('old_file.pdf');

// Test connection
$status = test_aws_connection();
```

## 🔒 Security Best Practices

1. **Never commit AWS credentials to Git**
2. **Use IAM roles with minimal permissions**
3. **Enable S3 bucket versioning**
4. **Use SSL for RDS connections**
5. **Regularly rotate access keys**
6. **Monitor AWS CloudTrail logs**

## 💰 Cost Optimization

1. **S3 Storage Classes:**
   - Use IA (Infrequent Access) for old files
   - Use Glacier for archival

2. **RDS:**
   - Use appropriate instance size
   - Enable automated backups
   - Consider read replicas for scaling

3. **CloudFront:**
   - Configure appropriate cache TTL
   - Use compression

## 🆘 Troubleshooting

### Common Issues:

1. **S3 Access Denied:**
   - Check IAM permissions
   - Verify bucket policy
   - Confirm region settings

2. **RDS Connection Failed:**
   - Check security group rules
   - Verify endpoint and port
   - Ensure public access enabled

3. **SSL Certificate Error:**
   - Verify certificate file exists
   - Check file permissions
   - Ensure SSL enabled in config

### Debug Commands:
```bash
# Test AWS CLI access
aws s3 ls

# Check RDS connectivity
mysql -h your-endpoint -P 3306 -u admin -p

# Verify SSL certificate
openssl x509 -in app/Config/rds-ca-2019-root.pem -text
```

## 🚀 Production Deployment

1. **Environment Variables:**
   - Use AWS Systems Manager Parameter Store
   - Or AWS Secrets Manager for sensitive data

2. **Infrastructure as Code:**
   - Use the provided Terraform files
   - Or AWS CloudFormation templates

3. **Monitoring:**
   - Enable CloudWatch logging
   - Set up SNS notifications
   - Monitor costs with AWS Budgets

## 📞 Support

For issues with this integration:
1. Check the troubleshooting section
2. Review AWS CloudTrail logs
3. Test with the provided test scripts
4. Verify all environment variables are set

---
**🎉 AWS Integration Setup Complete!**

Your RISE CRM now has full AWS backend and storage capabilities. Configure your credentials and start using cloud-powered file storage and database services!
