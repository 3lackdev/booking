# Booking System

A flexible resource booking system for meeting rooms, vehicles, equipment, and various services.

<p align="center">
  <img src="assets/Image/Screenshot 2568-04-25 at 13.02.46.png" alt="Booking System Screenshot" width="800">
</p>

## Key Features

- **User Authentication**: Secure registration and login system with role-based access control (user/admin)
- **Resource Management**: Add, edit, and manage various resource categories
- **Booking Management**: Book resources, manage reservations, and view booking history
- **Admin Dashboard**: Comprehensive dashboard for administrators to manage the entire system
- **Reports and Analytics**: Usage data summaries, booking trends, and most active users
- **System Settings**: Customize website name, contact email, and booking policies
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS
- **Modern Gradient Theme**: UI design with beautiful gradients and glass effects
- **Notification System**: Alert users when booking status changes

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML, CSS (Tailwind CSS), JavaScript
- **Dependencies**: 
  - Tailwind CSS (via CDN)
  - Flatpickr (for date/time picker)
  - Font Awesome (for icons)
  - Chart.js (for displaying graphs in reports)

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache or Nginx)
- Enabled mysqli extension in PHP

## Installation

### Method 1: Using Automatic Setup (Recommended)

1. Download all code to your server
2. Access the system via web browser (e.g., http://your-server/booking/)
3. The system will automatically open the setup.php page
4. Enter your database connection information and website settings
5. Click the "Install Database" button to install the system

### Method 2: Manual Installation

1. Clone or download all code to your server
```
git clone https://github.com/3lackdev/booking.git
```

2. Create a MySQL database and import the database structure
```
mysql -u your_username -p -e "CREATE DATABASE booking_system"
mysql -u your_username -p booking_system < database/schema.sql
```

3. Configure database connection in `config/database.php`
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'booking_system');
```

4. Configure your web server to point to the project's main directory
5. Access the system through your web browser

## Default Admin Account

After installation, you can log in with the default admin account:
- **Username**: admin
- **Password**: admin123

**Important: Change the admin password after first login for security reasons**

## Project Structure

```
booking-system/
├── admin/                 # Admin dashboard and management pages
│   ├── dashboard.php      # Main admin dashboard
│   ├── bookings.php       # Manage all bookings
│   ├── resources.php      # Manage resources
│   ├── users.php          # Manage users
│   ├── reports.php        # Reports and statistics
│   ├── settings.php       # System settings
│   └── view_booking.php   # View booking details
├── classes/               # PHP class files
│   ├── ResourceManager.php # Manage resources and categories
│   └── BookingManager.php  # Manage bookings
├── config/                # Configuration files
│   └── database.php       # Database connection settings
├── database/              # Database structure
│   └── schema.sql         # SQL script for creating the database
├── includes/              # Reusable PHP includes
│   ├── auth.php           # Authentication functions
│   ├── footer.php         # Page footer
│   ├── header.php         # Page header
│   └── utilities.php      # Various utility functions
├── setup.php              # Automatic system installation page
├── index.php              # Homepage
├── login.php              # Login page
├── register.php           # Registration page
├── profile.php            # User profile page
├── resources.php          # Resource listing page
├── view_resource.php      # View resource details and make bookings
├── my_bookings.php        # My bookings page
├── view_booking.php       # View booking details
└── README.md              # This file
```

## User Roles

1. **Regular Users**:
   - View and book available resources
   - Manage their own bookings
   - View booking history
   - Edit personal profile information

2. **Administrators**:
   - Manage resource categories and resources
   - Approve or reject booking requests
   - View all bookings in the system
   - Manage user accounts
   - Configure system settings
   - View reports and usage statistics

## Basic User Guide

### For Regular Users

1. **Booking Resources**:
   - Log in and go to the "Resources" page
   - Select a category or search for your desired resource
   - Click on a resource to view details and availability
   - Select your desired date and time, and fill in booking details
   - Click the "Book Now" button to confirm your booking

2. **Managing Bookings**:
   - Go to the "My Bookings" page to see all your bookings
   - Click on a booking to view additional details
   - You can cancel bookings with "Pending" or "Confirmed" status

3. **Editing Profile**:
   - Click on your username in the top right corner and select "Profile"
   - Update personal information or change your password as needed

### For Administrators

1. **Managing Users**:
   - Go to "Admin > Users"
   - Add, edit, or delete users as needed
   - Reset user passwords in case users forget their passwords

2. **Managing Resources**:
   - Go to "Admin > Resources" to manage resources
   - Add new resources by specifying category, name, details, location, and capacity
   - Edit or delete existing resources

3. **Managing Bookings**:
   - Go to "Admin > Bookings"
   - View and filter all bookings by status (pending, confirmed, canceled, completed)
   - Approve or reject pending bookings

4. **System Settings**:
   - Go to "Admin > Settings"
   - Customize website name, description, contact email
   - Set booking policies such as the number of days that can be booked in advance, maximum booking duration

5. **Viewing Reports**:
   - Go to "Admin > Reports"
   - View statistics about bookings, most booked resources, and most active users

## Customization

### Adding New Resource Categories

1. Log in with an admin account
2. Go to the admin dashboard
3. Click on "Resources"
4. Click on the "Categories" tab
5. Add a new category with description

### Adding New Resources

1. Log in with an admin account
2. Go to the admin dashboard
3. Click on "Resources"
4. Click the "Add New Resource" button
5. Add a new resource, selecting the appropriate category

### Customizing Theme and Style

The system uses Tailwind CSS via CDN, which makes customization easy:

1. Edit the `includes/header.php` file to customize the main theme
2. Adjust the Tailwind color configuration in the `<script>` tag with `tailwind.config`
3. Add or edit custom CSS in the `<style>` tag

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support or inquiries, please contact [pongsan_k@outlook.com](mailto:your-pongsan_k@outlook.com)

## Contributing

Contributions are welcome! Please submit a Pull Request or open an Issue to report bugs or suggest new features. 