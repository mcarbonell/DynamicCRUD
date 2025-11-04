# Product Overview

## Purpose
DynamicCRUD is a powerful PHP library that automatically generates CRUD (Create, Read, Update, Delete) forms with validation based on database structure. It eliminates repetitive CRUD code by analyzing MySQL/PostgreSQL schemas and creating fully functional forms with validation, security, and advanced features out of the box.

## Value Proposition
- **Zero-config form generation** - Analyzes database schema and generates forms automatically
- **Metadata-driven configuration** - Configure everything via JSON in database table/column comments
- **Enterprise-ready security** - Built-in CSRF protection, SQL injection prevention, XSS protection
- **Production-ready features** - Authentication, RBAC, soft deletes, audit logging, i18n, templates
- **Developer-friendly** - 3 lines of code for basic CRUD, extensive hooks for customization

## Key Features

### Core CRUD
- Automatic form generation from SQL schema
- Full CRUD operations (Create, Read, Update, Delete)
- Smart NULL handling for nullable fields
- ENUM field support with auto-generated selects
- File uploads with MIME type validation
- Automatic validation (server + client-side JavaScript)

### Relationships
- Foreign keys auto-detection with dropdown selects
- Many-to-many relationships with multi-select UI
- Custom display columns for related data
- Automatic JOIN queries for related data

### Security & Authentication (v2.1)
- **Authentication System** - Register, login, logout with rate limiting
- **RBAC** - Role-based access control with row-level security
- **Soft Deletes** - Mark records as deleted, restore or permanently delete
- CSRF protection built-in
- SQL injection prevention with prepared statements
- XSS protection with automatic escaping
- Password hashing (bcrypt)
- Session management with remember me

### Table Metadata System (v2.0)
- **UI/UX Customization** - Display names, icons, colors, list views
- **Dynamic Forms** - Tabbed layouts, fieldsets, organized field groups
- **Automatic Behaviors** - Auto-timestamps, auto-slug generation
- **Search & Filters** - Full-text search, dropdown filters, date ranges

### Advanced Features
- **CLI Tool** - Command-line interface for project management
- **Multi-database support** - MySQL 5.7+, PostgreSQL 12+
- **Internationalization (i18n)** - 3 languages (EN, ES, FR)
- **Template System** - Blade-like syntax for custom layouts
- **Hooks/Events system** - 10 lifecycle hooks for custom logic
- **Virtual fields** - Password confirmation, terms acceptance
- **Automatic transactions** - Rollback on error
- **Audit logging** - Change tracking with user/IP/timestamp
- **Caching system** - Schema metadata caching for performance
- **Accessibility** - ARIA labels, keyboard navigation

## Target Users

### Primary Users
- **PHP Developers** building web applications with database-driven forms
- **Full-stack Developers** needing rapid CRUD interface generation
- **Backend Developers** creating admin panels and data management interfaces

### Use Cases
1. **Admin Panels** - Quickly build admin interfaces for managing database records
2. **Data Entry Forms** - Generate forms for data collection and management
3. **Content Management** - Create CMS-like interfaces for content editing
4. **User Management** - Build user registration, login, and profile management
5. **Rapid Prototyping** - Quickly prototype database-driven applications
6. **Internal Tools** - Create internal tools for data management and reporting

### Ideal For
- Projects requiring rapid development of CRUD interfaces
- Applications with complex database relationships
- Multi-language applications (i18n support)
- Projects requiring role-based access control
- Applications needing audit trails and change tracking
- Teams wanting to reduce boilerplate code

## Project Statistics
- **27 PHP classes** (~9,000 lines of code)
- **22 working examples** across 9 categories
- **14 technical documents**
- **242 automated tests** (100% passing, 90% coverage)
- **3 languages supported** (English, Spanish, French)
- **2 databases supported** (MySQL, PostgreSQL)
- **10 lifecycle hooks** for customization
- **16+ metadata options** for field customization
