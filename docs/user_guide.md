# PayrollPro User Guide

## Table of Contents
1. [Getting Started](#getting-started)
2. [User Management](#user-management)
3. [Employee Management](#employee-management)
4. [Payroll Processing](#payroll-processing)
5. [Reports](#reports)
6. [Master Data](#master-data)
7. [Security](#security)

## Getting Started

### Logging In
1. Open your web browser and navigate to the PayrollPro URL
2. Enter your username and password
3. Click "Sign In"

**Default Admin Credentials:**
- Username: `admin`
- Password: `password`

*Note: Please change the default password after first login*

### Dashboard Overview
The dashboard provides a quick overview of:
- Total employees and active count
- Current payroll period
- Total earnings and net payable
- Employee distribution by department
- Recent system activities

## User Management

### Creating Users
1. Navigate to **Settings > User Management**
2. Click "Add User"
3. Fill in the required information:
   - Username (unique)
   - Email address
   - Full name
   - Password
   - Role assignment
4. Click "Save"

### User Roles
- **Super Admin**: Full system access
- **HR Admin**: Employee and payroll management
- **Payroll Manager**: Payroll processing and reports
- **Unit HR**: Department-specific employee access
- **Viewer**: Read-only access

## Employee Management

### Adding Employees

#### Basic Information
1. Go to **Employees > Add Employee**
2. Fill in personal details:
   - Employee code (auto-generated if empty)
   - First name and last name
   - Email and phone
   - Date of birth and gender
   - Address

#### Employment Details
3. Select employment information:
   - Department and designation
   - Cost center
   - Reporting manager
   - Join date
   - Employment type (Permanent/Contract/Temporary)

#### Statutory Details
4. Enter statutory information:
   - PAN number
   - Aadhaar number
   - UAN number (PF)
   - PF number
   - ESI number

#### Bank Details
5. Add bank information:
   - Bank account number
   - Bank name
   - IFSC code

### Managing Employee Documents
1. Go to employee profile
2. Click "Upload Document"
3. Select document type (Photo, ID Proof, etc.)
4. Choose file and upload

### Salary Structure Assignment
1. Navigate to employee profile
2. Click "Salary Structure"
3. Set effective date
4. Enter amounts for each salary component
5. Save the structure

## Payroll Processing

### Creating Payroll Periods
1. Go to **Payroll > Periods**
2. Click "Add Period"
3. Set:
   - Period name (e.g., "January 2024")
   - Start and end dates
   - Financial year
4. Save the period

### Processing Payroll
1. Navigate to **Payroll > Process**
2. Select payroll period
3. Choose employees (or select all)
4. Click "Process Payroll"
5. Review calculations
6. Lock the period when satisfied

### Manual Adjustments
1. During payroll processing
2. Click "Edit" next to any component
3. Enter manual override amount
4. Add remarks if needed
5. Save changes

### Generating Payslips
1. Go to **Payroll > Payslips**
2. Select period and employee
3. Choose format (PDF/Excel)
4. Download or email to employee

## Reports

### Standard Reports
1. **Salary Register**: Complete salary details for a period
2. **Component Report**: Component-wise breakdown
3. **Bank Transfer**: Bank-ready transfer file
4. **Tax Reports**: TDS, PF, ESI summaries
5. **Loan Report**: Outstanding loan details

### Generating Reports
1. Navigate to **Reports**
2. Select report type
3. Set filters:
   - Period/Date range
   - Department
   - Employee selection
4. Choose export format
5. Generate and download

### Custom Report Builder
1. Go to **Reports > Custom Builder**
2. Select tables and fields
3. Apply filters and sorting
4. Save report template
5. Generate report

## Master Data

### Departments
- Add new departments
- Assign department heads
- Set department codes

### Designations
- Create designations
- Assign to departments
- Set grade levels

### Salary Components
- Configure earnings and deductions
- Set calculation formulas
- Define tax applicability

### Leave Types
- Set up leave categories
- Configure annual limits
- Enable carry-forward

### Holidays
- Add company holidays
- Set holiday types
- Import holiday calendar

## Security

### Password Management
1. Click user profile (top right)
2. Select "Change Password"
3. Enter current password
4. Set new password
5. Confirm and save

### Session Security
- Sessions automatically expire after 30 minutes
- Log out when finished
- Don't share login credentials

### Data Backup
- Regular database backups recommended
- Export important reports
- Keep offline copies of critical data

## Troubleshooting

### Common Issues

**Login Problems:**
- Check username and password
- Verify caps lock status
- Contact administrator if locked out

**Upload Failures:**
- Check file size (max 5MB)
- Verify file format
- Ensure sufficient disk space

**Report Errors:**
- Verify date ranges
- Check data availability
- Try smaller date ranges

### Getting Help
- Check system logs for errors
- Contact system administrator
- Review user permissions

## Best Practices

### Data Entry
- Verify all information before saving
- Use consistent naming conventions
- Keep employee records updated

### Payroll Processing
- Process payroll in test mode first
- Review calculations before finalizing
- Backup data before major operations

### Security
- Change default passwords
- Use strong passwords
- Log out after use
- Restrict user access appropriately

---

*For technical support or additional features, contact your system administrator.*