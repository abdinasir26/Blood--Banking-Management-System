### 👥 Group D Members 

| ID       | Name                      
| -------- | ------------------------- |
| C1220572 | Ahmed Abduqadir Abdullahi |
| C1220587 | Abdulhaq Mohamed Abdulle  |
| C1220439 | Imran Hashi Abdullahi     | 
| C1221228 | Mohamed Abdi Warsame      | 

#  BBMS - Blood Banking Management System

> **A modern, responsive, and secure web application for managing blood donations, requests, and inventory.**

##  Project Overview

**BBMS** (Blood Banking Management System) is a complete solution designed to bridge the gap between blood donors and patients/hospitals. It provides a platform for:
- **Donors** to register, schedule donations, and track their donation history.
- **Patients/Hospitals** to request blood units during emergencies.
- **Administrators** to manage users, approve/reject requests, track inventory, and generate system reports.

Built with **PHP (PDO)**, **MySQL**, and **Bootstrap 5**, it works seamlessly on XAMPP/WAMP environments.

##  Key Features

###  Public Module (Guest Access)
- **Live Stock Availability**: Real-time ticker showing available units for all blood groups.
- **Activity Feed**: View recently fulfilled blood requests.
- **Secure Registration**: Form for new donors to sign up.
- **Authentication**: Secure Login and "Forgot Password" functionality.

###  Donor Module (Member Area)
- **Dashboard**: Personal statistics (Total Donations, Pending Requests).
- **Donate Blood**: Simple form to submit a donation intent (including medical notes).
- **Request Blood**: Emergency request form for blood units.
- **History Tracking**: View status of past donations and requests.
- **Profile Management**: Update contact details and profile picture.

###  Admin Module (Control Panel)
- **Dashboard**: High-level system overview (Total Users, Pending Actions, Alerts).
- **User Management**: 
  - View, Edit, Activate, or Ban users.
  - Create new Admin or Donor accounts.
- **Inventory Management**:
  - **Blood Groups**: Add/Edit/Delete blood types.
  - **Stock Control**: Manual adjustment of blood units in the store.
- **Workflow Approvals**:
  - **Donations**: Approve pending donations -> Automatically increases stock.
  - **Requests**: Approve pending requests -> Automatically checks & deducts stock.
- **Reports**: Generate printable summaries of system health and inventory.

##  Technology Stack

- **Backend**: PHP 7.4+ (PDO for database security)
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Icons**: FontAwesome 6
- **Server**: Apache (via XAMPP/WAMP)

##  Installation & Setup

### 1. Prerequisites
Ensure you have **XAMPP**, **WAMP**, or a similar PHP local server installed.

### 2. Project Placement
- Copy the `BBMS` folder.
- Paste it into your server's root directory:
  - **XAMPP**: `C:\xampp\htdocs\`
  - **WAMP**: `C:\wamp64\www\`

### 3. Database Configuration
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Create a new database named **`bbms`**.
3. Import the provided SQL file:
   - Go to `Import` tab.
   - Select file: `bbms/database/bbms.sql`.
   - Click **Go**.

### 4. Application Configuration
If you use a database password or different host, update `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Your MySQL Username
define('DB_PASS', '');          // Your MySQL Password
define('DB_NAME', 'bbms');
```

### 5. Launch
Open your browser and navigate to:
**[http://localhost/BBMS/](http://localhost/BBMS/)**


##  Default Credentials

Use the following credentials to access the Admin Panel:

| Role | Username | Password |
|------|----------|----------|
| **Admin** | `admin` | `Admin@123` |

> **Note**: You can create new Donor accounts via the Registration page.


##  Project Structure

```
BBMS/
├── admin/                 # Admin module pages
│   ├── dashboard.php
│   ├── users.php
│   ├── donations.php
│   └── ...
├── auth/                  # Authentication scripts
│   ├── login.php
│   ├── register.php
│   └── ...
├── donor/                 # Donor module pages
│   ├── dashboard.php
│   ├── donate_create.php
│   └── ...
├── assets/                # Static assets
│   ├── css/style.css      # Custom Red Theme
│   └── js/app.js
├── database/
│   └── bbms.sql           # Database Schema
├── includes/              # Reusable components
│   ├── db.php             # Database Connection
│   ├── functions.php      # Helper Functions
│   └── ...
├── uploads/               # User profile pictures
└── index.php              # Public Home Page
```


##  Security Features
- **Password Hashing**: Uses `password_hash()` (BCRYPT) for storing passwords.
- **Prepared Statements**: PDO used everywhere to prevent SQL Injection.
- **Session Management**: Secure session handling with timeout (auto-logout after 5 mins inactivity).
- **Access Control**: Role-based redirection (`requireAdmin()`, `requireLogin()`).
- **Input Sanitization**: All user inputs are cleaned before processing.


## Contributing

1. Fork the repository.
2. Create your feature branch.
3. Commit your changes.
4. Push to the branch.
5. Open a Pull Request.


© 2025 **BBMS Project**. All Rights Reserved.


