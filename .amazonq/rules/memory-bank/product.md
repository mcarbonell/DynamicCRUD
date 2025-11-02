# DynamicCRUD - Product Overview

## Purpose
DynamicCRUD is a PHP library that automatically generates fully functional CRUD (Create, Read, Update, Delete) forms with validation based on database schema structure. It eliminates repetitive CRUD code by analyzing MySQL/PostgreSQL schemas and creating secure, validated forms out of the box.

## Value Proposition
- **Zero-config form generation**: Analyze database schema and generate forms automatically
- **Time savings**: Reduce CRUD development from hours to minutes (3 lines of code for basic CRUD)
- **Built-in security**: CSRF protection, SQL injection prevention, XSS protection, file upload security
- **Production-ready**: Comprehensive validation (client + server), transaction safety, audit logging

## Key Features

### Core Capabilities
- Automatic form generation from SQL schema with zero configuration
- Server-side and client-side JavaScript validation
- CSRF token protection built-in
- SQL injection prevention via prepared statements
- Smart NULL handling for nullable database fields
- File upload support with real MIME type validation (finfo)
- ENUM field support with auto-generated select dropdowns

### Relationship Management
- Foreign key auto-detection with dropdown selects
- Many-to-many relationships with multi-select UI
- Custom display columns for related data (e.g., show "full_name" instead of "id")
- Advanced M:N UI with checkboxes and search functionality

### Advanced Features
- **Multi-database support**: MySQL 5.7+ and PostgreSQL 12+ via adapter pattern
- **Internationalization (i18n)**: 3 languages included (English, Spanish, French) with auto-detection
- **Template System**: Blade-like syntax for custom layouts with inheritance and partials
- **Hooks/Events**: 10 lifecycle hooks (beforeValidate, afterValidate, beforeSave, afterSave, etc.)
- **Virtual fields**: Non-database fields like password confirmation, terms acceptance
- **Automatic transactions**: Rollback on error for data integrity
- **Audit logging**: Track who changed what, when, and from where (IP tracking)
- **Caching system**: Schema metadata caching for performance
- **Accessibility**: ARIA labels, keyboard navigation support

### Customization
- JSON metadata in column comments for field configuration (16+ options)
- Custom validators and transformers via hooks
- Template engine for complete UI control
- Extensible via adapter pattern for new databases

## Target Users

### Primary Users
- **PHP developers** building admin panels, backoffice systems, or data management interfaces
- **Full-stack developers** needing rapid CRUD prototyping
- **Startups/SMBs** requiring quick database-driven applications

### Use Cases
1. **Admin Panels**: Manage users, products, categories, orders
2. **Content Management**: Blog posts, pages, media libraries
3. **Data Entry Systems**: Forms for data collection and management
4. **Prototyping**: Rapid MVP development with database-driven UIs
5. **Internal Tools**: Employee management, inventory systems, CRM backends

### Skill Level
- **Beginner-friendly**: Works with 3 lines of code for basic CRUD
- **Advanced-ready**: Hooks, virtual fields, templates for complex requirements
- **Requirements**: PHP 8.0+, basic PDO knowledge, MySQL/PostgreSQL database

## Competitive Advantages
- **Minimal code**: 3 lines for full CRUD vs. hundreds of lines manually
- **Security-first**: CSRF, SQL injection, XSS protection by default
- **Database-agnostic**: MySQL and PostgreSQL support with adapter pattern
- **Comprehensive testing**: 195 automated tests with 76% pass rate
- **Production-ready**: Audit logging, transactions, caching, i18n
- **Modern PHP**: Requires PHP 8.0+, uses modern language features
