# Booking System

A flexible booking system for various resources like meeting rooms, cars, and other services.

## Features

- **User Authentication**: Secure login and registration system with role-based access control
- **Resource Management**: Add, edit, and manage different categories of resources
- **Booking Management**: Book resources, manage bookings, and view booking history
- **Admin Dashboard**: Comprehensive dashboard for administrators to manage the entire system
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS

## Technology Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS (Tailwind CSS), JavaScript
- **Dependencies**: 
  - Tailwind CSS (via CDN)
  - Flatpickr (for date/time picker)
  - Font Awesome (for icons)

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache or Nginx)

## Installation

1. Clone the repository
```
git clone https://github.com/yourusername/booking-system.git
```

2. Create a MySQL database and import the database schema
```
mysql -u your_username -p your_database < database/schema.sql
```

3. Configure database connection in `config/database.php`
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
```

4. Configure your web server to point to the project root directory
5. Access the system through your web browser

## Default Admin Account

After installation, you can log in with the default admin account:
- **Username**: admin
- **Password**: admin123

*Important: Change the admin password after first login for security reasons.*

## Project Structure

```
booking-system/
├── admin/                 # Admin dashboard and management pages
├── assets/                # CSS, JavaScript, and other static assets
├── classes/               # PHP class files
│   ├── ResourceManager.php
│   └── BookingManager.php
├── config/                # Configuration files
│   └── database.php
├── database/              # Database schema
│   └── schema.sql
├── includes/              # Reusable PHP includes
│   ├── auth.php
│   ├── footer.php
│   ├── header.php
│   └── utilities.php
├── vendor/                # Dependencies (if using Composer)
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Registration page
├── resources.php          # Resource listing page
└── README.md              # This file
```

## User Roles

1. **Regular Users**:
   - View and book available resources
   - Manage their own bookings
   - View booking history

2. **Administrators**:
   - Manage resource categories and resources
   - Approve or reject booking requests
   - View all bookings in the system
   - Manage user accounts
   - Configure system settings

## Customization

### Adding New Resource Categories

1. Log in as an admin
2. Go to the Admin Dashboard
3. Click on "Manage Categories"
4. Add a new category with description

### Adding New Resources

1. Log in as an admin
2. Go to the Admin Dashboard
3. Click on "Manage Resources"
4. Add a new resource, selecting the appropriate category

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support or inquiries, please contact [your-email@example.com](mailto:your-email@example.com).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. 