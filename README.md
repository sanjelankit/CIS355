# CS355 Final Project - Issue Tracker Web App

## 👤 Test Users

### Admin:
- **Email**: `admin@example.com`
- **Password**: `admin123`

### Regular User:
- **Email**: `user@example.com`
- **Password**: `user123`


This is a PHP and MySQL-based Issue Tracker web application developed as part of the CS355 final project at SVSU.

## 🔧 Features

### ✅ Authentication
- Secure login/logout system
- Role-based access (Admin and Non-Admin users)

### ✅ Admin Capabilities
- Manage users (Create, Read, Update, Delete)
- Upload and view issue-related PDFs
- Delete any comment or issue
- Access all issues regardless of creator

### ✅ Users Capabilities
- Create, view, update, and delete their own issues
- Add and manage their own comments on issues
- View all issues (Open by default, with “All” option)

### ✅ Functional Modules
- **Persons Module**: Full CRUD (admin-only)
- **Issues Module**: Full CRUD with optional PDF upload
- **Comments Module**: Full CRUD tied to issues
- **PDF Uploads**: Admins can upload PDF attachments
- **Security**: Frontend and backend checks to restrict unauthorized actions

## 💡 Extra Credit Implemented
- ✅ Person’s name shown in issue list
- ✅ Sortable issue list by multiple columns
- ✅ Pagination: view 10 issues per page
- ✅ PDF saved to DB and downloadable

## 🛡️ Security Features
- Update/delete buttons only visible to creators or admins
- Server-side permission validation for all critical operations
- Session-based access control for every page

## 🖥️ Tech Stack
- PHP (Vanilla)
- MySQL (via PDO)
- HTML/CSS (vanilla + responsive design)
- XAMPP (local testing)

## 📂 Database Schema Overview
- `iss_persons`: user data, hashed passwords, role (admin/regular)
- `iss_issues`: issues reported by users
- `iss_comments`: comments linked to issues and users

## 🚀 Running Locally
1. Start XAMPP and run Apache & MySQL
2. Clone the repo or download it
3. Import the provided SQL file into phpMyAdmin
4. Set up your database connection in `database/database.php`
5. Open `login.php` in your browser to get started

> Note: Passwords are hashed using `md5 + salt` or `bcrypt` depending on final version.

## 📁 File Structure

