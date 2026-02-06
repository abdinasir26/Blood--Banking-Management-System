# BBMS Project - Task Distribution for 5 Team Members

## 📋 Project Division Overview
This document divides the Blood Banking Management System (BBMS) project into 5 main tasks, each with detailed subtasks. Each task is designed to be handled by one team member, ensuring clear responsibilities and minimal conflicts.

---

## 👤 **Task 1: Database & Core Backend Setup**
**Assigned To:** Person 1  
**Focus:** Database architecture, configuration, and core backend utilities

### Subtasks:
- [ ] 1.1 Database Setup & Configuration
  - Review and optimize `database/bbms.sql` schema
  - Ensure all foreign key relationships are properly defined
  - Create database indexes for performance optimization
  - Document database structure and relationships

- [ ] 1.2 Database Connection Management
  - Review `includes/config.php` for database credentials
  - Test and validate `includes/db.php` PDO connection
  - Implement connection error handling
  - Add database connection pooling if needed

- [ ] 1.3 Core Utility Functions
  - Review and test `includes/functions.php`
  - Verify session management and timeout functionality
  - Test security functions (password hashing, input sanitization)
  - Implement additional helper functions if needed

- [ ] 1.4 Database Maintenance Scripts
  - Review `setup_db.php` - initial database setup
  - Review `upgrade_db.php` - database upgrade script
  - Review `reset_admin.php` - admin account reset
  - Test `debug_blood_store.php` and `fix_blood_store.php`
  - Document all maintenance scripts usage

- [ ] 1.5 Data Seeding & Testing
  - Seed blood groups table with default data
  - Seed blood_store table with initial inventory
  - Create sample donor accounts for testing
  - Create sample donations and requests for testing

---

## 👤 **Task 2: Authentication & User Management**
**Assigned To:** Person 2  
**Focus:** User authentication, registration, and profile management

### Subtasks:
- [ ] 2.1 User Registration System
  - Review and test `auth/register.php`
  - Validate form inputs (email, username, password strength)
  - Test duplicate username/email prevention
  - Implement email verification (if required)
  - Test profile picture upload functionality

- [ ] 2.2 Login & Authentication
  - Review and test `auth/login.php`
  - Test username/password validation
  - Verify session creation and role-based redirection
  - Test "Remember Me" functionality with `remember_tokens` table
  - Implement brute-force protection (if needed)

- [ ] 2.3 Password Management
  - Review and test `auth/forgot_password.php`
  - Implement password reset token generation
  - Test password reset email functionality
  - Validate new password strength requirements
  - Test logout functionality in `auth/logout.php`

- [ ] 2.4 User Profile Management (Donor)
  - Review and test `donor/profile.php`
  - Test profile information updates (name, phone, email)
  - Test blood group selection
  - Test profile picture upload and display
  - Test medical information updates

- [ ] 2.5 Session Security
  - Test session timeout functionality (20 minutes)
  - Review `session_expired.php` redirect page
  - Test role-based access control (admin vs donor)
  - Implement CSRF protection (if needed)
  - Test concurrent login handling

---

## 👤 **Task 3: Donor Module & Blood Donation Workflow**
**Assigned To:** Person 3  
**Focus:** Donor dashboard and blood donation/request management

### Subtasks:
- [ ] 3.1 Donor Dashboard
  - Review and test `donor/dashboard.php`
  - Display donor statistics (total donations, pending requests)
  - Show recent activity and notifications
  - Display upcoming appointments (if applicable)
  - Test responsive layout and UI elements

- [ ] 3.2 Blood Donation Creation
  - Review and test `donor/donate_create.php`
  - Test donation form submission (blood group, amount, disease notes)
  - Validate blood group selection
  - Test medical notes/disease information input
  - Verify donation status is set to "pending"
  - Test database insertion into `donations` table

- [ ] 3.3 Donation History & Tracking
  - Review and test `donor/donations.php`
  - Display list of all donations (pending, approved, rejected)
  - Filter by status (all, pending, approved, rejected)
  - Show donation details (date, amount, status)
  - Implement search functionality for donations

- [ ] 3.4 Donation Certificates
  - Review and test `donor/donation_certificate.php`
  - Generate printable donation certificate
  - Include donor details and donation information
  - Review `donor/request_certificate.php` functionality
  - Implement PDF generation (if required)

- [ ] 3.5 Donor Navigation & Layout
  - Review `donor/layout/` directory structure
  - Ensure consistent navigation across donor pages
  - Test sidebar/navbar visibility and functionality
  - Verify logout and profile access links

---

## 👤 **Task 4: Blood Request & Inventory Management**
**Assigned To:** Person 4  
**Focus:** Blood request system and inventory management

### Subtasks:
- [ ] 4.1 Blood Request Creation
  - Review and test `donor/request_create.php`
  - Test request form (blood group, amount, hospital name, urgency)
  - Validate urgency levels (normal, urgent)
  - Test database insertion into `requests` table
  - Verify request status is set to "pending"

- [ ] 4.2 Request History & Tracking
  - Review and test `donor/requests.php`
  - Display list of all requests (pending, approved, rejected)
  - Filter by status and urgency
  - Show request details (hospital, amount, date)
  - Implement search functionality

- [ ] 4.3 Request Certificate
  - Review and test `donor/request_certificate.php`
  - Generate printable request certificate/receipt
  - Include requester and hospital information
  - Show approval status and date

- [ ] 4.4 Blood Inventory Management (Admin)
  - Review and test `admin/blood_store.php` and `admin/views/blood_stock_view.php`
  - Display current inventory for all blood groups
  - Implement manual stock adjustment functionality
  - Test automatic stock updates on donation approval
  - Test automatic stock deduction on request approval
  - Add low stock alerts and notifications

- [ ] 4.5 Blood Group Management (Admin)
  - Review and test `admin/blood_groups.php` and `admin/views/blood_groups_view.php`
  - Display all blood groups
  - Implement add/edit/delete blood group functionality
  - Ensure cascade updates to related tables
  - Test blood group validation

---

## 👤 **Task 5: Admin Module & Reporting**
**Assigned To:** Person 5  
**Focus:** Admin dashboard, workflows, user management, and reports

### Subtasks:
- [ ] 5.1 Admin Dashboard
  - Review and test `admin/dashboard.php` and `admin/views/home_view.php`
  - Display high-level statistics (total users, pending donations, pending requests)
  - Show blood stock availability ticker
  - Display recent activity feed
  - Show system alerts (low stock, urgent requests)
  - Implement responsive dashboard layout

- [ ] 5.2 User Management (Admin)
  - Review and test `admin/users.php` and `admin/views/users_view.php`
  - Display all users (admin and donor)
  - Implement user search and filter functionality
  - Test user activation/deactivation
  - Implement user editing (role change, status update)
  - Test user deletion with cascade handling
  - Review debug scripts: `admin/debug_users.php`, `admin/debug_users_sidebar.php`

- [ ] 5.3 Donation Approval Workflow (Admin)
  - Review and test `admin/donations.php` and `admin/views/donations_view.php`
  - Display all donations (pending, approved, rejected)
  - Implement donation approval functionality
  - Test automatic blood stock increment on approval
  - Implement donation rejection with reason
  - Track approval timestamp and approver ID
  - Send notification to donor on approval/rejection

- [ ] 5.4 Request Approval Workflow (Admin)
  - Review and test `admin/requests.php` and `admin/views/requests_view.php`
  - Display all blood requests (pending, approved, rejected)
  - Implement request approval with stock validation
  - Test automatic stock deduction on approval
  - Implement rejection with reason (insufficient stock)
  - Track approval timestamp and approver ID
  - Send notification to requester on approval/rejection

- [ ] 5.5 Reports & Analytics
  - Review and test `admin/reports.php` and `admin/views/reports_view.php`
  - Generate donation summary report (by date range, blood group)
  - Generate request fulfillment report
  - Generate user activity report
  - Generate inventory status report
  - Implement printable report templates
  - Add export functionality (CSV/PDF if needed)

- [ ] 5.6 Advanced Search & Filtering
  - Review and test `admin/views/search_view.php`
  - Implement global search across users, donations, requests
  - Add advanced filtering options
  - Test search result accuracy

---

## 📦 **Common Tasks (All Team Members)**

### Frontend & UI Components (Shared)
- [ ] Review and test `includes/header.php` - page header
- [ ] Review and test `includes/navbar.php` - navigation bar
- [ ] Review and test `includes/sidebar.php` - sidebar navigation
- [ ] Review and test `includes/footer.php` - page footer
- [ ] Review `assets/css/` - custom styles
- [ ] Review `assets/js/` - JavaScript functionality
- [ ] Review `assets/images/` - image assets

### Public Pages (Shared)
- [ ] Review and test `index.php` - landing page
- [ ] Test public blood stock availability ticker
- [ ] Test activity feed on public page
- [ ] Verify registration link accessibility

### Testing & Quality Assurance (All)
- [ ] Test responsive design on mobile/tablet/desktop
- [ ] Test browser compatibility (Chrome, Firefox, Edge, Safari)
- [ ] Verify all forms have proper validation
- [ ] Test SQL injection prevention with PDO
- [ ] Test XSS prevention with input sanitization
- [ ] Check for broken links and navigation
- [ ] Verify error handling and user feedback messages

### Documentation (All)
- [ ] Document your assigned module's API endpoints
- [ ] Add inline code comments for complex logic
- [ ] Update README.md with any new features
- [ ] Create troubleshooting guide for common issues

---

## 🔗 **Task Dependencies**

**Critical Path:**
1. **Task 1** must be completed first (Database & Backend Setup)
2. **Task 2** depends on Task 1 (Authentication needs database)
3. **Tasks 3 & 4** can run in parallel after Tasks 1 & 2
4. **Task 5** should start after Tasks 1 & 2, can run parallel with 3 & 4

**Integration Points:**
- Task 2 (User Management) integrates with Task 5 (Admin User Management)
- Task 3 (Donations) integrates with Task 5 (Donation Approvals)
- Task 4 (Requests) integrates with Task 5 (Request Approvals)
- Task 4 (Inventory) integrates with Tasks 3 & 5 (Stock Updates)

---

## 📌 **Important Notes**

### Security Considerations
- All team members must use PDO prepared statements (already implemented)
- Never expose raw SQL in error messages
- All user inputs must be sanitized using `cleanInput()`
- Passwords must be hashed using `password_hash()` with BCRYPT
- Session timeout is set to 20 minutes (1200 seconds)

### Testing Environment
- Use XAMPP/WAMP local server
- Database name: `bbms`
- Default admin credentials: `admin` / `1234`
- Test with different user roles (admin and donor)

### Code Standards
- Follow existing PHP code style
- Use meaningful variable and function names
- Add comments for complex business logic
- Keep functions small and focused
- Use Bootstrap 5 classes for UI consistency

### Communication
- Report any bugs or blockers immediately
- Coordinate integration points with dependent tasks
- Share test accounts and sample data
- Document any database schema changes

---

## ✅ **Definition of Done**

Each task is considered complete when:
- [ ] All subtasks are tested and working
- [ ] Code is properly commented and documented
- [ ] Integration with other modules is verified
- [ ] Responsive design is validated
- [ ] Security best practices are followed
- [ ] No console errors or warnings
- [ ] User feedback messages are clear and helpful
- [ ] Task is peer-reviewed by another team member

---

**Last Updated:** February 6, 2026  
**Project:** Blood Banking Management System (BBMS)  
**Total Tasks:** 5  
**Total Subtasks:** 68+
