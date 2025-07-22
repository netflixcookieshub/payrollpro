# Installation Guide

## System Requirements

### Server Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 512MB RAM (2GB recommended)
- **Storage**: Minimum 1GB free space

### PHP Extensions Required
- PDO and PDO_MySQL
- GD or ImageMagick
- OpenSSL
- FileInfo
- Mbstring
- JSON
- Session
- Curl

## Installation Steps

### 1. Download and Extract
```bash
# Download the system files
wget https://github.com/your-repo/payroll-system.zip
unzip payroll-system.zip
cd payroll-system
```

### 2. Set File Permissions
```bash
# Set proper permissions
chmod 755 public/
chmod -R 777 uploads/
chmod -R 755 app/
chmod 644 config/*.php
```

### 3. Database Setup

#### Create Database
```sql
-- Login to MySQL
mysql -u root -p

-- Create database
CREATE DATABASE payroll_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (optional)
CREATE USER 'payroll_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON payroll_system.* TO 'payroll_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Import Schema
```bash
# Import the database schema
mysql -u root -p payroll_system < database.sql
```

### 4. Configuration

#### Database Configuration
Edit `config/database.php`:
```php
private $host = 'localhost';
private $database = 'payroll_system';
private $username = 'payroll_user';
private $password = 'secure_password';
```

#### Application Configuration
Edit `config/config.php`:
```php
// Update base URL
define('BASE_URL', 'https://your-domain.com');

// Update email settings
define('SMTP_HOST', 'your-smtp-host');
define('SMTP_USERNAME', 'your-email@domain.com');
define('SMTP_PASSWORD', 'your-email-password');
```

### 5. Web Server Configuration

#### Apache Configuration
Create `.htaccess` in root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ public/$1 [L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

Set DocumentRoot to `/path/to/payroll-system/public`

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/payroll-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
}
```

### 6. SSL Configuration (Recommended)

#### Using Let's Encrypt
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 7. Final Steps

#### Test Installation
1. Open your browser and navigate to your domain
2. You should see the login page
3. Default credentials:
   - Username: `admin`
   - Password: `password`

#### Security Checklist
- [ ] Change default admin password
- [ ] Update database credentials
- [ ] Set proper file permissions
- [ ] Enable SSL/HTTPS
- [ ] Configure firewall
- [ ] Set up regular backups

## Post-Installation Configuration

### 1. Initial Setup
1. Login with default credentials
2. Go to Profile → Change Password
3. Update admin profile information
4. Configure company details

### 2. Master Data Setup
1. **Departments**: Add your company departments
2. **Designations**: Create job positions
3. **Salary Components**: Configure pay components
4. **Tax Slabs**: Set up current tax rates
5. **Holidays**: Add company holidays
6. **Leave Types**: Configure leave categories

### 3. Employee Data
1. Add employee records
2. Assign salary structures
3. Upload employee documents
4. Set up reporting relationships

### 4. System Configuration
1. Configure email settings for payslip delivery
2. Set up backup schedules
3. Configure user roles and permissions
4. Test payroll processing with sample data

## Troubleshooting

### Common Issues

#### Database Connection Error
```
Error: Connection failed: SQLSTATE[HY000] [2002] Connection refused
```
**Solution**: Check database credentials and ensure MySQL is running

#### File Permission Error
```
Error: Permission denied
```
**Solution**: Set proper file permissions:
```bash
chmod -R 777 uploads/
chmod -R 755 app/
```

#### PHP Extension Missing
```
Error: Class 'PDO' not found
```
**Solution**: Install required PHP extensions:
```bash
sudo apt install php8.0-mysql php8.0-gd php8.0-mbstring
```

#### Session Issues
```
Error: Session could not be started
```
**Solution**: Check session directory permissions:
```bash
sudo chmod 777 /var/lib/php/sessions
```

### Performance Optimization

#### PHP Configuration
Edit `php.ini`:
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
```

#### MySQL Optimization
Edit `my.cnf`:
```ini
[mysqld]
innodb_buffer_pool_size = 256M
query_cache_size = 64M
max_connections = 100
```

#### Apache Optimization
Enable compression:
```apache
LoadModule deflate_module modules/mod_deflate.so

<Location />
    SetOutputFilter DEFLATE
    SetEnvIfNoCase Request_URI \
        \.(?:gif|jpe?g|png)$ no-gzip dont-vary
</Location>
```

## Backup and Maintenance

### Database Backup
```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u payroll_user -p payroll_system > backup_$DATE.sql
gzip backup_$DATE.sql
```

### File Backup
```bash
# Backup uploads and configuration
tar -czf payroll_backup_$DATE.tar.gz uploads/ config/
```

### Automated Backup
Add to crontab:
```bash
# Daily backup at 2 AM
0 2 * * * /path/to/backup-script.sh
```

## Security Hardening

### File Security
```bash
# Remove write permissions from config files
chmod 644 config/*.php

# Secure uploads directory
echo "Options -Indexes" > uploads/.htaccess
```

### Database Security
```sql
-- Remove test databases
DROP DATABASE IF EXISTS test;

-- Secure root account
UPDATE mysql.user SET Password=PASSWORD('new_password') WHERE User='root';
DELETE FROM mysql.user WHERE User='';
FLUSH PRIVILEGES;
```

### Application Security
1. Enable HTTPS only
2. Set secure session cookies
3. Implement rate limiting
4. Regular security updates
5. Monitor access logs

## Support

For technical support:
- Check the documentation in `/docs/`
- Review system logs in `/logs/`
- Contact system administrator
- Submit issues on GitHub

---

**Important**: Always test the installation on a staging environment before deploying to production.