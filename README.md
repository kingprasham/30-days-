# Customer Tracking & Billing Management System

A comprehensive PHP-based system for tracking customer billing, payments, dealer management, and business analytics with Excel import capabilities.

## Features

- **Dashboard** - Real-time analytics with charts and key metrics
- **Customer Management** - Track customers with fuzzy name matching
- **Dealer Management** - Manage dealers with territory assignments
- **Excel Import** - Upload and process Excel files with automatic normalization
- **Challan/Invoice Management** - Track deliveries and billing
- **Contract Management** - Manage customer contracts with renewal tracking
- **Reports** - Various reports including defaulters, revenue, state-wise analysis
- **User Management** - Role-based access control (Admin/Staff)

## Tech Stack

- PHP 8+
- MySQL
- Bootstrap 5
- Chart.js
- PHPSpreadsheet
- jQuery, DataTables, Select2

## Installation

### Prerequisites

- XAMPP (or LAMP/WAMP) with PHP 8+ and MySQL
- Composer (for PHPSpreadsheet)

### Setup Steps

1. **Copy files to XAMPP**
   ```
   Copy this folder to: C:\xampp\htdocs\papa\30 days\
   ```

2. **Install dependencies**
   - Composer and PHPSpreadsheet are already installed in the `vendor` folder

3. **Setup Database**
   - Start Apache and MySQL in XAMPP Control Panel
   - Open your browser and navigate to:
     ```
     http://localhost/papa/30%20days/setup_database.php
     ```
   - This will create the database and all tables automatically

4. **Login**
   - Navigate to: `http://localhost/papa/30%20days/`
   - **Username:** admin
   - **Password:** 123456
   - **IMPORTANT:** Change password after first login!

## Database Configuration

The database credentials are configured in `config/database.php`:
- **Host:** localhost
- **Database:** customer_tracker
- **Username:** root
- **Password:** (empty)

## User Roles

### Admin
- Full access to all features
- Can add, edit, and delete all records
- Access to settings and user management

### Staff
- Can add new records
- Can view all records
- Cannot edit or delete
- No access to settings

## Excel Upload Format

Your Excel file should have these columns:
- Installation Date
- Monthly Commitment
- Rate
- State
- Location
- Customer Name (required)
- Billed (Yes/No)
- Challan Date
- Challan No
- Product columns (A3 White PVC Film, A4 White PVC Film, etc.)
- Delivery Thru
- Remark
- Material Sending Location

## Key Features Explained

### Name Normalization
The system automatically:
- Removes extra spaces
- Detects duplicates (e.g., "Company (sono 1)" and "Company (sono 2)")
- Uses fuzzy matching to find similar names
- Allows manual name mapping for typos

### 30-Day Defaulters
Automatically tracks customers who haven't had a challan in 30 days

### Contract Tracking
- Track contract start/end dates
- Automatic renewal reminders
- Price escalation tracking

### Dealer Commission
- Assign multiple dealers to one customer
- Track commission amounts per dealer
- Territory-based dealer management

## Directory Structure

```
├── api/                    # AJAX API endpoints
├── assets/                 # CSS, JS, images
├── classes/               # PHP classes (Models)
├── config/                # Configuration files
├── database/              # SQL setup script
├── includes/              # Header, footer, functions
├── pages/                 # Application pages
│   ├── customers/
│   ├── dealers/
│   ├── products/
│   ├── challans/
│   ├── contracts/
│   ├── uploads/
│   ├── reports/
│   └── settings/
├── uploads/               # Uploaded Excel files
├── vendor/                # Composer dependencies
├── index.php              # Login page
└── setup_database.php     # Database setup script
```

## Default Data

After setup, the system includes:
- 37 Indian states
- 4 product categories (PVC Films, Papers, Photo Papers, Blue Films)
- 17 products
- 2 user roles (Admin, Staff)
- 1 admin user (admin/123456)

## Troubleshooting

### Database Connection Error
- Make sure MySQL is running in XAMPP
- Check database credentials in `config/database.php`

### Excel Upload Not Working
- Check file permissions on `uploads/excel/` folder
- Make sure PHPSpreadsheet is installed
- Check file size (max 10MB)

### Charts Not Loading
- Make sure Chart.js CDN is accessible
- Check browser console for JavaScript errors

## Security Notes

1. **Change default admin password immediately**
2. In production:
   - Change database credentials
   - Set `error_reporting(0)` in config.php
   - Use HTTPS
   - Implement stronger password policies
3. Backup database regularly

## Support

For issues or questions, check:
1. PHP error logs: `C:\xampp\php\logs\php_error_log`
2. Apache error logs: `C:\xampp\apache\logs\error.log`

## Version

**Version:** 1.0.0
**Created:** December 2024

## License

Proprietary - For internal use only
