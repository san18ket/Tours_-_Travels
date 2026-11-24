# Tours & Travels Management System

A web-based Tours & Travels Management System built with PHP and MySQL
for managing bookings, customers, vehicles, drivers, employees, trip
assignments, invoices, payments, travel operations, and related business
workflows.

## Features

-   Dashboard for business and travel management information
-   Tour and package management
-   Booking and reservation management
-   Customer profiles and documents
-   Vehicle and fleet management
-   Driver management and driver profiles
-   Trip dispatch and trip assignments
-   GPS tracking and driver location support
-   Invoice and payment management
-   Employee management
-   Role-based access control
-   English, Hindi, and Marathi language support
-   Mobile driver application
-   Authentication, CSRF protection, request validation, and
    rate-limiting components
-   PWA/installable web-app support

## Technology Stack

### Web Application

-   PHP
-   MySQL
-   Custom MVC-style application architecture
-   HTML5
-   CSS3
-   JavaScript

### Mobile Driver Application

-   React Native
-   JavaScript
-   Android

### Development Tools

-   Visual Studio Code
-   XAMPP or another compatible PHP/MySQL environment
-   Git and GitHub
-   Node.js and npm
-   Android Studio for Android development

## Requirements

Before running the project, make sure the environment provides:

-   PHP with the extensions required by the application
-   MySQL or a compatible database server
-   Apache or another compatible web server
-   Composer
-   Git
-   Node.js and npm for mobile-driver development
-   Android Studio when building/running the Android application

## Project Structure

``` text
Tours & Travels/
│
├── admin/
│   ├── app/
│   │   ├── Controllers/
│   │   ├── Core/
│   │   ├── Languages/
│   │   ├── Migrations/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Views/
│   │
│   ├── config/
│   ├── mobile-driver/
│   ├── public/
│   │   └── assets/
│   ├── storage/
│   ├── index.php
│   ├── DEPLOYMENT.md
│   ├── SECURITY.md
│   ├── .env.example
│   ├── composer.json
│   └── README.md
│
└── index.html
```

## Installation

### 1. Clone the Repository

``` bash
git clone YOUR_GITHUB_REPOSITORY_URL
```

Replace `YOUR_GITHUB_REPOSITORY_URL` with the actual repository URL.

### 2. Open the Application Directory

``` bash
cd Tours_Travels/admin
```

### 3. Install PHP Dependencies

``` bash
composer install
```

The current application declares PHPMailer as a Composer dependency.

### 4. Configure Environment

Copy the environment template:

``` bash
cp .env.example .env
```

On Windows, copy `.env.example` to `.env` using File Explorer or:

``` powershell
Copy-Item .env.example .env
```

Configure the values for your environment.

Example:

``` env
APP_NAME="Tours & Travels"
APP_ENV=development
APP_DEBUG=true

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tours_travels_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Tours & Travels"
MAIL_ADMIN_ADDRESS=
```

For production, use the database and mail credentials supplied by the
hosting provider.

**Never commit `.env` or production credentials to Git.**

### 5. Configure Apache

Configure the web server so the application's public entry point is
served correctly.

The application entry point is:

``` text
admin/index.php
```

Use the deployment documentation in `DEPLOYMENT.md` for server-specific
configuration.

### 6. Database Setup

Start MySQL and make sure the configured database is available.

The application contains database migration/initialization
functionality. Follow the project's deployment documentation for the
appropriate production setup and migration process.

Do not overwrite an existing production database without a verified
backup and migration plan.

## Mobile Driver Application

The mobile application is located at:

``` text
admin/mobile-driver/
```

Install its dependencies:

``` bash
cd admin/mobile-driver
npm install
```

Configure the API base URL using the mobile application's existing
configuration.

Use React Native and Android Studio tooling to run or build the Android
application.

## Environment Configuration

The `.env.example` file provides the configuration template.

Important configuration groups include:

### Application

``` env
APP_NAME="Tours & Travels"
APP_ENV=development
APP_DEBUG=true
```

For production, use the appropriate production values and normally
disable debug output.

### Database

``` env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Use the database name, username, password, and host provided by the
production hosting environment.

### Mail

``` env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Tours & Travels"
MAIL_ADMIN_ADDRESS=
```

For Gmail SMTP, use an appropriate Gmail App Password rather than
exposing a normal account password.

## Localization

The application includes multilingual support for:

-   English
-   Hindi
-   Marathi

Translation resources are located within:

``` text
admin/app/Languages/
```

The existing localization system should be used for new translated UI
strings rather than creating a second translation system.

## Company Media Storage

Company branding assets should use the application's canonical storage
structure.

Recommended structure:

``` text
public/
└── storage/
    └── company/
        ├── logo/
        │   └── company-logo.<ext>
        │
        ├── stamps/
        │   └── ...
        │
        └── pwa/
            └── app-icon.<ext>
```

The Company Logo and App/PWA Logo are independent assets.

The App/PWA Logo should use a single persistent source image:

``` text
company/pwa/app-icon.<ext>
```

PWA delivery should derive required icon representations from this
source without unnecessarily accumulating duplicate uploaded App Logo
files.

## Security

Do not commit or publish:

-   Database passwords
-   SMTP passwords or App Passwords
-   API keys
-   Session data
-   Customer private information
-   Identity documents
-   Production logs
-   Database backups
-   Other secrets

Use environment configuration for sensitive credentials.

Review:

``` text
SECURITY.md
```

before production deployment.

## Production Deployment

Before deploying to a live server:

1.  Create/configure the production database through the hosting
    environment when required.
2.  Configure the production `.env`.
3.  Set the correct application environment.
4.  Disable debug output in production.
5.  Install Composer dependencies.
6.  Configure Apache/web-server routing.
7.  Run the required database migrations.
8.  Verify storage permissions.
9.  Verify mail configuration.
10. Test authentication and permissions.
11. Test bookings, trips, payments, invoices, and related workflows.
12. Test the PWA/install/update flow.
13. Verify that sensitive files are not publicly exposed.
14. Create a backup before major database changes.

Refer to:

``` text
DEPLOYMENT.md
```

for project-specific deployment instructions.

## Development Guidelines

When modifying the application:

-   Preserve existing business logic unless the change specifically
    requires it.
-   Prefer the existing MVC, service, model, and helper architecture.
-   Reuse existing localization, notification, storage, and
    authentication systems.
-   Avoid duplicating existing functionality.
-   Do not hard-code production credentials.
-   Keep user-facing messages short and understandable.
-   Keep technical diagnostics in logs rather than exposing them in
    normal UI.
-   Test both desktop and mobile layouts for UI changes.
-   Test existing permissions and business rules after functional
    changes.

## Git and Repository Safety

Do not commit:

``` text
.env
```

or other files containing secrets.

Before pushing changes, review the changed files and make sure:

-   No credentials are included.
-   No customer private data is included.
-   No production database dump is included.
-   No unnecessary generated files are committed.
-   No unrelated source files were modified.

## Useful Commands

Install PHP dependencies:

``` bash
composer install
```

Update PHP dependencies when intentionally required:

``` bash
composer update
```

Install mobile dependencies:

``` bash
cd mobile-driver
npm install
```

## Documentation

Project documentation includes:

-   `README.md` --- project overview and setup
-   `DEPLOYMENT.md` --- deployment guidance
-   `SECURITY.md` --- security guidance
-   `mobile-driver/README.md` --- mobile driver application
    documentation

## License

No specific open-source license is declared in the existing project
documentation.

If this project will be distributed publicly, add an appropriate license
before redistribution.

## Author

Project author/maintainer information should be added here before public
distribution.
