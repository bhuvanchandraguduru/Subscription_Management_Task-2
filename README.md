# Subscription_Management_Task-2


Subscription Manager

A full-stack web-based project built with PHP, MySQL, HTML, CSS, and
JavaScript.
It helps users manage their subscriptions efficiently and provides
role-based access, reminders, currency conversion, and a modern
responsive dashboard.

------------------------------------------------------------------------

Features

1.  Authentication (Login & Register)
    -   Session-based authentication.
    -   Secure password hashing.
    -   Role-Based Access Control (RBAC).
2.  Role-Based Access
    -   Admin: View all registered users and their subscriptions.
    -   User: Can only manage their own subscriptions.
3.  Subscription Management
    -   Add, Edit, Delete, and Search subscriptions.
    -   Example: Netflix, Spotify, Amazon Prime, etc.
4.  Search & Popup Display
    -   Users can search subscriptions.
    -   Results displayed in a clean webpage or popup window.
5.  Reminders
    -   reminders.php (cron-ready script).
    -   Sends email reminders for upcoming subscription renewals.
    -   Built using PHPMailer with sample email templates included.
6.  Currency Conversion
    -   Integrated with exchangerate.host.
    -   Allows users to check live conversion rates.
    -   Dashboard shows subscription prices converted into a preferred
        currency.
    -   Dropdown to choose target currency dynamically.
7.  Dark/Light Theme Toggle
    -   Simple toggle button.
    -   Theme preference saved in localStorage.
8.  Responsive Design
    -   Mobile-first design.
    -   Works smoothly across devices.

------------------------------------------------------------------------

Project Structure

/subscriptions_manager │── db.php # Database connection (PDO) │──
login.php # User login │── register.php # User registration │──
logout.php # Logout │── dashboard.php # Main user dashboard │──
admin_dashboard.php # Admin dashboard │── add_subscription.php # Add
subscription │── edit_subscription.php# Edit subscription │──
delete_subscription.php # Delete subscription │── search.php # Search
subscriptions │── reminders.php # Cron job for reminders │──
currency_converter.php # Currency conversion logic │── assets/ │ ├──
css/ # Stylesheets │ ├── js/ # Scripts (theme toggle, AJAX calls) │ └──
images/ # UI assets └── README.txt # Documentation

------------------------------------------------------------------------

Installation & Setup

1.  Clone or Extract Project git clone
    https://github.com/your-repo/subscriptions_manager.git

2.  Setup Database

    -   Import subscriptions_manager.sql into MySQL.
    -   Update db.php with your credentials.

    Example (for MAMP): $host = ‘localhost’; $db =
    ‘subscription_manager_1’; $user = ‘root’; $pass = ‘root’;

3.  Run Locally

    -   Place project inside your local server root (MAMP/XAMPP:
        htdocs/).
    -   Start Apache & MySQL.
    -   Open in browser: http://localhost/subscriptions_manager

4.  Configure Cron (for Reminders)

    -   Add to crontab:

    -   -   -   -   -   php /path/to/reminders.php

------------------------------------------------------------------------

Dependencies

-   PHP 8+
-   MySQL
-   PHPMailer (for sending emails)
-   cURL (for currency conversion API requests)

------------------------------------------------------------------------

Screenshots

-   Login Page
-   Dashboard (User & Admin)
-   Add/Edit Subscription
-   Currency Converter
-   Dark/Light Mode

(Add screenshots here once project is running)

------------------------------------------------------------------------

Future Enhancements

-   Subscription analytics & charts.
-   Export to PDF/CSV.
-   Multi-language support.

------------------------------------------------------------------------

License

This project is for learning and personal use.
