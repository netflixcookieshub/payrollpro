# PayrollPro API Documentation

## Overview
PayrollPro provides a comprehensive REST API for integrating with external systems. The API supports employee management, payroll processing, attendance tracking, and reporting.

## Authentication
All API requests require authentication using API keys. Include the API key in the Authorization header:

```
Authorization: Bearer YOUR_API_KEY
```

## Base URL
```
https://your-domain.com/api/v1
```

## Rate Limiting
- 1000 requests per hour per API key
- Rate limit headers included in responses
- 429 status code when limit exceeded

## Endpoints

### Employees

#### Get All Employees
```
GET /employees
```

**Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Records per page (default: 25, max: 100)
- `department` (optional): Filter by department code
- `status` (optional): Filter by status (active, inactive, terminated)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "emp_code": "EMP001",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@company.com",
      "department_name": "IT",
      "designation_name": "Software Engineer",
      "status": "active"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 25,
    "total": 100,
    "pages": 4
  }
}
```

#### Get Employee by ID
```
GET /employees/{id}
```

#### Create Employee
```
POST /employees
```

**Request Body:**
```json
{
  "emp_code": "EMP002",
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane.smith@company.com",
  "department_id": 2,
  "designation_id": 4,
  "join_date": "2024-01-15"
}
```

#### Update Employee
```
PUT /employees/{id}
```

#### Delete Employee
```
DELETE /employees/{id}
```

### Attendance

#### Get Attendance Records
```
GET /attendance
```

**Parameters:**
- `date` (optional): Specific date (YYYY-MM-DD)
- `employee_id` (optional): Filter by employee
- `start_date` (optional): Date range start
- `end_date` (optional): Date range end

#### Mark Attendance
```
POST /attendance
```

**Request Body:**
```json
{
  "employee_id": 1,
  "attendance_date": "2024-01-15",
  "check_in": "09:00:00",
  "check_out": "18:00:00",
  "status": "present",
  "total_hours": 8.0
}
```

### Payroll

#### Get Payroll Data
```
GET /payroll
```

**Parameters:**
- `period_id` (required): Payroll period ID
- `employee_id` (optional): Filter by employee

#### Process Payroll
```
POST /payroll
```

**Request Body:**
```json
{
  "period_id": 1,
  "employee_ids": [1, 2, 3],
  "options": {
    "include_arrears": true,
    "calculate_tds": true,
    "process_loans": true
  }
}
```

### Salary Structure

#### Get Employee Salary Structure
```
GET /salary-structure/{employee_id}
```

#### Update Salary Structure
```
POST /salary-structure/{employee_id}
```

**Request Body:**
```json
{
  "effective_date": "2024-01-01",
  "components": [
    {
      "component_id": 1,
      "amount": 30000.00
    },
    {
      "component_id": 2,
      "amount": 12000.00
    }
  ]
}
```

## Webhooks

PayrollPro supports webhooks for real-time data synchronization:

### Webhook Endpoints
- `/integrations/webhook/attendance` - Attendance data from biometric devices
- `/integrations/webhook/hrms` - Employee data from external HRMS
- `/integrations/webhook/banking` - Banking transaction confirmations

### Webhook Security
All webhooks are verified using HMAC-SHA256 signatures. Include the signature in the `X-Signature` header.

## Error Handling

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `429` - Rate Limit Exceeded
- `500` - Internal Server Error

### Error Response Format
```json
{
  "error": "Error message",
  "code": "ERROR_CODE",
  "details": {
    "field": "Validation error message"
  }
}
```

## SDK and Libraries

### PHP SDK
```php
use PayrollPro\Client;

$client = new Client('YOUR_API_KEY');
$employees = $client->employees()->getAll();
```

### JavaScript SDK
```javascript
import PayrollPro from 'payrollpro-js';

const client = new PayrollPro('YOUR_API_KEY');
const employees = await client.employees.getAll();
```

## Examples

### Complete Employee Onboarding
```bash
# Create employee
curl -X POST https://your-domain.com/api/v1/employees \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "emp_code": "EMP003",
    "first_name": "Alice",
    "last_name": "Johnson",
    "email": "alice.johnson@company.com",
    "department_id": 2,
    "designation_id": 4,
    "join_date": "2024-01-15"
  }'

# Set salary structure
curl -X POST https://your-domain.com/api/v1/salary-structure/3 \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "effective_date": "2024-01-15",
    "components": [
      {"component_id": 1, "amount": 35000.00},
      {"component_id": 2, "amount": 14000.00}
    ]
  }'
```

### Bulk Attendance Import
```bash
curl -X POST https://your-domain.com/api/v1/attendance \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "records": [
      {
        "employee_id": 1,
        "attendance_date": "2024-01-15",
        "check_in": "09:00:00",
        "check_out": "18:00:00",
        "status": "present"
      },
      {
        "employee_id": 2,
        "attendance_date": "2024-01-15",
        "check_in": "09:15:00",
        "check_out": "18:00:00",
        "status": "late"
      }
    ]
  }'
```

## Support

For API support and questions:
- Documentation: https://your-domain.com/docs/api
- Support Email: api-support@company.com
- GitHub Issues: https://github.com/company/payrollpro-api