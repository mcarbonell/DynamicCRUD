# DynamicCRUD - Examples

Progressive examples demonstrating all features of DynamicCRUD library.

## 📚 Learning Path

### 01. Basic CRUD
Start here! Learn the fundamentals in 3 lines of code.
- `index.php` - Complete CRUD with zero configuration

### 02. Relationships
Master database relationships.
- `foreign-keys.php` - Automatic foreign key dropdowns
- `many-to-many.php` - M:N relationships with multi-select

### 03. Customization
Customize forms with metadata and file uploads.
- `metadata.php` - JSON metadata for field configuration
- `file-uploads.php` - Secure file upload handling

### 04. Advanced Features
Extend functionality with hooks and virtual fields.
- `hooks.php` - 10 lifecycle hooks for custom logic
- `virtual-fields.php` - Password confirmation, terms acceptance
- `validation.php` - Custom validation rules

### 05. Production Features
Enterprise-ready features.
- `i18n.php` - Multi-language support (EN, ES, FR)
- `templates.php` - Blade-like template system
- `audit.php` - Change tracking and audit logs

### 06. Multi-Database
Database-specific examples.
- `mysql.php` - MySQL-specific features
- `postgresql.php` - PostgreSQL-specific features

## 🚀 Quick Start

1. **Setup database:**
   ```bash
   mysql -u root -p < setup/mysql.sql
   # or
   psql -U postgres < setup/postgresql.sql
   ```

2. **Run any example:**
   ```bash
   php -S localhost:8000
   # Open http://localhost:8000/01-basic/
   ```

## 📋 Requirements

- PHP 8.0+
- MySQL 5.7+ or PostgreSQL 12+
- PDO extension

## 🔧 Database Configuration

All examples use these default credentials:
- **Host:** localhost
- **Database:** test
- **User:** root
- **Password:** rootpassword

Edit the connection in each file if your setup differs.
