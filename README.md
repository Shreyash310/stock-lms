# StockVerse — Stock Market Learning Platform

A fully functional, production-ready PHP + MySQL learning management system for stock market education.

## 🚀 Quick Setup

### Prerequisites
- **XAMPP** (Apache + MySQL + PHP) installed and running

### Installation Steps

1. **Copy the project** to your XAMPP htdocs folder:
   ```
   Copy the entire `stock-lms` folder to C:\xampp\htdocs\
   ```

2. **Start XAMPP** — Start Apache and MySQL from XAMPP Control Panel

3. **Create the database** — Open phpMyAdmin (`http://localhost/phpmyadmin`):
   - Click "Import" tab
   - Select `database/setup.sql`
   - Click "Go" to execute

   OR run via MySQL CLI:
   ```bash
   mysql -u root < database/setup.sql
   ```

4. **Open in browser**: `http://localhost/stock-lms/`

### Login Credentials

| Role  | Email                    | Password   |
|-------|--------------------------|------------|
| Admin | admin@stockverse.com     | password   |
| User  | demo@stockverse.com      | password   |

> ⚠️ **Change passwords** after setup! Register a new admin from phpMyAdmin by updating the `role` field.

## 📁 Project Structure

```
stock-lms/
├── index.php                 # Landing page
├── config.php                # Database & app config
├── database/setup.sql        # Full DB schema + seed data
├── assets/
│   ├── css/                  # Stylesheets (3 files)
│   └── js/app.js             # Client-side logic
├── includes/
│   ├── db.php                # PDO database connection
│   ├── auth.php              # Authentication helpers
│   ├── header.php            # Reusable header/sidebar
│   └── footer.php            # Reusable footer
├── auth/
│   ├── login.php             # Login page
│   ├── register.php          # Registration page
│   └── logout.php            # Session destroy
├── user/
│   ├── dashboard.php         # User dashboard with stats
│   ├── modules.php           # Module & chapter listing
│   ├── chapter.php           # Chapter reading view
│   └── quiz.php              # Interactive quizzes
└── admin/
    ├── dashboard.php         # Admin overview
    ├── modules.php           # CRUD modules
    ├── chapters.php          # CRUD chapters
    ├── quizzes.php           # CRUD quiz questions
    └── users.php             # View user progress
```

## ✨ Features

- **Authentication**: Register, login, logout with session-based auth and password hashing
- **Learning Modules**: 5 pre-loaded modules with 14 chapters of real stock market content
- **Quiz Engine**: 28 MCQ questions with instant scoring and results storage
- **Progress Tracking**: Mark chapters complete, progress bars per module
- **User Dashboard**: Stats, continue learning, recent quiz results
- **Admin Panel**: Full CRUD for modules, chapters, quizzes + user progress view
- **Dark Mode**: Toggle with localStorage persistence
- **Search**: Filter modules and chapters instantly
- **Responsive**: Mobile-first design with sidebar navigation
- **Security**: PDO prepared statements, CSRF tokens, input sanitization

## ⚙️ Configuration

Edit `config.php` to update:
- Database credentials (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`)
- Base URL (`BASE_URL`) — change if hosted in a subdirectory

## 🔐 Security

- All database queries use PDO prepared statements
- Passwords hashed with `password_hash()` (bcrypt)
- CSRF token validation on all forms
- Output sanitized with `htmlspecialchars()`
- Route protection via session-based guards
