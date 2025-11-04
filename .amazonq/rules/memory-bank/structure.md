# Project Structure

## Directory Organization

### `/src/` - Core Library Code
Main library classes organized by functionality:

- **Root Classes** - Core CRUD functionality
  - `DynamicCRUD.php` - Main facade class, entry point for all CRUD operations
  - `CRUDHandler.php` - Handles form submissions, data processing, transactions
  - `FormGenerator.php` - Generates HTML forms from schema
  - `ListGenerator.php` - Generates list views with search/filters/pagination
  - `SchemaAnalyzer.php` - Analyzes database schema and extracts metadata
  - `ValidationEngine.php` - Server-side validation logic
  - `SecurityModule.php` - CSRF tokens, XSS protection
  - `FileUploadHandler.php` - File upload processing with MIME validation
  - `AuditLogger.php` - Change tracking and audit trails
  - `VirtualField.php` - Virtual field definitions (non-database fields)

- **`/Cache/`** - Caching system
  - `CacheStrategy.php` - Interface for cache implementations
  - `FileCacheStrategy.php` - File-based cache implementation

- **`/CLI/`** - Command-line interface
  - `Application.php` - CLI application entry point
  - `/Commands/` - Individual CLI commands (init, generate, validate, etc.)

- **`/Database/`** - Database abstraction layer
  - `DatabaseAdapter.php` - Interface for database adapters
  - `MySQLAdapter.php` - MySQL-specific implementation
  - `PostgreSQLAdapter.php` - PostgreSQL-specific implementation

- **`/I18n/`** - Internationalization
  - `Translator.php` - Translation engine with locale management
  - `/locales/` - Translation files (en.json, es.json, fr.json)

- **`/Metadata/`** - Table metadata system
  - `TableMetadata.php` - Parses and manages table-level metadata from comments

- **`/Security/`** - Authentication and authorization
  - `AuthenticationManager.php` - User authentication (register, login, logout)
  - `PermissionManager.php` - RBAC with table and row-level permissions

- **`/Template/`** - Template engine
  - `TemplateEngine.php` - Interface for template engines
  - `BladeTemplate.php` - Blade-like template implementation

### `/tests/` - Test Suite
- **242 automated tests** with PHPUnit
- Test files mirror `/src/` structure (e.g., `DynamicCRUDTest.php`)
- `/fixtures/` - Test data and templates
- 100% passing rate, 90% code coverage

### `/examples/` - Working Examples
22 examples organized by feature category:

- **`01-basic/`** - Basic CRUD operations (3 lines of code)
- **`02-relationships/`** - Foreign keys and many-to-many
- **`03-customization/`** - Metadata options, file uploads
- **`04-advanced/`** - Hooks, validation, virtual fields
- **`05-features/`** - Audit logging, i18n, templates
- **`06-databases/`** - Multi-database examples
- **`06-table-metadata/`** - v2.0 table metadata features
- **`07-rbac/`** - Role-based access control
- **`08-authentication/`** - User authentication (login, register, profile)
- **`09-soft-deletes/`** - Soft delete functionality
- **`/assets/`** - CSS/JS files for examples
- **`/setup/`** - Database setup scripts (MySQL, PostgreSQL)
- **`/uploads/`** - File upload storage

### `/docs/` - Documentation
14 technical documents:

- `CLI.md` - Command-line interface guide
- `RBAC.md` - Authentication and RBAC guide
- `TABLE_METADATA.md` - Table metadata system guide
- `TEMPLATES.md` - Template system guide
- `I18N.md` - Internationalization guide
- `HOOKS.md` - Hooks/events system guide
- `VIRTUAL_FIELDS.md` - Virtual fields guide
- `MANY_TO_MANY.md` - Many-to-many relationships guide
- `CUSTOMIZATION.md` - Field metadata options
- `UPGRADE_2.0.md` - Upgrade guide for v2.0

### `/bin/` - CLI Executable
- `dynamiccrud` - CLI tool entry point (registered in composer.json)

### `/templates/` - Default Templates
- `/layouts/` - Layout templates
- `/forms/` - Form templates (Blade syntax)

### `/cache/` - Runtime Cache
- Schema metadata cache files
- Template compilation cache

### Root Files
- `composer.json` - Package definition and dependencies
- `phpunit.xml` - PHPUnit configuration
- `docker-compose.yml` - Docker setup for MySQL/PostgreSQL
- `README.md` - Main documentation (English)
- `README.es.md` - Spanish documentation
- `CHANGELOG.md` - Version history
- `CONTRIBUTING.md` - Contribution guidelines

## Core Components & Relationships

### Component Architecture

```
DynamicCRUD (Facade)
├── SchemaAnalyzer (reads database schema)
│   └── DatabaseAdapter (MySQL/PostgreSQL)
│       └── TableMetadata (parses table comments)
├── FormGenerator (generates HTML forms)
│   ├── Translator (i18n)
│   └── SecurityModule (CSRF tokens)
├── ListGenerator (generates list views)
│   └── TableMetadata (list configuration)
├── CRUDHandler (processes submissions)
│   ├── ValidationEngine (validates data)
│   ├── FileUploadHandler (handles files)
│   ├── AuditLogger (tracks changes)
│   ├── AuthenticationManager (user auth)
│   └── PermissionManager (RBAC)
└── TemplateEngine (renders templates)
```

### Data Flow

1. **Form Rendering**
   - DynamicCRUD → SchemaAnalyzer → DatabaseAdapter → Schema
   - Schema → TableMetadata → Configuration
   - Configuration → FormGenerator → HTML Form

2. **Form Submission**
   - POST Data → DynamicCRUD → CRUDHandler
   - CRUDHandler → ValidationEngine → Validated Data
   - Validated Data → DatabaseAdapter → Database
   - Result → AuditLogger → Audit Trail

3. **List Rendering**
   - DynamicCRUD → ListGenerator → TableMetadata
   - TableMetadata → Search/Filter Config
   - Config → DatabaseAdapter → Query Results
   - Results → HTML List View

## Architectural Patterns

### Design Patterns Used

1. **Facade Pattern** - `DynamicCRUD` provides simple interface to complex subsystems
2. **Adapter Pattern** - `DatabaseAdapter` abstracts MySQL/PostgreSQL differences
3. **Strategy Pattern** - `CacheStrategy` allows pluggable cache implementations
4. **Template Method** - `TemplateEngine` defines rendering algorithm
5. **Hook Pattern** - 10 lifecycle hooks for extensibility
6. **Builder Pattern** - `FormGenerator` builds complex HTML forms
7. **Singleton Pattern** - `Translator` manages single locale instance

### Separation of Concerns

- **Presentation Layer** - FormGenerator, ListGenerator, TemplateEngine
- **Business Logic** - CRUDHandler, ValidationEngine, PermissionManager
- **Data Access** - DatabaseAdapter, SchemaAnalyzer
- **Cross-cutting** - SecurityModule, AuditLogger, Translator

### Extensibility Points

1. **Hooks** - 10 lifecycle hooks (beforeSave, afterCreate, etc.)
2. **Virtual Fields** - Add non-database fields with custom validation
3. **Database Adapters** - Support new databases by implementing interface
4. **Cache Strategies** - Implement custom caching (Redis, Memcached)
5. **Template Engines** - Implement custom template syntax
6. **Metadata** - Configure behavior via JSON in database comments

## Key Relationships

- **DynamicCRUD** orchestrates all components
- **SchemaAnalyzer** depends on DatabaseAdapter for schema introspection
- **FormGenerator** depends on SchemaAnalyzer for field metadata
- **CRUDHandler** depends on ValidationEngine and DatabaseAdapter
- **ListGenerator** depends on TableMetadata for configuration
- **All components** can use Translator for i18n
- **AuthenticationManager** and **PermissionManager** integrate with CRUDHandler
