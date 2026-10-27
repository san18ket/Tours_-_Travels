# Tours & Travels Management System

A web-based Tours & Travels Management System built with PHP and MySQL to manage tour bookings, customers, vehicles, drivers, employees, trip assignments, invoices, and travel operations.

## 🚀 Features

- **Dashboard:** View important business and travel management information.
- **Tour Management:** Create, update, and manage tour packages.
- **Booking Management:** Manage customer bookings and reservations.
- **Customer Management:** Maintain customer profiles and documents.
- **Fleet Management:** Manage vehicles and fleet information.
- **Driver Management:** Manage drivers and driver profiles.
- **Trip Dispatch:** Assign trips and manage trip operations.
- **GPS Tracking:** Support for driver location tracking and GPS logs.
- **Invoice Management:** Generate and manage invoices and payments.
- **Employee Management:** Manage employee information.
- **Role-Based Access Control:** Manage user roles and permissions.
- **Multilingual Support:** English, Hindi, and Marathi.
- **Mobile Driver App:** A React Native application for driver-related operations.
- **Security:** Includes authentication, CSRF protection, request validation, and rate-limiting components.

## 🛠️ Technologies Used

### Backend
- PHP
- MySQL
- Custom MVC-style application architecture

### Frontend
- HTML5
- CSS3
- JavaScript

### Mobile Application
- React Native
- JavaScript
- Android

### Development Tools
- Visual Studio Code
- XAMPP or another compatible PHP/MySQL environment
- Git and GitHub

## 📋 Requirements

Before installing the project, make sure you have:

- PHP with the extensions required by the application
- MySQL or a compatible database server
- Apache or another compatible web server
- Git
- Node.js and npm for mobile application development
- Android Studio for building and running the Android application

## ⚙️ Installation and Setup

### 1. Clone the Repository

```bash
git clone YOUR_GITHUB_REPOSITORY_URL
```

Replace `YOUR_GITHUB_REPOSITORY_URL` with your actual GitHub repository URL.

### 2. Move into the Project Directory

```bash
cd Tours_travels
```

### 3. Set Up the Database

1. Start Apache and MySQL using XAMPP or your preferred environment.
2. Create a new MySQL database.
3. Configure the database connection using the application's configuration files.
4. Run the application's setup process or database migrations as described in `DEPLOYMENT.md`.

**Important:** Never commit production database credentials or passwords to GitHub.

### 4. Configure the Application

Review the files in the `config/` directory.

Configure the database connection, application URL, security settings, and storage paths according to your local environment.

Use local configuration values and keep any sensitive credentials out of version control.

### 5. Start the Application

Configure your Apache virtual host or document root according to the project's deployment instructions.

The application entry point is:

```text
index.php
```

Open the configured local application URL in your browser.

For the exact server configuration and setup requirements, refer to `DEPLOYMENT.md`.

## 📱 Mobile Driver Application

The mobile application is located in:

```text
mobile-driver/
```

To install its JavaScript dependencies:

```bash
cd mobile-driver
npm install
```

Configure the API base URL in the mobile application's configuration files before running it.

You can then use the appropriate React Native and Android development tools to launch the application.

## 🗂️ Project Structure

```text
Tours_travels/
│
├── app/
│   ├── Controllers/     # Application controllers
│   ├── Core/            # Core framework components
│   ├── Languages/       # Language translations
│   ├── Migrations/      # Database migrations
│   ├── Models/          # Data models
│   ├── Services/        # Business logic and services
│   └── Views/           # Application pages and templates
│
├── config/              # Application configuration
├── mobile-driver/       # Mobile driver application
├── public/
│   └── assets/          # CSS, images, icons, and other assets
├── storage/             # Application storage
├── tests/               # Application tests
├── index.php            # Application entry point
├── DEPLOYMENT.md        # Deployment instructions
├── SECURITY.md          # Security documentation
├── .gitignore           # Files excluded from Git
└── README.md            # Project documentation
```

## 🧪 Testing

The project includes PHP-based test scripts in the `tests/` directory.

To run the test suite, configure the application and its required services first, then execute:

```bash
php tests/run_all.php
```

## 🔐 Security Notes

- Do not publish passwords, API keys, or other secrets.
- Do not upload real customer identity documents or private customer information.
- Do not commit database backups, session files, cookies, or application logs.
- Configure secure session handling and access permissions before deployment.
- Review `SECURITY.md` for additional security guidance.

## 📌 Future Improvements

- Enhanced booking and reservation workflows.
- Online payment gateway integration.
- Improved trip tracking and reporting.
- Additional mobile application features.
- Enhanced analytics and business reports.

## 👨‍💻 Author

**Your Name**

Replace this with your name or GitHub profile.

## 📄 License

Choose and add an appropriate open-source license before distributing this project. If no license is provided, others do not automatically receive permission to reuse or redistribute the code.
