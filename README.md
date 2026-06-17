# 💰 Finote

A simple and modern personal finance tracking web application built with PHP and MySQL.

Finote helps users manage their finances by tracking income, expenses, budgets, and savings goals in one place through a clean and responsive dashboard.

---

## ✨ Features

### 📊 Dashboard Overview

* Financial summary dashboard
* Total income and expenses tracking
* Current balance overview
* Recent transaction history
* Budget and savings insights

### 💸 Transaction Management

* Add income and expense transactions
* Edit and delete transactions
* Transaction details and history
* Category-based organization

### 🏦 Account Management

* Create multiple financial accounts
* Manage account balances
* Track money across different accounts

### 🗂 Category Management

* Custom income categories
* Custom expense categories
* Easy transaction classification

### 🎯 Budget Planning

* Set monthly budgets
* Monitor spending progress
* Compare actual expenses against planned budgets

### 💎 Savings Goals

* Create savings targets
* Track progress toward goals
* Add and manage savings transactions

### 👤 User System

* Secure authentication
* User profile management
* Profile photo uploads
* Password updates

### 🌙 Modern UI

* Responsive design
* Dark mode support
* Mobile-friendly interface
* Bootstrap-powered layout

---

## 🛠 Tech Stack

**Backend**

* PHP
* MySQL
* MySQLi

**Frontend**

* HTML5
* CSS3
* JavaScript
* Bootstrap 5

---

## 🚀 Installation

### 1. Clone the repository

```bash
git clone https://github.com/yourusername/finote.git
cd finote
```

### 2. Create a database

Create a MySQL database:

```sql
CREATE DATABASE web2;
```

### 3. Import database

Import your SQL schema/database file into MySQL.

If needed, run additional migrations:

```sql
database/migrations.sql
```

### 4. Database connection setup

Update `db.php`:

```php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "web2";
```

### 5. Run the website

Place the project inside your web server directory:

**XAMPP**

```text
htdocs/finote
```

Then open:

```text
http://localhost/finote
```

---

## 📸 Preview

Add screenshots here:

```md
![Dashboard](Screenshot%20(2).png)
```

---

## 🔒 Security Features

* Password hashing support
* Session-based authentication
* CSRF protection
* Input sanitization
* Authentication guards for protected pages
