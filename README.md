# Enterprise Payroll Management System

A comprehensive, enterprise-grade Payroll Management System built with **Core PHP (OOP)** and **MySQL** following MVC architecture. This system provides complete payroll processing capabilities with advanced features for managing employees, salary structures, and generating reports.

## 🚀 Features

### 👨‍💼 Employee Management
- Complete employee profile management
- Department, designation, and cost center assignment
- Statutory details management (PAN, Aadhaar, UAN, PF, ESI)
- Document upload and management
- Photo and signature upload
- Advanced search and filtering

### 📊 Payroll Processing
- Automated salary computation
- Formula-based component calculations
- Pro-rata salary calculations
- Loss of Pay (LOP) handling
- TDS calculation with tax slabs
- Loan EMI auto-deduction
- Arrears and variable pay management
- Payroll period management

### 💰 Salary Management
- Flexible salary component master
- Dynamic formula builder
- Salary structure templates
- Component-wise calculations
- Earnings, deductions, and reimbursements

### 🏦 Compliance & Statutory
- **Provident Fund (PF) Module**
  - Auto PF calculations
  - ECR file generation
  - PF contribution tracking
  
- **ESI Module**
  - ESI contribution calculations
  - Threshold management
  - ESI reports
  
- **Income Tax/TDS Module**
  - Dynamic tax slab configuration
  - Investment declarations
  - Auto TDS calculations
  - Form 16 data preparation

### 💳 Loan & Advance Management
- Multiple loan types
- EMI calculations and deductions
- Outstanding balance tracking
- Partial payments and closures

### 📈 Comprehensive Reports
- Salary registers
- Component-wise reports
- Bank transfer statements
- Tax reports (TDS, PF, ESI)
- Custom report builder
- Multiple export formats (Excel, CSV, PDF)

### 🔐 Security & Access Control
- Role-based access control
- User management with permissions
- Audit trail logging
- Session management
- CSRF protection
- Input validation and sanitization

### 📱 Modern UI/UX
- Responsive design with Tailwind CSS
- Interactive dashboard with widgets
- Real-time data updates
- Mobile-friendly interface
- Modern card-based layouts

### 🕒 Attendance Management
- Daily attendance tracking
- Bulk attendance marking
- Attendance reports and analytics
- Integration with payroll processing
- Mobile-friendly attendance interface

### 💳 Enhanced Loan Management
- Multiple loan types with different terms
- Automated EMI calculations
- Payment tracking and history
- Outstanding balance management
- Loan performance analytics

## 🛠️ Technical Architecture

### Core Technologies
- **Backend**: Core PHP 8+ with OOP principles
- **Database**: MySQL 8+
- **Frontend**: HTML5, Tailwind CSS, Vanilla JavaScript
- **Architecture**: MVC (Model-View-Controller)
- **Security**: BCrypt password hashing, PDO prepared statements

### Folder Structure
```
payroll-system/
├── app/
│   ├── controllers/          # Application controllers
│   ├── models/              # Data models
│   ├── views/               # View templates
│   └── core/                # Core framework classes
├── config/                  # Configuration files
├── public/                  # Public assets and entry point
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   └── index.php           # Front controller
├── uploads/                # File uploads
├── docs/                   # Documentation
├── lang/                   # Language files
├── supabase/migrations/   # Database migrations
└── README.md              # This file
```

## 📋 Prerequisites

- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher
- **Web Server**: Apache/Nginx
- **Extensions**: PDO, GD, OpenSSL, FileInfo

## 🚀 Installation

### 1. Clone or Download
```bash
# Clone the repository (if using git)
git clone <repository-url>
cd payroll-system

# Or download and extract the ZIP file
```

### 2. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE payroll_system;

# Import the schema
mysql -u root -p payroll_system < database.sql
```

### 3. Configuration
```bash
# Update database configuration
cp config/database.php.example config/database.php
# Edit database.php with your credentials
```

### 4. Web Server Setup

#### Apache (.htaccess included)
```apache
DocumentRoot /path/to/payroll-system/public
```

#### Nginx
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
}
```

### 5. Permissions
```bash
# Set proper permissions
chmod 755 public/
chmod -R 777 uploads/
chmod -R 755 app/
```

### 6. Access the Application
- Open your web browser
- Navigate to your domain/server address
- Default login credentials:
  - **Username**: `admin`
  - **Password**: `password`

## 📖 Usage Guide

### Initial Setup
1. **Login** with default admin credentials
2. **Change default password** in profile settings
3. **Setup master data**:
   - Add departments and designations
   - Configure salary components
   - Set up tax slabs
   - Add holidays and leave types

### Employee Management
1. **Add employees** through the employee management module
2. **Upload documents** and photos as needed
3. **Assign salary structures** to employees
4. **Configure reporting relationships**

### Payroll Processing
1. **Create payroll periods** (monthly/quarterly)
2. **Import attendance data** or mark attendance
3. **Process payroll** for selected employees
4. **Review and approve** salary calculations
5. **Generate payslips** and reports

### Report Generation
1. Access the **Reports** module
2. Select desired report type
3. Apply filters (period, department, etc.)
4. **Export** in preferred format

## 🔧 Configuration Options

### Database Configuration (`config/database.php`)
```php
private $host = 'localhost';
private $database = 'payroll_system';
private $username = 'root';
private $password = '';
```

### Application Settings (`config/config.php`)
```php
// Session timeout (seconds)
define('SESSION_TIMEOUT', 1800);

// File upload limits
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Pagination
define('RECORDS_PER_PAGE', 25);
```

## 🔒 Security Features

- **Authentication**: Secure login with session management
- **Authorization**: Role-based access control
- **Data Protection**: SQL injection prevention with PDO
- **CSRF Protection**: Token-based request validation
- **Input Validation**: Server-side data sanitization
- **Password Security**: BCrypt hashing
- **Audit Trail**: Complete activity logging

## 📊 Default Data

The system comes with:
- **5 User roles** (Super Admin, HR Admin, Payroll Manager, Unit HR, Viewer)
- **5 Departments** with designations
- **8 Salary components** (Basic, HRA, TA, MA, PF, ESI, PT, TDS)
- **3 Sample employees** with salary structures
- **Tax slabs** for FY 2024-25
- **Basic holiday calendar**

## 🤝 Contributing

1. Fork the project
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

For support and questions:
- Check the documentation in `/docs/`
- Review the code comments
- Create an issue for bugs or feature requests

## 🚀 Future Enhancements

Planned features for future releases:
- **Mobile app** for employees
- **Advanced analytics** and AI insights
- **Integration APIs** for third-party systems
- **Multi-company** support
- **Advanced workflow** management
- **Biometric integration**
- **Cloud deployment** options

---

**Built with ❤️ for modern enterprises**