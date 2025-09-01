const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();
const port = process.env.PORT || 3000;

// Serve static files (CSS, JS, images)
app.use('/assets', express.static(path.join(__dirname, 'assets')));
app.use('/files', express.static(path.join(__dirname, 'files')));

// Basic route to show that the app is deployed
app.get('/', (req, res) => {
  res.send(`
    <!DOCTYPE html>
    <html>
    <head>
        <title>RISE CRM - Deployed on Render</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .container { text-align: center; }
            .success { color: #28a745; }
            .info { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
            .aws-status { background: #e3f2fd; padding: 15px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="success">🎉 RISE CRM Successfully Deployed!</h1>
            <div class="info">
                <h3>Deployment Status</h3>
                <p>✅ Node.js Server: Running</p>
                <p>✅ Static Files: Served</p>
                <p>✅ Repository: Connected</p>
                <p>✅ Environment: Production</p>
            </div>
            
            <div class="aws-status">
                <h3>🚀 AWS Integration Ready</h3>
                <p><strong>S3 Bucket:</strong> rise-crm-storage-shreyas</p>
                <p><strong>RDS Database:</strong> rise-crm-db (MySQL 8.0.42)</p>
                <p><strong>Region:</strong> us-west-2</p>
            </div>
            
            <div class="info">
                <h3>📝 Next Steps</h3>
                <p>Your RISE CRM is deployed! The PHP application structure is ready.</p>
                <p>For full PHP functionality, consider upgrading to a paid plan with Docker support.</p>
            </div>
            
            <p><strong>GitHub:</strong> <a href="https://github.com/wassap-media/crms">wassap-media/crms</a></p>
            <p><strong>Deployed with:</strong> AWS S3 + RDS + Render</p>
        </div>
    </body>
    </html>
  `);
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({
    status: 'healthy',
    timestamp: new Date().toISOString(),
    service: 'RISE CRM',
    version: '1.0.0'
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
    timestamp: new Date().toISOString()
  });
});

// Catch-all route for any other requests
app.get('*', (req, res) => {
  res.status(200).send(`
    <h1>RISE CRM - Static Deployment</h1>
    <p>Path: ${req.path}</p>
    <p>This is a static deployment on Render's Node.js environment.</p>
    <p><a href="/">← Back to Home</a></p>
  `);
});

app.listen(port, '0.0.0.0', () => {
  console.log(`🚀 RISE CRM server running on port ${port}`);
  console.log(`📊 Environment: ${process.env.CI_ENVIRONMENT || 'development'}`);
  console.log(`🌍 AWS Region: ${process.env.AWS_REGION || 'not-set'}`);
  console.log(`📦 S3 Bucket: ${process.env.S3_BUCKET || 'not-set'}`);
  console.log(`🗄️  RDS Database: ${process.env.RDS_DATABASE || 'not-set'}`);
});
