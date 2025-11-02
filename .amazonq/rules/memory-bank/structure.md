# DynamicCRUD - Project Structure

## Directory Organization

```
dynamicCRUD/
├── src/                    # Core library source code (PSR-4: DynamicCRUD\)
│   ├── Cache/             # Caching strategies for schema metadata
│   ├── Database/          # Database adapter pattern (MySQL, PostgreSQL)
│   ├── I18n/              # Internationalization (Translator + locales)
│   ├── Template/          # Blade-like template engine
│   └── *.php              # Core classes (DynamicCRUD, FormGenerator, etc.)
├── tests/                 # PHPUnit test suite (195 tests)
├── examples/              # 11 working demo files + SQL setup scripts
├── docs/                  # Technical documentation (8 guides)
├── templates/             # Default Blade templates (forms, layouts)
├── cache/                 # Template and schema cache storage
├── uploads/               # File upload destination
└── vendor/                # Composer dependencies
```

## Core Components

### 1. Entry Point
- **DynamicCRUD.php**: Main facade class, orchestrates all components
  - Initializes database adapter, schema analyzer, form generator, CRUD handler
  - Provides public API: `renderForm()`, `handleSubmission()`, `addHook()`, `addManyToMany()`
  - Manages lifecycle hooks and virtual fields

### 2. Database Layer (Adapter Pattern)
- **Database/DatabaseAdapter.php**: Interface for database operations
- **Database/MySQLAdapter.php**: MySQL-specific implementation
- **Database/PostgreSQLAdapter.php**: PostgreSQL-specific implementation
  - Auto-detects driver from PDO connection
  - Abstracts schema queries, foreign key detection, ENUM handling

### 3. Schema Analysis
- **SchemaAnalyzer.php**: Extracts database schema metadata
  - Reads table columns, types, constraints, foreign keys
  - Parses JSON metadata from column comments
  - Caches schema data for performance

### 4. Form Generation
- **FormGenerator.php**: Generates HTML forms from schema
  - Creates input fields based on column types and metadata
  - Handles foreign key dropdowns, ENUM selects, file inputs
  - Supports 16+ metadata options (type, label, placeholder, min, max, etc.)
  - Generates client-side JavaScript validation

### 5. CRUD Operations
- **CRUDHandler.php**: Handles Create, Read, Update, Delete operations
  - Executes INSERT/UPDATE/DELETE with prepared statements
  - Manages transactions with automatic rollback
  - Handles many-to-many relationship persistence
  - Integrates with hooks system

### 6. Validation
- **ValidationEngine.php**: Server-side validation
  - Validates required fields, data types, constraints
  - Checks foreign key references
  - Validates file uploads (MIME type, size)
  - Returns structured error messages

### 7. Security
- **SecurityModule.php**: Security features
  - CSRF token generation and validation
  - Input sanitization (XSS prevention)
  - Session management for tokens

### 8. File Handling
- **FileUploadHandler.php**: Secure file upload processing
  - Real MIME type validation using `finfo`
  - File size checks
  - Unique filename generation
  - Configurable upload directory

### 9. Audit System
- **AuditLogger.php**: Change tracking
  - Logs all CRUD operations (create, update, delete)
  - Tracks user ID, IP address, timestamp
  - Stores old/new values as JSON
  - Requires `audit_log` table

### 10. Internationalization
- **I18n/Translator.php**: Multi-language support
  - Auto-detects language from URL, session, or browser
  - Loads translations from JSON files (locales/)
  - Supports 3 languages: English, Spanish, French
  - Provides client-side and server-side translation

### 11. Template System
- **Template/TemplateEngine.php**: Interface for template engines
- **Template/BladeTemplate.php**: Blade-like template implementation
  - Supports @if, @foreach, @extends, @section, @yield, @include
  - Automatic escaping ({{ }} vs {!! !!})
  - File caching for performance
  - Layout inheritance and partials

### 12. Virtual Fields
- **VirtualField.php**: Non-database fields
  - Defines fields not stored in database (e.g., password_confirmation)
  - Custom validators and transformers
  - Used for UI-only fields (terms acceptance, captcha)

### 13. Caching
- **Cache/CacheStrategy.php**: Interface for caching strategies
- **Cache/FileCacheStrategy.php**: File-based cache implementation
  - Caches schema metadata to reduce database queries
  - Configurable TTL (time-to-live)

### 14. List Generation
- **ListGenerator.php**: Generates data tables/lists
  - Creates HTML tables from database records
  - Supports pagination, sorting, filtering
  - Integrates with CRUD operations (edit/delete links)

## Architectural Patterns

### Design Patterns Used
1. **Facade Pattern**: DynamicCRUD class provides simplified interface
2. **Adapter Pattern**: Database adapters for MySQL/PostgreSQL
3. **Strategy Pattern**: Cache strategies, template engines
4. **Observer Pattern**: Hooks/events system for lifecycle callbacks
5. **Builder Pattern**: FormGenerator builds complex HTML forms
6. **Template Method**: CRUDHandler defines CRUD workflow with hook points

### Data Flow
```
User Request → DynamicCRUD (facade)
  ↓
  ├→ SchemaAnalyzer → DatabaseAdapter → Database
  ├→ FormGenerator → HTML Form
  ├→ ValidationEngine → Validation Results
  ├→ CRUDHandler → DatabaseAdapter → Database
  ├→ SecurityModule → CSRF Validation
  ├→ FileUploadHandler → File System
  ├→ AuditLogger → audit_log table
  └→ Translator → Localized Strings
```

### Component Relationships
- **DynamicCRUD** orchestrates all components
- **SchemaAnalyzer** depends on **DatabaseAdapter**
- **FormGenerator** uses **SchemaAnalyzer** output and **Translator**
- **CRUDHandler** uses **ValidationEngine**, **SecurityModule**, **AuditLogger**
- **ValidationEngine** uses **SchemaAnalyzer** metadata
- All components can use **Cache** for performance

## Configuration Files
- **composer.json**: Package definition, dependencies, autoloading (PSR-4)
- **phpunit.xml**: Test suite configuration, coverage settings
- **docker-compose.yml**: MySQL and PostgreSQL containers for development
- **.github/workflows/**: CI/CD pipelines (tests, code quality, releases)

## Extension Points
1. **Database Adapters**: Implement `DatabaseAdapter` interface for new databases
2. **Cache Strategies**: Implement `CacheStrategy` interface for Redis, Memcached
3. **Template Engines**: Implement `TemplateEngine` interface for Twig, Smarty
4. **Hooks**: Register callbacks at 10 lifecycle points
5. **Virtual Fields**: Add custom non-database fields with validators
6. **Translations**: Add new languages via JSON files in `I18n/locales/`
