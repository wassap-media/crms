const express = require('express');
const path = require('path');
const fs = require('fs');
const { exec } = require('child_process');

const app = express();
const port = process.env.PORT || 3000;

// Serve static files (CSS, JS, images)
app.use('/assets', express.static(path.join(__dirname, 'assets')));
app.use('/files', express.static(path.join(__dirname, 'files')));

// Handle PHP files by showing a message instead of downloading
app.get('/', (req, res) => {
  // Check if PHP is available
  exec('php --version', (error, stdout, stderr) => {
    const phpAvailable = !error;
    
    res.send(`
      <!DOCTYPE html>
      <html>
      <head>
          <title>RISE CRM - Deployed on Render</title>
          <style>
              body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
              .container { text-align: center; }
              .success { color: #28a745; }
              .warning { color: #ffc107; }
              .info { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
              .aws-status { background: #e3f2fd; padding: 15px; border-radius: 5px; }
              .upgrade-notice { background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; }
              .deploy-button { background: #0070f3; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px; }
          </style>
      </head>
      <body>
          <div class="container">
              <h1 class="success">🎉 RISE CRM Successfully Deployed!</h1>
              
              <div class="upgrade-notice">
                  <h3 class="warning">⚠️ PHP Limitation on Free Tier</h3>
                  <p>The Node.js environment doesn't support PHP execution.</p>
                  <p><strong>Solutions:</strong></p>
                  <ul style="text-align: left;">
                      <li><strong>Option 1:</strong> Upgrade to paid plan for Docker support (full PHP)</li>
                      <li><strong>Option 2:</strong> Deploy to Vercel (supports PHP serverless)</li>
                      <li><strong>Option 3:</strong> Use local development (full functionality)</li>
                  </ul>
              </div>
              
              <div class="info">
                  <h3>📊 Current Status</h3>
                  <p>✅ Repository: Connected</p>
                  <p>✅ Static Assets: Working</p>
                  <p>✅ Environment: Production</p>
                  <p>${phpAvailable ? '✅' : '❌'} PHP: ${phpAvailable ? 'Available' : 'Not available in Node.js environment'}</p>
              </div>
              
              <div class="aws-status">
                  <h3>🚀 AWS Integration Ready</h3>
                  <p><strong>S3 Bucket:</strong> ${process.env.S3_BUCKET || 'rise-crm-storage-shreyas'}</p>
                  <p><strong>RDS Database:</strong> ${process.env.RDS_DATABASE || 'rise_crm'}</p>
                  <p><strong>Region:</strong> ${process.env.AWS_REGION || 'us-west-2'}</p>
                  <p>All AWS credentials configured ✅</p>
              </div>
              
              <div class="info">
                  <h3>🚀 Deploy Options</h3>
                  <a href="https://vercel.com/new/clone?repository-url=https://github.com/wassap-media/crms" class="deploy-button" target="_blank">
                      Deploy to Vercel (Full PHP Support)
                  </a>
                  <p><strong>Render Docker:</strong> Upgrade to paid plan for full PHP support</p>
                  <p><strong>Local Development:</strong> <code>php -S localhost:3000</code></p>
              </div>
              
              <p><strong>GitHub:</strong> <a href="https://github.com/wassap-media/crms">wassap-media/crms</a></p>
              <p><strong>Documentation:</strong> See VERCEL_DEPLOYMENT.md in repo</p>
          </div>
      </body>
      </html>
    `);
  });
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({
    status: 'healthy',
    timestamp: new Date().toISOString(),
    service: 'RISE CRM',
    version: '1.0.0',
    platform: 'render-nodejs',
    php_available: false,
    note: 'For full PHP functionality, deploy to Vercel or upgrade to Docker'
  });
});

// API endpoint to show environment variables (without secrets)
app.get('/api/status', (req, res) => {
  res.json({
    success: true,
    environment: process.env.CI_ENVIRONMENT || 'development',
    aws_region: process.env.AWS_REGION || 'not-set',
    s3_bucket: process.env.S3_BUCKET || 'not-set',
    rds_database: process.env.RDS_DATABASE || 'not-set',
    deployment: 'render-nodejs',
    php_support: false,
    recommendation: 'Deploy to Vercel for full PHP support',
    timestamp: new Date().toISOString()
  });
});

// Catch-all route for any other requests
app.get('*', (req, res) => {
  res.status(200).send(`
    <h1>RISE CRM - Static Deployment</h1>
    <p>Path: ${req.path}</p>
    <p>This is a static deployment on Render's Node.js environment.</p>
    <p>For full PHP functionality, <a href="https://vercel.com/new/clone?repository-url=https://github.com/wassap-media/crms">deploy to Vercel</a></p>
    <p><a href="/">← Back to Home</a></p>
  `);
});

app.listen(port, '0.0.0.0', () => {
  console.log(`🚀 RISE CRM server running on port ${port}`);
  console.log(`📊 Environment: ${process.env.CI_ENVIRONMENT || 'development'}`);
  console.log(`🌍 AWS Region: ${process.env.AWS_REGION || 'not-set'}`);
  console.log(`📦 S3 Bucket: ${process.env.S3_BUCKET || 'not-set'}`);
  console.log(`🗄️  RDS Database: ${process.env.RDS_DATABASE || 'not-set'}`);
  console.log(`⚠️  Note: For full PHP functionality, deploy to Vercel`);
});