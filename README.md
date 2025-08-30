# RISE Ultimate Project Manager - Local Development Setup

## Overview
RISE is a comprehensive CRM and project management system built with CodeIgniter 4. This guide will help you set up the project for local development.

## System Requirements
- **PHP**: 8.1 or higher (✅ You have PHP 8.3.25)
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache/Nginx or PHP built-in server
- **Extensions**: mysqli, curl, mbstring, intl, json, mysqlnd, xml, gd, zlib

## Prerequisites Check
✅ PHP 8.3.25 installed  
✅ All required extensions available  
✅ Composer available for dependency management  

## Quick Start

### 1. Database Setup
Create a MySQL database named `rise_crm`:
```sql
CREATE DATABASE rise_crm CHARACTER SET utf8 COLLATE utf8_general_ci;
```

### 2. Configuration
The database configuration is already updated in `app/Config/Database.php`:
- Host: localhost
- Database: rise_crm
- Username: root
- Password: (empty)
- Prefix: rise_

### 3. Start Development Server
```bash
php -S localhost:3000
```

### 4. Access Installation
Open your browser and go to: http://localhost:3000

The system will automatically redirect you to the installation page where you can:
- Verify system requirements
- Configure database connection
- Import the database schema
- Create admin user

## Project Structure
```
├── app/                    # Application code
│   ├── Config/           # Configuration files
│   ├── Controllers/      # Controller classes
│   ├── Models/          # Model classes
│   ├── Views/           # View templates
│   └── ThirdParty/      # External libraries
├── system/               # CodeIgniter framework
├── assets/               # Frontend assets (CSS, JS, images)
├── files/                # File uploads and storage
├── install/              # Installation scripts
└── writable/             # Writable directories (logs, cache)
```

## Dependencies
This project includes pre-packaged dependencies:
- **Stripe**: Payment processing
- **Google API**: Calendar, Drive, Gmail integration
- **Pusher**: Real-time notifications
- **PHPOffice**: Excel import/export
- **TCPDF**: PDF generation
- **IMAP**: Email processing

## Development Workflow
1. **Database**: Use the provided `install/database.sql` for initial setup
2. **Configuration**: Modify `app/Config/` files as needed
3. **Customization**: Extend controllers and models in `app/` directory
4. **Assets**: Frontend files are in `assets/` directory

## Troubleshooting

### Common Issues
1. **Permission Errors**: Ensure `writable/` directory is writable
2. **Database Connection**: Verify MySQL service is running
3. **Port Conflicts**: Change port in server command if 3000 is busy

### Logs
- Application logs: `writable/logs/`
- Error logs: Check your web server error logs

## Support
- Documentation: https://risedocs.fairsketch.com
- CodeIgniter 4 Docs: https://codeigniter4.github.io/userguide/

## License
This is a commercial product. Please refer to the license terms included with your purchase.
